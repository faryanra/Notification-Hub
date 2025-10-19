<?php
// Prevent direct access to this file for security reasons
if ( ! defined( 'ABSPATH' ) ) {
    exit;  // Exit if accessed directly to avoid security vulnerabilities
}

/**
 * Class NH_Collector
 * This class handles collecting notifications from various sources and storing them in the database.
 * It hooks into WordPress events to capture notifications.
 */
class NH_Collector {

    /**
     * Constructor: Sets up hooks to collect notifications from sources like WP core.
     * Why? To automatically capture events without manual calls, improving integration.
     */
    public function __construct() {
        error_log( 'NH: Collector constructor loaded' );  // Log to confirm init

        // Hook for new comments (WP core)
        add_action( 'comment_post', [ $this, 'on_new_comment' ] );  // Hooks into comment_post to capture new comments

        // Delay WooCommerce hooks until Woo is fully initialized
        add_action( 'woocommerce_init', [ $this, 'register_woocommerce_hooks' ] );  // Use Woo init to ensure WC() available

        // In constructor, after Woo
        if ( function_exists( 'wpcf7' ) ) {  // Check if Contact Form 7 is active
            error_log( 'NH: CF7 detected, adding hook' );  // Log to confirm
            add_action( 'wpcf7_mail_sent', [ $this, 'on_form_submission' ] );  // Hooks into form submission after mail sent
        } else {
            error_log( 'NH: CF7 not detected' );  // Log if missing
        }
    }

