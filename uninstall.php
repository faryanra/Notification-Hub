<?php
// Notification Hub - uninstall cleanup
// Runs only on full delete, not on deactivate.

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Unschedule cleanup cron if still there
$timestamp = wp_next_scheduled('nh_cron_cleanup');
if ($timestamp) {
    wp_unschedule_event($timestamp, 'nh_cron_cleanup');
}

// Delete options used by the plugin
$opts = [
    'nh_retention_days',
    'nh_email_to',
    'nh_slack_webhook',
    'nh_telegram_token',
    'nh_telegram_chat_id',
    'nh_license_key',
    'nh_db_version',
];
foreach ($opts as $opt) {
    delete_option($opt); 
}

// We do NOT drop tables nh_notifications / nh_hooks here.
// Keeping data is safer for users (they might reinstall).
// If we ever add a setting "delete all data on uninstall", we check that flag here.
