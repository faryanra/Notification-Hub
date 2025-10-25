<?php
// License Manager / Pro Gate
// - Central place to check if Pro features should be enabled
// - Prepares us for remote validation in 1.4.x without breaking free users

if (!defined('ABSPATH')) exit;

class NH_License {

    /**
     * Check if Pro features should be unlocked.
     * For now, "Pro" == we have a non-empty license key stored.
     * Later this can become "is valid & active per remote API".
     */
    public static function is_pro() {
        $key = get_option('nh_license_key', '');
        return !empty($key);
    }

    /**
     * Validate and store a license key.
     *
     * Right now (1.3.9):
     * - basic local validation only
     * - if key "looks fine", we save it
     *
     * Future (1.4.x+ Pro):
     * - this method can call remote API (your license server / Envato / etc)
     * - can store status like 'valid', 'expired', 'revoked', etc.
     *
     * @param string $key Raw license key from user input (Settings screen)
     * @return bool true if accepted, false if rejected
     */
    public static function validate($key) {
        // sanitize input to avoid DB pollution
        $clean = sanitize_text_field($key);

        // minimum length rule to avoid junk like "1" or empty post
        // you can tune this rule when you define real license format
        if (strlen($clean) < 8) {
            return false;
        }

        // store in wp_options
        update_option('nh_license_key', $clean);

        // In future:
        // update_option('nh_license_status', 'valid'); etc.

        return true;
    }

    /**
     * Force-remove/deactivate the current license.
     * Useful if user clicks "Deactivate License".
     */
    public static function deactivate() {
        delete_option('nh_license_key');
        // future: delete_option('nh_license_status');
    }

    /**
     * (Optional forward-compat hook)
     * This is where you'd ping remote API and refresh validity.
     * We are not calling this automatically in 1.3.9,
     * but it's here so the structure is ready for 1.4.0+.
     */
    public static function refresh_status() {
        // pseudo:
        // $key = get_option('nh_license_key', '');
        // if (!$key) return false;
        //
        // $resp = wp_remote_post('https://your-license-server/verify', [...]);
        // if (is_wp_error($resp)) return false;
        //
        // $data = json_decode(wp_remote_retrieve_body($resp), true);
        // if (!empty($data['valid'])) {
        //     update_option('nh_license_status', 'valid');
        //     return true;
        // }
        //
        // update_option('nh_license_status', 'invalid');
        // return false;

        return null; // noop for now
    }
}