    /**
     * Registers WooCommerce hooks after Woo init.
     * Why? To avoid 'Woo not detected' error, as Woo loads after plugins.
     */
    public function register_woocommerce_hooks() {
        if ( function_exists( 'WC' ) ) {  // Double-check if Woo is active
            error_log( 'NH: WooCommerce detected, adding hooks after init' );  // Log to confirm

            add_action( 'woocommerce_checkout_order_created', [ $this, 'on_new_order' ], 10, 1 );  // For frontend checkout (works for bank transfer)
            add_action( 'woocommerce_order_status_changed', [ $this, 'on_order_status_changed' ], 10, 4 );  // For status changes
            add_action( 'woocommerce_process_shop_order_meta', [ $this, 'on_admin_order' ], 10, 2 );  // For admin create/edit
        } else {
            error_log( 'NH: WooCommerce still not detected after init' );  // Log if issue persists
        }
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
    public static function add_notification( $source, $type, $title, $message, $meta = [] ) {
        global $wpdb;  // WordPress database object

        $table_name = $wpdb->prefix . 'nh_notifications';  // Table name with prefix

        // Prepare data with sanitization for security
        $data = [
            'source'  => sanitize_text_field( $source ),   // Sanitize source to prevent injection
            'type'    => sanitize_text_field( $type ),     // Sanitize type
            'title'   => sanitize_text_field( $title ),    // Sanitize title
            'message' => wp_kses_post( $message ),         // Allow safe HTML in message (e.g., links) but prevent XSS
            'meta'    => wp_json_encode( $meta ),          // Encode meta as JSON for flexibility
        ];

        // Insert into database – no prepare needed for insert, but data is sanitized
        $wpdb->insert( $table_name, $data );  // Performs the insert operation

        // After insert, send to Telegram if enabled
        NH_Notifier::send_telegram( $title . ': ' . $message );  // Send title + message
                // After Telegram send in add_notification
        NH_Notifier::send_email( $title . ': ' . $message );  // NEW: Send email if configured
        NH_Notifier::send_slack( $title . ': ' . $message );  // NEW: Send to Slack if configured
    }

    /**
     * Example callback: Captures new comments and adds as notification.
     * Why? To demonstrate collection; triggered on 'comment_post' hook.
     * @param int $comment_id ID of the new comment.
     */
    public function on_new_comment( $comment_id ) {
        $comment = get_comment( $comment_id );  // Get comment object for details
        if ( $comment ) {
            $title = 'New Comment';  // Simple title for the notification
            $message = 'A new comment was posted by ' . esc_html( $comment->comment_author ) . ' on post ID ' . $comment->comment_post_ID;  // Message with escaped details for safety
            self::add_notification( 'wordpress', 'info', $title, $message, [ 'comment_id' => $comment_id ] );  // Store with meta
        }
    }

    /**
     * Callback for new WooCommerce order (checkout created).
     * Why? To collect order notifications from WooCommerce core, works for bank transfer too.
     * @param WC_Order $order The new order object.
     */
    public function on_new_order( $order ) {
        $order_id = $order->get_id();  // Get ID from object
        error_log( 'NH: Woo checkout order created hook fired for order ' . $order_id );  // Log to debug if hook fires
        $title = 'New Order';  // Simple title
        $message = 'A new order #' . $order_id . ' was created. Total: ' . $order->get_formatted_order_total() . '. Status: ' . $order->get_status();  // Message with details including status
        self::add_notification( 'woocommerce', 'info', $title, $message, [ 'order_id' => $order_id ] );  // Store with meta
    }

    /**
     * Callback for WooCommerce order status change.
     * Why? To capture changes like on-hold to processing for bank transfer.
     * @param int $order_id Order ID.
     * @param string $old_status Old status.
     * @param string $new_status New status.
     * @param WC_Order $order Order object.
     */
    public function on_order_status_changed( $order_id, $old_status, $new_status, $order ) {
        error_log( 'NH: Woo status change hook fired for order ' . $order_id . ' from ' . $old_status . ' to ' . $new_status );  // Log to debug
        if ( $new_status === 'processing' ) {  // Example: notify on processing
            $title = 'Order Status Changed';
            $message = 'Order #' . $order_id . ' status changed from ' . $old_status . ' to ' . $new_status . '. Total: ' . $order->get_formatted_order_total();
            self::add_notification( 'woocommerce', 'info', $title, $message, [ 'order_id' => $order_id ] );
        }
    }

    /**
     * Callback for admin-created or edited WooCommerce order.
     * Why? To capture orders created in admin (woocommerce_new_order doesn't fire in admin).
     * @param int $post_id The post ID (order ID).
     * @param WP_Post $post The post object.
     */
    public function on_admin_order( $post_id, $post ) {
        if ( $post->post_type !== 'shop_order' ) return;  // Only for orders
        error_log( 'NH: Woo admin hook fired for order ' . $post_id );  // Log to debug if hook fires
        $order = wc_get_order( $post_id );  // Get order object
        if ( $order && $order->get_status() === 'processing' ) {  // Example: only on processing status
            $title = 'New Order (Admin)';
            $message = 'A new order #' . $post_id . ' was created in admin. Total: ' . $order->get_formatted_order_total();
            self::add_notification( 'woocommerce', 'info', $title, $message, [ 'order_id' => $post_id ] );  // Store with meta
        }
    }

    /**
     * Callback for Contact Form 7 submission.
     * Why? To collect form notifications from CF7 core.
     * @param WPCF7_ContactForm $contact_form The form object.
     */
    public function on_form_submission( $contact_form ) {
        error_log( 'NH: CF7 submission hook fired for form ' . $contact_form->id() );  // Log to debug
        $submission = WPCF7_Submission::get_instance();  // Get submission data
        if ( $submission ) {
            $posted_data = $submission->get_posted_data();  // Get form fields
            $title = 'New Form Submission';  // Simple title
            $message = 'A new submission from form "' . $contact_form->title() . '". Email: ' . (isset($posted_data['your-email']) ? sanitize_email($posted_data['your-email']) : 'N/A');  // Message with example field (sanitize for safety)
            self::add_notification( 'cf7', 'info', $title, $message, [ 'form_id' => $contact_form->id(), 'posted_data' => wp_json_encode($posted_data) ] );  // Store with meta
        }
    }
}
