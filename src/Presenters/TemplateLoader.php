<?php

namespace NotificationHub\Presenters;

/**
 * Loads PHP templates with data.
 *
 * @since 1.7.2
 */
final class TemplateLoader {
    /**
     * Render a template and return output.
     *
     * @param string $relativePath Path relative to NH_TEMPLATES_DIR.
     * @param array<string,mixed> $data
     */
    public function render(string $relativePath, array $data = []): string {
        $file = defined('NH_TEMPLATES_DIR') ? NH_TEMPLATES_DIR . ltrim($relativePath, '/') : '';
        if ($file === '' || !file_exists($file)) {
            return '';
        }

        ob_start();
        // Template expects $data variable.
        require $file;
        return (string) ob_get_clean();
    }
}
