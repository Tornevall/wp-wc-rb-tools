<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tornevall_Resurs_Toolbox_Plugin {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        register_activation_hook(TORNEVALL_RESURS_TOOLBOX_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(TORNEVALL_RESURS_TOOLBOX_PLUGIN_FILE, [$this, 'deactivate']);

        add_action('plugins_loaded', [$this, 'init']);
    }

    public function activate() {
        update_option('tornevall_resurs_toolbox_version', TORNEVALL_RESURS_TOOLBOX_VERSION);
        delete_option('tornevalls_resurs_version');
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function init() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }

        add_action('woocommerce_api_wc_resurs_bank', ['Tornevall_Resurs_Toolbox_Ip_Info_Proxy', 'maybe_handle_request'], 0);
        add_action('admin_menu', [$this, 'register_woocommerce_submenu_link'], 99);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        Tornevall_Resurs_Toolbox_Settings::register();
        Tornevall_Resurs_Toolbox_Part_Payment_Widget::init();
        Tornevall_Resurs_Toolbox_Order_Status_Tester::init();
        Tornevall_Resurs_Toolbox_Settings_Tab::register();
    }

    /**
     * Add a direct entry under WooCommerce menu to the toolbox settings tab.
     */
    public function register_woocommerce_submenu_link(): void {
        add_submenu_page(
            'woocommerce',
            __('Tornevalls Toolbox for Resurs', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            __('Tornevalls Toolbox for Resurs', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            'manage_woocommerce',
            'admin.php?page=wc-settings&tab=tornevall_resurs_toolbox'
        );
    }

    public function enqueue_admin_assets($hook_suffix) {
        if (!$this->is_toolbox_settings_screen($hook_suffix)) {
            return;
        }

        $style_handle = 'tornevall-resurs-toolbox-admin-page';
        $script_handle = 'tornevall-resurs-toolbox-admin-page';
        $style_path = TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'assets/admin-page.css';
        $script_path = TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'assets/admin-page.js';
        $style_version = file_exists($style_path) ? (string)filemtime($style_path) : TORNEVALL_RESURS_TOOLBOX_VERSION;
        $script_version = file_exists($script_path) ? (string)filemtime($script_path) : TORNEVALL_RESURS_TOOLBOX_VERSION;
        $resurs_environment = get_option('resursbank_environment', 'test');
        $is_resurs_production = is_string($resurs_environment) && strtolower($resurs_environment) === 'prod';

        wp_register_style(
            $style_handle,
            TORNEVALL_RESURS_TOOLBOX_PLUGIN_URL . 'assets/admin-page.css',
            [],
            $style_version
        );
        wp_enqueue_style($style_handle);

        wp_register_script(
            $script_handle,
            TORNEVALL_RESURS_TOOLBOX_PLUGIN_URL . 'assets/admin-page.js',
            [],
            $script_version,
            true
        );

        $config = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'orderStatusTester' => [
                'nonce'             => wp_create_nonce('tornevall_resurs_order_status_nonce'),
                'enabled'           => !$is_resurs_production,
                'selectPlaceholder' => __('— Select recent order —', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'loadingText'       => __('Loading orders...', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'updatingText'      => __('Updating status...', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'guestLabel'        => __('Guest', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'errorText'         => __('Could not load orders', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'updateErrorText'   => __('Could not update order status', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'invalidOrderIdText'=> __('Invalid order ID', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'selectStatusText'  => __('Please select a status', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'disabledText'      => __('Order Status Tester is disabled when Resurs environment is Production.', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            ],
            'messages' => [
                'latestVersion' => __('Latest version on Bitbucket:', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'updateAvailable' => __('An update is available!', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'yourVersion' => __('Your version:', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'viewRelease' => __('View Bitbucket Release', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'viewOnWordPress' => __('View on WordPress.com', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'upgradeInPlugins' => __('Upgrade in Plugins', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'devDetected' => __('Development Version Detected', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'runningVersion' => __('You are running version', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'newerThanStable' => __('which is newer than the latest stable release', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'thisCouldBe' => __('This could be:', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'devPreRelease' => __('A development/pre-release version', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'hotfixNotTagged' => __('A hotfix not yet tagged on Bitbucket', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'unreleasedUpdate' => __('An unreleased update from Resurs Bank', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'verifyInstall' => __('If you did not intentionally upgrade, please verify your installation.', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'latestStable' => __('You are running the latest stable version!', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'errorChecking' => __('Error checking version:', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'errorLabel' => __('Error:', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                'failedConnect' => __('Failed to connect to the server', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
            ],
        ];

        wp_enqueue_script($script_handle);
        wp_add_inline_script(
            $script_handle,
            'window.tornevallResursToolboxAdmin = ' . wp_json_encode($config) . ';',
            'before'
        );
    }

    private function is_toolbox_settings_screen($hook_suffix) {
        return is_string($hook_suffix) && 'woocommerce_page_wc-settings' === $hook_suffix;
    }



    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: WooCommerce plugin name. */
                    esc_html__('Tornevall Networks Toolbox for Resurs Bank Payments requires %s to be installed and active.', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    '<strong>WooCommerce</strong>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}
