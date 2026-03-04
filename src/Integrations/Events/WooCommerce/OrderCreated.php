<?php

namespace NotificationHub\Integrations\Events\WooCommerce;

use NotificationHub\Builders\NotificationBuilder;
use NotificationHub\Conditionals\IsWooCommerceActive;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;
use NotificationHub\Repositories\NotificationsRepository;

/**
 * Creates a notification when a WooCommerce order is created.
 *
 * @since 1.7.2
 */
final class OrderCreated implements Integration {
    public function register(Loader $loader): void {
        if (!(new IsWooCommerceActive())->passes()) {
            return;
        }

        $loader->addAction('woocommerce_new_order', [$this, 'handle'], 10, 1);
    }

    public function handle($order_id): void {
        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('woocommerce')
            ->type('order_created')
            ->title(sprintf(__('New order #%d', 'notification-hub'), (int) $order_id))
            ->message(__('A new WooCommerce order was created.', 'notification-hub'))
            ->status(0)
            ->priority(2)
            ->tags(['orders'])
            ->build();

        $repo->insert($data);
    }
}


