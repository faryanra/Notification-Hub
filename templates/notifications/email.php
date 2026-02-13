<?php
/**
 * Email Notification Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
</head>
<body>
	<h2><?php echo esc_html( $title ); ?></h2>
	<p><?php echo wp_kses_post( $message ); ?></p>
</body>
</html>
