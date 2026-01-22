<?php
/**
 * License presenter.
 *
 * Builds view-model and UI notices from normalized license state.
 *
 * @package Notification_Hub
 * @since 1.7.2
 */

defined('ABSPATH') || exit;

class NH_License_Presenter {

    /**
     * Build a view-model from state.
     *
     * @since 1.7.2
     * @param array $state License state.
     * @return array<string,mixed>
     */
    public function build_view_model(array $state): array {
        return [
            'status'      => (string) ($state['status'] ?? 'unknown'),
            'features'    => (array) ($state['features'] ?? []),
            'domain'      => (string) ($state['domain'] ?? ''),
            'last_check'  => (int) ($state['last_check'] ?? 0),
            'grace_until' => (int) ($state['grace_until'] ?? 0),
            'message'     => (string) ($state['message'] ?? ''),
        ];
    }

    /**
     * Build the primary notice (single notice policy).
     *
     * @since 1.7.2
     * @param array $args Context args.
     * @param array $vm View-model.
     * @return array{type:string,text:string,auto_hide:bool}
     */
    public function build_primary_notice(array $args, array $vm): array {
        $error      = isset($args['error']) ? sanitize_key((string) $args['error']) : '';
        $did_save   = !empty($args['did_save']);
        $did_revoke = !empty($args['did_revoke']);

        $status = sanitize_key((string) ($vm['status'] ?? ''));
        $msg    = (string) ($vm['message'] ?? '');

        $notice = [
            'type'      => '',
            'text'      => '',
            'auto_hide' => false,
        ];

        if ($error === 'invalid_key') {
            $notice['type'] = 'error';
            $notice['text'] = esc_html__('Invalid license key format. Use: NH-PRO-XXXX-XXXX', 'notification-hub');
            return $notice;
        }

        if ($did_revoke) {
            $notice['type'] = 'info';
            $notice['text'] = esc_html__('License revoked.', 'notification-hub');
            $notice['auto_hide'] = true;
            return $notice;
        }

        if ($did_save) {
            if ($status === 'active') {
                $notice['type'] = 'success';
                $notice['text'] = esc_html__('License activated.', 'notification-hub');
                $notice['auto_hide'] = true;
                return $notice;
            }

            if ($msg !== '') {
                // For non-active states, prefer normalized message.
                $notice['type'] = 'warning';
                $notice['text'] = sanitize_text_field($msg);
                return $notice;
            }

            $notice['type'] = 'info';
            $notice['text'] = esc_html__('License saved. Please refresh to verify status.', 'notification-hub');
            $notice['auto_hide'] = true;
            return $notice;
        }

        return $notice;
    }

    /**
     * Build a hint (only shown when there is no primary notice).
     *
     * @since 1.7.2
     * @param array $vm View-model.
     * @return string
     */
    public function build_hint(array $vm): string {
        $status = sanitize_key((string) ($vm['status'] ?? 'unknown'));
        $msg    = (string) ($vm['message'] ?? '');

        switch ($status) {
            case 'active':
                return 'License is active.';

            case 'grace':
                return 'License check failed temporarily. Premium remains enabled during the grace window. Check your server/WAF logs.';

            case 'expired':
                return 'License is expired. Renew your subscription and save the updated license key.';

            case 'revoked':
                return 'License was revoked. Revoke locally and enter a new valid license key.';

            case 'banned':
                return 'License is banned. Contact support.';

            case 'inactive':
                if (stripos($msg, 'anti-bot') !== false || stripos($msg, 'cloudflare') !== false) {
                    return 'Your license endpoint may be blocked by Cloudflare/WAF. Allowlist the verify.php path and disable challenges for it.';
                }

                if (stripos($msg, 'domain') !== false) {
                    return 'Possible domain mismatch. Ensure the license is issued for this site domain and re-verify.';
                }

                return 'License is inactive. Verify server URL and key, then try again.';

            default:
                return 'License status is unknown. Save server URL and key, then refresh.';
        }
    }
}
