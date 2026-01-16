<?php
if (!defined('ABSPATH')) exit;

class NH_Table_Columns {

    public static function column_cb($item) {
        return sprintf('<input type="checkbox" name="ids[]" value="%d" />', (int) $item->id);
    }

    public static function column_id($item) {
        return (int) $item->id;
    }

    // ✅ Title بدون dot
    public static function column_title($item) {
        $id = (int) $item->id;
        
        // Title
        $title = esc_html($item->title ?: __('(no title)', 'notification-hub'));
        if ($admin_link = self::get_admin_link($item)) {
            $title_html = '<a href="' . esc_url($admin_link) . '" target="_blank">' . $title . '</a>';
        } else {
            $title_html = '<strong>' . $title . '</strong>';
        }

        // Row actions
        $actions = self::get_row_actions($item);
        $actions_html = '<div class="row-actions">' . implode(' | ', $actions) . '</div>';

        return $title_html . $actions_html;
    }

    public static function column_view($item) {
        $nonce = wp_create_nonce('nh_view_' . $item->id);
        return sprintf(
            '<span class="nh-eye nh-open-modal" data-id="%d" data-nonce="%s" title="%s">'
            . '<span class="dashicons dashicons-visibility"></span>'
            . '</span>',
            (int) $item->id,
            esc_attr($nonce),
            esc_attr__('View Details', 'notification-hub')
        );
    }

    // ✅ Created با data attribute برای JavaScript
    public static function column_created($item) {
        return '<span class="nh-created-time" data-raw="' . esc_attr($item->created_at) . '">' 
               . esc_html($item->created_at) 
               . '</span>';
    }

    public static function column_source($item) {
        return esc_html($item->source ?: '—');
    }

    public static function column_type($item) {
        return esc_html($item->type ?: '—');
    }

    public static function column_priority($item) {
        return esc_html($item->priority ?: '—');
    }

    public static function column_status($item) {
        $badges = [];

        if ((int) $item->status === 3) {
            $badges[] = '<span class="nh-status-important">Important</span>';
        }

        if ((int) $item->status === 1) {
            $badges[] = '<span class="nh-status-archived">Archived</span>';
        }

        if (empty($item->read_at)) {
            $badges[] = '<span class="nh-status-unread">Unread</span>';
        } else {
            $badges[] = '<span class="nh-status-read">Read</span>';
        }

        return '<div class="nh-status-badges">' . implode('', $badges) . '</div>';
    }

    private static function get_admin_link($item) {
        if ($item->source === 'Comments' && !empty($item->object_id)) {
            return admin_url('comment.php?action=editcomment&c=' . (int) $item->object_id);
        }
        if ($item->source === 'Posts' && !empty($item->object_id)) {
            return get_edit_post_link((int) $item->object_id);
        }
        return null;
    }

    private static function get_row_actions($item) {
        $id = (int) $item->id;
        $actions = [];

        if (empty($item->read_at)) {
            $actions[] = '<a href="#" class="nh-mark-read" data-id="' . $id . '">' . __('Mark as Read', 'notification-hub') . '</a>';
        } else {
            $actions[] = '<a href="#" class="nh-mark-unread" data-id="' . $id . '">' . __('Mark as Unread', 'notification-hub') . '</a>';
        }

        if ((int) $item->status === 3) {
            $actions[] = '<a href="#" class="nh-unmark-important" data-id="' . $id . '">' . __('Remove Important', 'notification-hub') . '</a>';
        } else {
            $actions[] = '<a href="#" class="nh-mark-important" data-id="' . $id . '">' . __('Mark Important', 'notification-hub') . '</a>';
        }

        $actions[] = '<a href="#" class="nh-delete-notification" data-id="' . $id . '" style="color:#b32d2e;">' . __('Delete', 'notification-hub') . '</a>';

        return $actions;
    }
}
