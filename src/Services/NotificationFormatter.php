<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Normalize outbound payloads for channel delivery.
 *
 * @since 1.0.0
 */
final class NotificationFormatter {
    /**
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function format(array $payload): array {
        $context = $this->normalizeContext($payload['context'] ?? []);

        $source = sanitize_key((string) ($payload['source'] ?? ($context['source'] ?? '')));
        $type = sanitize_key((string) ($payload['type'] ?? ($context['type'] ?? '')));

        $title = wp_strip_all_tags((string) ($payload['title'] ?? ''));
        if ($title === '') {
            $title = $this->defaultTitle($source);
        }

        $summary = $this->firstNonEmptyString([
            $payload['summary'] ?? '',
            $payload['body'] ?? '',
            $payload['message'] ?? '',
        ]);
        $summary = wp_strip_all_tags($summary);
        $summary = $this->normalizeLegacySummary($summary, $source, $type, $context);

        $inferred = $this->inferSourceType($source, $type, $title, $summary);
        $source = $inferred['source'];
        $type = $inferred['type'];

        if ($summary === '') {
            $summary = $title;
        }

        $subject = wp_strip_all_tags((string) ($payload['subject'] ?? ''));
        if ($subject === '') {
            $subject = $title;
        }

        $link = $this->resolveLink($payload, $context, $source, $type);

        $source_human = wp_strip_all_tags((string) ($payload['source_human'] ?? ''));
        if ($source_human === '') {
            $source_human = $this->humanSource($source);
        }

        $type_human = wp_strip_all_tags((string) ($payload['type_human'] ?? ''));
        if ($type_human === '') {
            $type_human = $this->humanType($source, $type);
        }

        $cta_label = wp_strip_all_tags((string) ($payload['cta_label'] ?? ($context['cta_label'] ?? '')));
        if ($cta_label === '' && $link !== '') {
            $cta_label = $this->defaultCtaLabel($link, $source, $type);
        }

        $payload['title'] = $title;
        $payload['subject'] = $subject;
        $payload['summary'] = $summary;
        $payload['body'] = $summary;
        $payload['message'] = $summary;
        $payload['source'] = $source;
        $payload['type'] = $type;
        $payload['source_human'] = $source_human;
        $payload['type_human'] = $type_human;
        $payload['context'] = $context;
        $payload['link'] = $link;
        $payload['cta_label'] = $cta_label;

        return $payload;
    }

    /**
     * @param mixed $raw
     * @return array<string,mixed>
     */
    private function normalizeContext($raw): array {
        if (is_array($raw)) {
            return $raw;
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    /**
     * @param array<int,mixed> $values
     */
    private function firstNonEmptyString(array $values): string {
        foreach ($values as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $text = trim((string) $value);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $context
     */
    private function resolveLink(array $payload, array $context, string $source, string $type): string {
        $title_text = isset($payload['title']) ? (string) $payload['title'] : '';
        $message_text = $this->firstNonEmptyString([
            $payload['summary'] ?? '',
            $payload['body'] ?? '',
            $payload['message'] ?? '',
        ]);

        $candidates = [
            $payload['link'] ?? '',
            $payload['url'] ?? '',
            $payload['review_url'] ?? '',
            $payload['admin_link'] ?? '',
            $payload['edit_url'] ?? '',
            $context['admin_link'] ?? '',
            $context['link'] ?? '',
            $context['url'] ?? '',
            $context['review_url'] ?? '',
            $context['edit_url'] ?? '',
            $context['post_edit_url'] ?? '',
            $context['comment_edit_url'] ?? '',
            $context['order_edit_url'] ?? '',
            $context['user_edit_url'] ?? '',
        ];

        foreach ($candidates as $candidate) {
            if (!is_scalar($candidate)) {
                continue;
            }

            $url = esc_url_raw((string) $candidate);
            if ($url !== '') {
                return $url;
            }
        }

        if ($source === 'wordpress' && $type === 'post_status_changed') {
            $post_id = isset($context['post_id']) ? absint($context['post_id']) : 0;
            if ($post_id <= 0) {
                $post_id = $this->extractObjectId($title_text . ' ' . $message_text, 'post');
            }
            if ($post_id > 0) {
                $edit = get_edit_post_link($post_id, '');
                if (is_string($edit) && $edit !== '') {
                    return esc_url_raw($edit);
                }
            }
        }

        if ($source === 'wordpress' && $type === 'comment_posted') {
            $comment_id = isset($context['comment_id']) ? absint($context['comment_id']) : 0;
            if ($comment_id <= 0) {
                $comment_id = $this->extractObjectId($title_text . ' ' . $message_text, 'comment');
            }
            if ($comment_id > 0) {
                return esc_url_raw(admin_url('comment.php?action=editcomment&c=' . $comment_id));
            }
        }

        if ($source === 'wordpress' && $type === 'user_registered') {
            $user_id = isset($context['user_id']) ? absint($context['user_id']) : 0;
            if ($user_id <= 0) {
                $user_id = $this->extractObjectId($title_text . ' ' . $message_text, 'user');
            }
            if ($user_id > 0) {
                return esc_url_raw(admin_url('user-edit.php?user_id=' . $user_id));
            }
        }

        if ($source === 'woocommerce' && $type === 'order_created') {
            $order_id = isset($context['order_id']) ? absint($context['order_id']) : 0;
            if ($order_id <= 0) {
                $order_id = $this->extractObjectId($title_text . ' ' . $message_text, 'order');
            }
            if ($order_id > 0) {
                return esc_url_raw(admin_url('post.php?post=' . $order_id . '&action=edit'));
            }
        }

        if ($source === 'woocommerce' && $type === 'low_stock') {
            $product_id = isset($context['product_id']) ? absint($context['product_id']) : 0;
            if ($product_id <= 0) {
                $product_id = $this->extractObjectId($title_text . ' ' . $message_text, 'product');
            }
            if ($product_id > 0) {
                return esc_url_raw(admin_url('post.php?post=' . $product_id . '&action=edit'));
            }
        }

        if ($source === 'contactform7' && $type === 'form_submitted') {
            return esc_url_raw(admin_url('admin.php?page=wpcf7'));
        }

        if ($source === 'custom_hook' && $type === 'custom_hook_triggered') {
            return esc_url_raw(admin_url('admin.php?page=nh-hooks'));
        }

        if ($type === 'channel_test' || $source === 'test') {
            return esc_url_raw(admin_url('admin.php?page=nh-dashboard'));
        }

        return '';
    }

    private function defaultTitle(string $source): string {
        if ($source === '') {
            return __('Notification update', 'notification-hub');
        }

        return sprintf(
            __('%s notification', 'notification-hub'),
            $this->humanSource($source)
        );
    }

    private function defaultCtaLabel(string $link, string $source, string $type): string {
        $composite = $source . ':' . $type;
        $map = [
            'wordpress:post_status_changed' => __('Open Post', 'notification-hub'),
            'wordpress:comment_posted' => __('Open Comment', 'notification-hub'),
            'wordpress:user_registered' => __('Open User', 'notification-hub'),
            'woocommerce:order_created' => __('Open Order', 'notification-hub'),
            'woocommerce:low_stock' => __('Open Product', 'notification-hub'),
            'contactform7:form_submitted' => __('Open Forms', 'notification-hub'),
            'custom_hook:custom_hook_triggered' => __('Open Hooks', 'notification-hub'),
            'test:channel_test' => __('Open Notification Hub', 'notification-hub'),
            'hook_test:channel_test' => __('Open Hooks Page', 'notification-hub'),
        ];

        if (isset($map[$composite])) {
            return (string) $map[$composite];
        }

        if (strpos($link, '/wp-admin/') !== false) {
            return __('Open in WordPress', 'notification-hub');
        }

        return __('Open details', 'notification-hub');
    }

    private function humanSource(string $source): string {
        $map = [
            'wordpress' => __('WordPress', 'notification-hub'),
            'woocommerce' => __('WooCommerce', 'notification-hub'),
            'contactform7' => __('Contact Form 7', 'notification-hub'),
            'custom_hook' => __('Custom Hook', 'notification-hub'),
            'test' => __('System Test', 'notification-hub'),
            'hook_test' => __('Hook Test', 'notification-hub'),
        ];

        if (isset($map[$source])) {
            return (string) $map[$source];
        }

        if ($source === '') {
            return __('System', 'notification-hub');
        }

        return ucwords(str_replace(['-', '_'], ' ', $source));
    }

    private function humanType(string $source, string $type): string {
        $composite = $source . ':' . $type;
        $map = [
            'wordpress:post_status_changed' => __('Post Status Changed', 'notification-hub'),
            'wordpress:user_registered' => __('New User Registered', 'notification-hub'),
            'wordpress:comment_posted' => __('New Comment Posted', 'notification-hub'),
            'wordpress:email_sent' => __('Email Sent', 'notification-hub'),
            'woocommerce:order_created' => __('New Order Created', 'notification-hub'),
            'woocommerce:low_stock' => __('Low Stock Alert', 'notification-hub'),
            'contactform7:form_submitted' => __('Form Submitted', 'notification-hub'),
            'custom_hook:custom_hook_triggered' => __('Custom Hook Triggered', 'notification-hub'),
            'test:channel_test' => __('Channel Test', 'notification-hub'),
        ];

        if (isset($map[$composite])) {
            return (string) $map[$composite];
        }

        if ($type === '') {
            return __('General Notification', 'notification-hub');
        }

        return ucwords(str_replace(['-', '_'], ' ', $type));
    }

    /**
     * @param array<string,mixed> $context
     */
    private function normalizeLegacySummary(string $summary, string $source, string $type, array $context): string {
        $summary = trim($summary);

        if (strcasecmp($summary, 'A Contact Form 7 submission occurred.') === 0) {
            $preview = isset($context['submission_preview']) ? wp_strip_all_tags((string) $context['submission_preview']) : '';
            if ($preview !== '') {
                return sprintf(__('A Contact Form 7 submission was received. Preview: %s', 'notification-hub'), $preview);
            }
            return __('A Contact Form 7 submission was received.', 'notification-hub');
        }

        if (strcasecmp($summary, 'A new WooCommerce order was created.') === 0) {
            $order_number = isset($context['order_number']) ? wp_strip_all_tags((string) $context['order_number']) : '';
            if ($order_number !== '') {
                return sprintf(__('A new WooCommerce order #%s was created.', 'notification-hub'), $order_number);
            }
            return __('A new WooCommerce order was created.', 'notification-hub');
        }

        if (preg_match('/^Status:\s*([a-z0-9_-]+)\s+\?\?\?\s+([a-z0-9_-]+)$/i', $summary, $m) === 1) {
            $from = $this->humanizeStatusKey((string) $m[1]);
            $to = $this->humanizeStatusKey((string) $m[2]);
            return sprintf(__('Status changed from %1$s to %2$s.', 'notification-hub'), $from, $to);
        }

        if ($source === 'contactform7' && $type === 'form_submitted') {
            $preview = isset($context['submission_preview']) ? wp_strip_all_tags((string) $context['submission_preview']) : '';
            if ($preview !== '') {
                return sprintf(__('A Contact Form 7 submission was received. Preview: %s', 'notification-hub'), $preview);
            }

            if ($summary === '' || stripos($summary, 'contact form 7 submission') !== false) {
                return __('A Contact Form 7 submission was received.', 'notification-hub');
            }
        }

        if ($source === 'woocommerce' && $type === 'order_created' && stripos($summary, 'order was created') !== false) {
            $order_number = isset($context['order_number']) ? wp_strip_all_tags((string) $context['order_number']) : '';
            if ($order_number !== '') {
                return sprintf(__('A new WooCommerce order #%s was created.', 'notification-hub'), $order_number);
            }
        }

        return $summary;
    }

    private function humanizeStatusKey(string $status): string {
        $status = sanitize_key($status);
        $map = [
            'new' => __('New', 'notification-hub'),
            'inherit' => __('Revision', 'notification-hub'),
            'auto-draft' => __('Auto Draft', 'notification-hub'),
            'draft' => __('Draft', 'notification-hub'),
            'pending' => __('Pending Review', 'notification-hub'),
            'private' => __('Private', 'notification-hub'),
            'publish' => __('Published', 'notification-hub'),
            'future' => __('Scheduled', 'notification-hub'),
            'trash' => __('Trash', 'notification-hub'),
        ];

        if (isset($map[$status])) {
            return (string) $map[$status];
        }

        return ucwords(str_replace(['-', '_'], ' ', $status));
    }

    private function extractObjectId(string $text, string $label): int {
        $text = trim($text);
        if ($text === '') {
            return 0;
        }

        $pattern = '/\b' . preg_quote($label, '/') . '\s*#?\s*(\d+)\b/i';
        if (preg_match($pattern, $text, $m) === 1) {
            return absint($m[1]);
        }

        if (preg_match('/#(\d+)\b/', $text, $m) === 1) {
            return absint($m[1]);
        }

        return 0;
    }

    /**
     * @return array{source:string,type:string}
     */
    private function inferSourceType(string $source, string $type, string $title, string $summary): array {
        if ($source !== '' && $type !== '') {
            return ['source' => $source, 'type' => $type];
        }

        $haystack = strtolower(trim($title . ' ' . $summary));
        if ($haystack === '') {
            return ['source' => $source, 'type' => $type];
        }

        if (strpos($haystack, 'contact form 7') !== false || strpos($haystack, 'form submission') !== false) {
            return ['source' => 'contactform7', 'type' => 'form_submitted'];
        }

        if (strpos($haystack, 'woocommerce') !== false || strpos($haystack, 'order #') !== false || strpos($haystack, 'new order') !== false) {
            return ['source' => 'woocommerce', 'type' => 'order_created'];
        }

        if (strpos($haystack, 'status changed') !== false || strpos($haystack, 'status:') !== false) {
            return ['source' => 'wordpress', 'type' => 'post_status_changed'];
        }

        if (strpos($haystack, 'comment') !== false) {
            return ['source' => 'wordpress', 'type' => 'comment_posted'];
        }

        return ['source' => $source, 'type' => $type];
    }
}
