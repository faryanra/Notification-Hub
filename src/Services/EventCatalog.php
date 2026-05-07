<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Supported notification events catalog.
 *
 * @since 1.0.0
 */
final class EventCatalog {
    private const KNOWN_KEYS = [
        'wordpress:post_status_changed',
        'wordpress:user_registered',
        'wordpress:comment_posted',
        'wordpress:email_sent',
        'woocommerce:order_created',
        'woocommerce:low_stock',
        'contactform7:form_submitted',
        'custom_hook:custom_hook_triggered',
    ];

    /**
     * @return array<int,array{key:string,source:string,type:string,label:string}>
     */
    public static function definitions(): array {
        return [
            [
                'key' => 'wordpress:post_status_changed',
                'source' => 'wordpress',
                'type' => 'post_status_changed',
                'label' => __('WordPress: Post status changed', 'notification-hub'),
            ],
            [
                'key' => 'wordpress:user_registered',
                'source' => 'wordpress',
                'type' => 'user_registered',
                'label' => __('WordPress: New user registered', 'notification-hub'),
            ],
            [
                'key' => 'wordpress:comment_posted',
                'source' => 'wordpress',
                'type' => 'comment_posted',
                'label' => __('WordPress: New comment posted', 'notification-hub'),
            ],
            [
                'key' => 'wordpress:email_sent',
                'source' => 'wordpress',
                'type' => 'email_sent',
                'label' => __('WordPress: Email sent event', 'notification-hub'),
            ],
            [
                'key' => 'woocommerce:order_created',
                'source' => 'woocommerce',
                'type' => 'order_created',
                'label' => __('WooCommerce: New order created', 'notification-hub'),
            ],
            [
                'key' => 'woocommerce:low_stock',
                'source' => 'woocommerce',
                'type' => 'low_stock',
                'label' => __('WooCommerce: Low stock alert', 'notification-hub'),
            ],
            [
                'key' => 'contactform7:form_submitted',
                'source' => 'contactform7',
                'type' => 'form_submitted',
                'label' => __('Contact Form 7: Form submitted', 'notification-hub'),
            ],
            [
                'key' => 'custom_hook:custom_hook_triggered',
                'source' => 'custom_hook',
                'type' => 'custom_hook_triggered',
                'label' => __('Custom Hooks: Hook triggered', 'notification-hub'),
            ],
        ];
    }

    /**
     * @return array<int,string>
     */
    public static function defaultKeys(): array {
        return self::KNOWN_KEYS;
    }

    public static function keyFor(string $source, string $type): string {
        return sanitize_key($source) . ':' . sanitize_key($type);
    }

    public static function isKnownKey(string $key): bool {
        return in_array($key, self::KNOWN_KEYS, true);
    }

    /**
     * @param mixed $value
     * @return array<int,string>
     */
    public static function sanitizeKeys($value): array {
        $list = [];

        if (is_array($value)) {
            $list = $value;
        } elseif (is_string($value) && $value !== '') {
            $list = [$value];
        }

        $allowed = self::KNOWN_KEYS;
        $normalized = [];

        foreach ($list as $item) {
            $key = sanitize_text_field((string) $item);
            if ($key === '' || $key === '__none__') {
                continue;
            }

            if (in_array($key, $allowed, true)) {
                $normalized[] = $key;
            }
        }

        return array_values(array_unique($normalized));
    }
}
