<?php
/**
 * Presenter Factory
 *
 * Creates presenter instances.
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Factories;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Presenter_Factory {

	public static function create( string $page, $container ) {
		switch ( $page ) {
			case 'dashboard':
				return new \Notification_Hub\Presenters\Dashboard_Presenter( $container );

			case 'settings':
				return new \Notification_Hub\Presenters\Settings_Presenter( $container );

			case 'hooks':
				return new \Notification_Hub\Presenters\Hooks_Presenter( $container );

			case 'channels':
				return new \Notification_Hub\Presenters\Channels_Presenter( $container );

			case 'license':
				return new \Notification_Hub\Presenters\License_Presenter( $container );

			case 'logs':
				return new \Notification_Hub\Presenters\Logs_Presenter( $container );

			case 'analytics':
				return new \Notification_Hub\Presenters\Analytics_Presenter( $container );

			default:
				return null;
		}
	}
}
