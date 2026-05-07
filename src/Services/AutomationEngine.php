<?php
namespace NotificationHub\Services;


if (!defined('ABSPATH')) {
    exit;
}

use NotificationHub\Repositories\NotificationsRepository;
use NotificationHub\Repositories\RulesRepository;

/**
 * Automation rules engine (MVP).
 *
 * Evaluates enabled rules on newly inserted notifications and applies actions.
 *
 * @since 1.0.0
 */
final class AutomationEngine {
    private RulesRepository $rules;
    private NotificationsRepository $notifications;
    private NotificationDispatcher $dispatcher;

    public function __construct(
        ?RulesRepository $rules = null,
        ?NotificationsRepository $notifications = null,
        ?NotificationDispatcher $dispatcher = null
    ) {
        $this->rules = $rules ?: new RulesRepository();
        $this->notifications = $notifications ?: new NotificationsRepository();
        $this->dispatcher = $dispatcher ?: ServiceFactory::makeNotificationDispatcher();
    }

    /**
     * @param array<string,mixed> $notification
     * @param array<int,string> $alreadyDispatchedChannels
     */
    public function processOnInsert(int $notificationId, array $notification, array $alreadyDispatchedChannels = []): void {
        if ($notificationId <= 0) {
            return;
        }

        $dispatchedChannelsMap = [];
        foreach ($alreadyDispatchedChannels as $channel) {
            $key = sanitize_key((string) $channel);
            if ($key !== '') {
                $dispatchedChannelsMap[$key] = true;
            }
        }

        $rules = $this->rules->listEnabledOrdered();
        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            if (!$this->matchesRule($rule, $notification)) {
                continue;
            }

            $ruleId = (int) ($rule['id'] ?? 0);
            $ruleName = isset($rule['name']) ? (string) $rule['name'] : ('rule_' . $ruleId);

            EventLogger::info('automation', 'rule_matched', 'Automation rule matched', [
                'rule_id' => $ruleId,
                'rule_name' => $ruleName,
                'notification_id' => $notificationId,
                'source' => isset($notification['source']) ? (string) $notification['source'] : '',
                'type' => isset($notification['type']) ? (string) $notification['type'] : '',
            ]);

            $this->applyActions($rule, $notificationId, $notification, $dispatchedChannelsMap);
        }
    }

    /**
     * @param array<string,mixed> $rule
     * @param array<string,mixed> $notification
     */
    private function matchesRule(array $rule, array $notification): bool {
        $conditionsRaw = isset($rule['conditions']) ? (string) $rule['conditions'] : '';
        $conditions = json_decode($conditionsRaw, true);
        if (!is_array($conditions)) {
            EventLogger::warn('automation', 'rule_conditions_invalid', 'Rule conditions JSON invalid', [
                'rule_id' => (int) ($rule['id'] ?? 0),
            ]);
            return false;
        }

        $all = isset($conditions['all']) && is_array($conditions['all']) ? $conditions['all'] : [];
        if (empty($all)) {
            // No conditions => never auto-match in MVP.
            return false;
        }

        foreach ($all as $cond) {
            if (!is_array($cond)) {
                return false;
            }

            $field = sanitize_key((string) ($cond['field'] ?? ''));
            $op = sanitize_key((string) ($cond['op'] ?? 'eq'));
            $value = $cond['value'] ?? null;

            if ($field === '' || $value === null) {
                return false;
            }

            if (!$this->matchCondition($notification, $field, $op, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string,mixed> $notification
     * @param mixed $expected
     */
    private function matchCondition(array $notification, string $field, string $op, $expected): bool {
        switch ($field) {
            case 'source':
            case 'type':
                $actual = isset($notification[$field]) ? sanitize_key((string) $notification[$field]) : '';
                $exp = sanitize_key((string) $expected);
                if ($op === 'eq' || $op === '') {
                    return $actual === $exp;
                }
                if ($op === 'neq') {
                    return $actual !== $exp;
                }
                return false;

            case 'min_priority':
            case 'priority':
                $actualPriority = isset($notification['priority']) ? (int) $notification['priority'] : 0;
                $expPriority = (int) $expected;
                if ($op === 'gte' || $op === '' || $op === 'eq') {
                    return $actualPriority >= $expPriority;
                }
                if ($op === 'gt') {
                    return $actualPriority > $expPriority;
                }
                if ($op === 'lte') {
                    return $actualPriority <= $expPriority;
                }
                return false;
        }

        return false;
    }

    /**
     * @param array<string,mixed> $rule
     * @param array<string,mixed> $notification
     * @param array<string,bool> $dispatchedChannelsMap
     */
    private function applyActions(array $rule, int $notificationId, array $notification, array &$dispatchedChannelsMap): void {
        $actionsRaw = isset($rule['actions']) ? (string) $rule['actions'] : '';
        $actions = json_decode($actionsRaw, true);
        if (!is_array($actions)) {
            EventLogger::warn('automation', 'rule_actions_invalid', 'Rule actions JSON invalid', [
                'rule_id' => (int) ($rule['id'] ?? 0),
                'notification_id' => $notificationId,
            ]);
            return;
        }

        if (isset($actions['set']) && is_array($actions['set'])) {
            $this->applySetActions($notificationId, $actions['set']);
        }

        if (isset($actions['dispatch']) && is_array($actions['dispatch'])) {
            $this->applyDispatchActions($notificationId, $notification, $actions['dispatch'], (int) ($rule['id'] ?? 0), $dispatchedChannelsMap);
        }
    }

    /**
     * @param array<string,mixed> $set
     */
    private function applySetActions(int $notificationId, array $set): void {
        if (array_key_exists('archive', $set)) {
            if ((bool) $set['archive']) {
                $this->notifications->markArchived($notificationId);
            } else {
                $this->notifications->unarchive($notificationId);
            }
        }

        if (array_key_exists('important', $set)) {
            if ((bool) $set['important']) {
                $this->notifications->markImportant($notificationId);
            } else {
                $this->notifications->unmarkImportant($notificationId);
            }
        }

        if (array_key_exists('mark_read', $set)) {
            if ((bool) $set['mark_read']) {
                $this->notifications->markRead($notificationId);
            } else {
                $this->notifications->markUnread($notificationId);
            }
        }

        EventLogger::info('automation', 'rule_set_applied', 'Automation set-actions applied', [
            'notification_id' => $notificationId,
            'important' => !empty($set['important']) ? 1 : 0,
            'archive' => !empty($set['archive']) ? 1 : 0,
            'mark_read' => !empty($set['mark_read']) ? 1 : 0,
        ]);
    }

    /**
     * @param array<string,mixed> $notification
     * @param array<string,mixed> $dispatch
     * @param array<string,bool> $dispatchedChannelsMap
     */
    private function applyDispatchActions(int $notificationId, array $notification, array $dispatch, int $ruleId, array &$dispatchedChannelsMap): void {
        $channels = isset($dispatch['channels']) && is_array($dispatch['channels']) ? $dispatch['channels'] : [];
        $channels = array_values(array_unique(array_filter(array_map('sanitize_key', $channels))));
        if (empty($channels)) {
            return;
        }

        $mode = sanitize_key((string) ($dispatch['mode'] ?? 'queued'));
        if (!in_array($mode, ['queued', 'immediate'], true)) {
            $mode = 'queued';
        }

        $payload = [
            'title'   => isset($notification['title']) ? (string) $notification['title'] : '',
            'subject' => isset($notification['title']) ? (string) $notification['title'] : '',
            'body'    => isset($notification['message']) ? wp_strip_all_tags((string) $notification['message']) : '',
            'message' => isset($notification['message']) ? wp_strip_all_tags((string) $notification['message']) : '',
            'source'  => isset($notification['source']) ? (string) $notification['source'] : '',
            'type'    => isset($notification['type']) ? (string) $notification['type'] : '',
            'context' => isset($notification['context']) && is_array($notification['context']) ? $notification['context'] : [],
            'link'    => isset($notification['link']) ? esc_url_raw((string) $notification['link']) : '',
        ];

        foreach ($channels as $channel) {
            if (isset($dispatchedChannelsMap[$channel])) {
                EventLogger::info('automation', 'rule_dispatch_skipped_duplicate', 'Rule dispatch skipped due to duplicate channel', [
                    'rule_id' => $ruleId,
                    'notification_id' => $notificationId,
                    'channel' => $channel,
                ]);
                continue;
            }

            if (!$this->isChannelAllowed($channel)) {
                EventLogger::warn('automation', 'rule_dispatch_channel_blocked', 'Rule dispatch channel blocked', [
                    'rule_id' => $ruleId,
                    'notification_id' => $notificationId,
                    'channel' => $channel,
                ]);
                continue;
            }

            if ($mode === 'immediate') {
                $result = $this->dispatcher->sendNowDetailed($channel, $payload);
                EventLogger::info('automation', 'rule_dispatch_immediate', 'Rule dispatch immediate executed', [
                    'rule_id' => $ruleId,
                    'notification_id' => $notificationId,
                    'channel' => $channel,
                    'ok' => !empty($result['ok']) ? 1 : 0,
                    'retryable' => !empty($result['retryable']) ? 1 : 0,
                    'http_code' => (int) ($result['http_code'] ?? 0),
                ]);
                $dispatchedChannelsMap[$channel] = true;
            } else {
                $this->dispatcher->queueSend($channel, $payload);
                EventLogger::info('automation', 'rule_dispatch_queued', 'Rule dispatch queued', [
                    'rule_id' => $ruleId,
                    'notification_id' => $notificationId,
                    'channel' => $channel,
                ]);
                $dispatchedChannelsMap[$channel] = true;
            }
        }
    }

    private function isChannelAllowed(string $channel): bool {
        if ($channel === 'email') {
            return true;
        }

        return in_array($channel, ['telegram', 'slack'], true);
    }
}

