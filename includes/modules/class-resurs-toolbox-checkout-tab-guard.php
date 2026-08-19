<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Prevents concurrent checkout tabs from submitting the same WooCommerce session.
 *
 * The browser tab identifier is only a coordination token. The WooCommerce session
 * remains authoritative and the cart/customer session is never cleared by this module.
 */
class Tornevall_Resurs_Toolbox_Checkout_Tab_Guard
{
    public const OPTION_ENABLED = 'tornevall_resurs_toolbox_checkout_tab_guard_enabled';
    public const FIELD_NAME = 'tornevall_checkout_tab_id';
    public const HEADER_NAME = 'X-Tornevall-Checkout-Tab';
    public const AJAX_ACTION = 'tornevall_checkout_tab_guard';
    public const NONCE_ACTION = 'tornevall_resurs_checkout_tab_guard';

    private const SESSION_KEY = 'tornevall_checkout_tab_guard';
    private const LOCK_TTL = 120;
    private const TEXT_DOMAIN = 'tornevall-networks-toolbox-for-resurs-bank-payments';

    public static function init(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_assets']);
        add_action('wc_ajax_' . self::AJAX_ACTION, [self::class, 'handle_lease_request']);
        add_action('woocommerce_after_checkout_validation', [self::class, 'validate_classic_checkout'], 5, 2);
        add_filter('rest_request_before_callbacks', [self::class, 'validate_store_api_checkout'], 5, 3);

        // Legacy Resurs Checkout posts its checkout form data to WC_Resurs_Bank instead
        // of passing through WC_Checkout::process_checkout(). Validate that path too.
        add_action('woocommerce_api_wc_resurs_bank', [self::class, 'validate_legacy_resurs_checkout'], -1000);
    }

    public static function is_enabled(): bool
    {
        $enabled = get_option(self::OPTION_ENABLED, '0') === '1';

        return (bool)apply_filters('tornevall_resurs_checkout_tab_guard_enabled', $enabled);
    }

    public static function enqueue_assets(): void
    {
        if (!self::is_enabled() || !self::is_checkout_screen()) {
            return;
        }

        $handle = 'tornevall-resurs-checkout-tab-guard';
        $scriptPath = TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'assets/checkout-tab-guard.js';
        $scriptVersion = file_exists($scriptPath)
            ? (string)filemtime($scriptPath)
            : TORNEVALL_RESURS_TOOLBOX_VERSION;

        wp_register_script(
            $handle,
            TORNEVALL_RESURS_TOOLBOX_PLUGIN_URL . 'assets/checkout-tab-guard.js',
            [],
            $scriptVersion,
            true
        );

        $endpoint = class_exists('WC_AJAX')
            ? WC_AJAX::get_endpoint(self::AJAX_ACTION)
            : add_query_arg('wc-ajax', self::AJAX_ACTION, home_url('/'));

        $config = [
            'endpoint' => $endpoint,
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
            'fieldName' => self::FIELD_NAME,
            'headerName' => self::HEADER_NAME,
            'heartbeatMs' => 20000,
            'blockedRetryMs' => 10000,
            'message' => __(
                'Checkout is already open in another tab. Close the other checkout tab before continuing.',
                self::TEXT_DOMAIN
            ),
        ];

        wp_enqueue_script($handle);
        wp_add_inline_script(
            $handle,
            'window.tornevallResursCheckoutTabGuard = ' . wp_json_encode($config) . ';',
            'before'
        );
    }

    public static function handle_lease_request(): void
    {
        if (!self::is_enabled()) {
            wp_send_json_error(['message' => 'Checkout tab guard is disabled.'], 404);
        }

        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        $session = self::get_session();
        if ($session === null) {
            wp_send_json_error([
                'message' => __('WooCommerce session is unavailable.', self::TEXT_DOMAIN),
            ], 409);
        }

        $operation = isset($_POST['operation'])
            ? sanitize_key(wp_unslash((string)$_POST['operation']))
            : 'claim';
        $tabId = self::extract_tab_id($_POST);

        if ($tabId === '') {
            wp_send_json_error([
                'message' => __('Invalid checkout tab identifier.', self::TEXT_DOMAIN),
            ], 400);
        }

        $lock = self::get_lock();
        $now = time();
        $owner = false;

        if ($operation === 'release') {
            if (self::lock_belongs_to($lock, $tabId)) {
                self::clear_lock();
            }

            wp_send_json_success([
                'owner' => false,
                'released' => true,
            ]);
        }

        if (!in_array($operation, ['claim', 'heartbeat'], true)) {
            wp_send_json_error([
                'message' => __('Invalid checkout tab guard operation.', self::TEXT_DOMAIN),
            ], 400);
        }

        if (
            $lock === null
            || self::lock_belongs_to($lock, $tabId)
            || self::lock_is_expired($lock, $now)
        ) {
            self::set_lock($tabId, $now);
            $owner = true;
        }

        wp_send_json_success([
            'owner' => $owner,
            'expiresIn' => $owner ? self::LOCK_TTL : self::remaining_lock_seconds($lock, $now),
        ]);
    }

