<?php

namespace NotificationHub;

/**
 * Simple hook loader/manager.
 *
 * @since 1.7.2
 */
final class Loader {
    /**
     * @var array<int, array{hook:string, callback:callable, priority:int, accepted_args:int}>
     */
    private $actions = [];

    /**
     * @var array<int, array{hook:string, callback:callable, priority:int, accepted_args:int}>
     */
    private $filters = [];

    /**
     * @since 1.7.2
     * @param string   $hook Hook name.
     * @param callable $callback Callback.
     * @param int      $priority Priority.
     * @param int      $accepted_args Accepted args.
     * @return void
     */
    public function addAction(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void {
        $this->actions[] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * @since 1.7.2
     * @param string   $hook Hook name.
     * @param callable $callback Callback.
     * @param int      $priority Priority.
     * @param int      $accepted_args Accepted args.
     * @return void
     */
    public function addFilter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void {
        $this->filters[] = [
            'hook' => $hook,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Register all hooks.
     *
     * @since 1.7.2
     * @return void
     */
    public function run(): void {
        foreach ($this->actions as $action) {
            add_action($action['hook'], $action['callback'], $action['priority'], $action['accepted_args']);
        }

        foreach ($this->filters as $filter) {
            add_filter($filter['hook'], $filter['callback'], $filter['priority'], $filter['accepted_args']);
        }
    }
}
