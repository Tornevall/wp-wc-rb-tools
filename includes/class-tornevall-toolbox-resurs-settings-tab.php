<?php

namespace Tornevalls\ToolboxResurs;

if (!defined('ABSPATH')) {
    exit;
}

class Tornevall_Toolbox_Resurs_Settings_Tab {
    public const TAB_KEY = 'resurs_toolbox';
    public const RESURS_PLUGIN_FILE = 'resurs-bank-payments-for-woocommerce/resurs-bank-payments-for-woocommerce.php';

    public static function register(): void {
        add_filter('woocommerce_settings_tabs_array', [self::class, 'add_tab'], 999);
        add_action('woocommerce_settings_tabs_' . self::TAB_KEY, [self::class, 'render']);
        add_action('woocommerce_update_options_' . self::TAB_KEY, [Tornevall_Toolbox_Resurs_Settings::class, 'save_from_woocommerce']);
    }

    public static function add_tab(array $tabs): array {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $label = __('Tornevalls Toolbox for Resurs Bank', 'tornevalls-tools-for-resurs-bank-payments');
        $toolbox_tab = [self::TAB_KEY => $label];

        if (is_plugin_active(self::RESURS_PLUGIN_FILE) && array_key_exists('resursbank', $tabs)) {
            // Insert directly after Resurs tab if active
            $new_tabs = [];
            foreach ($tabs as $key => $value) {
                $new_tabs[$key] = $value;
                if ($key === 'resursbank') {
                    $new_tabs[self::TAB_KEY] = $label;
                }
            }
            return $new_tabs;
        }

        // Otherwise append to end
        return array_merge($tabs, $toolbox_tab);
    }

    public static function render(): void {
        Tornevall_Toolbox_Resurs_Admin_Page::render();
    }
}
