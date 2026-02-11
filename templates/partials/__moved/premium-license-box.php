<?php
/**
 * Premium License Box Partial
 *
 * Premium-only UI partial rendered inside Settings → Premium tab.
 * This is designed to be easy to extract into the Premium ZIP by filename prefix.
 *
 * @package Notification_Hub
 * @since 1.7.1
 */

if (!defined('ABSPATH')) {
    exit;
}

// Premium requires the license class.
if (!class_exists('NH_License')) {
    return;
}

// Expect $active_tab from parent template.
$active_tab = isset($active_tab) ? (string) $active_tab : 'general';

$current_key   = NH_License::get_key();
$is_premium_on = NH_License::is_pro();
$server_url    = method_exists('NH_License', 'get_server_url') ? NH_License::get_server_url() : '';
$state         = method_exists('NH_License', 'get_state') ? NH_License::get_state() : [];

// Mask key (keep first 4 + last 4 visible).
$masked = '';
if (!empty($current_key)) {
    $len = strlen($current_key);
    $masked = $len > 8
        ? substr($current_key, 0, 4) . str_repeat('•', $len - 8) . substr($current_key, -4)
        : $current_key;
}

$has_saved = (!empty($server_url) || !empty($current_key));

// Detect action outcomes (URL flags).
$did_save   = isset($_GET['nh_license_saved']);
$did_revoke = isset($_GET['nh_license_revoked']);
$error      = isset($_GET['nh_license_error']) ? sanitize_key(wp_unslash($_GET['nh_license_error'])) : '';

// Try to read a normalized status from state.
$status = '';
if (is_array($state) && isset($state['status'])) {
    $status = sanitize_key((string) $state['status']);
}

// Decide what to show:
// - At most ONE primary notice.
// - Tips only when there is NO primary notice.
// - Never show server "active for domain" message as a warning.
$primary_notice_type = '';
$primary_notice_text = '';
$primary_auto_hide   = false;

if ($error === 'invalid_key') {
    $primary_notice_type = 'error';
    $primary_notice_text = esc_html__('Invalid license key format. Use: NH-PRO-XXXX-XXXX', 'notification-hub');
    $primary_auto_hide   = false;
} elseif ($did_revoke) {
    $primary_notice_type = 'info';
    $primary_notice_text = esc_html__('License revoked.', 'notification-hub');
    $primary_auto_hide   = true;
} elseif ($did_save) {
    // After save we show a single, status-aware message.
    if ($status === 'active') {
        $primary_notice_type = 'success';
        $primary_notice_text = esc_html__('License activated.', 'notification-hub');
        $primary_auto_hide   = true;
    } elseif (!empty($state['message'])) {
        // For non-active states, prefer the server/state message as primary.
        $primary_notice_type = 'warning';
        $primary_notice_text = sanitize_text_field((string) $state['message']);
        $primary_auto_hide   = false;
    } else {
        // Fallback.
        $primary_notice_type = 'info';
        $primary_notice_text = esc_html__('License saved. Please refresh to verify status.', 'notification-hub');
        $primary_auto_hide   = true;
    }
} else {
    // No recent action. Show nothing by default.
    $primary_notice_type = '';
}

// Tip only when there is no primary notice and only after an action.
$show_tip = false;
$hint = '';
if (!$primary_notice_text && ($did_save || $did_revoke || $error !== '')) {
    $hint = method_exists('NH_License', 'status_hint') ? NH_License::status_hint($state) : '';
    $show_tip = ($hint !== '');
}
?>

