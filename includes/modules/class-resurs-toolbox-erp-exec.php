<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ERP Exec Module
 *
 * Provides direct capture/cancel/refund execution against Resurs Bank payments
 * without going through WooCommerce status transitions.
 *
 * Uses the main Resurs Bank plugin's OrderManagement classes,
 * but all code here lives entirely in this toolbox plugin.
 */
class Tornevall_Resurs_Toolbox_Erp_Exec
{
    /**
     * Execute a direct ERP payment action for a Resurs order.
     *
     * @param string               $action    capture|cancel|refund
     * @param array<string, mixed> $orderInfo Must include 'order_id'. Optional: 'amount', 'reason'.
     * @return array<string, mixed>
     * @throws RuntimeException|InvalidArgumentException
     */
    public static function exec(string $action, array $orderInfo): array
    {
        $action = strtolower(trim($action));

        $orderIdRaw = $orderInfo['order_id'] ?? $orderInfo['id'] ?? null;

        if (!is_numeric($orderIdRaw) || (int)$orderIdRaw <= 0) {
            throw new InvalidArgumentException('ERP request is missing a valid order_id.');
        }

        $orderId = (int)$orderIdRaw;
        $order   = wc_get_order($orderId);

        if (!$order instanceof WC_Order) {
            throw new RuntimeException(sprintf('Order %d could not be resolved.', $orderId));
        }

        if (
            !class_exists('\Resursbank\Woocommerce\Util\Metadata') ||
            !\Resursbank\Woocommerce\Util\Metadata::isValidResursPayment(order: $order)
        ) {
            throw new RuntimeException('Order is not linked to a valid Resurs payment.');
        }

        do_action('resursbank_erp_exec_before', $action, $order, $orderInfo);

        $result = match ($action) {
            'capture' => self::execCapture($order),
            'cancel'  => self::execCancel($order),
            'refund'  => self::execRefund($order, $orderInfo),
            default   => throw new InvalidArgumentException(
                sprintf('Unsupported ERP action "%s". Allowed: capture, cancel, refund.', $action)
            ),
        };

        do_action('resursbank_erp_exec_after', $action, $order, $orderInfo, $result);

        return $result;
    }

    /**
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    private static function execCapture(WC_Order $order): array
    {
        if (
            !class_exists('\Resursbank\Woocommerce\Database\Options\OrderManagement\EnableCapture') ||
            !\Resursbank\Woocommerce\Database\Options\OrderManagement\EnableCapture::isEnabled()
        ) {
            throw new RuntimeException('Capture is disabled in Resurs plugin settings.');
        }

        if (!\Resursbank\Woocommerce\Modules\OrderManagement\OrderManagement::canCapture(order: $order)) {
            throw new RuntimeException('Capture is not allowed for the current payment state.');
        }

        \Resursbank\Woocommerce\Modules\OrderManagement\Action\Capture::exec(order: $order);

        return ['ok' => true, 'action' => 'capture', 'order_id' => (int)$order->get_id()];
    }

    /**
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    private static function execCancel(WC_Order $order): array
    {
        if (
            !class_exists('\Resursbank\Woocommerce\Database\Options\OrderManagement\EnableCancel') ||
            !\Resursbank\Woocommerce\Database\Options\OrderManagement\EnableCancel::isEnabled()
        ) {
            throw new RuntimeException('Cancel is disabled in Resurs plugin settings.');
        }

        if (!\Resursbank\Woocommerce\Modules\OrderManagement\OrderManagement::canCancel(order: $order)) {
            throw new RuntimeException('Cancel is not allowed for the current payment state.');
        }

        \Resursbank\Woocommerce\Modules\OrderManagement\Action\Cancel::exec(order: $order);

        return ['ok' => true, 'action' => 'cancel', 'order_id' => (int)$order->get_id()];
    }

    /**
     * @param array<string, mixed> $orderInfo
     * @return array<string, mixed>
     * @throws RuntimeException
     */
    private static function execRefund(WC_Order $order, array $orderInfo): array
    {
        if (
            !class_exists('\Resursbank\Woocommerce\Database\Options\OrderManagement\EnableRefund') ||
            !\Resursbank\Woocommerce\Database\Options\OrderManagement\EnableRefund::isEnabled()
        ) {
            throw new RuntimeException('Refund is disabled in Resurs plugin settings.');
        }

        if (!\Resursbank\Woocommerce\Modules\OrderManagement\OrderManagement::canRefund(order: $order)) {
            throw new RuntimeException('Refund is not allowed for the current payment state.');
        }

        $total     = (float)$order->get_total();
        $refunded  = (float)$order->get_total_refunded();
        $remaining = max(0.0, $total - $refunded);
        $amount    = isset($orderInfo['amount']) ? (float)$orderInfo['amount'] : $remaining;
        $reason    = (string)($orderInfo['reason'] ?? 'Refund triggered via ERP execution.');

        if ($amount <= 0.0) {
            throw new RuntimeException('Refund amount must be greater than zero.');
        }

        if ($amount > $remaining) {
            throw new RuntimeException(
                sprintf('Refund amount %.2f exceeds remaining refundable %.2f.', $amount, $remaining)
            );
        }

        $refundResult = wc_create_refund([
            'amount'         => $amount,
            'reason'         => $reason,
            'order_id'       => $order->get_id(),
            // The Resurs plugin handles the actual payment refund via its own hooks.
            'refund_payment' => false,
            'restock_items'  => false,
        ]);

        if ($refundResult instanceof WP_Error) {
            throw new RuntimeException('Refund failed: ' . $refundResult->get_error_message());
        }

        if (!$refundResult instanceof WC_Order_Refund) {
            throw new RuntimeException('Refund creation returned unexpected type.');
        }

        return [
            'ok'        => true,
            'action'    => 'refund',
            'order_id'  => (int)$order->get_id(),
            'refund_id' => (int)$refundResult->get_id(),
            'amount'    => $amount,
        ];
    }
}

