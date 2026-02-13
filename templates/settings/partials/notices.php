<?php
/**
 * Notices Partial
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notices = get_transient( 'nh_admin_notices' );

if ( empty( $notices ) ) {
	return;
}

foreach ( $notices as $notice ) :
	$type    = $notice['type'] ?? 'info';
	$message = $notice['message'] ?? '';
	?>
	<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
		<p><?php echo wp_kses_post( $message ); ?></p>
	</div>
	<?php
endforeach;

delete_transient( 'nh_admin_notices' );
