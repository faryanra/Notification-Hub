<?php
/**
 * License HTTP client (wrapper).
 *
 * This is the future home for remote verify calls.
 * For now, it delegates to legacy NH_License_Client for compatibility.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_HTTP_Client {

    /**
     * Remote verify.
     *
     * @since 1.7.2
     * @param string $key License key.
     * @param string $server_url Server URL.
     * @param bool   $debug Debug flag.
     * @return array<string,mixed>
     */
    public static function remote_verify(string $key, string $server_url, bool $debug = false): array {
        // Ensure legacy client exists.
        if (!class_exists('NH_License_Client')) {
            $legacy = NH_PLUGIN_DIR . 'modules/license/class-nh-license-client.php';
            if (file_exists($legacy)) {
                require_once $legacy;
            }
        }

        if (class_exists('NH_License_Client') && method_exists('NH_License_Client', 'remote_verify')) {
            return (array) NH_License_Client::remote_verify($key, $server_url, $debug);
        }

        return [
            'ok'      => false,
            'message' => 'License client missing.',
            'state'   => [],
        ];
    }
}
