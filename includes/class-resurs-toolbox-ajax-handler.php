<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-resurs-toolbox-version-checker.php';


class Tornevall_Resurs_Toolbox_Ajax_Handler {

    public static function init() {
        add_action('wp_ajax_tornevall_resurs_toolbox_check_version', [self::class, 'check_version']);
    }

    /**
     * AJAX handler for checking Bitbucket version
     */
    public static function check_version() {
        try {
            // First check: user permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                ], 403);
            }

            // Second check: nonce validation (CSRF protection)
            $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
            if (!$nonce || !wp_verify_nonce($nonce, 'tornevall_resurs_toolbox_nonce')) {
                wp_send_json_error([
                    'message' => __('Security check failed', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                ], 403);
            }

            // Third check: required input validation
            $installed_version = isset($_POST['installed_version']) ? sanitize_text_field(wp_unslash($_POST['installed_version'])) : '';

            if (empty($installed_version)) {
                wp_send_json_error([
                    'message' => __('No installed version provided', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                ]);
            }


            $result = Tornevall_Resurs_Toolbox_Version_Checker::check_for_updates($installed_version);

            if (!isset($result['success']) || !$result['success']) {
                wp_send_json_error($result);
            }

            wp_send_json_success($result);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}


