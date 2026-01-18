<?php
/**
 * Human-friendly source label.
 *
 * @since 1.6.3
 * @param string $source Source slug.
 * @return string
 */
function nh_human_source(string $source): string {
    $map = [
        'wp_core'     => 'WordPress',
        'woocommerce' => 'WooCommerce',
        'cf7'         => 'Contact Form 7',
        'hook'        => 'Custom Hook',
    ];

    $source = sanitize_key($source);
    return $map[$source] ?? ($source !== '' ? $source : 'Unknown');
}

/**
 * Human-friendly type label.
 *
 * @since 1.6.3
 * @param string $type Type slug.
 * @return string
 */
function nh_human_type(string $type): string {
    $map = [
        'comment_new'          => 'New Comment',
        'order_created'        => 'New Order',
        'low_stock'            => 'Low Stock',
        'form_sent'            => 'Form Submitted',
        'form_failed'          => 'Form Failed',
        'post_status_changed'  => 'Post Status Changed',
        'user_registered'      => 'New User',
    ];

    $type = sanitize_key($type);
    return $map[$type] ?? ($type !== '' ? $type : 'Unknown');
}