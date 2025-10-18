<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class NH_Pro_Features
 * Handles pro features like Telegram, with license check.
 */
class NH_Pro_Features {

    /**
     * Checks if pro is activated (simple for MVP, replace with real license API).
     * Why? To enable/disable pro features.
     * @return bool True if pro active.
     */
    public static function is_pro_active() {
        return get_option( 'nh_pro_license', false ) === 'valid';  // Simple check, update with real validation
    }

    /**
     * Validates license (placeholder for Envato/CodeCanyon API).
     * @param string $license License key from user.
     * @return bool Valid or not.
     */
    public static function validate_license( $license ) {
        // Placeholder - in real, use wp_remote_get to Envato API
        if ( ! empty( $license ) ) {  // Test: any non-empty is valid
            update_option( 'nh_pro_license', 'valid' );
            return true;
        }
        return false;
    }
}