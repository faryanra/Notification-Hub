<?php
/**
 * Premium License Debug Panel (Partial)
 *
 * Shows internal cached state (read-only) to help debugging issues like:
 * - WAF / anti-bot challenge pages
 * - domain mismatch
 * - expired / revoked
 * - refresh TTL / grace window
 *
 * Premium-only partial (kept easy to extract by filename prefix).
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('NH_License')) {
    return;
}

$state = NH_License::get_state();
$status = isset($state['status']) ? (string) $state['status'] : 'unknown';
$domain = isset($state['domain']) ? (string) $state['domain'] : '';
$last_check = isset($state['last_check']) ? (int) $state['last_check'] : 0;
$grace_until = isset($state['grace_until']) ? (int) $state['grace_until'] : 0;
$license_hash = isset($state['license_hash']) ? (string) $state['license_hash'] : '';

$ttl = defined('NH_License::CHECK_TTL') ? (int) NH_License::CHECK_TTL : 0;
$next_refresh = ($last_check > 0 && $ttl > 0) ? ($last_check + $ttl) : 0;

$server_url = method_exists('NH_License', 'get_server_url') ? NH_License::get_server_url() : '';
$key = method_exists('NH_License', 'get_key') ? NH_License::get_key() : '';

$masked = '';
if ($key !== '') {
    $len = strlen($key);
    $masked = $len > 8
        ? substr($key, 0, 4) . str_repeat('•', $len - 8) . substr($key, -4)
        : $key;
}

$fmt = static function (int $ts): string {
    if ($ts <= 0) {
        return '—';
    }

    // Use WP timezone.
    return date_i18n('Y-m-d H:i:s', $ts);
};
?>

<div class="postbox nh-license-debug" style="margin-top:12px;">
    <h2 class="hndle"><span><?php esc_html_e('License Debug (Premium)', 'notification-hub'); ?></span></h2>
    <div class="inside">
        <p class="description">
            <?php esc_html_e('This panel is read-only and helps debugging licensing issues (WAF, domain mismatch, expired/revoked).', 'notification-hub'); ?>
        </p>

        <table class="widefat striped" role="presentation">
            <tbody>
                <tr>
                    <th style="width:220px;"><?php esc_html_e('Status', 'notification-hub'); ?></th>
                    <td><code><?php echo esc_html($status !== '' ? $status : 'unknown'); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Domain (licensed)', 'notification-hub'); ?></th>
                    <td><code><?php echo esc_html($domain !== '' ? $domain : '—'); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Server URL', 'notification-hub'); ?></th>
                    <td><code><?php echo esc_html($server_url !== '' ? $server_url : '—'); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Saved key (masked)', 'notification-hub'); ?></th>
                    <td><code><?php echo esc_html($masked !== '' ? $masked : '—'); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Last check', 'notification-hub'); ?></th>
                    <td><code><?php echo esc_html($fmt($last_check)); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Next refresh (TTL)', 'notification-hub'); ?></th>
                    <td><code><?php echo esc_html($fmt($next_refresh)); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Grace until', 'notification-hub'); ?></th>
                    <td><code><?php echo esc_html($fmt($grace_until)); ?></code></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('License hash', 'notification-hub'); ?></th>
                    <td><code><?php echo esc_html($license_hash !== '' ? $license_hash : '—'); ?></code></td>
                </tr>
            </tbody>
        </table>

        <p class="description" style="margin-top:10px;">
            <?php
            esc_html_e(
                'Tip: if Status is “inactive” with an anti-bot message, allowlist your verify endpoint in Cloudflare/WAF.',
                'notification-hub'
            );
            ?>
        </p>
    </div>
</div>