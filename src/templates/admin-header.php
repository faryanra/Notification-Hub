<?php
/**
 * Admin Header Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = $args['title'] ?? __( 'Notification Hub', 'notification-hub' );
?>

<div class="wrap nh-admin-wrap">
	<h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>
	<hr class="wp-header-end">
