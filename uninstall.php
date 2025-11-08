<?php
// Notification Hub — professional uninstall 

if (!defined('WP_UNINSTALL_PLUGIN')) exit;

global $wpdb;

// 0) Detect multisite uninstall
$is_network = is_multisite() && isset($_GET['networkwide']) && (int)$_GET['networkwide'] === 1;

// 1) Unschedule cron
if ($ts = wp_next_scheduled('nh_cron_cleanup')) {
    wp_unschedule_event($ts, 'nh_cron_cleanup');
}

// 2) Clear Action Scheduler jobs (if installed)
if (function_exists('as_unschedule_action')) {
    while (as_next_scheduled_action('nh_process_send')) {
        as_unschedule_action('nh_process_send');
    }
}

// 3) Delete options
$site_opts = [
    'nh_retention_days',
    'nh_email_to',
    'nh_slack_webhook',
    'nh_telegram_bot_token',
    'nh_telegram_chat_id',
    'nh_license_key',
    'nh_license_valid',
    'nh_db_version',
    'nh_keep_data_on_uninstall',
    'nh_badge_last_seen_at',
];

$delete_site_options = function() use ($site_opts) {
    foreach ($site_opts as $opt) {
        delete_option($opt);
    }
};

// 4) Detect keep-data setting directly via SQL (not get_option)
$keep_val = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name='nh_keep_data_on_uninstall'");
$keep_val = is_null($keep_val) ? '1' : trim($keep_val); // default = keep
$drop_all_data = in_array($keep_val, ['0', 0, false, '', null], true);

if ($drop_all_data) {
    $drop_for_blog = function() use ($wpdb) {
        $tbl1 = $wpdb->prefix . 'nh_notifications';
        $tbl2 = $wpdb->prefix . 'nh_hooks';
        $wpdb->query("DROP TABLE IF EXISTS {$tbl1}");
        $wpdb->query("DROP TABLE IF EXISTS {$tbl2}");
    };

    if ($is_network) {
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
        foreach ($blog_ids as $bid) {
            switch_to_blog($bid);
            $drop_for_blog();
            restore_current_blog();
        }
    } else {
        $drop_for_blog();
    }
}

// 5) Delete site options finally
if ($is_network) {
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
    foreach ($blog_ids as $bid) {
        switch_to_blog($bid);
        $delete_site_options();
        restore_current_blog();
    }
} else {
    $delete_site_options();
}
