<?php
namespace NotificationHub\Integrations\Events\WooCommerce;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Conditionals\IsWooCommerceActive;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification for low stock alerts.
 *
 * @since 1.0.0
 */
final class LowStockAlert implements Integration {
    public function register(Loader $loader): void {
        if (!(new IsWooCommerceActive())->passes()) {
            return;
        }

        $loader->addAction('woocommerce_low_stock', [$this, 'handle'], 10, 1);
    }

    public function handle($product): void {
        $product_id = 0;
        $product_name = '';
        $stock_qty = null;

        if (is_object($product) && method_exists($product, 'get_id')) {
            $product_id = (int) $product->get_id();
        }
        if (is_object($product) && method_exists($product, 'get_name')) {
            $product_name = (string) $product->get_name();
        }
        if (is_object($product) && method_exists($product, 'get_stock_quantity')) {
            $raw_qty = $product->get_stock_quantity();
            if (is_numeric($raw_qty)) {
                $stock_qty = (int) $raw_qty;
            }
        }

        if ($product_name === '' && $product_id > 0) {
            $product_name = get_the_title($product_id);
        }
        if (!is_string($product_name) || $product_name === '') {
            $product_name = $product_id > 0
                ? sprintf(__('Product #%d', 'notification-hub'), $product_id)
                : __('Product', 'notification-hub');
        }

        $admin_link = $product_id > 0 ? admin_url('post.php?post=' . $product_id . '&action=edit') : '';

        $repo = new NotificationsRepository();

        if ($stock_qty !== null) {
            $msg = sprintf(
                __('%1$s is low on stock. Remaining quantity: %2$d.', 'notification-hub'),
                wp_strip_all_tags($product_name),
                $stock_qty
            );
        } else {
            $msg = sprintf(
                __('%s is low on stock.', 'notification-hub'),
                wp_strip_all_tags($product_name)
            );
        }

        $data = NotificationBuilder::make()
            ->source('woocommerce')
            ->type('low_stock')
            ->title(sprintf(__('Low stock alert: %s', 'notification-hub'), wp_strip_all_tags($product_name)))
            ->message($msg)
            ->status(3)
            ->priority(3)
            ->tags(['inventory'])
            ->context([
                'product_id' => $product_id,
                'product_name' => wp_strip_all_tags($product_name),
                'stock_quantity' => $stock_qty,
                'admin_link' => $admin_link,
            ])
            ->link($admin_link)
            ->build();

        $repo->insert($data);
    }
}

