<?php

namespace NotificationHub\Repositories;

/**
 * Settings repository.
 *
 * Mirrors legacy option keys (nh_retention_days, nh_email_to, ...).
 *
 * @since 1.7.2
 */
final class SettingsRepository {
    public const OPT_RETENTION_DAYS = 'nh_retention_days';
    public const OPT_EMAIL_TO = 'nh_email_to';
    public const OPT_KEEP_DATA_ON_UNINSTALL = 'nh_keep_data_on_uninstall';

    // Pro options (stored even if addon inactive; UI will gate them).
    public const OPT_TELEGRAM_BOT_TOKEN = 'nh_telegram_bot_token';
    public const OPT_TELEGRAM_CHAT_ID = 'nh_telegram_chat_id';
    public const OPT_SLACK_WEBHOOK = 'nh_slack_webhook';

    /**
     * @return array{retention_days:int,email_to:string,keep_data_on_uninstall:bool}
     */
    public function getGeneral(): array {
        $retention = (int) get_option(self::OPT_RETENTION_DAYS, 90);
        if ($retention <= 0) {
            $retention = 90;
        }

        $email = (string) get_option(self::OPT_EMAIL_TO, '');
        $email = sanitize_text_field($email);

        $keepRaw = get_option(self::OPT_KEEP_DATA_ON_UNINSTALL, '1');
        $keep = !in_array((string) $keepRaw, ['0', 'false'], true);

        return [
            'retention_days' => $retention,
            'email_to' => $email,
            'keep_data_on_uninstall' => $keep,
        ];
    }

    /**
     * @param array{retention_days?:int,email_to?:string,keep_data_on_uninstall?:bool} $data
     */
    public function updateGeneral(array $data): void {
        if (array_key_exists('retention_days', $data)) {
            $days = (int) $data['retention_days'];
            $days = max(1, min(3650, $days));
            update_option(self::OPT_RETENTION_DAYS, $days);
        }

        if (array_key_exists('email_to', $data)) {
            $email = sanitize_text_field((string) $data['email_to']);
            update_option(self::OPT_EMAIL_TO, $email);
        }

        if (array_key_exists('keep_data_on_uninstall', $data)) {
            update_option(self::OPT_KEEP_DATA_ON_UNINSTALL, (bool) $data['keep_data_on_uninstall']);
        }
    }

    /**
     * @return array{telegram_bot_token:string,telegram_chat_id:string,slack_webhook:string}
     */
    public function getPro(): array {
        return [
            'telegram_bot_token' => sanitize_text_field((string) get_option(self::OPT_TELEGRAM_BOT_TOKEN, '')),
            'telegram_chat_id' => sanitize_text_field((string) get_option(self::OPT_TELEGRAM_CHAT_ID, '')),
            'slack_webhook' => esc_url_raw((string) get_option(self::OPT_SLACK_WEBHOOK, '')),
        ];
    }

    /**
     * @param array{telegram_bot_token?:string,telegram_chat_id?:string,slack_webhook?:string} $data
     */
    public function updatePro(array $data): void {
        if (array_key_exists('telegram_bot_token', $data)) {
            update_option(self::OPT_TELEGRAM_BOT_TOKEN, sanitize_text_field((string) $data['telegram_bot_token']));
        }

        if (array_key_exists('telegram_chat_id', $data)) {
            update_option(self::OPT_TELEGRAM_CHAT_ID, sanitize_text_field((string) $data['telegram_chat_id']));
        }

        if (array_key_exists('slack_webhook', $data)) {
            update_option(self::OPT_SLACK_WEBHOOK, esc_url_raw((string) $data['slack_webhook']));
        }
    }
}
