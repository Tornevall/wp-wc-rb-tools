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
        // Translations are auto-loaded on WordPress.org; no manual load needed.

        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
            return;
        }

        Tornevall_Toolbox_Resurs_Settings_Tab::register();
    }


    public function woocommerce_missing_notice() {
        $message = sprintf(
            /* translators: %s: WooCommerce plugin name */
            esc_html__('Tornevalls Tools for Resurs Bank requires %s to be installed and active.', 'tornevalls-tools-for-resurs-bank-payments'),
            '<strong>' . esc_html__('WooCommerce', 'tornevalls-tools-for-resurs-bank-payments') . '</strong>'
        );
        ?>
        <div class="notice notice-error">
            <p>
                <?php echo wp_kses($message, ['strong' => []]); ?>
            </p>
        </div>
        <?php
    }
}
