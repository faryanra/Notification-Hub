<?php
namespace NotificationHub\Integrations\Admin;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\SettingsRepository;
use NotificationHub\Services\EventCatalog;

/**
 * Register settings (legacy-identical options keys).
 *
 * @since 1.0.0
 */
final class SettingsRegistration implements Integration {
    /**
     * @since 1.0.0
     */
    public function register(Loader $loader): void {
        $loader->addAction('admin_init', [$this, 'registerSettings']);
    }

    /**
     * @since 1.0.0
     */
    public function registerSettings(): void {
        // General.
        register_setting(
            'nh_settings_general',
            'nh_retention_days',
            [
                'type' => 'integer',
                'sanitize_callback' => [self::class, 'sanitizeRetentionDays'],
                'default' => 90,
            ]
        );
        register_setting(
            'nh_settings_general',
            'nh_email_to',
            [
                'type' => 'string',
                'sanitize_callback' => [self::class, 'sanitizeEmailTo'],
                'default' => '',
            ]
        );
        register_setting(
            'nh_settings_general',
            'nh_keep_data_on_uninstall',
            [
                'type' => 'boolean',
                'sanitize_callback' => [self::class, 'sanitizeBoolLike'],
                'default' => true,
            ]
        );
        register_setting(
            'nh_settings_general',
            SettingsRepository::OPT_ENABLED_EVENTS,
            [
                'type' => 'array',
                'sanitize_callback' => [SettingsRepository::class, 'sanitizeEnabledEvents'],
                'default' => EventCatalog::defaultKeys(),
            ]
        );

        // Channels.
        register_setting(
            'nh_settings_channels',
            'nh_telegram_bot_token',
            [
                'type' => 'string',
                'sanitize_callback' => [self::class, 'sanitizeText'],
                'default' => '',
            ]
        );
        register_setting(
            'nh_settings_channels',
            'nh_telegram_chat_id',
            [
                'type' => 'string',
                'sanitize_callback' => [self::class, 'sanitizeText'],
                'default' => '',
            ]
        );
        register_setting(
            'nh_settings_channels',
            'nh_slack_webhook',
            [
                'type' => 'string',
                'sanitize_callback' => [self::class, 'sanitizeUrl'],
                'default' => '',
            ]
        );
    }

    /**
     * @param mixed $value
     */
    public static function sanitizeRetentionDays($value): int {
        $days = absint($value);
        if ($days <= 0) {
            $days = 90;
        }

        return min(3650, $days);
    }

    /**
     * @param mixed $value
     */
    public static function sanitizeEmailTo($value): string {
        $email = sanitize_email((string) $value);
        if ($email === '' && is_email((string) get_option('admin_email'))) {
            return (string) get_option('admin_email');
        }

        return $email;
    }

    /**
     * @param mixed $value
     */
    public static function sanitizeBoolLike($value): bool {
        return rest_sanitize_boolean($value);
    }

    /**
     * @param mixed $value
     */
    public static function sanitizeText($value): string {
        return sanitize_text_field((string) $value);
    }

    /**
     * @param mixed $value
     */
    public static function sanitizeUrl($value): string {
        return esc_url_raw((string) $value);
    }
}

