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

        Tornevall_Resurs_Toolbox_Settings::register();
        Tornevall_Resurs_Toolbox_Part_Payment_Widget::init();
        Tornevall_Resurs_Toolbox_Settings_Tab::register();
    }


    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <?php
                printf(
                    /* translators: %s: WooCommerce plugin name */
                    __('Tornevall Networks Toolbox for Resurs Bank Payments requires %s to be installed and active.', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    '<strong>WooCommerce</strong>'
                );
                ?>
            </p>
        </div>
        <?php
    }
}

