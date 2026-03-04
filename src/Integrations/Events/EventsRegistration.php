<?php

namespace NotificationHub\Integrations\Events;

use NotificationHub\Conditionals\IsContactForm7Active;
use NotificationHub\Conditionals\IsWooCommerceActive;
use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Registers all event source integrations.
 *
 * @since 1.7.2
 */
final class EventsRegistration implements Integration {
    public function register(Loader $loader): void {
        // WordPress core events.
        (new WordPress\CommentPosted())->register($loader);
        (new WordPress\UserRegistered())->register($loader);
        (new WordPress\PostStatusChanged())->register($loader);
        (new WordPress\CustomHooksLoader())->register($loader);

        // Optional integrations.
        if ((new IsWooCommerceActive())->passes()) {
            (new WooCommerce\OrderCreated())->register($loader);
            (new WooCommerce\LowStockAlert())->register($loader);
        }

        if ((new IsContactForm7Active())->passes()) {
            (new ContactForm7\FormSubmitted())->register($loader);
        }
    }
}
