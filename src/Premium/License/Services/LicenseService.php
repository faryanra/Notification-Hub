<?php

namespace NotificationHub\Premium\License\Services;

use NotificationHub\Premium\License\Http\LicenseClient;
use NotificationHub\Premium\License\Policy\DomainPolicy;
use NotificationHub\Premium\License\Policy\KeyFormatPolicy;
use NotificationHub\Premium\License\Storage\OptionStore;
use WP_Error;

/**
 * License service.
 *
 * @since 1.7.2
 */
final class LicenseService {
    private OptionStore $store;
    private KeyFormatPolicy $keyPolicy;
    private DomainPolicy $domainPolicy;

    public function __construct(OptionStore $store, KeyFormatPolicy $keyPolicy, DomainPolicy $domainPolicy) {
        $this->store = $store;
        $this->keyPolicy = $keyPolicy;
        $this->domainPolicy = $domainPolicy;
    }

    /**
     * @return bool|WP_Error
     */
    public function saveKey(string $key) {
        if (!$this->keyPolicy->isValid($key)) {
            return new WP_Error('nh_license_key_invalid', 'Invalid key format');
        }

        $data = $this->store->get();
        $data['key'] = $key;
        $data['updated_at'] = time();

        return $this->store->set($data);
    }

    /**
     * @return bool
     */
    public function saveServer(string $serverUrl): bool {
        $serverUrl = esc_url_raw($serverUrl);
        $data = $this->store->get();
        $data['server'] = $serverUrl;
        $data['updated_at'] = time();
        return (bool) $this->store->set($data);
    }

    /**
     * @return array<string,mixed>|WP_Error
     */
    public function validate(): 
    	mixed {
        $data = $this->store->get();
        $key = isset($data['key']) ? (string) $data['key'] : '';
        $server = isset($data['server']) ? (string) $data['server'] : '';

        if ($key === '' || $server === '') {
            return new WP_Error('nh_license_missing', 'Key or server missing');
        }

        $client = new LicenseClient($server);
        return $client->validate($key, $this->domainPolicy->currentDomain());
    }

    public function revoke(): bool {
        return (bool) $this->store->clear();
    }
}
