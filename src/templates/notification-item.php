<?php
/**
 * Notification Item Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$notification = $args['notification'] ?? array();
$id           = $notification['id'] ?? 0;
$title        = $notification['title'] ?? '';
$message      = $notification['message'] ?? '';
$source       = $notification['source'] ?? '';
$status       = (int) ( $notification['status'] ?? 0 );
$created_at   = $notification['created_at'] ?? '';

$status_class = $status === 1 ? 'read' : 'unread';
?>

<div class="nh-notification-item nh-notification-item--<?php echo esc_attr( $status_class ); ?>" data-id="<?php echo (int) $id; ?>">
	<div class="nh-notification-item__header">
		<h3 class="nh-notification-item__title"><?php echo esc_html( $title ); ?></h3>
		<span class="nh-notification-item__badge"><?php echo esc_html( $source ); ?></span>
	</div>

	<div class="nh-notification-item__body">
		<p><?php echo wp_kses_post( $message ); ?></p>
	</div>

	<div class="nh-notification-item__footer">
		<span class="nh-notification-item__time"><?php echo esc_html( $created_at ); ?></span>
		<div class="nh-notification-item__actions">
			<?php if ( $status === 0 ) : ?>
				<button class="button button-small nh-mark-read" data-id="<?php echo (int) $id; ?>">
					<?php esc_html_e( 'Mark as Read', 'notification-hub' ); ?>
				</button>
			<?php endif; ?>
			<button class="button button-small button-link-delete nh-delete" data-id="<?php echo (int) $id; ?>">
				<?php esc_html_e( 'Delete', 'notification-hub' ); ?>
			</button>
		</div>
	</div>
</div>
