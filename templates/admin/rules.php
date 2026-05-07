<?php
/**
 * Rules Manager Template (Automation MVP).
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

$repo = new \NotificationHub\Repositories\RulesRepository();

$edit_id = isset($_GET['edit']) ? absint(wp_unslash($_GET['edit'])) : 0;
$edit_row = $edit_id > 0 ? $repo->getById($edit_id) : null;

$default_conditions = [
    'all' => [
        ['field' => 'source', 'op' => 'eq', 'value' => 'woocommerce'],
        ['field' => 'type', 'op' => 'eq', 'value' => 'order_new'],
    ],
];
$default_actions = [
    'set' => [
        'important' => true,
    ],
    'dispatch' => [
        'channels' => ['telegram'],
        'mode' => 'queued',
    ],
];

$conditions_value = $edit_row && isset($edit_row['conditions'])
    ? (string) $edit_row['conditions']
    : wp_json_encode($default_conditions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$actions_value = $edit_row && isset($edit_row['actions'])
    ? (string) $edit_row['actions']
    : wp_json_encode($default_actions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

$conditions_pretty = json_decode((string) $conditions_value, true);
if (is_array($conditions_pretty)) {
    $conditions_value = (string) wp_json_encode($conditions_pretty, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

$actions_pretty = json_decode((string) $actions_value, true);
if (is_array($actions_pretty)) {
    $actions_value = (string) wp_json_encode($actions_pretty, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

$notice_map = [
    'rule_saved'      => esc_html__('Rule created.', 'notification-hub'),
    'rule_updated'    => esc_html__('Rule updated.', 'notification-hub'),
    'rule_deleted'    => esc_html__('Rule deleted.', 'notification-hub'),
    'rule_duplicated' => esc_html__('Rule duplicated.', 'notification-hub'),
];

$error_code = isset($_GET['rule_error']) ? sanitize_key(wp_unslash($_GET['rule_error'])) : '';
$error_text = '';
if ($error_code === 'invalid_json') {
    $error_text = esc_html__('Invalid JSON in conditions/actions.', 'notification-hub');
} elseif ($error_code === 'missing_name') {
    $error_text = esc_html__('Rule name is required.', 'notification-hub');
} elseif ($error_code === 'missing_id') {
    $error_text = esc_html__('Rule id is missing.', 'notification-hub');
}
?>

<div class="wrap">
    <h1><?php esc_html_e('Automation Rules', 'notification-hub'); ?></h1>

    <?php foreach ($notice_map as $key => $msg) : ?>
        <?php $value = isset($_GET[$key]) ? sanitize_text_field(wp_unslash($_GET[$key])) : ''; ?>
        <?php if ($value === '1') : ?>
            <div class="notice notice-success is-dismissible"><p><?php echo esc_html($msg); ?></p></div>
        <?php elseif ($value === '0') : ?>
            <div class="notice notice-error is-dismissible"><p><?php echo esc_html__('Operation failed.', 'notification-hub'); ?></p></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php if ($error_text !== '') : ?>
        <div class="notice notice-error is-dismissible"><p><?php echo esc_html($error_text); ?></p></div>
    <?php endif; ?>

    <h2><?php echo $edit_row ? esc_html__('Edit Rule', 'notification-hub') : esc_html__('Create Rule', 'notification-hub'); ?></h2>

    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php if ($edit_row) : ?>
            <?php wp_nonce_field('nh_update_rule_' . (int) $edit_row['id']); ?>
            <input type="hidden" name="action" value="nh_update_rule" />
            <input type="hidden" name="id" value="<?php echo (int) $edit_row['id']; ?>" />
        <?php else : ?>
            <?php wp_nonce_field('nh_save_rule'); ?>
            <input type="hidden" name="action" value="nh_save_rule" />
        <?php endif; ?>

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="nh_rule_name"><?php esc_html_e('Name', 'notification-hub'); ?></label></th>
                <td>
                    <input id="nh_rule_name" name="name" type="text" class="regular-text" required value="<?php echo esc_attr($edit_row['name'] ?? ''); ?>" />
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="nh_rule_enabled"><?php esc_html_e('Enabled', 'notification-hub'); ?></label></th>
                <td>
                    <?php
                    $is_enabled = $edit_row ? !empty($edit_row['enabled']) : true;
                    ?>
                    <label>
                        <input id="nh_rule_enabled" name="enabled" type="checkbox" value="1" <?php checked($is_enabled); ?> />
                        <?php esc_html_e('Enable this rule', 'notification-hub'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="nh_rule_priority"><?php esc_html_e('Priority', 'notification-hub'); ?></label></th>
                <td>
                    <input id="nh_rule_priority" name="priority" type="number" class="small-text" value="<?php echo esc_attr($edit_row['priority'] ?? 100); ?>" />
                    <p class="description"><?php esc_html_e('Higher number runs first.', 'notification-hub'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="nh_rule_conditions"><?php esc_html_e('Conditions (JSON)', 'notification-hub'); ?></label></th>
                <td>
                    <textarea id="nh_rule_conditions" name="conditions" rows="10" class="large-text code"><?php echo esc_textarea($conditions_value); ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="nh_rule_actions"><?php esc_html_e('Actions (JSON)', 'notification-hub'); ?></label></th>
                <td>
                    <textarea id="nh_rule_actions" name="actions" rows="10" class="large-text code"><?php echo esc_textarea($actions_value); ?></textarea>
                </td>
            </tr>
        </table>

        <?php
        submit_button($edit_row ? esc_html__('Update Rule', 'notification-hub') : esc_html__('Save Rule', 'notification-hub'));
        if ($edit_row) {
            echo ' <a class="button button-secondary" href="' . esc_url(admin_url('admin.php?page=nh-rules')) . '">' . esc_html__('Cancel', 'notification-hub') . '</a>';
        }
        ?>
    </form>

    <hr />

    <h2><?php esc_html_e('Saved Rules', 'notification-hub'); ?></h2>
    <?php $rows = $repo->listAll(); ?>
    <?php if (!empty($rows)) : ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Name', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Enabled', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Priority', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Conditions', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Actions', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Updated', 'notification-hub'); ?></th>
                    <th><?php esc_html_e('Manage', 'notification-hub'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) : ?>
                    <?php
                    $rule_id = (int) ($row['id'] ?? 0);
                    $edit_url = admin_url('admin.php?page=nh-rules&edit=' . $rule_id);
                    $dup_url = wp_nonce_url(admin_url('admin-post.php?action=nh_duplicate_rule&id=' . $rule_id), 'nh_duplicate_rule_' . $rule_id);
                    $delete_url = wp_nonce_url(admin_url('admin-post.php?action=nh_delete_rule&id=' . $rule_id), 'nh_delete_rule_' . $rule_id);

                    $cond_summary = trim(preg_replace('/\s+/', ' ', (string) ($row['conditions'] ?? '')));
                    $act_summary = trim(preg_replace('/\s+/', ' ', (string) ($row['actions'] ?? '')));
                    if (strlen($cond_summary) > 140) {
                        $cond_summary = substr($cond_summary, 0, 140) . '...';
                    }
                    if (strlen($act_summary) > 140) {
                        $act_summary = substr($act_summary, 0, 140) . '...';
                    }
                    ?>
                    <tr>
                        <td><?php echo esc_html((string) $rule_id); ?></td>
                        <td><?php echo esc_html((string) ($row['name'] ?? '')); ?></td>
                        <td><?php echo !empty($row['enabled']) ? esc_html__('Yes', 'notification-hub') : esc_html__('No', 'notification-hub'); ?></td>
                        <td><?php echo esc_html((string) ((int) ($row['priority'] ?? 0))); ?></td>
                        <td><code><?php echo esc_html($cond_summary); ?></code></td>
                        <td><code><?php echo esc_html($act_summary); ?></code></td>
                        <td><?php echo esc_html((string) ($row['updated_at'] ?? '')); ?></td>
                        <td>
                            <a class="button button-secondary" href="<?php echo esc_url($edit_url); ?>"><?php esc_html_e('Edit', 'notification-hub'); ?></a>
                            <a class="button" href="<?php echo esc_url($dup_url); ?>"><?php esc_html_e('Duplicate', 'notification-hub'); ?></a>
                            <a class="button button-link-delete nh-confirm" href="<?php echo esc_url($delete_url); ?>" data-confirm="<?php echo esc_attr__('Delete this rule?', 'notification-hub'); ?>"><?php esc_html_e('Delete', 'notification-hub'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php esc_html_e('No rules yet.', 'notification-hub'); ?></p>
    <?php endif; ?>
</div>