    /**
     * Classic checkout validation runs before the payment gateway's process_payment().
     */
    public static function validate_classic_checkout(array $data, WP_Error $errors): void
    {
        unset($data);

        if (!self::is_enabled()) {
            return;
        }

        $tabId = self::extract_tab_id($_POST);
        if (!self::is_current_owner($tabId)) {
            $errors->add('tornevall_checkout_tab_conflict', self::get_conflict_message());
            return;
        }

        self::touch_lock($tabId);
    }

    /**
     * Checkout Block submits through the WooCommerce Store API instead of the
     * classic checkout form. Reject a stale tab before the checkout callback runs.
     *
     * @param mixed $response
     * @param mixed $handler
     * @return mixed
     */
    public static function validate_store_api_checkout($response, $handler, WP_REST_Request $request)
    {
        unset($handler);

        if (!self::is_enabled() || is_wp_error($response)) {
            return $response;
        }

        if (!self::is_store_api_checkout_request($request)) {
            return $response;
        }

        $tabId = self::sanitize_tab_id((string)$request->get_header(self::HEADER_NAME));
        if (!self::is_current_owner($tabId)) {
            return new WP_Error(
                'tornevall_checkout_tab_conflict',
                self::get_conflict_message(),
                ['status' => 409]
            );
        }

        self::touch_lock($tabId);

        return $response;
    }

    /**
     * Compatibility guard for the legacy Resurs Checkout/RCO pre-book endpoint.
     * Both the old and facelift RCO clients include checkout form fields in the
     * pre-book payload, either flat or below the wooCommerce key.
     */
    public static function validate_legacy_resurs_checkout(): void
    {
        if (!self::is_enabled()) {
            return;
        }

        $eventType = isset($_REQUEST['event-type'])
            ? sanitize_key(wp_unslash((string)$_REQUEST['event-type']))
            : '';

        if ($eventType !== 'prepare-omni-order') {
            return;
        }

        $tabId = self::extract_tab_id($_POST);
        if (self::is_current_owner($tabId)) {
            self::touch_lock($tabId);
            return;
        }

        wp_send_json([
            'success' => false,
            'errorCode' => 409,
            'errorString' => self::get_conflict_message(),
        ], 409);
    }

    public static function render_enabled_field(): void
    {
        $enabled = self::is_enabled();
        ?>
        <fieldset>
            <label>
                <input type="hidden" name="<?php echo esc_attr(self::OPTION_ENABLED); ?>" value="0" />
                <input
                    type="checkbox"
                    name="<?php echo esc_attr(self::OPTION_ENABLED); ?>"
                    value="1"
                    <?php checked(true, $enabled); ?>
                />
                <?php esc_html_e('Prevent concurrent checkout tabs', self::TEXT_DOMAIN); ?>
            </label>
            <p class="description">
                <?php esc_html_e(
                    'Allows one active checkout tab per WooCommerce session. Other tabs are blocked from submitting payment without clearing the cart or customer session.',
                    self::TEXT_DOMAIN
                ); ?>
            </p>
        </fieldset>
        <?php
    }

    public static function save_setting(): void
    {
        $raw = isset($_POST[self::OPTION_ENABLED])
            ? sanitize_text_field(wp_unslash((string)$_POST[self::OPTION_ENABLED]))
            : '0';

        update_option(self::OPTION_ENABLED, $raw === '1' ? '1' : '0');
    }

