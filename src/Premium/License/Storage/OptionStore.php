<?php

namespace NotificationHub\Premium\License\Storage;

/**
 * Option storage for premium license data.
 *
 * @since 1.7.2
 */
final class OptionStore {
    private const KEY = 'nh_premium_license';

    /**
     * @return array<string,mixed>
     */
    public function get(): array {
        $v = get_option(self::KEY, []);
        return is_array($v) ? $v : [];
    }

    /**
     * @param array<string,mixed> $data
     */
    public function set(array $data): bool {
        return update_option(self::KEY, $data, false);
    }

    public function clear(): bool {
        return delete_option(self::KEY);
    }
}
