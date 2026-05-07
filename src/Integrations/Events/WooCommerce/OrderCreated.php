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
 * Creates a notification when a WooCommerce order is created.
 *
 * @since 1.0.0
 */
final class OrderCreated implements Integration {
    public function register(Loader $loader): void {
        if (!(new IsWooCommerceActive())->passes()) {
            return;
        }

        $loader->addAction('woocommerce_new_order', [$this, 'handle'], 10, 1);
    }

    public function handle($order_id): void {
        $order_id = (int) $order_id;
        if ($order_id <= 0) {
            return;
        }

        $order = function_exists('wc_get_order') ? wc_get_order($order_id) : null;
        $order_number = $order && method_exists($order, 'get_order_number')
            ? (string) $order->get_order_number()
            : (string) $order_id;

        $customer_name = '';
        if ($order && method_exists($order, 'get_formatted_billing_full_name')) {
            $customer_name = trim((string) $order->get_formatted_billing_full_name());
        }
        if ($customer_name === '' && $order && method_exists($order, 'get_billing_first_name')) {
            $customer_name = trim((string) $order->get_billing_first_name());
        }

        $total_text = '';
        if ($order && method_exists($order, 'get_total') && method_exists($order, 'get_currency') && function_exists('wc_price')) {
            $total_value = (float) $order->get_total();
            $currency = (string) $order->get_currency();
            $total_text = wp_strip_all_tags((string) wc_price($total_value, ['currency' => $currency]));
        }

        $message = sprintf(
            __('A new WooCommerce order #%s was created.', 'notification-hub'),
            wp_strip_all_tags($order_number)
        );
        if ($customer_name !== '' && $total_text !== '') {
            $message = sprintf(
                __('A new order was placed by %1$s. Total: %2$s.', 'notification-hub'),
                wp_strip_all_tags($customer_name),
                $total_text
            );
        } elseif ($total_text !== '') {
            $message = sprintf(__('A new order was placed. Total: %s.', 'notification-hub'), $total_text);
        } elseif ($customer_name !== '') {
            $message = sprintf(__('A new order was placed by %s.', 'notification-hub'), wp_strip_all_tags($customer_name));
        }

        $admin_link = admin_url('post.php?post=' . $order_id . '&action=edit');
        $repo = new NotificationsRepository();

        $data = NotificationBuilder::make()
            ->source('woocommerce')
            ->type('order_created')
            ->title(sprintf(__('New order #%s', 'notification-hub'), wp_strip_all_tags($order_number)))
            ->message($message)
            ->status(0)
            ->priority(2)
            ->tags(['orders'])
            ->context([
                'order_id' => $order_id,
                'order_number' => wp_strip_all_tags($order_number),
                'customer_name' => wp_strip_all_tags($customer_name),
                'order_total' => $total_text,
                'actor' => wp_strip_all_tags($customer_name),
                'admin_link' => $admin_link,
                'cta_label' => __('Open Order', 'notification-hub'),
            ])
            ->link($admin_link)
            ->build();

        $repo->insert($data);
    }
}



