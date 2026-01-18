<?php
/**
 * NH_Int_WooCommerce
 *
 * WooCommerce integration for Notification Hub.
 *
 * Listens to key WooCommerce events and creates notifications:
 * - New order
 * - Low stock
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Int_WooCommerce {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var NH_Core_Registry|mixed
     */
    protected $r;

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param mixed $registry Registry instance.
     */
    public function __construct($registry) {
        $this->r = $registry;

        if (class_exists('WooCommerce')) {
            add_action('woocommerce_new_order', [$this, 'on_new_order'], 10, 1);
            add_action('woocommerce_low_stock', [$this, 'on_low_stock'], 10, 1);

            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('NH_Int_WooCommerce: hooks registered');
            }
        } elseif (defined('WP_DEBUG') && WP_DEBUG) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('NH_Int_WooCommerce: WooCommerce not active');
        }
    }

    /**
     * Handle new order.
     *
     * @since 1.6.2
     * @param int $order_id Order ID.
     * @return void
     */
    public function on_new_order($order_id) {
        if (!function_exists('wc_get_order')) {
            return;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }

        $total = (float) $order->get_total();

        /* translators: %d: Order ID. */
        $title = sprintf(esc_html__('New Order #%d', 'notification-hub'), (int) $order_id);

        /* translators: %s: Order total with currency. */
        $message = sprintf(esc_html__('Total: %s', 'notification-hub'), wc_price($total));

        $e = [
            'source'  => 'woocommerce',
            'type'    => 'order_created',
            'title'   => $title,
            'message' => $message,
            'context' => [
                'order_id'  => (int) $order_id,
                'total'     => $total,
                'currency'  => function_exists('get_woocommerce_currency') ? get_woocommerce_currency() : '',
            ],
        ];

        $db = $this->r->get_svc('db');
        if ($db && method_exists($db, 'insert_notification')) {
            $db->insert_notification($e);
        }

        $this->fanout_send($e);
    }

    /**
     * Handle low stock.
     *
     * @since 1.6.2
     * @param mixed $product WC_Product.
     * @return void
     */
    public function on_low_stock($product) {
        if (!is_object($product) || !method_exists($product, 'get_name')) {
            return;
        }

        $qty = method_exists($product, 'get_stock_quantity') ? $product->get_stock_quantity() : 0;

        /* translators: %s: Product name. */
        $title = sprintf(esc_html__('Low stock: %s', 'notification-hub'), (string) $product->get_name());

        /* translators: %d: Stock quantity. */
        $message = sprintf(esc_html__('Stock: %d', 'notification-hub'), (int) $qty);

        $product_id = method_exists($product, 'get_id') ? (int) $product->get_id() : 0;

        $e = [
            'source'  => 'woocommerce',
            'type'    => 'low_stock',
            'title'   => $title,
            'message' => $message,
            'context' => [
                'product_id' => $product_id,
                'stock'      => (int) $qty,
            ],
        ];

        $db = $this->r->get_svc('db');
        if ($db && method_exists($db, 'insert_notification')) {
            $db->insert_notification($e);
        }

        $this->fanout_send($e);
    }

    /**
     * Fan-out event to all channels.
     *
     * @since 1.6.3
     * @param array $e Notification event.
     * @return void
     */
    protected function fanout_send(array $e): void {
        $notifier = $this->r->get_svc('notifier');
        if (!$notifier) {
            return;
        }

        $context = (isset($e['context']) && is_array($e['context'])) ? $e['context'] : [];
        $type    = isset($e['type']) ? (string) $e['type'] : '';

        // Admin deep-link.
        $link = '';
        if ($type === 'order_created' && !empty($context['order_id'])) {
            $link = function_exists('get_edit_post_link') ? (string) get_edit_post_link((int) $context['order_id'], '') : '';
        }
        if ($type === 'low_stock' && !empty($context['product_id'])) {
            $link = function_exists('get_edit_post_link') ? (string) get_edit_post_link((int) $context['product_id'], '') : '';
        }

        $payload = [
            'title'   => $e['title'] ?? '',
            'summary' => $e['message'] ?? '',
            'source'  => $e['source'] ?? 'woocommerce',
            'type'    => $type,
            'context' => $context,
            'link'    => $link,
            'no_log'  => true,
        ];

        if (method_exists($notifier, 'queue_send')) {
            $notifier->queue_send('email', $payload);
            $notifier->queue_send('telegram', $payload);
            $notifier->queue_send('slack', $payload);
            return;
        }

        if (method_exists($notifier, 'send_now')) {
            $notifier->send_now('email', $payload);
            $notifier->send_now('telegram', $payload);
            $notifier->send_now('slack', $payload);
        }
    }
}