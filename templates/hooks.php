<?php
/**
 * Hooks Manager Template
 *
 * Admin UI for creating, editing, testing, and deleting custom hooks.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) exit;

/**
 * Permission gate
 */
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('Not allowed', 'notification-hub'));
}

global $wpdb;
$table = $wpdb->prefix . 'nh_hooks';

/**
 * Prefill edit form (when ?edit={id} is present)
 */
$edit_id       = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_row      = null;
$edit_channels = [];

if ($edit_id > 0) {
    $edit_row = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$table} WHERE id=%d", $edit_id)
    );

    if ($edit_row) {
        $tmp = json_decode($edit_row->channels, true);
        $edit_channels = is_array($tmp) ? $tmp : [];
    }
}

/**
 * Admin notices (triggered by redirects: ?hook_saved=1, etc.)
 */
$notices = [
    'hook_saved'   => esc_html__('Hook created.', 'notification-hub'),
    'hook_updated' => esc_html__('Hook updated.', 'notification-hub'),
    'hook_deleted' => esc_html__('Hook deleted.', 'notification-hub'),
    'hook_tested'  => esc_html__('Test triggered.', 'notification-hub'),
];
?>

<div class="wrap">
    <h1><?php esc_html_e('Custom Hooks', 'notification-hub'); ?></h1>

    <?php foreach ($notices as $key => $msg): ?>
        <?php if (!empty($_GET[$key])): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo $msg; ?></p>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php
    /**
     * Hook create/update form
     *
     * - Create mode: action=nh_save_hook + nonce nh_save_hook
     * - Edit mode:   action=nh_update_hook + nonce nh_update_hook_{id}
     */
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php if ($edit_row): ?>
            <?php wp_nonce_field('nh_update_hook_' . $edit_row->id); ?>
            <input type="hidden" name="action" value="nh_update_hook">
            <input type="hidden" name="id" value="<?php echo intval($edit_row->id); ?>">
        <?php else: ?>
            <?php wp_nonce_field('nh_save_hook'); ?>
            <input type="hidden" name="action" value="nh_save_hook">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th>
                    <label for="nh_title"><?php esc_html_e('Title', 'notification-hub'); ?></label>
                </th>
                <td>
                    <input
                        name="title"
                        id="nh_title"
                        class="regular-text"
                        required
                        value="<?php echo esc_attr($edit_row->title ?? ''); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th>
                    <label for="nh_action"><?php esc_html_e('Action name', 'notification-hub'); ?></label>
                </th>
                <td>
                    <input
                        name="action_name"
                        id="nh_action"
                        class="regular-text"
                        required
                        value="<?php echo esc_attr($edit_row->action_name ?? ''); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Channels', 'notification-hub'); ?></th>
                <td>
                    <?php
                    /**
                     * Channel selection
                     *
                     * Note: Labels are i18n-ready and markup has no inline styles.
                     */
                    $channels = [
                        'email'    => esc_html__('Email', 'notification-hub'),
                        'telegram' => esc_html__('Telegram', 'notification-hub'),
                        'slack'    => esc_html__('Slack', 'notification-hub'),
                    ];

                    foreach ($channels as $key => $label) {
                        $checked = in_array($key, $edit_channels, true);

                        printf(
                            '<label class="nh-hook-channel"><input type="checkbox" name="channels[]" value="%s" %s> %s</label>',
                            esc_attr($key),
                            checked($checked, true, false),
                            $label
                        );
                    }
                    ?>
                </td>
            </tr>
        </table>

        <?php
        submit_button(
            $edit_row ? esc_html__('Update Hook', 'notification-hub') : esc_html__('Save Hook', 'notification-hub')
        );

        if ($edit_row) {
            echo ' <a href="' . esc_url(admin_url('admin.php?page=nh-hooks')) . '" class="button button-secondary">' .
                esc_html__('Cancel', 'notification-hub') .
            '</a>';
        }
        ?>
    </form>

    <hr>

    <?php
    /**
     * Saved hooks table (list + actions)
     */
    ?>
    <h2><?php esc_html_e('Saved Hooks', 'notification-hub'); ?></h2>

    <?php
    $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id DESC LIMIT 200");

    if ($rows):
        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Title', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Action', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Channels', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Status', 'notification-hub'); ?></th>
                    <th class="nh-hooks-actions-col"><?php esc_html_e('Actions', 'notification-hub'); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $r):
                    $chs = json_decode($r->channels, true) ?: [];

                    $test_nonce = wp_create_nonce('nh_test_hook_' . $r->id);
                    $delete_nonce = wp_create_nonce('nh_delete_hook_' . $r->id);

                    $test_url   = admin_url('admin-post.php?action=nh_test_hook&id=' . $r->id . '&_wpnonce=' . $test_nonce);
                    $delete_url = admin_url('admin-post.php?action=nh_delete_hook&id=' . $r->id . '&_wpnonce=' . $delete_nonce);
                    $edit_url   = admin_url('admin.php?page=nh-hooks&edit=' . $r->id);

                    $status_label = $r->status
                        ? esc_html__('Active', 'notification-hub')
                        : esc_html__('Inactive', 'notification-hub');
                    ?>
                    <tr>
                        <td><?php echo intval($r->id); ?></td>
                        <td><?php echo esc_html($r->title); ?></td>
                        <td><code><?php echo esc_html($r->action_name); ?></code></td>
                        <td><?php echo esc_html(implode(', ', $chs)); ?></td>
                        <td><?php echo $status_label; ?></td>
                        <td>
                            <a class="button" href="<?php echo esc_url($test_url); ?>">
                                <?php esc_html_e('Trigger Test', 'notification-hub'); ?>
                            </a>

                            <a class="button button-secondary" href="<?php echo esc_url($edit_url); ?>">
                                <?php esc_html_e('Edit', 'notification-hub'); ?>
                            </a>

                            <a
                                class="button button-link-delete nh-link-danger nh-confirm"
                                href="<?php echo esc_url($delete_url); ?>"
                                data-confirm="<?php echo esc_attr__('Delete this hook?', 'notification-hub'); ?>"
                            >
                                <?php esc_html_e('Delete', 'notification-hub'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p><?php esc_html_e('No hooks yet.', 'notification-hub'); ?></p>
    <?php endif; ?>
</div>
