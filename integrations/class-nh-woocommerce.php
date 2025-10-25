<?php
// WooCommerce integration (Free, fixed)

if (!defined('ABSPATH')) exit;

class NH_Int_WooCommerce {
    protected $r;

    public function __construct($registry){
        $this->r = $registry;

        if (class_exists('WooCommerce')) {
            add_action('woocommerce_new_order', [$this, 'on_new_order'], 10, 1);
            add_action('woocommerce_low_stock', [$this, 'on_low_stock'], 10, 1);
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('✅ NH_Int_WooCommerce: hooks registered');
            }
        } else {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('⚠️ NH_Int_WooCommerce: WooCommerce not active');
            }
        }
    }

    public function on_new_order($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) return;
        $total = (float)$order->get_total();

        $e = [
            'source'  => 'woocommerce',
            'type'    => 'order_created',
            'title'   => sprintf(__('New Order #%d','notification-hub'), $order_id),
            'message' => sprintf(__('Total: %s','notification-hub'), wc_price($total)),
            'context' => ['order_id'=>$order_id,'total'=>$total,'currency'=>get_woocommerce_currency()]
        ];

        $db = $this->r->get_svc('db');
        if ($db) $db->insert_notification($e);

        $notifier = $this->r->get_svc('notifier');
        if ($notifier) {
            $notifier->send([
                'channel' => 'email',
                'title'   => $e['title'],
                'body'    => $e['message'],
                'source'  => $e['source'],
                'multi'   => ['slack','telegram']
            ]);
        }
    }

    public function on_low_stock($product) {
        if (!is_object($product) || !method_exists($product,'get_name')) return;
        $qty = method_exists($product,'get_stock_quantity') ? $product->get_stock_quantity() : 0;

        $e = [
            'source'  => 'woocommerce',
            'type'    => 'low_stock',
            'title'   => sprintf(__('Low stock: %s','notification-hub'), $product->get_name()),
            'message' => sprintf(__('Stock: %d','notification-hub'), $qty),
            'context' => ['product_id'=>method_exists($product,'get_id') ? $product->get_id() : 0]
        ];

        $db = $this->r->get_svc('db');
        if ($db) $db->insert_notification($e);

        $notifier = $this->r->get_svc('notifier');
        if ($notifier) {
            $notifier->send([
                'channel' => 'email',
                'title'   => $e['title'],
                'body'    => $e['message'],
                'source'  => $e['source'],
                'multi'   => ['slack','telegram']
            ]);
        }
    }
}
