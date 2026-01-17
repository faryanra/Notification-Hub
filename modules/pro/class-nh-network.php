<?php
/**
 * NH_Network_Settings
 *
 * Network admin settings for multisite (Pro).
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Network_Settings {

    /**
     * Constructor.
     *
     * @since 1.6.2
     */
    public function __construct() {
        if (is_multisite() && is_main_site()) {
            add_action('network_admin_menu', [$this, 'menu']);
            add_action('network_admin_edit_nh_save_network', [$this, 'save']);
        }
    }

    /**
     * Register network admin menu.
     *
     * @since 1.6.2
     * @return void
     */
    public function menu(): void {
        add_menu_page(
            esc_html__('Notification Hub (Network)', 'notification-hub'),
            esc_html__('Notification Hub', 'notification-hub'),
            'manage_network_options',
            'nh-network',
            [$this, 'render'],
            'dashicons-megaphone',
            58
        );
    }

    /**
     * Render network policy page.
     *
     * @since 1.6.2
     * @return void
     */
    public function render(): void {
        if (!current_user_can('manage_network_options')) {
            wp_die(esc_html__('No permission.', 'notification-hub'));
        }

        $opt = get_site_option(
            'nh_network_policy',
            [
                'email_to'        => '',
                'retention_days'  => '',
                'channels'        => ['email', 'telegram', 'slack'],
            ]
        );

        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Notification Hub — Network Policy', 'notification-hub'); ?></h1>

            <form method="post" action="edit.php?action=nh_save_network">
                <?php wp_nonce_field('nh_network_policy'); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enforce Email To', 'notification-hub'); ?></th>
                        <td>
                            <input type="email" name="email_to" value="<?php echo esc_attr($opt['email_to']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Enforce Retention Days', 'notification-hub'); ?></th>
                        <td>
                            <input type="number" min="0" name="retention_days" value="<?php echo esc_attr($opt['retention_days']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Allowed Channels', 'notification-hub'); ?></th>
                        <td>
                            <?php
                            $all = ['email', 'telegram', 'slack', 'webhook'];

                            foreach ($all as $ch) {
                                printf(
                                    '<label><input type="checkbox" name="channels[]" value="%1$s" %2$s> %3$s</label>&nbsp; ',
                                    esc_attr($ch),
                                    checked(in_array($ch, (array) ($opt['channels'] ?? []), true), true, false),
                                    esc_html($ch)
                                );
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <?php submit_button(esc_html__('Save Policy', 'notification-hub')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Save network policy.
     *
     * @since 1.6.2
     * @return void
     */
    public function save(): void {
        if (!current_user_can('manage_network_options')) {
            wp_die(esc_html__('No permission.', 'notification-hub'));
        }

        check_admin_referer('nh_network_policy');

        $email_to       = isset($_POST['email_to']) ? sanitize_email(wp_unslash($_POST['email_to'])) : '';
        $retention_days = isset($_POST['retention_days']) ? absint(wp_unslash($_POST['retention_days'])) : '';

        $channels = [];
        if (isset($_POST['channels'])) {
            $channels = array_values(
                array_intersect(
                    (array) wp_unslash($_POST['channels']),
                    ['email', 'telegram', 'slack', 'webhook']
                )
            );
        }

        $opt = [
            'email_to'       => $email_to,
            'retention_days' => $retention_days,
            'channels'       => $channels,
        ];

        update_site_option('nh_network_policy', $opt);
        wp_safe_redirect(network_admin_url('admin.php?page=nh-network&updated=1'));
        exit;
    }
}