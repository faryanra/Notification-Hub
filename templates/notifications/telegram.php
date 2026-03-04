<?php
/**
 * Telegram notification template.
 *
 * Variables:
 * - $data (array)
 *
 * @package Notification_Hub
 * @since 1.6.3
 */

if (!defined('ABSPATH')) {
    exit;
}

$title   = isset($data['title']) ? (string) $data['title'] : '';
$summary = isset($data['summary']) ? (string) $data['summary'] : '';
$link    = isset($data['link']) ? (string) $data['link'] : '';
$src     = isset($data['source_human']) ? (string) $data['source_human'] : '';
$type    = isset($data['type_human']) ? (string) $data['type_human'] : '';
$actor   = isset($data['context']['actor']) ? (string) $data['context']['actor'] : '';

$lines = [];
if ($title !== '') {
    $lines[] = $title;
}
if ($actor !== '' && $summary !== '') {
    $lines[] = sprintf('By: %s', $actor);
    $lines[] = '“' . wp_strip_all_tags($summary) . '”';
} elseif ($summary !== '') {
    $lines[] = wp_strip_all_tags($summary);
}
if ($src !== '' || $type !== '') {
    $lines[] = sprintf('Source: %s • Type: %s', $src !== '' ? $src : '-', $type !== '' ? $type : '-');
}
if ($link !== '') {
    $lines[] = 'Review: ' . $link;
}

echo trim(implode("\n", $lines));