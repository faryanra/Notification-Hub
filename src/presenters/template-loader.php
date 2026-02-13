<?php
/**
 * Template Loader
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

namespace Notification_Hub\Presenters;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template Loader
 */
class Template_Loader {

	public static function load( $template, $data = array() ) {
		$file = NH_PLUGIN_DIR . 'templates/' . $template . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		extract( $data );

		include $file;
	}
}
