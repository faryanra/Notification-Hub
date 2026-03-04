<?php

namespace NotificationHub\Integrations\Admin;

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Register settings (legacy-identical options keys).
 *
 * @since 1.7.2
 */
final class SettingsRegistration implements Integration {
    /**
     * @since 1.7.2
     */
    public function register(Loader $loader): void {
        $loader->addAction('admin_init', [$this, 'registerSettings']);
    }

    /**
     * @since 1.7.2
     */
    public function registerSettings(): void {
        // General.
        register_setting('nh_settings_general', 'nh_retention_days');
        register_setting('nh_settings_general', 'nh_email_to');
        register_setting('nh_settings_general', 'nh_keep_data_on_uninstall');

        // Pro.
        register_setting('nh_settings_premium', 'nh_telegram_bot_token');
        register_setting('nh_settings_premium', 'nh_telegram_chat_id');
        register_setting('nh_settings_premium', 'nh_slack_webhook');
    }
}
