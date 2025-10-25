<?php
// Notification Hub - uninstall cleanup
// Runs only on full delete (uninstall), not on deactivate.
// Goal in this mode: clean settings, unschedule cron, KEEP data tables for possible reinstall.

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 1. Unschedule cleanup cron job
$timestamp = wp_next_scheduled('nh_cron_cleanup');
if ($timestamp) {
    wp_unschedule_event($timestamp, 'nh_cron_cleanup');
}

// 2. Delete plugin options (settings / license / internal version markers)
$opts = [
    'nh_retention_days',
    'nh_email_to',
    'nh_slack_webhook',
    'nh_telegram_bot_token',
    'nh_telegram_chat_id',
    'nh_license_key',
    'nh_db_version',
];
foreach ($opts as $opt) {
    delete_option($opt);
}

// 3. DO NOT drop custom tables.
// We intentionally keep nh_notifications / nh_hooks so the admin
// doesn't lose history if they reinstall the plugin.
//
// If you ever add a setting like "Delete all plugin data on uninstall? yes/no",
// you'd check that option here and then conditionally DROP TABLES.
