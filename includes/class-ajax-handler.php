<?php

namespace Tornevalls\ToolboxResurs;

if (!defined('ABSPATH')) {
    exit;
}


class Tornevall_Resurs_AJAX_Handler {

    public static function init() {
        add_action('wp_ajax_resurs_check_version', [self::class, 'check_version']);
    }

    /**
     * AJAX handler for checking Bitbucket version
     */
    public static function check_version() {
        try {
            $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

            // Verify nonce
            if ($nonce === '' || !wp_verify_nonce($nonce, 'resurs_toolbox_nonce')) {
                wp_send_json_error([
                    'message' => __('Security check failed', 'tornevalls-tools-for-resurs-bank-payments'),
                ], 403);
            }

            // Check capabilities
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'tornevalls-tools-for-resurs-bank-payments'),
                ], 403);
            }

            // Get installed version from POST
            $installed_version = isset($_POST['installed_version'])
                ? sanitize_text_field(wp_unslash($_POST['installed_version']))
                : '';

            if (empty($installed_version)) {
                wp_send_json_error([
                    'message' => __('No installed version provided', 'tornevalls-tools-for-resurs-bank-payments'),
                ]);
            }

            // Check for updates
            if (!class_exists(__NAMESPACE__ . '\\Tornevall_Bitbucket_Version_Checker')) {
                require_once plugin_dir_path(__FILE__) . 'class-bitbucket-version-checker.php';
            }

            $result = Tornevall_Bitbucket_Version_Checker::check_for_updates($installed_version);

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
