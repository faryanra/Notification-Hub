<?php
/**
 * Contact Form 7 Form Submitted Event
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Integrations\Events\Contact_Form_7;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Services\Notification_Dispatcher;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Form Submitted
 */
class Form_Submitted implements Integration_Interface {

	private $repo;
	private $dispatcher;

	public function __construct( Notifications $repo, Notification_Dispatcher $dispatcher ) {
		$this->repo       = $repo;
		$this->dispatcher = $dispatcher;
	}

	public function register() {
		add_action( 'wpcf7_mail_sent', array( $this, 'handle' ), 10, 1 );
	}

	public function handle( $contact_form ) {
		if ( ! $contact_form ) {
			return;
		}

		$submission = \WPCF7_Submission::get_instance();

		if ( ! $submission ) {
			return;
		}

		$posted_data = $submission->get_posted_data();

		$notification_id = $this->repo->create(
			array(
				'title'   => sprintf(
					__( 'New Contact Form Submission: %s', 'notification-hub' ),
					$contact_form->title()
				),
				'message' => sprintf(
					__( 'From: %s', 'notification-hub' ),
					$posted_data['your-email'] ?? __( 'Unknown', 'notification-hub' )
				),
				'type'    => 'cf7_submission',
				'status'  => 'unread',
			)
		);

		if ( $notification_id ) {
			do_action( 'nh_notification_created', $notification_id, 'cf7_submission' );
		}
	}
}
