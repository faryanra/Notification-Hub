<?php
/**
 * Email Sender Channel
 *
 * Sends notifications via email.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Channels;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Sender
 */
class Email_Sender implements Integration_Interface {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'nh_notification_created', array( $this, 'send' ), 10, 2 );
	}

	/**
	 * Send email notification.
	 *
	 * @param int    $notification_id Notification ID.
	 * @param string $type            Notification type.
	 * @return void
	 */
	public function send( $notification_id, $type ) {
		$enabled = get_option( 'nh_email_enabled', true );

		if ( ! $enabled ) {
			return;
		}

		global $wpdb;

		$notification = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}nh_notifications WHERE id = %d",
				$notification_id
			)
		);

		if ( ! $notification ) {
			return;
		}

		$to      = get_option( 'nh_admin_email', get_option( 'admin_email' ) );
		$subject = $notification->title;
		$message = $notification->message;

		wp_mail( $to, $subject, $message );
	}
}
