<?php

namespace NotificationHub\Integrations\Events\WooCommerce;

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Conditionals\IsWooCommerceActive;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification for low stock alerts.
 *
 * @since 1.7.2
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
        if (is_object($product) && method_exists($product, 'get_id')) {
            $product_id = (int) $product->get_id();
        }

        $repo = new NotificationsRepository();

        $msg = $product_id
            ? sprintf(__('Product #%d is low on stock.', 'notification-hub'), $product_id)
            : __('A product is low on stock.', 'notification-hub');

        $data = NotificationBuilder::make()
            ->source('woocommerce')
            ->type('low_stock')
            ->title(__('Low stock alert', 'notification-hub'))
            ->message($msg)
            ->status(3)
            ->priority(3)
            ->tags(['inventory'])
            ->build();

        $repo->insert($data);
    }
}