<div id="nh-license-box" class="postbox nh-license-box nh-tab <?php echo $active_tab === 'premium' ? 'is-active' : ''; ?>" data-tab="premium">
    <h2 class="hndle">
        <span class="nh-license-head-left">
            <span><?php esc_html_e('License & Premium Features', 'notification-hub'); ?></span>

            <?php if ($is_premium_on) : ?>
                <span class="nh-license-status nh-license-status--active">★ <?php esc_html_e('PREMIUM', 'notification-hub'); ?></span>
            <?php else : ?>
                <span class="nh-license-status nh-license-status--locked">☆ <?php esc_html_e('LOCKED', 'notification-hub'); ?></span>
            <?php endif; ?>

            <?php if ($has_saved) : ?>
                <span class="nh-license-saved-pill"><?php esc_html_e('Saved', 'notification-hub'); ?></span>
            <?php endif; ?>
        </span>

        <span class="nh-license-head-right">
            <?php if ($has_saved) : ?>
                <a
                    href="#"
                    id="nh-license-edit"
                    class="button button-secondary"
                    data-label-edit="<?php echo esc_attr__('Edit', 'notification-hub'); ?>"
                    data-label-cancel="<?php echo esc_attr__('Cancel', 'notification-hub'); ?>"
                >
                    <?php esc_html_e('Edit', 'notification-hub'); ?>
                </a>
            <?php endif; ?>
        </span>
    </h2>

    <div class="inside">
        <?php if ($primary_notice_text !== '') : ?>
            <div class="notice notice-<?php echo esc_attr($primary_notice_type); ?> is-dismissible <?php echo $primary_auto_hide ? 'nh-auto-hide' : ''; ?>" <?php echo $primary_auto_hide ? 'data-auto-hide="1"' : ''; ?>>
                <p><?php echo esc_html($primary_notice_text); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($show_tip) : ?>
            <div class="notice notice-info is-dismissible nh-auto-hide" data-auto-hide="1">
                <p><strong><?php esc_html_e('Tip:', 'notification-hub'); ?></strong> <?php echo esc_html($hint); ?></p>
            </div>
        <?php endif; ?>

        <p class="description">
            <?php esc_html_e('Enter your license server URL and key. Save once, then manage Premium features from this tab.', 'notification-hub'); ?>
        </p>

        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('nh_save_license_bundle'); ?>
            <input type="hidden" name="action" value="nh_save_license_bundle" />

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="nh_license_server_url"><?php esc_html_e('License Server URL', 'notification-hub'); ?></label></th>
                    <td>
                        <div class="nh-license-locked-wrap <?php echo $has_saved ? 'is-locked' : ''; ?>">
                            <input
                                type="url"
                                name="nh_license_server_url"
                                id="nh_license_server_url"
                                value="<?php echo esc_attr($server_url); ?>"
                                placeholder="<?php echo esc_attr__('https://your-domain.com/license/verify.php', 'notification-hub'); ?>"
                                class="regular-text nh-license-field"
                                data-lockable="1"
                                <?php echo $has_saved ? 'readonly' : ''; ?>
                            >
                        </div>

                        <?php if (!empty($server_url)) : ?>
                            <div class="nh-license-preview">
                                <?php
                                echo sprintf(
                                    /* translators: %s: server url */
                                    esc_html__('Saved URL: %s', 'notification-hub'),
                                    '<code>' . esc_html($server_url) . '</code>'
                                );
                                ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="nh_license_key"><?php esc_html_e('License Key', 'notification-hub'); ?></label></th>
                    <td>
                        <div class="nh-license-row">
                            <div class="nh-license-locked-wrap <?php echo $has_saved ? 'is-locked' : ''; ?>">
                                <input
                                    type="text"
                                    name="nh_license_key"
                                    id="nh_license_key"
                                    value=""
                                    placeholder="<?php echo esc_attr($masked !== '' ? $masked : esc_html__('Paste your license key (e.g. NH-PRO-AB12-CD34)', 'notification-hub')); ?>"
                                    class="regular-text nh-license-field"
                                    autocomplete="off"
                                    data-lockable="1"
                                    <?php echo $has_saved ? 'readonly' : ''; ?>
                                >
                            </div>

                            <div class="nh-license-actions">
                                <?php submit_button(esc_html__('Save', 'notification-hub'), 'primary', 'submit', false); ?>

                                <?php if ($has_saved) : ?>
                                    <a
                                        href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=nh_license_revoke&tab=premium'), 'nh_license_revoke')); ?>"
                                        class="button"
                                        onclick="return confirm('<?php echo esc_js(esc_html__('Revoke and clear local license data?', 'notification-hub')); ?>');"
                                    >
                                        <?php esc_html_e('Revoke', 'notification-hub'); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($has_saved) : ?>
                            <p class="description nh-license-hint">
                                <?php
                                echo sprintf(
                                    /* translators: %s: masked license key */
                                    esc_html__('Saved key: %s', 'notification-hub'),
                                    '<code>' . esc_html($masked !== '' ? $masked : '••••') . '</code>'
                                );
                                ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
