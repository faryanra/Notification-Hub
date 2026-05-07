<?php
namespace NotificationHub\Initializers;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Integrations\Integration;
use NotificationHub\Loader;

/**
 * Ensure required capabilities exist for admins.
 *
 * @since 1.0.0
 */
final class Capabilities implements Integration {
    /**
     * @since 1.0.0
     */
    public function register(Loader $loader): void {
        // Run on admin_init to avoid unnecessary frontend work.
        $loader->addAction('admin_init', [$this, 'ensure']);
    }

    public function ensure(): void {
        $role = get_role('administrator');
        if (!$role) {
            return;
        }

        // Future-proof custom capability; for now dashboard uses manage_options.
        if (!$role->has_cap('nh_manage_notifications')) {
            $role->add_cap('nh_manage_notifications');
        }
    }
}

