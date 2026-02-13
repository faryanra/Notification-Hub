<?php
/**
 * Settings Tabs Partial
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_tab = $_GET['tab'] ?? 'general';

$tabs = array(
	'general' => __( 'General', 'notification-hub' ),
	'premium' => __( 'Premium', 'notification-hub' ),
);
?>

<h2 class="nav-tab-wrapper">
	<?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
		<a 
			href="<?php echo esc_url( admin_url( 'admin.php?page=notification-hub-settings&tab=' . $tab_key ) ); ?>"
			class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>"
		>
			<?php echo esc_html( $tab_label ); ?>
		</a>
	<?php endforeach; ?>
</h2>
