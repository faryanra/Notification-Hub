<?php
/**
 * Uninstall Script
 *
 * Fired when plugin is deleted.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete options
delete_option( 'nh_version' );
delete_option( 'nh_email_to' );
delete_option( 'nh_retention_days' );
delete_option( 'nh_telegram_bot_token' );
delete_option( 'nh_telegram_chat_id' );
delete_option( 'nh_slack_webhook_url' );
delete_option( 'nh_license_key' );
delete_option( 'nh_license_status' );

// Delete tables
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}nh_notifications" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}nh_queue" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}nh_custom_hooks" );

// Delete user meta
$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'nh_%'" );

// Clear scheduled events
wp_clear_scheduled_hook( 'nh_cron_cleanup' );
wp_clear_scheduled_hook( 'nh_process_queue' );

// Flush cache
wp_cache_flush();
