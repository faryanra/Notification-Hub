<?php
/**
 * Slack notification template.
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
$link = isset($data['link']) ? (string) $data['link'] : '';
$source = isset($data['source_human']) ? wp_strip_all_tags((string) $data['source_human']) : '';
$type = isset($data['type_human']) ? wp_strip_all_tags((string) $data['type_human']) : '';
$actor = isset($data['context']['actor']) ? wp_strip_all_tags((string) $data['context']['actor']) : '';
$cta_label = isset($data['cta_label']) ? wp_strip_all_tags((string) $data['cta_label']) : '';

if ($title === '') {
    $title = __('New Notification', 'notification-hub');
}
if ($cta_label === '') {
    $cta_label = __('Open in WordPress', 'notification-hub');
}

$lines = [];
$lines[] = '[Notification Hub]';
$lines[] = $title;

if ($summary !== '') {
    $lines[] = '';
    $lines[] = $summary;
}

if ($actor !== '') {
    $lines[] = '';
    $lines[] = sprintf(__('Triggered by: %s', 'notification-hub'), $actor);
}

$meta_source = $source !== '' ? $source : __('Unknown', 'notification-hub');
$meta_type = $type !== '' ? $type : __('General', 'notification-hub');
$lines[] = '';
$lines[] = sprintf(__('Source: %1$s | Event: %2$s', 'notification-hub'), $meta_source, $meta_type);

if ($link !== '') {
    $lines[] = sprintf(__('Action: <%1$s|%2$s>', 'notification-hub'), esc_url_raw($link), $cta_label);
}

echo trim(implode("\n", $lines));
