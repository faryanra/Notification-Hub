<?php
namespace NotificationHub\Integrations\Events\WordPress;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when a post status changes.
 *
 * @since 1.0.0
 */
final class PostStatusChanged implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('transition_post_status', [$this, 'handle'], 10, 3);
    }

    public function handle($new_status, $old_status, $post): void {
        if (!is_object($post) || !isset($post->ID)) {
            return;
        }

        if ((string) $new_status === (string) $old_status) {
            return;
        }

        $post_id = (int) $post->ID;
        if ($post_id <= 0) {
            return;
        }

        $new_status = sanitize_key((string) $new_status);
        $old_status = sanitize_key((string) $old_status);

        if ($this->shouldSkipTransition($post_id, $post, $old_status, $new_status)) {
            return;
        }

        $post_title = get_the_title($post_id);
        if (!is_string($post_title) || $post_title === '') {
            $post_title = sprintf(__('Post #%d', 'notification-hub'), $post_id);
        }

        $post_type = isset($post->post_type) ? sanitize_key((string) $post->post_type) : 'post';
        $post_type_obj = get_post_type_object($post_type);
        $post_type_label = ($post_type_obj && isset($post_type_obj->labels->singular_name))
            ? (string) $post_type_obj->labels->singular_name
            : ucfirst(str_replace('_', ' ', $post_type));

        $old_human = $this->humanStatus($old_status);
        $new_human = $this->humanStatus($new_status);
        $clean_post_title = wp_strip_all_tags($post_title);
        $copy = $this->buildTransitionCopy($post_type_label, $clean_post_title, $old_human, $new_human, $new_status);

        $review_link = '';
        $revision_parent = wp_is_post_revision($post_id);
        if ($revision_parent) {
            $review_link = admin_url('revision.php?revision=' . $post_id);
        }
        if ($review_link === '') {
            $edit_link = get_edit_post_link($post_id, '');
            if (is_string($edit_link) && $edit_link !== '') {
                $review_link = $edit_link;
            }
        }

        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('wordpress')
            ->type('post_status_changed')
            ->title($copy['title'])
            ->message($copy['message'])
            ->status(0)
            ->priority(1)
            ->tags(['posts'])
            ->context([
                'post_id' => $post_id,
                'post_type' => $post_type,
                'post_title' => $clean_post_title,
                'old_status' => $old_status,
                'new_status' => $new_status,
                'old_status_human' => $old_human,
                'new_status_human' => $new_human,
                'admin_link' => $review_link,
                'cta_label' => __('Open Post', 'notification-hub'),
            ])
            ->link($review_link)
            ->build();

        $repo->insert($data);
    }

    private function shouldSkipTransition(int $post_id, object $post, string $old_status, string $new_status): bool {
        if ((bool) wp_is_post_revision($post_id) || (bool) wp_is_post_autosave($post_id)) {
            return true;
        }

        $post_type = isset($post->post_type) ? sanitize_key((string) $post->post_type) : '';
        if ($post_type === 'revision' || $post_type === 'nav_menu_item' || $post_type === 'custom_css') {
            return true;
        }

        // Revisions and transient editor statuses create noisy events for end users.
        if ($old_status === 'inherit' || $new_status === 'inherit') {
            return true;
        }

        if ($new_status === 'auto-draft') {
            return true;
        }

        if ($old_status === 'new' && in_array($new_status, ['auto-draft', 'draft'], true)) {
            return true;
        }

        if ($old_status === 'auto-draft' && $new_status === 'draft') {
            return true;
        }

        return false;
    }

    private function humanStatus(string $status): string {
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

    /**
     * @return array{title:string,message:string}
     */
    private function buildTransitionCopy(
        string $post_type_label,
        string $post_title,
        string $old_human,
        string $new_human,
        string $new_status
    ): array {
        $title = sprintf(__('Status changed: %s', 'notification-hub'), $post_title);
        $message = sprintf(
            __('WordPress updated %1$s "%2$s" from %3$s to %4$s.', 'notification-hub'),
            $post_type_label,
            $post_title,
            $old_human,
            $new_human
        );

        if ($new_status === 'publish') {
            $title = sprintf(__('Published: %s', 'notification-hub'), $post_title);
            $message = sprintf(
                __('WordPress published %1$s "%2$s".', 'notification-hub'),
                $post_type_label,
                $post_title
            );
        } elseif ($new_status === 'trash') {
            $title = sprintf(__('Moved to Trash: %s', 'notification-hub'), $post_title);
            $message = sprintf(
                __('WordPress moved %1$s "%2$s" to Trash.', 'notification-hub'),
                $post_type_label,
                $post_title
            );
        } elseif ($new_status === 'pending') {
            $title = sprintf(__('Pending Review: %s', 'notification-hub'), $post_title);
            $message = sprintf(
                __('WordPress sent %1$s "%2$s" for review.', 'notification-hub'),
                $post_type_label,
                $post_title
            );
        } elseif ($new_status === 'future') {
            $title = sprintf(__('Scheduled: %s', 'notification-hub'), $post_title);
            $message = sprintf(
                __('WordPress scheduled %1$s "%2$s" for publication.', 'notification-hub'),
                $post_type_label,
                $post_title
            );
        }

        return [
            'title' => $title,
            'message' => $message,
        ];
    }
}



