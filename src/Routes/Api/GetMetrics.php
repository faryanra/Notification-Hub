<?php
namespace NotificationHub\Routes\Api;


if (!defined('ABSPATH')) {
    exit;
}

use InvalidArgumentException;
use NotificationHub\Services\MetricsService;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST route: GET /nh/v1/metrics
 *
 * @since 1.0.0
 */
final class GetMetrics {
    private MetricsService $metrics;

    public function __construct(?MetricsService $metrics = null) {
        $this->metrics = $metrics ?: new MetricsService();
    }

    public function handle(WP_REST_Request $request): WP_REST_Response {
        $range = sanitize_key((string) $request->get_param('range'));
        if ($range === '') {
            $range = '7d';
        }

        try {
            $payload = $this->metrics->getCountsByDay($range);
        } catch (InvalidArgumentException $e) {
            return new WP_REST_Response(
                [
                    'code'    => 'invalid_range',
                    'message' => esc_html__('Invalid range. Allowed values: 7d, 30d.', 'notification-hub'),
                ],
                400
            );
        }

        return new WP_REST_Response($payload, 200);
    }
}
