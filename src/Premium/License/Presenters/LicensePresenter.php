<?php

namespace NotificationHub\Premium\License\Presenters;

use NotificationHub\Premium\License\Storage\OptionStore;

/**
 * Presents license data for admin UI.
 *
 * @since 1.7.2
 */
final class LicensePresenter {
    private OptionStore $store;

    public function __construct(OptionStore $store) {
        $this->store = $store;
    }

    /**
     * @return array<string,mixed>
     */
    public function data(): array {
        $d = $this->store->get();

        return [
            'key'    => isset($d['key']) ? (string) $d['key'] : '',
            'server' => isset($d['server']) ? (string) $d['server'] : '',
        ];
    }
}
