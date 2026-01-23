<?php
/**
 * Premium License Box markup (fallback)
 *
 * This is a safety fallback used only if the moved markup file isn't packaged.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

if (!defined('ABSPATH')) {
    exit;
}

// If the moved markup exists, prefer it.
$moved = NH_PLUGIN_DIR . 'templates/partials/__moved/premium-license-box.php';
if (file_exists($moved)) {
    include $moved;
    return;
}

?>

<div id="nh-license-box" class="nh-license-box">
    <h2><?php esc_html_e('License', 'notification-hub'); ?></h2>

    <?php
    $saved  = isset($_GET['nh_license_saved']) ? sanitize_text_field(wp_unslash($_GET['nh_license_saved'])) : '';
    $revoked = isset($_GET['nh_license_revoked']) ? sanitize_text_field(wp_unslash($_GET['nh_license_revoked'])) : '';
    $error  = isset($_GET['nh_license_error']) ? sanitize_text_field(wp_unslash($_GET['nh_license_error'])) : '';

    if ($saved === '1') :
        ?>
        <div class="notice notice-success is-dismissible nh-auto-hide" data-auto-hide="1">
            <p><?php esc_html_e('License saved successfully.', 'notification-hub'); ?></p>
        </div>
    <?php elseif ($revoked === '1') : ?>
        <div class="notice notice-success is-dismissible nh-auto-hide" data-auto-hide="1">
            <p><?php esc_html_e('License revoked successfully.', 'notification-hub'); ?></p>
        </div>
    <?php elseif ($error !== '') : ?>
        <div class="notice notice-error is-dismissible nh-auto-hide" data-auto-hide="1">
            <p>
                <?php
                if ($error === 'invalid_key') {
                    esc_html_e('Invalid license key format.', 'notification-hub');
                } else {
                    esc_html_e('License error occurred.', 'notification-hub');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <?php
    $server_url = class_exists('NH_License') && defined('NH_License::OPT_SERVER_URL')
        ? get_option(NH_License::OPT_SERVER_URL, '')
        : '';

    $license_key = class_exists('NH_License') && method_exists('NH_License', 'get_key')
        ? (string) NH_License::get_key()
        : '';

    $license_key = trim($license_key);
    $locked = $license_key !== '';
    ?>

    <div class="nh-license-actions">
        <button
            id="nh-license-edit"
            class="button"
            type="button"
            data-label-edit="<?php echo esc_attr__('Edit', 'notification-hub'); ?>"
            data-label-cancel="<?php echo esc_attr__('Cancel', 'notification-hub'); ?>"
        >
            <?php esc_html_e('Edit', 'notification-hub'); ?>
        </button>
    </div>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nh-license-form">
        <input type="hidden" name="action" value="nh_save_license_bundle">
        <?php wp_nonce_field('nh_save_license_bundle'); ?>

        <div class="nh-license-locked-wrap <?php echo $locked ? 'is-locked' : ''; ?>">
            <table class="form-table">
                <tr>
                    <th>
                        <label for="nh_license_server_url"><?php esc_html_e('License Server URL', 'notification-hub'); ?></label>
                    </th>
                    <td>
                        <input
                            name="nh_license_server_url"
                            id="nh_license_server_url"
                            type="url"
                            class="regular-text"
                            value="<?php echo esc_attr($server_url); ?>"
                            data-lockable="1"
                            <?php echo $locked ? 'readonly="readonly"' : ''; ?>
                        >
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="nh_license_key"><?php esc_html_e('License Key', 'notification-hub'); ?></label>
                    </th>
                    <td>
                        <input
                            name="nh_license_key"
                            id="nh_license_key"
                            type="text"
                            class="regular-text"
                            value="<?php echo esc_attr($license_key); ?>"
                            data-lockable="1"
                            <?php echo $locked ? 'readonly="readonly"' : ''; ?>
                        >
                        <p class="description">
                            <?php esc_html_e('Format: NH-PRO-XXXX-XXXX', 'notification-hub'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <p>
            <button type="submit" class="button button-primary" <?php echo $locked ? 'disabled="disabled"' : ''; ?>>
                <?php esc_html_e('Save License', 'notification-hub'); ?>
            </button>
        </p>
    </form>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="nh-license-revoke-form">
        <input type="hidden" name="action" value="nh_license_revoke">
        <?php wp_nonce_field('nh_license_revoke'); ?>

        <p>
            <button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to revoke this license?', 'notification-hub')); ?>');">
                <?php esc_html_e('Revoke License', 'notification-hub'); ?>
            </button>
        </p>
    </form>
</div>
