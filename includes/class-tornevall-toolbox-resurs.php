<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tornevall_Toolbox_Resurs {
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
        register_activation_hook(TORNEVALLS_RESURS_PLUGIN_FILE, [$this, 'activate']);
        register_deactivation_hook(TORNEVALLS_RESURS_PLUGIN_FILE, [$this, 'deactivate']);

        add_action('plugins_loaded', [$this, 'init']);
    }

    public function activate() {
        add_option('tornevalls_resurs_version', TORNEVALLS_RESURS_VERSION);
        flush_rewrite_rules();
    }

    public function deactivate() {
        flush_rewrite_rules();
    }

    public function init() {
        load_plugin_textdomain(
            'tornevalls-tools-for-resurs-bank-payments',
            false,
            dirname(TORNEVALLS_RESURS_PLUGIN_BASENAME) . '/languages'
        );

        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }

        Tornevall_Toolbox_Resurs_Settings_Tab::register();
    }


    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: WooCommerce plugin name */
                    __('Tornevalls Tools for Resurs Bank requires %s to be installed and active.', 'tornevalls-tools-for-resurs-bank-payments'),
                    '<strong>WooCommerce</strong>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}
