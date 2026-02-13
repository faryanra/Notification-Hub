<?php
/**
 * Admin Footer Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

	<div class="nh-footer">
		<p><?php
			printf(
				/* translators: %s: plugin version */
				esc_html__( 'Notification Hub v%s', 'notification-hub' ),
				esc_html( NH_VERSION )
			);
		?></p>
	</div>
</div>
