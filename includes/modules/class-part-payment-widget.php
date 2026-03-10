<?php
/**
 * Part Payment Widget Module for Tornevalls Toolbox
 *
 * Handles rendering of the Part Payment widget as a shortcode or via filter.
 * Can be configured to use either auto-rendering via action hook or manual shortcode.
 *
 * @package Tornevalls\ToolboxResurs
 */

namespace Tornevalls\ToolboxResurs\Modules;

use Exception;
use Tornevalls\ToolboxResurs\Tornevall_Toolbox_Resurs_Settings;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Part Payment Widget Module
 */
class Part_Payment_Widget {


    /**
     * Initialize the module.
     */
    public static function init(): void {
        $enabled = get_option(
            Tornevall_Toolbox_Resurs_Settings::OPTION_PP_SHORTCODE_ENABLED,
            '0'
        );

        $shortcodeName = (string) get_option(
            Tornevall_Toolbox_Resurs_Settings::OPTION_PP_SHORTCODE_NAME,
            Tornevall_Toolbox_Resurs_Settings::DEFAULT_SHORTCODE_NAME
        );

        $shortcodeName = sanitize_key($shortcodeName);
        if ($shortcodeName === '') {
            $shortcodeName = Tornevall_Toolbox_Resurs_Settings::DEFAULT_SHORTCODE_NAME;
        }

        // Register the shortcode
        add_shortcode($shortcodeName, [self::class, 'render_shortcode']);

        if ($enabled === '1') {
            // Remove after all plugin init has completed.
            add_action('wp', static function (): void {
                remove_action(
                    'woocommerce_single_product_summary',
                    'Resursbank\\Woocommerce\\Modules\\PartPayment\\PartPayment::renderWidget'
                );
            }, 1);
        }
    }

    /**
     * Render the Part Payment widget via shortcode.
     *
     * @param array $atts Shortcode attributes.
     */
    public static function render_shortcode(array $atts = []): string {
        try {
            if (!class_exists('\\Resursbank\\Woocommerce\\Modules\\PartPayment\\PartPayment')) {
                return '';
            }

            ob_start();
            \Resursbank\Woocommerce\Modules\PartPayment\PartPayment::renderWidget();
            return (string) (ob_get_clean() ?: '');
        } catch (Exception $e) {
            return '';
        }
    }
}
