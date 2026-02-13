<?php
/**
 * License Box Partial
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$license_key = get_option( 'nh_license_key', '' );
$status      = get_option( 'nh_license_status', 'inactive' );
?>

<div class="nh-license-box">
	<h3><?php esc_html_e( 'License Key', 'notification-hub' ); ?></h3>
	
	<p>
		<input 
			type="text" 
			id="nh-license-key" 
			name="nh_license_key" 
			value="<?php echo esc_attr( $license_key ); ?>" 
			class="regular-text"
			placeholder="XXXX-XXXX-XXXX-XXXX"
		/>
	</p>

	<p>
		<span class="nh-license-status <?php echo esc_attr( 'status-' . $status ); ?>">
			<?php
			if ( $status === 'active' ) {
				echo '✅ ' . esc_html__( 'Active', 'notification-hub' );
			} else {
				echo '❌ ' . esc_html__( 'Inactive', 'notification-hub' );
			}
			?>
		</span>
	</p>

	<p>
		<button type="button" id="nh-save-license" class="button button-primary">
			<?php esc_html_e( 'Save License', 'notification-hub' ); ?>
		</button>
		
		<?php if ( ! empty( $license_key ) ) : ?>
			<button type="button" id="nh-revoke-license" class="button button-secondary">
				<?php esc_html_e( 'Revoke License', 'notification-hub' ); ?>
			</button>
		<?php endif; ?>
	</p>
</div>
