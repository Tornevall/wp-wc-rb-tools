<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/class-resurs-toolbox-version-checker.php';


class Tornevall_Resurs_Toolbox_Ajax_Handler {

    public static function init() {
        add_action('wp_ajax_tornevall_resurs_toolbox_check_version', [self::class, 'check_version']);
        add_action('wp_ajax_tornevall_resurs_order_status_recent_orders', [self::class, 'get_recent_orders']);
        add_action('wp_ajax_tornevall_resurs_order_status_update', [self::class, 'update_order_status']);
    }

    /**
     * AJAX handler for checking Bitbucket version
     */
    public static function check_version() {
        try {
            // First check: nonce validation (CSRF protection) — must come before reading any input
            check_ajax_referer('tornevall_resurs_toolbox_nonce', 'nonce');

            // Second check: user permissions (authorization)
            if (!current_user_can('manage_woocommerce')) {
                wp_send_json_error([
                    'message' => __('Insufficient permissions', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
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

    /**
     * AJAX handler for fetching recent orders for the Order Status Tester dropdown.
     */
    public static function get_recent_orders() {
        check_ajax_referer('tornevall_resurs_order_status_nonce', 'nonce');

        if (Tornevall_Resurs_Toolbox_Order_Status_Tester::is_resurs_production()) {
            wp_send_json_error([
                'message' => __('Order Status Tester is disabled when Resurs environment is Production.', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            ], 403);
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            ], 403);
        }

        wp_send_json_success(Tornevall_Resurs_Toolbox_Order_Status_Tester::get_recent_orders_data(limit: 5));
    }

    /**
     * AJAX handler for updating order status in real-time (no page reload).
     */
    public static function update_order_status() {
        check_ajax_referer('tornevall_resurs_order_status_nonce', 'nonce');

        if (Tornevall_Resurs_Toolbox_Order_Status_Tester::is_resurs_production()) {
            wp_send_json_error([
                'message' => __('Order Status Tester is disabled when Resurs environment is Production.', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            ], 403);
        }

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error([
                'message' => __('Insufficient permissions', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            ], 403);
        }

        $order_id = isset($_POST['order_id']) ? (int)sanitize_text_field(wp_unslash($_POST['order_id'])) : 0;
        $new_status = isset($_POST['new_status']) ? sanitize_text_field(wp_unslash($_POST['new_status'])) : '';
        $order_note = isset($_POST['order_note']) ? sanitize_textarea_field(wp_unslash($_POST['order_note'])) : '';

        $result = Tornevall_Resurs_Toolbox_Order_Status_Tester::update_order_status(
            order_id: $order_id,
            new_status: $new_status,
            order_note: $order_note
        );

        if (is_wp_error($result)) {
            wp_send_json_error([
                'message' => $result->get_error_message(),
            ], 400);
        }

        wp_send_json_success($result);
    }
}


