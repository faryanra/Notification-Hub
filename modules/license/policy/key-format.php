<?php
/**
 * License key format policy.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Key_Format {

    /**
     * Validate strict key format.
     *
     * @since 1.7.2
     * @param string $key License key.
     * @return bool
     */
    public static function validate_key_format(string $key): bool {
        $key = strtoupper(trim((string) $key));
        if ($key === '') {
            return false;
        }

        // NOTE: Keep the regex in sync with NH_License facade.
        return (bool) preg_match('/^NH-PRO-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key);
    }
}
