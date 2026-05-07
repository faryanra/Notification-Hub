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
 * Creates a notification when a new user registers.
 *
 * @since 1.0.0
 */
final class UserRegistered implements Integration {
    public function register(Loader $loader): void {
        $loader->addAction('user_register', [$this, 'handle'], 10, 1);
    }

    public function handle($user_id): void {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return;
        }

        $user = get_userdata($user_id);
        $user_login = $user && isset($user->user_login) ? (string) $user->user_login : ('user_' . $user_id);
        $user_email = $user && isset($user->user_email) ? (string) $user->user_email : '';
        $role = '';
        if ($user && isset($user->roles) && is_array($user->roles) && !empty($user->roles[0])) {
            $role = (string) $user->roles[0];
        }

        $admin_link = admin_url('user-edit.php?user_id=' . $user_id);

        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('wordpress')
            ->type('user_registered')
            ->title(sprintf(__('New user registration: %s', 'notification-hub'), wp_strip_all_tags($user_login)))
            ->message(
                $role !== ''
                    ? sprintf(__('A new %1$s account was created on your site.', 'notification-hub'), wp_strip_all_tags($role))
                    : __('A new user account was created on your site.', 'notification-hub')
            )
            ->status(0)
            ->priority(1)
            ->tags(['users'])
            ->context([
                'user_id' => $user_id,
                'user_login' => wp_strip_all_tags($user_login),
                'user_email' => sanitize_email($user_email),
                'user_role' => sanitize_key($role),
                'actor' => wp_strip_all_tags($user_login),
                'admin_link' => $admin_link,
            ])
            ->link($admin_link)
            ->build();

        $repo->insert($data);
    }
}



