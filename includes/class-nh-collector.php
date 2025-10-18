<?php
// Prevent direct access to this file for security reasons
if (! defined('ABSPATH')) {
    exit;  // Exit if accessed directly to avoid security vulnerabilities
}

/**
 * Class NH_Collector
 * This class handles collecting notifications from various sources and storing them in the database.
 * It hooks into WordPress events to capture notifications.
 */
class NH_Collector
{

    /**
     * Constructor: Sets up hooks to collect notifications from sources like WP core.
     * Why? To automatically capture events without manual calls, improving integration.
     */
    public function __construct()
    {
        // Example hook: Capture new comments as notifications
        add_action('comment_post', [$this, 'on_new_comment']);  // Hooks into comment_post to capture new comments
        // Add more hooks later for WooCommerce, forms, etc.
    }

    /**
     * Adds a notification to the database.
     * Why? Central method to store notifications safely with sanitization and preparation.
     * @param string $source Source of the notification (e.g., 'wordpress').
     * @param string $type Type (e.g., 'info', 'warning').
     * @param string $title Notification title.
     * @param string $message Full message.
     * @param array $meta Optional meta data as array (will be JSON encoded).
     */
    public static function add_notification($source, $type, $title, $message, $meta = [])
    {
        global $wpdb;  // WordPress database object

        $table_name = $wpdb->prefix . 'nh_notifications';  // Table name with prefix

        // Prepare data with sanitization for security
        $data = [
            'source'  => sanitize_text_field($source),   // Sanitize source to prevent injection
            'type'    => sanitize_text_field($type),     // Sanitize type
            'title'   => sanitize_text_field($title),    // Sanitize title
            'message' => wp_kses_post($message),         // Allow safe HTML in message (e.g., links) but prevent XSS
            'meta'    => wp_json_encode($meta),          // Encode meta as JSON for flexibility
        ];

        // Insert into database – no prepare needed for insert, but data is sanitized
        $wpdb->insert($table_name, $data);  // Performs the insert operation

        // After insert, send to Telegram if enabled
        if (class_exists('NH_Pro_Features') && NH_Pro_Features::is_pro_active()) {
            NH_Notifier::send_telegram($title . ': ' . $message);  // Send title + message
        }
    }



    /**
     * Example callback: Captures new comments and adds as notification.
     * Why? To demonstrate collection; triggered on 'comment_post' hook.
     * @param int $comment_id ID of the new comment.
     */
    public function on_new_comment($comment_id)
    {
        $comment = get_comment($comment_id);  // Get comment object for details
        if ($comment) {
            $title = 'New Comment';  // Simple title for the notification
            $message = 'A new comment was posted by ' . esc_html($comment->comment_author) . ' on post ID ' . $comment->comment_post_ID;  // Message with escaped details for safety
            self::add_notification('wordpress', 'info', $title, $message, ['comment_id' => $comment_id]);  // Store with meta for future reference
        }
    }
}
