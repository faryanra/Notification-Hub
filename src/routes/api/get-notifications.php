<?php
/**
 * Get Notifications REST Route
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Routes\Api;

use Notification_Hub\Repositories\Notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Get_Notifications {

	private $repo;

	public function __construct( Notifications $repo ) {
		$this->repo = $repo;
	}

	public function handle( $request ) {
		$page     = $request->get_param( 'page' ) ?? 1;
		$per_page = $request->get_param( 'per_page' ) ?? 20;
		$status   = $request->get_param( 'status' ) ?? '';
		$type     = $request->get_param( 'type' ) ?? '';

		$notifications = $this->repo->get_all(
			array(
				'limit'  => $per_page,
				'offset' => ( $page - 1 ) * $per_page,
				'status' => $status,
				'type'   => $type,
			)
		);

		$total = $this->repo->count(
			array(
				'status' => $status,
				'type'   => $type,
			)
		);

		return rest_ensure_response(
			array(
				'items' => $notifications,
				'total' => $total,
				'page'  => $page,
				'pages' => ceil( $total / $per_page ),
			)
		);
	}
}
