<?php
/**
 * Email Sender Channel
 *
 * Sends notifications via email using wp_mail().
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
 * Email_Sender Class
 */
class Email_Sender implements Integration_Interface {

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'nh_send_email', array( $this, 'send' ), 10, 1 );
	}

	/**
	 * Send email notification.
	 *
	 * @param array $payload Notification payload.
	 * @return void
	 */
	public function send( $payload ) {
		if ( ! is_array( $payload ) ) {
			return;
		}

		$title   = isset( $payload['title'] ) ? sanitize_text_field( $payload['title'] ) : esc_html__( 'Notification', 'notification-hub' );
		$summary = isset( $payload['summary'] ) ? wp_kses_post( $payload['summary'] ) : '';
		$link    = isset( $payload['link'] ) ? esc_url_raw( $payload['link'] ) : '';

		// Get recipient.
		$to = get_option( 'nh_email_to', get_option( 'admin_email' ) );
		if ( ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		// Build message.
		$message  = '<html><body>';
		$message .= '<h2>' . esc_html( $title ) . '</h2>';
		$message .= '<p>' . wp_kses_post( $summary ) . '</p>';

		if ( $link ) {
			$message .= '<p><a href="' . esc_url( $link ) . '">' . esc_html__( 'View Details', 'notification-hub' ) . '</a></p>';
		}

		$message .= '<hr>';
		$message .= '<p style="font-size:12px;color:#999;">' . esc_html__( 'Sent by Notification Hub', 'notification-hub' ) . '</p>';
		$message .= '</body></html>';

		// Send.
		wp_mail(
			$to,
			$title,
			$message,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
	}
}
