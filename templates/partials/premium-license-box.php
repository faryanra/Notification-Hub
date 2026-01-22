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

// Presenter (UI decisions).
if (!class_exists('NH_License_Presenter')) {
    $presenter_file = NH_PLUGIN_DIR . 'modules/license/presenters/license-presenter.php';
    if (file_exists($presenter_file)) {
        require_once $presenter_file;
    }
}

$vm = [];
$primary_notice = ['type' => '', 'text' => '', 'auto_hide' => false];
$hint = '';

if (class_exists('NH_License_Presenter')) {
    $p = new NH_License_Presenter();
    $vm = $p->build_view_model(is_array($state) ? $state : []);
    $primary_notice = $p->build_primary_notice(
        [
            'error'      => $error,
            'did_save'   => $did_save,
            'did_revoke' => $did_revoke,
        ],
        $vm
    );

    if (empty($primary_notice['text']) && ($did_save || $did_revoke || $error !== '')) {
        $hint = $p->build_hint($vm);
    }
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
        <?php if (!empty($primary_notice['text'])) : ?>
            <div class="notice notice-<?php echo esc_attr((string) $primary_notice['type']); ?> is-dismissible <?php echo !empty($primary_notice['auto_hide']) ? 'nh-auto-hide' : ''; ?>" <?php echo !empty($primary_notice['auto_hide']) ? 'data-auto-hide="1"' : ''; ?>>
                <p><?php echo esc_html((string) $primary_notice['text']); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($hint !== '') : ?>
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
