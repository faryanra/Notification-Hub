<?php

namespace NotificationHub\Routes\Admin;

use NotificationHub\Helpers\Security;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Admin AJAX route: Get unread count.
 *
 * Used by admin bar badge refresh.
 *
 * @since 1.7.2
 */
final class GetUnreadCount {
    public function handle(): void {
        Security::ensureCanManageOptions();
        check_ajax_referer('nh_ajax_nonce', '_wpnonce');

        $repo  = new NotificationsRepository();
        $count = $repo->countUnread();

        wp_send_json_success(['count' => (int) $count]);
    }
}
