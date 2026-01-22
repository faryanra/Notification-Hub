<?php
/**
 * Notifier module bootstrap.
 *
 * Only registers hooks / wires dependencies.
 */

defined('ABSPATH') || exit;

return function ($r, $context = 'frontend') {
    // Notifier service can be used in all contexts.
    if (!$r || !method_exists($r, 'get_svc')) {
        return;
    }

    if (!$r->get_svc('notifier') && class_exists('NH_Notifier')) {
        $r->set('notifier', new NH_Notifier($r));
    }

    // Queue hooks should be registered by the queue class (temporary legacy behavior).
    if (class_exists('NH_Queue')) {
        NH_Queue::hook_processor($r);
    }
};
