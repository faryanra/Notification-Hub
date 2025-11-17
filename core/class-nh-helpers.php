<?php
// Helpers (Logging, Utility)

if (!defined('ABSPATH')) exit;

class NH_Helpers {

    /**
     * Structured logger for all Notification Hub modules.
     * Usage: NH_Helpers::log('message', 'info|debug|error')
     */
    public static function log($msg, $level = 'info') {
        if (!defined('WP_DEBUG') || !WP_DEBUG) return;
        $prefix = '[' . strtoupper($level) . '][NH]';
        if (!is_string($msg)) $msg = wp_json_encode($msg);
        error_log("$prefix $msg");
    }

    /**
     * Quick JSON pretty print (optional for debugging)
     */
    public static function json_pretty($data) {
        return wp_json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Safe string truncation for logs
     */
    public static function truncate($text, $limit = 200) {
        $text = (string)$text;
        return strlen($text) > $limit ? substr($text, 0, $limit) . '…' : $text;
    }
}
