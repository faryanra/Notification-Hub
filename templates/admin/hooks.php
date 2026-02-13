<?php
/**
 * Custom Hooks Page Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1>
		<?php esc_html_e( 'Custom Hooks', 'notification-hub' ); ?>
		<a href="#" class="page-title-action" id="nh-add-hook"><?php esc_html_e( 'Add New', 'notification-hub' ); ?></a>
	</h1>

	<div class="nh-hooks-list">
		<?php if ( empty( $hooks ) ) : ?>
			<p><?php esc_html_e( 'No custom hooks found. Create your first custom hook!', 'notification-hub' ); ?></p>
		<?php else : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Hook Name', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Title', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Status', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Created', 'notification-hub' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'notification-hub' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $hooks as $hook ) : ?>
						<tr>
							<td><code><?php echo esc_html( $hook->hook_name ); ?></code></td>
							<td><?php echo esc_html( $hook->title ); ?></td>
							<td>
								<span class="nh-status nh-status-<?php echo esc_attr( $hook->status ); ?>">
									<?php echo esc_html( ucfirst( $hook->status ) ); ?>
								</span>
							</td>
							<td><?php echo esc_html( human_time_diff( strtotime( $hook->created_at ), current_time( 'timestamp' ) ) . ' ago' ); ?></td>
							<td>
								<button class="button button-small nh-test-hook" data-id="<?php echo esc_attr( $hook->id ); ?>">
									<?php esc_html_e( 'Test', 'notification-hub' ); ?>
								</button>
								<button class="button button-small nh-edit-hook" data-id="<?php echo esc_attr( $hook->id ); ?>">
									<?php esc_html_e( 'Edit', 'notification-hub' ); ?>
								</button>
								<button class="button button-small button-link-delete nh-delete-hook" data-id="<?php echo esc_attr( $hook->id ); ?>">
									<?php esc_html_e( 'Delete', 'notification-hub' ); ?>
								</button>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<!-- Add/Edit Hook Modal -->
	<div id="nh-hook-modal" class="nh-modal" style="display:none;">
		<div class="nh-modal-content">
			<span class="nh-modal-close">&times;</span>
			<h2 id="nh-modal-title"><?php esc_html_e( 'Add Custom Hook', 'notification-hub' ); ?></h2>
			<form id="nh-hook-form">
				<input type="hidden" id="nh-hook-id" value="">
				
				<p>
					<label for="nh-hook-name"><?php esc_html_e( 'Hook Name', 'notification-hub' ); ?></label>
					<input type="text" id="nh-hook-name" class="regular-text" required>
				</p>

				<p>
					<label for="nh-hook-title"><?php esc_html_e( 'Title', 'notification-hub' ); ?></label>
					<input type="text" id="nh-hook-title" class="regular-text" required>
				</p>

				<p>
					<label for="nh-hook-message"><?php esc_html_e( 'Message', 'notification-hub' ); ?></label>
					<textarea id="nh-hook-message" rows="4" class="large-text"></textarea>
				</p>

				<p>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Save', 'notification-hub' ); ?></button>
					<button type="button" class="button nh-modal-close"><?php esc_html_e( 'Cancel', 'notification-hub' ); ?></button>
				</p>
			</form>
		</div>
	</div>
</div>
