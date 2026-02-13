<?php
/**
 * Stats Card Template
 *
 * @package Notification_Hub
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title = $args['title'] ?? '';
$value = $args['value'] ?? 0;
$icon  = $args['icon'] ?? 'chart-bar';
$color = $args['color'] ?? 'blue';
?>

<div class="nh-stat-card nh-stat-card--<?php echo esc_attr( $color ); ?>">
	<div class="nh-stat-card__icon">
		<span class="dashicons dashicons-<?php echo esc_attr( $icon ); ?>"></span>
	</div>
	<div class="nh-stat-card__content">
		<h3 class="nh-stat-card__title"><?php echo esc_html( $title ); ?></h3>
		<p class="nh-stat-card__value"><?php echo esc_html( $value ); ?></p>
	</div>
</div>
