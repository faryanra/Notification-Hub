<?php
/**
 * Analytics Template (counts by day).
 *
 * @package Notification_Hub
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!current_user_can('manage_options')) {
    wp_die(esc_html__('Not allowed', 'notification-hub'));
}

$rangeRaw = isset($_GET['range']) ? sanitize_key(wp_unslash($_GET['range'])) : '7d';
$allowedRanges = ['7d', '30d'];
$range = in_array($rangeRaw, $allowedRanges, true) ? $rangeRaw : '7d';

$service = new \NotificationHub\Services\MetricsService();

try {
    $metrics = $service->getCountsByDay($range);
} catch (\InvalidArgumentException $e) {
    $metrics = $service->getCountsByDay('7d');
    $range = '7d';
}

$days = isset($metrics['days']) && is_array($metrics['days']) ? $metrics['days'] : [];
$max = 1;
foreach ($days as $row) {
    $max = max($max, (int) ($row['count'] ?? 0));
}

$baseUrl = admin_url('admin.php?page=nh-analytics');
$exportUrl = wp_nonce_url(
    admin_url('admin-post.php?action=nh_export_metrics&range=' . rawurlencode($range)),
    'nh_export_metrics'
);
$class_7d = $range === '7d' ? 'button-primary' : 'button-secondary';
$class_30d = $range === '30d' ? 'button-primary' : 'button-secondary';
?>

<div class="wrap nh-analytics">
    <h1><?php esc_html_e('Notification Analytics', 'notification-hub'); ?></h1>

    <div class="nh-analytics-toolbar">
        <div class="nh-analytics-ranges">
            <a class="button <?php echo esc_attr($class_7d); ?>" href="<?php echo esc_url(add_query_arg('range', '7d', $baseUrl)); ?>"><?php esc_html_e('7 Days', 'notification-hub'); ?></a>
            <a class="button <?php echo esc_attr($class_30d); ?>" href="<?php echo esc_url(add_query_arg('range', '30d', $baseUrl)); ?>"><?php esc_html_e('30 Days', 'notification-hub'); ?></a>
        </div>

        <a class="button button-secondary" href="<?php echo esc_url($exportUrl); ?>">
            <span class="dashicons dashicons-download nh-export-csv__icon" aria-hidden="true"></span>
            <?php esc_html_e('Export CSV', 'notification-hub'); ?>
        </a>
    </div>

    <div class="nh-analytics-summary">
        <div class="nh-analytics-card">
            <div class="nh-analytics-label"><?php esc_html_e('Total', 'notification-hub'); ?></div>
            <div class="nh-analytics-value"><?php echo esc_html((string) ((int) ($metrics['total'] ?? 0))); ?></div>
        </div>
        <div class="nh-analytics-card">
            <div class="nh-analytics-label"><?php esc_html_e('From', 'notification-hub'); ?></div>
            <div class="nh-analytics-value"><?php echo esc_html((string) ($metrics['from'] ?? '')); ?></div>
        </div>
        <div class="nh-analytics-card">
            <div class="nh-analytics-label"><?php esc_html_e('To', 'notification-hub'); ?></div>
            <div class="nh-analytics-value"><?php echo esc_html((string) ($metrics['to'] ?? '')); ?></div>
        </div>
        <div class="nh-analytics-card">
            <div class="nh-analytics-label"><?php esc_html_e('Timezone', 'notification-hub'); ?></div>
            <div class="nh-analytics-value"><?php echo esc_html((string) ($metrics['timezone'] ?? '')); ?></div>
        </div>
    </div>

    <div class="nh-analytics-bars">
        <?php foreach ($days as $row) : ?>
            <?php
            $date = isset($row['date']) ? (string) $row['date'] : '';
            $count = isset($row['count']) ? (int) $row['count'] : 0;
            $pct = (int) round(($count / $max) * 100);
            ?>
            <div class="nh-analytics-bar-row">
                <div class="nh-analytics-bar-date"><?php echo esc_html($date); ?></div>
                <div class="nh-analytics-bar-track">
                    <span class="nh-analytics-bar-fill" style="width: <?php echo esc_attr((string) $pct); ?>%;"></span>
                </div>
                <div class="nh-analytics-bar-count"><?php echo esc_html((string) $count); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <table class="widefat striped nh-analytics-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Date', 'notification-hub'); ?></th>
                <th><?php esc_html_e('Count', 'notification-hub'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($days as $row) : ?>
                <tr>
                    <td><?php echo esc_html((string) ($row['date'] ?? '')); ?></td>
                    <td><?php echo esc_html((string) ((int) ($row['count'] ?? 0))); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
