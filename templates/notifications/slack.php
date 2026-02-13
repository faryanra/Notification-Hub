<?php
/**
 * Slack Notification Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Slack message format (JSON)
return array(
	'text'        => esc_html( $title ),
	'attachments' => array(
		array(
			'text' => esc_html( $message ),
		),
	),
);
