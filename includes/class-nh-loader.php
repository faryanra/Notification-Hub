<?php
// Prevent direct access to this file for security reasons
if (! defined('ABSPATH')) {
    exit;  // Exit if accessed directly to avoid security vulnerabilities
}

/**
 * Class NH_Loader
 * This class handles loading all actions, filters, and initializations for the plugin.
 * It keeps the main file clean by centralizing hooks here.
 */
class NH_Loader
{

    /**
     * Constructor: Sets up all necessary hooks when the class is instantiated.
     * Why? To ensure hooks are added only when needed, improving performance.
     */
    public function __construct()
    {
        // Add admin menu page for the notification dashboard
        add_action('admin_menu', [$this, 'add_menu']);  // Hooks into admin_menu to add our custom page

        // Add badge to admin bar for quick notification count
        add_action('admin_bar_menu', [$this, 'add_bar_badge'], 999);  // High priority (999) to add it at the end of the bar

        // Enqueues CSS/JS only on admin pages
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);  // Enqueues CSS/JS only on admin pages

        // Register settings for Telegram
        add_action('admin_init', [$this, 'register_settings']);

        new NH_Collector();  // Instantiates the collector class to set up its event hooks
    }

    /**
     * Enqueues styles and scripts for the plugin.
     * Why? To load CSS for styling the dashboard and JS for dynamic updates like badge count.
     * @param string $hook The current admin page hook.
     */
    public function enqueue_assets($hook)
    {
        if ($hook === 'toplevel_page_nh-hub') {  // Load only on our dashboard page to optimize performance
            // Enqueue CSS
            wp_enqueue_style('nh-styles', NH_URL . 'assets/css/nh-styles.css', [], NH_VERSION);  // Custom styles for table and UX

            // Enqueue JS
            wp_enqueue_script('nh-scripts', NH_URL . 'assets/js/nh-scripts.js', ['jquery'], NH_VERSION, true);  // Custom JS with jQuery dependency, loaded in footer
        }
    }

    /**
     * Adds the menu page to the WordPress admin sidebar.
     * Why? To provide a central UI for users to view notifications, improving UX.
     */
    public function add_menu()
    {
        add_menu_page(
            'Notification Hub',          // Page title in browser
            'Notifications',             // Menu title in sidebar
            'manage_options',            // Capability required (admin level)
            'nh-hub',                    // Unique slug for the page
            [$this, 'dashboard_page'], // Callback function to render the page
            'dashicons-bell',            // Icon for the menu (WordPress dashicon for bell, relevant to notifications)
            25                           // Position in sidebar (after Posts, before Media)
        );
        add_submenu_page(
            'nh-hub',                   // Parent slug
            'Notification Settings',    // Page title
            'Settings',                 // Menu title
            'manage_options',           // Capability
            'nh-settings',              // Slug
            [$this, 'settings_page']  // Callback
        );
    }

    /**
     * Registers settings for Telegram.
     * Why? To save token and chat ID securely using settings API.
     */
    public function register_settings()
    {
        register_setting('nh_settings_group', 'nh_telegram_token');  // Register token setting
        register_setting('nh_settings_group', 'nh_telegram_chat_id');  // Register chat ID setting
        register_setting( 'nh_settings_group', 'nh_pro_license_key' );
    }

    /**
     * Renders the settings page.
     * Why? To provide UX for entering Telegram details.
     */
    public function settings_page()
    {
        echo '<div class="wrap">';
        echo '<h1>Notification Settings</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('nh_settings_group');  // Security fields
        do_settings_sections('nh_settings_group');  // Sections (none for simple form)
        echo '<table class="form-table">';
        echo '<tr><th>Telegram Bot Token</th><td><input type="text" name="nh_telegram_token" value="' . esc_attr(get_option('nh_telegram_token')) . '" /> <p class="description">Get from BotFather.</p></td></tr>';
        echo '<tr><th>Telegram Chat ID</th><td><input type="text" name="nh_telegram_chat_id" value="' . esc_attr(get_option('nh_telegram_chat_id')) . '" /> <p class="description">Your chat ID.</p></td></tr>';
        echo '</table>';
        submit_button();  // Save button
        echo '</form>';
        echo '</div>';
        // After main form
        echo '<h2>Test Telegram</h2>';
        echo '<form method="post">';
        if (isset($_POST['nh_test_telegram'])) {
            $test_message = 'Test from Notification Hub';
            if (NH_Notifier::send_telegram($test_message)) {
                echo '<p style="color: green;">Test message sent successfully!</p>';
            } else {
                echo '<p style="color: red;">Test failed. Check debug.log.</p>';
            }
        }
        echo '<input type="submit" name="nh_test_telegram" value="Send Test Message" class="button">';
        echo '</form>';
        // Pro license section
        echo '<h2>Pro License</h2>';
        $license = get_option('nh_pro_license_key', '');
        if (isset($_POST['nh_save_license'])) {
            $new_license = sanitize_text_field($_POST['nh_license_key']);
            if (NH_Pro_Features::validate_license($new_license)) {
                update_option('nh_pro_license_key', $new_license);
                echo '<p style="color: green;">License activated!</p>';
            } else {
                echo '<p style="color: red;">Invalid license.</p>';
            }
        }
        echo '<form method="post">';
        echo '<input type="text" name="nh_license_key" value="' . esc_attr($license) . '" placeholder="Enter Pro License Key" />';
        echo '<input type="submit" name="nh_save_license" value="Activate Pro" class="button">';
        echo '</form>';
        if (! NH_Pro_Features::is_pro_active()) {
            echo '<p>Upgrade to Pro for Telegram, AI, and more! Buy from CodeCanyon.</p>';
        }
    }

    /**
     * Renders the content of the dashboard page.
     * Why? This is the main UI where notifications will be listed; start simple for good UX.
     */
    public function dashboard_page()
    {
        global $wpdb;  // WordPress database object

        $table_name = $wpdb->prefix . 'nh_notifications';  // Table name

        // Query to get all notifications, ordered by created_at DESC, limit 20 for MVP
        $notifications = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 20");  // Fetches recent notifications

        // Wrap content
        echo '<div class="wrap">';
        echo '<h1>Notification Hub</h1>';  // Main heading

        // Notification table for UX
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Title</th><th>Message</th><th>Created At</th><th>Status</th></tr></thead>';
        echo '<tbody>';
        if ($notifications) {
            foreach ($notifications as $notif) {
                // Escape outputs for security and UX (color-coded status)
                $status_class = ($notif->status === 'new') ? 'style="color: red;"' : 'style="color: green;"';  // Red for new, green for read
                echo '<tr>';
                echo '<td>' . esc_html($notif->id) . '</td>';
                echo '<td>' . esc_html($notif->title) . '</td>';
                echo '<td>' . wp_kses_post($notif->message) . '</td>';  // Allow safe HTML in message
                echo '<td>' . esc_html($notif->created_at) . '</td>';
                echo '<td ' . $status_class . '>' . esc_html($notif->status) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5">No notifications yet.</td></tr>';  // Placeholder if empty
        }
        echo '</tbody></table>';

        echo '</div>';
    }

    /**
     * Gets the count of unread notifications for the badge.
     * Why? To display dynamic count in admin bar for real-time UX.
     * @return int Unread count.
     */
    public function get_unread_count()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'nh_notifications';
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'new'");  // Counts only 'new' status
    }

    /**
     * Adds a badge to the admin bar showing notification count.
     * Updated to use dynamic count.
     * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
     */
    public function add_bar_badge($wp_admin_bar)
    {
        $count = $this->get_unread_count();  // Get dynamic unread count
        $title = 'Notifications <span class="nh-count">' . $count . '</span>';  // Title with count
        if ($count > 0) {
            $title = '<span style="color: red;">' . $title . '</span>';  // Red if unread > 0 for UX attention
        }
        $args = [
            'id'    => 'nh-badge',
            'title' => $title,
            'href'  => admin_url('admin.php?page=nh-hub'),
        ];
        $wp_admin_bar->add_node($args);
    }
}
