<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Order Status Tester Module
 * Provides utilities for testing Resurs Bank order status updates.
 */
class Tornevall_Resurs_Toolbox_Order_Status_Tester {
    const NONCE_ACTION = 'tornevall_resurs_order_status_tester';
    const NONCE_NAME = '_tornevall_resurs_order_status_nonce';

    public static function init() {
        add_action('admin_init', [__CLASS__, 'handle_form_submission']);
    }

    public static function render() {
        if (self::is_resurs_production()) {
            echo '<div class="notice notice-warning"><p>';
            echo esc_html__('Order Status Tester is disabled when Resurs environment is Production.', 'tornevall-networks-toolbox-for-resurs-bank-payments');
            echo '</p></div>';
            return;
        }

        $updated_order_id = null;
        $error_message = null;

        // Check if form was just submitted
        if (isset($_GET['tornevall_resurs_status_updated'])) {
            $updated_order_id = (int)sanitize_text_field(wp_unslash($_GET['tornevall_resurs_status_updated']));
            $updated_status = sanitize_text_field(wp_unslash($_GET['tornevall_resurs_new_status'] ?? ''));
            $previous_status = sanitize_text_field(wp_unslash($_GET['tornevall_resurs_old_status'] ?? ''));
            $status_changed_at = sanitize_text_field(wp_unslash($_GET['tornevall_resurs_status_changed_at'] ?? ''));
            $status_actor = sanitize_text_field(wp_unslash($_GET['tornevall_resurs_status_actor'] ?? ''));
            $status_note_added = sanitize_text_field(wp_unslash($_GET['tornevall_resurs_status_note_added'] ?? '0')) === '1';

            if ($updated_order_id > 0 && $updated_status !== '') {
                echo '<div class="notice notice-success is-dismissible"><p>';
                printf(
                    esc_html__('Order #%d status updated to "%s"', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    $updated_order_id,
                    esc_html($updated_status)
                );
                echo '</p></div>';

                echo '<div class="notice notice-info"><p><strong>';
                esc_html_e('Status update details', 'tornevall-networks-toolbox-for-resurs-bank-payments');
                echo '</strong></p><ul style="margin: 0 0 0 16px; list-style: disc;">';

                echo '<li>';
                printf(
                    esc_html__('Order: #%d', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    $updated_order_id
                );
                echo '</li>';

                if ($previous_status !== '') {
                    echo '<li>';
                    printf(
                        esc_html__('Previous status: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                        esc_html($previous_status)
                    );
                    echo '</li>';
                }

                echo '<li>';
                printf(
                    esc_html__('New status: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    esc_html($updated_status)
                );
                echo '</li>';

                if ($status_actor !== '') {
                    echo '<li>';
                    printf(
                        esc_html__('Updated by: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                        esc_html($status_actor)
                    );
                    echo '</li>';
                }

                if ($status_changed_at !== '') {
                    echo '<li>';
                    printf(
                        esc_html__('Updated at: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                        esc_html($status_changed_at)
                    );
                    echo '</li>';
                }

                echo '<li>';
                echo $status_note_added
                    ? esc_html__('Order note was added in this update.', 'tornevall-networks-toolbox-for-resurs-bank-payments')
                    : esc_html__('No order note was added in this update.', 'tornevall-networks-toolbox-for-resurs-bank-payments');
                echo '</li>';

                echo '<li>';
                esc_html_e('This tester updates WooCommerce order status locally in WordPress.', 'tornevall-networks-toolbox-for-resurs-bank-payments');
                echo '</li>';
                echo '</ul></div>';
            }
        }

        if (isset($_GET['tornevall_resurs_status_error'])) {
            $error_message = sanitize_text_field(wp_unslash($_GET['tornevall_resurs_status_error']));
            echo '<div class="notice notice-error is-dismissible"><p>';
            echo esc_html($error_message);
            echo '</p></div>';
        }

        // Get all available WooCommerce order statuses
        $statuses = wc_get_order_statuses();
        $recent_orders = self::get_recent_orders_data(limit: 5);
        ?>
        <div class="tornevall-resurs-order-status-tester">
            <h2><?php esc_html_e('Order Status Tester', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></h2>
            <p><?php esc_html_e('Test and debug order status updates for Resurs Bank payments.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></p>
            <div id="tornevall-resurs-order-status-live-result" aria-live="polite"></div>

            <form method="post" action="" class="tornevall-resurs-form-tester" id="tornevall-resurs-form-tester" novalidate onsubmit="return false;">
                <input type="hidden" name="action" value="tornevall_resurs_order_status_tester">
                <?php wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME); ?>

                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            <label for="tornevall-resurs-order-id">
                                <?php esc_html_e('Order ID', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                            </label>
                        </th>
                        <td>
                            <div class="tornevall-resurs-order-quick-select-wrap">
                                <select
                                    id="tornevall-resurs-order-quick-select"
                                    class="regular-text"
                                    aria-label="<?php esc_attr_e('Quick-select recent order', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>"
                                >
                                    <option value=""><?php esc_html_e('— Select recent order —', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></option>
                                    <?php foreach ($recent_orders as $recent_order): ?>
                                        <option value="<?php echo esc_attr((string)$recent_order['id']); ?>">
                                            <?php
                                            echo esc_html(
                                                '#' . $recent_order['id'] .
                                                '  ' . $recent_order['customer'] .
                                                '  [' . $recent_order['status'] . ']' .
                                                '  ' . $recent_order['date']
                                            );
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="spinner tornevall-resurs-orders-spinner" id="tornevall-resurs-orders-spinner" aria-hidden="true"></span>
                            </div>
                            <input
                                type="number"
                                id="tornevall-resurs-order-id"
                                name="order_id"
                                class="regular-text"
                                min="1"
                                required
                                placeholder="<?php esc_attr_e('e.g., 123', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>"
                            >
                            <p class="description">
                                <?php esc_html_e('Select from recent orders above, or type an order ID manually.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="tornevall-resurs-status">
                                <?php esc_html_e('New Order Status', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                            </label>
                        </th>
                        <td>
                            <select id="tornevall-resurs-status" name="new_status" class="regular-text" required>
                                <option value=""><?php esc_html_e('— Select Status —', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></option>
                                <?php foreach ($statuses as $status_key => $status_label): ?>
                                    <option value="<?php echo esc_attr($status_key); ?>">
                                        <?php echo esc_html($status_label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php esc_html_e('Select the status to update the order to.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="tornevall-resurs-note">
                                <?php esc_html_e('Order Note', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                            </label>
                        </th>
                        <td>
                            <textarea 
                                id="tornevall-resurs-note" 
                                name="order_note" 
                                class="regular-text" 
                                rows="4"
                                placeholder="<?php esc_attr_e('Optional note to add to the order', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>"
                            ></textarea>
                            <p class="description">
                                <?php esc_html_e('Add an optional note when updating the order status.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <div class="tornevall-resurs-toolbox-submit">
                    <button type="button" class="button button-primary" id="tornevall-resurs-update-status-btn">
                        <?php esc_html_e('Update Order Status', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                    </button>
                    <span class="spinner" id="tornevall-resurs-update-status-spinner" aria-hidden="true"></span>
                </div>

                <div id="tornevall-resurs-order-status-progress" class="tornevall-resurs-order-status-progress" style="display:none;">
                    <p class="description" id="tornevall-resurs-order-status-progress-text"></p>
                </div>
            </form>

            <p class="description" style="margin-top: 18px;">
                <?php esc_html_e('Tip: Use the dropdown above to pick one of the five latest orders quickly.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Handle form submission for order status update.
     */
    public static function handle_form_submission() {
        // Check if this is our form submission
        if (!isset($_POST[self::NONCE_NAME])) {
            return;
        }

        if (self::is_resurs_production()) {
            wp_die(esc_html__('Order Status Tester is disabled when Resurs environment is Production.', 'tornevall-networks-toolbox-for-resurs-bank-payments'));
        }

        // Verify nonce
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            wp_die(esc_html__('Security check failed', 'tornevall-networks-toolbox-for-resurs-bank-payments'));
        }

        // Verify user has capability
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Insufficient permissions', 'tornevall-networks-toolbox-for-resurs-bank-payments'));
        }

        // Get and validate input
        $order_id = isset($_POST['order_id']) ? (int)sanitize_text_field(wp_unslash($_POST['order_id'])) : 0;
        $new_status = isset($_POST['new_status']) ? sanitize_text_field(wp_unslash($_POST['new_status'])) : '';
        $order_note = isset($_POST['order_note']) ? sanitize_textarea_field(wp_unslash($_POST['order_note'])) : '';

        $result = self::update_order_status(
            order_id: $order_id,
            new_status: $new_status,
            order_note: $order_note
        );

        if (is_wp_error($result)) {
            wp_safe_redirect(add_query_arg([
                'page' => 'wc-settings',
                'tab' => 'tornevall_resurs_toolbox',
                'section' => 'order_status_tester',
                'tornevall_resurs_status_error' => $result->get_error_message(),
            ], admin_url('admin.php')));
            exit;
        }

        wp_safe_redirect(add_query_arg([
            'page' => 'wc-settings',
            'tab' => 'tornevall_resurs_toolbox',
            'section' => 'order_status_tester',
            'tornevall_resurs_status_updated' => (string)$result['order_id'],
            'tornevall_resurs_old_status' => (string)$result['old_status'],
            'tornevall_resurs_new_status' => (string)$result['new_status'],
            'tornevall_resurs_status_changed_at' => (string)$result['updated_at'],
            'tornevall_resurs_status_actor' => (string)$result['updated_by'],
            'tornevall_resurs_status_note_added' => !empty($result['note_added']) ? '1' : '0',
        ], admin_url('admin.php')));
        exit;
    }

    /**
     * Update a WooCommerce order status and return structured result data.
     *
     * @return array<string, int|string|bool>|WP_Error
     */
    public static function update_order_status(int $order_id, string $new_status, string $order_note): array|WP_Error
    {
        if ($order_id <= 0) {
            return new WP_Error('invalid_order_id', __('Invalid order ID', 'tornevall-networks-toolbox-for-resurs-bank-payments'));
        }

        if ($new_status === '') {
            return new WP_Error('missing_status', __('Please select a status', 'tornevall-networks-toolbox-for-resurs-bank-payments'));
        }

        $order = wc_get_order($order_id);
        if (!$order instanceof WC_Order) {
            return new WP_Error(
                'order_not_found',
                sprintf(
                    __('Order #%d not found', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    $order_id
                )
            );
        }

        $status_to_set = strpos($new_status, 'wc-') === 0 ? substr($new_status, 3) : $new_status;
        $old_status_name = wc_get_order_status_name($order->get_status());
        $note_added = false;

        if ($order_note !== '') {
            $order->add_order_note(
                sprintf(
                    __('[Resurs Test] %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    $order_note
                )
            );
            $note_added = true;
        }

        try {
            $order->set_status($status_to_set);
            $order->save();
        } catch (Throwable $e) {
            return new WP_Error(
                'update_failed',
                sprintf(
                    __('Error updating order: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    $e->getMessage()
                )
            );
        }

        $current_user = wp_get_current_user();
        $updated_by = $current_user instanceof WP_User && $current_user->exists()
            ? ($current_user->display_name !== '' ? $current_user->display_name : $current_user->user_login)
            : '';

        $new_status_name = (string)wc_get_order_status_name($status_to_set);
        $detail_lines = [];
        $detail_lines[] = sprintf(
            __('Order: #%d', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            (int)$order->get_id()
        );
        $detail_lines[] = sprintf(
            __('Previous status: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            $old_status_name
        );
        $detail_lines[] = sprintf(
            __('New status: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            $new_status_name
        );

        if ($updated_by !== '') {
            $detail_lines[] = sprintf(
                __('Updated by: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                $updated_by
            );
        }

        $detail_lines[] = sprintf(
            __('Updated at: %s', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            current_time('mysql')
        );
        $detail_lines[] = $note_added
            ? __('Order note was added in this update.', 'tornevall-networks-toolbox-for-resurs-bank-payments')
            : __('No order note was added in this update.', 'tornevall-networks-toolbox-for-resurs-bank-payments');
        $detail_lines[] = __('This tester updates WooCommerce order status locally in WordPress.', 'tornevall-networks-toolbox-for-resurs-bank-payments');

        return [
            'order_id' => (int)$order->get_id(),
            'old_status' => (string)$old_status_name,
            'new_status' => $new_status_name,
            'updated_at' => (string)current_time('mysql'),
            'updated_by' => (string)$updated_by,
            'note_added' => $note_added,
            'success_message' => sprintf(
                __('Order #%d status updated to "%s"', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                (int)$order->get_id(),
                $new_status_name
            ),
            'details_title' => __('Status update details', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            'details' => $detail_lines,
        ];
    }

    /**
     * Use Resurs plugin environment setting (prod/test) as source of truth.
     */
    public static function is_resurs_production(): bool
    {
        $resurs_wp_util = '\\Resursbank\\Woocommerce\\Util\\WordPress';

        if (is_callable([$resurs_wp_util, 'getEnvironmentFromAdminAjax'])) {
            $ajax_environment = $resurs_wp_util::getEnvironmentFromAdminAjax();
            if ($ajax_environment !== null && $ajax_environment !== '') {
                return strtolower((string)$ajax_environment) === 'prod';
            }
        }

        $environment = get_option('resursbank_environment', 'test');

        return is_string($environment) && strtolower($environment) === 'prod';
    }

    /**
     * Shared recent-order formatter used by both initial server rendering and AJAX endpoint.
     *
     * @return array<int, array<string, int|string>>
     */
    public static function get_recent_orders_data(int $limit = 5): array
    {
        $orders = wc_get_orders([
            'limit'   => $limit,
            'orderby' => 'date',
            'order'   => 'DESC',
            'type'    => 'shop_order',
        ]);

        $data = [];
        foreach ($orders as $order) {
            $date = $order->get_date_created();
            $customer = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
            $data[] = [
                'id'         => (int)$order->get_id(),
                'date'       => $date ? $date->format('Y-m-d H:i') : '',
                'customer'   => $customer !== '' ? $customer : __('Guest', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'status'     => wc_get_order_status_name($order->get_status()),
                'status_key' => $order->get_status(),
                'total'      => wp_strip_all_tags($order->get_formatted_order_total()),
            ];
        }

        return $data;
    }
}

