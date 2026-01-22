<?php
/**
 * License presenter.
 *
 * Builds view-model and UI notices from normalized license state.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Presenter {

    /**
     * Build a view-model from state.
     *
     * @since 1.7.2
     * @param array $state License state.
     * @return array<string,mixed>
     */
    public function build_view_model(array $state): array {
        return [
            'status'      => (string) ($state['status'] ?? 'unknown'),
            'features'    => (array) ($state['features'] ?? []),
            'domain'      => (string) ($state['domain'] ?? ''),
            'last_check'  => (int) ($state['last_check'] ?? 0),
            'grace_until' => (int) ($state['grace_until'] ?? 0),
        ];
    }
}
