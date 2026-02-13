<?php
/**
 * Premium Settings Tab Partial
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php include NH_PLUGIN_DIR . 'templates/settings/partials/premium/top.php'; ?>

<?php if ( defined( 'NH_PRO_ACTIVE' ) && NH_PRO_ACTIVE ) : ?>
	<?php include NH_PLUGIN_DIR . 'templates/settings/partials/premium/license-box.php'; ?>
	<?php include NH_PLUGIN_DIR . 'templates/settings/partials/premium/settings-fields.php'; ?>
	<?php include NH_PLUGIN_DIR . 'templates/settings/partials/premium/license-debug-panel.php'; ?>
<?php else : ?>
	<?php include NH_PLUGIN_DIR . 'templates/settings/partials/premium/upgrade-panel.php'; ?>
<?php endif; ?>
