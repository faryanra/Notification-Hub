<?php
/**
 * Telegram Notification Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Telegram message format
echo '<b>' . esc_html( $title ) . '</b>' . "\n\n";
echo esc_html( $message );