    private static function is_checkout_screen(): bool
    {
        if (!function_exists('is_checkout') || !is_checkout()) {
            return false;
        }

        if (function_exists('is_order_received_page') && is_order_received_page()) {
            return false;
        }

        if (function_exists('is_checkout_pay_page') && is_checkout_pay_page()) {
            return false;
        }

        return true;
    }

    private static function is_store_api_checkout_request(WP_REST_Request $request): bool
    {
        if (strtoupper($request->get_method()) !== 'POST') {
            return false;
        }

        $route = rtrim($request->get_route(), '/');

        return preg_match('#^/wc/store/v[0-9]+/checkout$#', $route) === 1;
    }

    private static function get_session(): ?WC_Session
    {
        if (!function_exists('WC')) {
            return null;
        }

        $woocommerce = WC();
        if (!$woocommerce || !$woocommerce->session instanceof WC_Session) {
            return null;
        }

        return $woocommerce->session;
    }

    /**
     * @return array{tabId:string,lastSeen:int}|null
     */
    private static function get_lock(): ?array
    {
        $session = self::get_session();
        if ($session === null) {
            return null;
        }

        $lock = $session->get(self::SESSION_KEY);
        if (!is_array($lock)) {
            return null;
        }

        $tabId = isset($lock['tabId']) ? self::sanitize_tab_id((string)$lock['tabId']) : '';
        $lastSeen = isset($lock['lastSeen']) ? (int)$lock['lastSeen'] : 0;

        if ($tabId === '' || $lastSeen <= 0) {
            return null;
        }

        return [
            'tabId' => $tabId,
            'lastSeen' => $lastSeen,
        ];
    }

    private static function set_lock(string $tabId, int $timestamp): void
    {
        $session = self::get_session();
        if ($session === null) {
            return;
        }

        $session->set(self::SESSION_KEY, [
            'tabId' => $tabId,
            'lastSeen' => $timestamp,
        ]);
    }

    private static function clear_lock(): void
    {
        $session = self::get_session();
        if ($session === null) {
            return;
        }

        $session->__unset(self::SESSION_KEY);
    }

    private static function touch_lock(string $tabId): void
    {
        if ($tabId === '') {
            return;
        }

        $lock = self::get_lock();
        if (self::lock_belongs_to($lock, $tabId)) {
            self::set_lock($tabId, time());
        }
    }

    private static function is_current_owner(string $tabId): bool
    {
        if ($tabId === '') {
            return false;
        }

        return self::lock_belongs_to(self::get_lock(), $tabId);
    }

    /**
     * @param array{tabId:string,lastSeen:int}|null $lock
     */
    private static function lock_belongs_to(?array $lock, string $tabId): bool
    {
        return $lock !== null && hash_equals($lock['tabId'], $tabId);
    }

    /**
     * @param array{tabId:string,lastSeen:int}|null $lock
     */
    private static function lock_is_expired(?array $lock, int $now): bool
    {
        if ($lock === null) {
            return true;
        }

        return ($now - $lock['lastSeen']) > self::LOCK_TTL;
    }

    /**
     * @param array{tabId:string,lastSeen:int}|null $lock
     */
    private static function remaining_lock_seconds(?array $lock, int $now): int
    {
        if ($lock === null) {
            return 0;
        }

        return max(0, self::LOCK_TTL - ($now - $lock['lastSeen']));
    }

    /**
     * @param array<string,mixed> $source
     */
    private static function extract_tab_id(array $source): string
    {
        if (isset($source[self::FIELD_NAME])) {
            return self::sanitize_tab_id((string)wp_unslash($source[self::FIELD_NAME]));
        }

        if (
            isset($source['wooCommerce'])
            && is_array($source['wooCommerce'])
            && isset($source['wooCommerce'][self::FIELD_NAME])
        ) {
            return self::sanitize_tab_id((string)wp_unslash($source['wooCommerce'][self::FIELD_NAME]));
        }

        return '';
    }

    private static function sanitize_tab_id(string $tabId): string
    {
        $tabId = strtolower(trim($tabId));
        if (preg_match('/^[a-z0-9-]{16,64}$/', $tabId) !== 1) {
            return '';
        }

        return $tabId;
    }

    private static function get_conflict_message(): string
    {
        return __(
            'Checkout is already open in another tab. Close the other checkout tab before continuing.',
            self::TEXT_DOMAIN
        );
    }
}
