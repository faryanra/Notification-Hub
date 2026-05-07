<?php
/**
 * NH_Table_Columns
 *
 * Column renderers for NH_Notifications_Table.
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Table_Columns {
    public static function column_cb($item): string {
        return sprintf('<input type="checkbox" name="ids[]" value="%d" />', (int) $item->id);
    }

    public static function column_id($item): int {
        return (int) $item->id;
    }

    public static function column_title($item): string {
        $title_text = !empty($item->title)
            ? (string) $item->title
            : __('(no title)', 'notification-hub');

        $title = esc_html($title_text);
        $admin_link = self::get_admin_link($item);

        if ($admin_link) {
            $title_html = '<a href="' . esc_url($admin_link) . '" target="_blank" rel="noopener noreferrer">' . $title . '</a>';
        } else {
            $title_html = '<strong>' . $title . '</strong>';
        }

        $actions = self::get_row_actions($item);
        $actions_html = !empty($actions)
            ? '<div class="row-actions">' . implode(' | ', $actions) . '</div>'
            : '';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        return $title_html . $actions_html;
    }

    public static function column_view($item): string {
        $id = (int) $item->id;
        $nonce = wp_create_nonce('nh_view_' . $id);

        return sprintf(
            '<span class="nh-eye nh-open-modal" data-id="%d" data-nonce="%s" title="%s"><span class="dashicons dashicons-visibility"></span></span>',
            $id,
            esc_attr($nonce),
            esc_attr__('View details', 'notification-hub')
        );
    }

    public static function column_created($item): string {
        $raw = !empty($item->created_at) ? (string) $item->created_at : '';
        $txt = $raw !== '' ? $raw : '-';

        return '<span class="nh-created-time" data-raw="' . esc_attr($raw) . '">' . esc_html($txt) . '</span>';
    }

    public static function column_source($item): string {
        $txt = !empty($item->source) ? (string) $item->source : '-';
        return esc_html($txt);
    }

    public static function column_type($item): string {
        $txt = !empty($item->type) ? (string) $item->type : '-';
        return esc_html($txt);
    }

    public static function column_priority($item): string {
        $txt = isset($item->priority) && $item->priority !== '' ? (string) $item->priority : '-';
        return esc_html($txt);
    }

    public static function column_status($item): string {
        $badges = [];

        if ((int) $item->status === 3) {
            $badges[] = '<span class="nh-status-important">' . esc_html__('Important', 'notification-hub') . '</span>';
        }

        if ((int) $item->status === 1) {
            $badges[] = '<span class="nh-status-archived">' . esc_html__('Archived', 'notification-hub') . '</span>';
        }

        if (empty($item->read_at)) {
            $badges[] = '<span class="nh-status-unread">' . esc_html__('Unread', 'notification-hub') . '</span>';
        } else {
            $badges[] = '<span class="nh-status-read">' . esc_html__('Read', 'notification-hub') . '</span>';
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        return '<div class="nh-status-badges">' . implode('', $badges) . '</div>';
    }

    private static function get_admin_link($item): ?string {
        $source_raw = !empty($item->source) ? (string) $item->source : '';
        $source = sanitize_key($source_raw);
        $context = self::get_context($item);

        if (!empty($context['admin_link']) && is_scalar($context['admin_link'])) {
            $url = esc_url_raw((string) $context['admin_link']);
            if ($url !== '') {
                return $url;
            }
        }

        if ($source === 'wordpress' && !empty($context['comment_id'])) {
            return admin_url('comment.php?action=editcomment&c=' . absint($context['comment_id']));
        }

        if (!empty($context['post_id'])) {
            $post_link = get_edit_post_link(absint($context['post_id']), '');
            if (is_string($post_link) && $post_link !== '') {
                return $post_link;
            }
        }

        if ($source === 'woocommerce' && !empty($context['order_id'])) {
            return admin_url('post.php?post=' . absint($context['order_id']) . '&action=edit');
        }

        if ($source === 'woocommerce' && !empty($context['product_id'])) {
            return admin_url('post.php?post=' . absint($context['product_id']) . '&action=edit');
        }

        if ($source === 'wordpress' && !empty($context['user_id'])) {
            return admin_url('user-edit.php?user_id=' . absint($context['user_id']));
        }

        $object_id = !empty($item->object_id) ? (int) $item->object_id : 0;
        if (!$object_id) {
            return null;
        }

        if (in_array($source, ['comments', 'comment'], true)) {
            return admin_url('comment.php?action=editcomment&c=' . $object_id);
        }

        if (in_array($source, ['posts', 'post'], true)) {
            $link = get_edit_post_link($object_id, '');
            return $link ? $link : null;
        }

        return null;
    }

    private static function get_row_actions($item): array {
        $id = (int) $item->id;
        $actions = [];

        if (empty($item->read_at)) {
            $actions[] = '<a href="#" class="nh-mark-read" data-id="' . esc_attr($id) . '">' . esc_html__('Mark as read', 'notification-hub') . '</a>';
        } else {
            $actions[] = '<a href="#" class="nh-mark-unread" data-id="' . esc_attr($id) . '">' . esc_html__('Mark as unread', 'notification-hub') . '</a>';
        }

        if ((int) $item->status === 3) {
            $actions[] = '<a href="#" class="nh-unmark-important" data-id="' . esc_attr($id) . '">' . esc_html__('Remove important', 'notification-hub') . '</a>';
        } else {
            $actions[] = '<a href="#" class="nh-mark-important" data-id="' . esc_attr($id) . '">' . esc_html__('Mark important', 'notification-hub') . '</a>';
        }

        $actions[] = '<a href="#" class="nh-delete-notification nh-link-danger" data-id="' . esc_attr($id) . '">' . esc_html__('Delete', 'notification-hub') . '</a>';

        return $actions;
    }

    /**
     * @return array<string,mixed>
     */
    private static function get_context($item): array {
        $raw = isset($item->context) ? (string) $item->context : '';
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
