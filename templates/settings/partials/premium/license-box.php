<?php
/**
 * Premium License Box
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

$active_tab = isset($active_tab) ? (string) $active_tab : 'general';

$has_license_class = class_exists('NH_License');

$current_key   = $has_license_class && method_exists('NH_License', 'get_key') ? (string) \NH_License::get_key() : '';
$is_premium_on = $has_license_class && method_exists('NH_License', 'is_pro') ? (bool) \NH_License::is_pro() : false;
$server_url    = $has_license_class && method_exists('NH_License', 'get_server_url') ? (string) \NH_License::get_server_url() : '';
$state         = $has_license_class && method_exists('NH_License', 'get_state') ? (array) \NH_License::get_state() : [];

// Mask key.
$masked = '';
if (!empty($current_key)) {
    $len = strlen($current_key);
    $masked = $len > 8
        ? substr($current_key, 0, 4) . str_repeat('•', $len - 8) . substr($current_key, -4)
        : $current_key;
}

$has_saved = (!empty($server_url) || !empty($current_key));

$did_save   = isset($_GET['nh_license_saved']);
$did_revoke = isset($_GET['nh_license_revoked']);
$error      = isset($_GET['nh_license_error']) ? sanitize_key(wp_unslash($_GET['nh_license_error'])) : '';

$status = '';
if (is_array($state) && isset($state['status'])) {
    $status = sanitize_key((string) $state['status']);
}

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
    if ($status === 'active') {
        $primary_notice_type = 'success';
        $primary_notice_text = esc_html__('License activated.', 'notification-hub');
        $primary_auto_hide   = true;
    } elseif (!empty($state['message'])) {
        $primary_notice_type = 'warning';
        $primary_notice_text = sanitize_text_field((string) $state['message']);
        $primary_auto_hide   = false;
    } else {
        $primary_notice_type = 'info';
        $primary_notice_text = esc_html__('License saved. Please refresh to verify status.', 'notification-hub');
        $primary_auto_hide   = true;
    }
}

$show_tip = false;
$hint = '';
if (!$primary_notice_text && ($did_save || $did_revoke || $error !== '')) {
    $hint = ($has_license_class && method_exists('NH_License', 'status_hint')) ? (string) \NH_License::status_hint($state) : '';
    $show_tip = ($hint !== '');
}
?>

<div id="nh-license-box" class="postbox nh-license-box" data-tab="premium">
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
                <a href="#" id="nh-license-edit" class="button button-secondary" data-label-edit="<?php echo esc_attr__('Edit', 'notification-hub'); ?>" data-label-cancel="<?php echo esc_attr__('Cancel', 'notification-hub'); ?>">
                    <?php esc_html_e('Edit', 'notification-hub'); ?>
                </a>
            <?php endif; ?>
        </span>
    </h2>

    <div class="inside">
        <?php if (!$has_license_class) : ?>
            <div class="notice notice-warning">
                <p><?php esc_html_e('Premium module is not loaded yet. You can still save server URL and key; premium status will activate after the premium module is available.', 'notification-hub'); ?></p>
            </div>
        <?php endif; ?>

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
