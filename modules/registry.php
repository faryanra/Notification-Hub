<?php
/**
 * Module registry for v1.7.2 modular refactor.
 *
 * This file is intentionally lightweight: it only declares which module bootstraps
 * should be loaded by the core loader.
 */

defined('ABSPATH') || exit;

return [
    // Core/admin UX.
    'admin'    => __DIR__ . '/admin/bootstrap.php',

    // Licensing (sensitive; refactor priority).
    'license'  => __DIR__ . '/license/bootstrap.php',

    // Notifications & queue.
    'notifier' => __DIR__ . '/notifier/bootstrap.php',

    // Dashboard (if feature exists).
    'dashboard'=> __DIR__ . '/dashboard/bootstrap.php',
];
