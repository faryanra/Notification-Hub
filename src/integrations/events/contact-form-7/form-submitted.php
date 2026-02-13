<?php
/**
 * Form Submitted Event (CF7)
 *
 * (Extracted from NH_Int_CF7::on_sent + on_failed)
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\Contact_Form_7;

use Notification_Hub\Integrations\Integration_Interface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Form_Submitted implements Integration_Interface {

	private $container;

	public function __construct( $container ) {
		$this->container = $container;
	}

	public function register(): void {
		if ( ! defined( 'WPCF7_VERSION' ) ) {
			return;
		}

		add_action( 'wpcf7_mail_sent', array( $this, 'on_sent' ), 10, 1 );
		add_action( 'wpcf7_mail_failed', array( $this, 'on_failed' ), 10, 1 );
	}

	public function on_sent( $contact_form ) {
		$form_title = is_object( $contact_form ) && method_exists( $contact_form, 'title' )
			? (string) $contact_form->title()
			: '';

		$title = sprintf(
			esc_html__( 'CF7: %s', 'notification-hub' ),
			$form_title
		);

		$form_id = is_object( $contact_form ) && method_exists( $contact_form, 'id' )
			? (int) $contact_form->id()
			: 0;

		$e = array(
			'source'  => 'cf7',
			'type'    => 'form_sent',
			'title'   => $title,
			'message' => esc_html__( 'Mail sent successfully.', 'notification-hub' ),
			'context' => array(
				'cf7_form_id' => $form_id,
				'form_title'  => $form_title,
			),
		);

		$db = $this->container->get_svc( 'db' );
		if ( $db && method_exists( $db, 'insert_notification' ) ) {
			$db->insert_notification( $e );
		}

		$this->fanout_send( $e );
	}

	public function on_failed( $contact_form ) {
		$form_title = is_object( $contact_form ) && method_exists( $contact_form, 'title' )
			? (string) $contact_form->title()
			: '';

		$title = sprintf(
			esc_html__( 'CF7: %s', 'notification-hub' ),
			$form_title
		);

		$form_id = is_object( $contact_form ) && method_exists( $contact_form, 'id' )
			? (int) $contact_form->id()
			: 0;

		$e = array(
			'source'  => 'cf7',
			'type'    => 'form_failed',
			'title'   => $title,
			'message' => esc_html__( 'Mail failed to send.', 'notification-hub' ),
			'context' => array(
				'cf7_form_id' => $form_id,
				'form_title'  => $form_title,
			),
		);

		$db = $this->container->get_svc( 'db' );
		if ( $db && method_exists( $db, 'insert_notification' ) ) {
			$db->insert_notification( $e );
		}

		$this->fanout_send( $e );
	}

	private function fanout_send( array $e ) {
		$notifier = $this->container->get_svc( 'notifier' );
		if ( ! $notifier ) {
			return;
		}

		$context = isset( $e['context'] ) && is_array( $e['context'] ) ? $e['context'] : array();

		$link = '';
		if ( ! empty( $context['cf7_form_id'] ) && function_exists( 'admin_url' ) ) {
			$link = (string) admin_url( 'admin.php?page=wpcf7&post=' . ( (int) $context['cf7_form_id'] ) . '&action=edit' );
		}

		$payload = array(
			'title'   => $e['title'] ?? '',
			'summary' => $e['message'] ?? '',
			'source'  => 'cf7',
			'type'    => $e['type'] ?? 'form_sent',
			'context' => $context,
			'link'    => $link,
			'no_log'  => true,
		);

		if ( method_exists( $notifier, 'queue_send' ) ) {
			$notifier->queue_send( 'email', $payload );
			$notifier->queue_send( 'telegram', $payload );
			$notifier->queue_send( 'slack', $payload );
		}
	}
}
