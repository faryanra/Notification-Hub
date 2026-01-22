<?php
/**
 * NH_Loader
 *
 * Orchestration-only loader for Notification Hub.
 *
 * Responsibilities:
 * - Detect context (admin/frontend/cron).
 * - Load & run module bootstraps.
 * - Keep a small registry of loaded modules.
 *
 * No business hooks should be registered here; each module bootstrap is responsible
 * for wiring its own hooks.
 *
 * @package Notification_Hub
 * @since 1.6.2
 */

if (!defined('ABSPATH')) {
    exit;
}

class NH_Loader {

    /**
     * Registry container.
     *
     * @since 1.6.2
     * @var NH_Core_Registry
     */
    protected $r;

    /**
     * Loaded module slugs.
     *
     * @since 1.7.2
     * @var array<string,bool>
     */
    protected $loaded_modules = [];

    /**
     * Constructor.
     *
     * @since 1.6.2
     * @param NH_Core_Registry $registry Registry instance.
     */
    public function __construct($registry) {
        $this->r = $registry;
    }

    /**
     * Boot plugin components.
     *
     * @since 1.6.2
     * @return void
     */
    public function boot() {
        // Context detection is intentionally kept very small; modules decide what to do.
        $context = $this->detect_context();

        // Load module bootstraps from a single registry source.
        $bootstraps = $this->get_module_bootstraps();

        foreach ($bootstraps as $slug => $bootstrap_path) {
            $this->load_module_bootstrap($slug, $bootstrap_path, $context);
        }
    }

    /**
     * Detect runtime context.
     *
     * @since 1.7.2
     * @return string One of: admin|frontend|cron|cli
     */
    protected function detect_context() {
        if (defined('WP_CLI') && WP_CLI) {
            return 'cli';
        }

        if (defined('DOING_CRON') && DOING_CRON) {
            return 'cron';
        }

        if (is_admin()) {
            return 'admin';
        }

        return 'frontend';
    }

    /**
     * Get module bootstrap map.
     *
     * @since 1.7.2
     * @return array<string,string>
     */
    protected function get_module_bootstraps() {
        $registry_file = NH_PLUGIN_DIR . 'modules/registry.php';
        if (!file_exists($registry_file)) {
            return [];
        }

        $bootstraps = require $registry_file;
        if (!is_array($bootstraps)) {
            return [];
        }

        return $bootstraps;
    }

    /**
     * Load and run a single module bootstrap.
     *
     * @since 1.7.2
     * @param string $slug Module slug.
     * @param string $bootstrap_path Absolute path to bootstrap.php.
     * @param string $context Context string.
     * @return void
     */
    protected function load_module_bootstrap($slug, $bootstrap_path, $context) {
        if (empty($slug) || empty($bootstrap_path)) {
            return;
        }

        if (!file_exists($bootstrap_path)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log(sprintf('Notification Hub: Missing module bootstrap %s (%s)', $slug, $bootstrap_path));
            }
            return;
        }

        $bootstrap = require $bootstrap_path;

        // Convention: bootstrap returns a callable.
        if (is_callable($bootstrap)) {
            try {
                call_user_func($bootstrap, $this->r, $context);
            } catch (Throwable $e) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    error_log(sprintf('Notification Hub: Module %s bootstrap failed: %s', $slug, $e->getMessage()));
                }
                return;
            }
        }

        $this->loaded_modules[$slug] = true;
    }

    /**
     * Check if a module has been loaded.
     *
     * @since 1.7.2
     * @param string $slug Module slug.
     * @return bool
     */
    public function is_module_loaded($slug) {
        return isset($this->loaded_modules[$slug]);
    }

    /**
     * Return loaded modules.
     *
     * @since 1.7.2
     * @return string[]
     */
    public function get_loaded_modules() {
        return array_keys($this->loaded_modules);
    }
}
