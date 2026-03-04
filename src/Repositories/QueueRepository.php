<?php

namespace NotificationHub\Repositories;

/**
 * Queue settings / strategy repository.
 *
 * For now, queue is stateless (Action Scheduler or WP-Cron) and does not persist jobs.
 * This repository only stores queue-related settings.
 *
 * @since 1.7.2
 */
final class QueueRepository {
    private const OPTION = 'nh_queue_settings';

    /**
     * @return array{localhost_immediate: bool}
     */
    public function get(): array {
        $defaults = [
            'localhost_immediate' => true,
        ];

        $opt = get_option(self::OPTION, []);
        $opt = is_array($opt) ? $opt : [];

        return [
            'localhost_immediate' => array_key_exists('localhost_immediate', $opt)
                ? (bool) $opt['localhost_immediate']
                : (bool) $defaults['localhost_immediate'],
        ];
    }

    /**
     * @param array{localhost_immediate?: bool} $settings
     */
    public function update(array $settings): void {
        $current = $this->get();

        if (array_key_exists('localhost_immediate', $settings)) {
            $current['localhost_immediate'] = (bool) $settings['localhost_immediate'];
        }

        update_option(self::OPTION, $current);
    }
}
