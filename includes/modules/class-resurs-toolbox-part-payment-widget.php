<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tornevall_Resurs_Toolbox_Part_Payment_Widget {
    public static function init(): void {
        $shortcodeName = Tornevall_Resurs_Toolbox_Settings::get_shortcode_name();

        add_shortcode($shortcodeName, [self::class, 'render_shortcode']);

        if ('1' === Tornevall_Resurs_Toolbox_Settings::get_shortcode_enabled()) {
            add_action('wp', [self::class, 'disable_default_rendering'], 1);
        }
    }

    public static function disable_default_rendering(): void {
        remove_action(
            'woocommerce_single_product_summary',
            'Resursbank\\Woocommerce\\Modules\\PartPayment\\PartPayment::renderWidget'
        );
    }

    public static function render_shortcode(array $atts = []): string {
        unset($atts);

        try {
            if (!class_exists('\\Resursbank\\Woocommerce\\Modules\\PartPayment\\PartPayment')) {
                return '';
            }

            ob_start();
            \Resursbank\Woocommerce\Modules\PartPayment\PartPayment::renderWidget();
            return (string) (ob_get_clean() ?: '');
        } catch (Throwable $throwable) {
            return '';
        }
    }
}

