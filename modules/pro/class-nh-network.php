<?php
if (!defined('ABSPATH')) exit;

class NH_Network_Settings {
    public function __construct() {
        if (is_multisite() && is_main_site()) {
            add_action('network_admin_menu', [$this, 'menu']);
            add_action('network_admin_edit_nh_save_network', [$this, 'save']);
        }
    }

    public function menu() {
        add_menu_page(
            __('Notification Hub (Network)','notification-hub'),
            __('Notification Hub','notification-hub'),
            'manage_network_options',
            'nh-network',
            [$this,'render'],
            'dashicons-megaphone',
            58
        );
    }

    public function render() {
        if (!current_user_can('manage_network_options')) wp_die(__('No permission','notification-hub'));
        $opt = get_site_option('nh_network_policy', [
            'email_to' => '',
            'retention_days' => '',
            'channels' => ['email','telegram','slack'],
        ]);
        ?>
        <div class="wrap">
          <h1><?php _e('Notification Hub — Network Policy','notification-hub');?></h1>
          <form method="post" action="edit.php?action=nh_save_network">
            <?php wp_nonce_field('nh_network_policy'); ?>
            <table class="form-table" role="presentation">
              <tr>
                <th scope="row"><?php _e('Enforce Email To','notification-hub');?></th>
                <td><input type="email" name="email_to" value="<?php echo esc_attr($opt['email_to']);?>" class="regular-text"></td>
              </tr>
              <tr>
                <th scope="row"><?php _e('Enforce Retention Days','notification-hub');?></th>
                <td><input type="number" min="0" name="retention_days" value="<?php echo esc_attr($opt['retention_days']);?>"></td>
              </tr>
              <tr>
                <th scope="row"><?php _e('Allowed Channels','notification-hub');?></th>
                <td>
                  <?php
                  $all = ['email','telegram','slack','webhook'];
                  foreach ($all as $ch) {
                      printf(
                        '<label><input type="checkbox" name="channels[]" value="%1$s" %2$s> %1$s</label>&nbsp; ',
                        esc_attr($ch),
                        checked(in_array($ch, (array)$opt['channels'], true), true, false)
                      );
                  }
                  ?>
                </td>
              </tr>
            </table>
            <?php submit_button(__('Save Policy','notification-hub')); ?>
          </form>
        </div>
        <?php
    }

    public function save() {
        if (!current_user_can('manage_network_options')) wp_die(__('No permission','notification-hub'));
        check_admin_referer('nh_network_policy');

        $email_to = isset($_POST['email_to']) ? sanitize_email($_POST['email_to']) : '';
        $retention_days = isset($_POST['retention_days']) ? absint($_POST['retention_days']) : '';
        $channels = isset($_POST['channels']) ? array_values(array_intersect((array)$_POST['channels'], ['email','telegram','slack','webhook'])) : [];

        $opt = [
            'email_to' => $email_to,
            'retention_days' => $retention_days,
            'channels' => $channels,
        ];
        update_site_option('nh_network_policy', $opt);
        wp_safe_redirect(network_admin_url('admin.php?page=nh-network&updated=1'));
        exit;
    }
}
