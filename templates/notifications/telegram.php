<?php
/**
 * Telegram notification template.
 *
 * Variables:
 * - $data (array)
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$title = isset($data['title']) ? wp_strip_all_tags((string) $data['title']) : '';
$summary = isset($data['summary']) ? wp_strip_all_tags((string) $data['summary']) : '';
$source = isset($data['source_human']) ? wp_strip_all_tags((string) $data['source_human']) : '';
$type = isset($data['type_human']) ? wp_strip_all_tags((string) $data['type_human']) : '';
$actor = isset($data['context']['actor']) ? wp_strip_all_tags((string) $data['context']['actor']) : '';

if ($title === '') {
    $title = __('New Notification', 'notification-hub');
}

$meta_source = $source !== '' ? $source : __('Unknown', 'notification-hub');
$meta_type = $type !== '' ? $type : __('General', 'notification-hub');

$lines = [];
$lines[] = '<b>' . esc_html__('Notification Hub', 'notification-hub') . '</b>';
$lines[] = '<b>' . esc_html($title) . '</b>';

if ($summary !== '') {
    $lines[] = esc_html($summary);
}

if ($actor !== '') {
    $lines[] = esc_html(sprintf(__('Triggered by: %s', 'notification-hub'), $actor));
}

$lines[] = esc_html(sprintf(__('Source: %1$s | Event: %2$s', 'notification-hub'), $meta_source, $meta_type));

echo trim(implode("\n", $lines));
