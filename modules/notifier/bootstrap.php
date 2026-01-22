<?php
/**
 * Notifier module bootstrap.
 *
 * Only registers hooks / wires dependencies.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

return function ($r, $context = 'frontend') {
    // Notifier service can be used in all contexts.
    if (!$r || !method_exists($r, 'get_svc')) {
        return;
    }

    // Legacy: ensure notifier service exists.
    if (!$r->get_svc('notifier') && class_exists('NH_Notifier')) {
        $r->set('notifier', new NH_Notifier($r));
    }

    // Legacy: queue registers its own hooks.
    if (class_exists('NH_Queue')) {
        NH_Queue::hook_processor($r);
    }
};
