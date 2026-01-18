<?php
/**
 * NH_Template
 *
 * Minimal template renderer for Notification Hub channel outputs.
 *
 * @package Notification_Hub
 * @since 1.6.3
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Template {

    /**
     * Render a notification output for a channel.
     *
     * @since 1.6.3
     * @param string $channel Channel slug (telegram|slack|email).
     * @param array  $payload Normalized payload.
     * @return string
     */
    public static function render_notification(string $channel, array $payload): string {
        $channel = sanitize_key($channel);

        $file = NH_PLUGIN_DIR . 'templates/notifications/' . $channel . '.php';
        if (!file_exists($file)) {
            return self::fallback_text($payload);
        }

        // Normalize common keys.
        $p = self::normalize_payload($payload);

        ob_start();
        // Variables available to templates.
        $data = $p;
        include $file;
        $out = (string) ob_get_clean();

        $out = trim($out);
        return ($out !== '') ? $out : self::fallback_text($payload);
    }

    /**
     * Normalize payload (safe, minimal).
     *
     * @since 1.6.3
     * @param array $payload Raw payload.
     * @return array<string, mixed>
     */
    private static function normalize_payload(array $payload): array {
        $title   = isset($payload['title']) && is_string($payload['title']) ? $payload['title'] : '';
        $summary = isset($payload['summary']) && is_string($payload['summary']) ? $payload['summary'] : '';

        // Back-compat.
        if ($summary === '' && !empty($payload['body']) && is_string($payload['body'])) {
            $summary = $payload['body'];
        }
        if ($summary === '' && !empty($payload['message']) && is_string($payload['message'])) {
            $summary = $payload['message'];
        }

        $source  = isset($payload['source']) && is_string($payload['source']) ? $payload['source'] : '';
        $type    = isset($payload['type']) && is_string($payload['type']) ? $payload['type'] : '';
        $link    = isset($payload['link']) && is_string($payload['link']) ? $payload['link'] : '';
        $context = isset($payload['context']) && is_array($payload['context']) ? $payload['context'] : [];

        // Human mapping.
        $source_h = function_exists('nh_human_source') ? (string) nh_human_source($source) : $source;
        $type_h   = function_exists('nh_human_type') ? (string) nh_human_type($type) : $type;

        // Type-specific title upgrade.
        if ($type === 'comment_new' && !empty($context['post_id'])) {
            $post_title = function_exists('get_the_title') ? (string) get_the_title((int) $context['post_id']) : '';
            if ($post_title !== '') {
                $title = sprintf('💬 %s “%s”', esc_html__('New comment on', 'notification-hub'), $post_title);
            }
        }

        if ($type === 'order_created' && !empty($context['order_id'])) {
            $title = sprintf('🛒 %s #%d', esc_html__('New order', 'notification-hub'), (int) $context['order_id']);
        }

        if ($type === 'low_stock' && !empty($context['product_id'])) {
            $title = '⚠️ ' . ($title !== '' ? $title : esc_html__('Low stock', 'notification-hub'));
        }

        if ($type === 'form_sent') {
            $title = '📨 ' . ($title !== '' ? $title : esc_html__('Form submitted', 'notification-hub'));
        }

        if ($type === 'form_failed') {
            $title = '❌ ' . ($title !== '' ? $title : esc_html__('Form failed', 'notification-hub'));
        }

        // Smart CTA label for email template.
        $cta = '';
        if ($link !== '') {
            if ($type === 'comment_new') {
                $cta = esc_html__('Review Comment', 'notification-hub');
            } elseif ($type === 'order_created') {
                $cta = esc_html__('View Order', 'notification-hub');
            } elseif ($type === 'low_stock') {
                $cta = esc_html__('Edit Product', 'notification-hub');
            } elseif ($type === 'user_registered') {
                $cta = esc_html__('View User', 'notification-hub');
            } elseif ($type === 'post_status_changed') {
                $cta = esc_html__('Edit Post', 'notification-hub');
            } elseif ($type === 'form_sent' || $type === 'form_failed') {
                $cta = esc_html__('Edit Form', 'notification-hub');
            } else {
                $cta = esc_html__('View Details', 'notification-hub');
            }
        }

        return [
            'title'         => $title,
            'summary'       => $summary,
            'source'        => $source,
            'type'          => $type,
            'link'          => $link,
            'cta_label'     => $cta,
            'context'       => $context,
            'source_human'  => $source_h,
            'type_human'    => $type_h,
            'site_name'     => function_exists('get_bloginfo') ? (string) get_bloginfo('name') : '',
            'site_url'      => function_exists('home_url') ? (string) home_url('/') : '',
        ];
    }

    /**
     * Fallback text for channels.
     *
     * @since 1.6.3
     * @param array $payload Raw payload.
     * @return string
     */
    private static function fallback_text(array $payload): string {
        if (!empty($payload['body']) && is_string($payload['body'])) {
            return $payload['body'];
        }

        if (!empty($payload['message']) && is_string($payload['message'])) {
            return $payload['message'];
        }

        return esc_html__('Empty message.', 'notification-hub');
    }
}