<?php

namespace Tornevalls\ToolboxResurs;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles registration and management of plugin settings.
 */
class Tornevall_Toolbox_Resurs_Settings {
    public const OPTION_PP_SHORTCODE_ENABLED = 'tornevalls_resurs_pp_shortcode_enabled';
    public const OPTION_PP_SHORTCODE_NAME = 'tornevalls_resurs_pp_shortcode_name';
    public const DEFAULT_SHORTCODE_NAME = 'resurs_partpayment_widget';

    public static function register(): void {
        add_action('admin_init', [self::class, 'register_settings']);
    }

    /**
     * Register settings fields for admin page.
     */
    public static function register_settings(): void {
        register_setting(
            'tornevalls_resurs_pp_shortcode_settings',
            self::OPTION_PP_SHORTCODE_ENABLED,
            [
                'sanitize_callback' => [self::class, 'sanitize_enabled'],
                'default' => '0',
            ]
        );

        register_setting(
            'tornevalls_resurs_pp_shortcode_settings',
            self::OPTION_PP_SHORTCODE_NAME,
            [
                'sanitize_callback' => [self::class, 'sanitize_shortcode_name'],
                'default' => self::DEFAULT_SHORTCODE_NAME,
            ]
        );

        add_settings_section(
            'tornevalls_resurs_pp_shortcode_section',
            __('Part Payment Widget Settings', 'tornevalls-tools-for-resurs-bank-payments'),
            [self::class, 'render_section_description'],
            'tornevalls_resurs_pp_shortcode_settings'
        );

        add_settings_field(
            'tornevalls_resurs_pp_shortcode_enabled',
            __('Enable Shortcode Rendering', 'tornevalls-tools-for-resurs-bank-payments'),
            [self::class, 'render_enabled_field'],
            'tornevalls_resurs_pp_shortcode_settings',
            'tornevalls_resurs_pp_shortcode_section'
        );

        add_settings_field(
            'tornevalls_resurs_pp_shortcode_name',
            __('Shortcode Name', 'tornevalls-tools-for-resurs-bank-payments'),
            [self::class, 'render_shortcode_name_field'],
            'tornevalls_resurs_pp_shortcode_settings',
            'tornevalls_resurs_pp_shortcode_section'
        );
    }

    /**
     * Render section description.
     */
    public static function render_section_description(): void {
        echo '<p>';
        esc_html_e('Control how the Part Payment widget is rendered on your site.', 'tornevalls-tools-for-resurs-bank-payments');
        echo '</p>';
    }

    /**
     * Render enabled checkbox field.
     */
    public static function render_enabled_field(): void {
        $enabled = get_option(self::OPTION_PP_SHORTCODE_ENABLED, '0');
        ?>
        <fieldset>
            <label>
                <input
                    type="hidden"
                    name="<?php echo esc_attr(self::OPTION_PP_SHORTCODE_ENABLED); ?>"
                    value="0"
                />
                <input
                    type="checkbox"
                    name="<?php echo esc_attr(self::OPTION_PP_SHORTCODE_ENABLED); ?>"
                    value="1"
                    <?php checked('1', $enabled); ?>
                />
                <?php esc_html_e('Disable default widget rendering and use shortcode instead', 'tornevalls-tools-for-resurs-bank-payments'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('When enabled, the Part Payment widget will not render automatically on product pages. Use the shortcode below instead.', 'tornevalls-tools-for-resurs-bank-payments'); ?>
            </p>
        </fieldset>
        <?php
    }

    /**
     * Render shortcode name field.
     */
    public static function render_shortcode_name_field(): void {
        $shortcode_name = get_option(self::OPTION_PP_SHORTCODE_NAME, self::DEFAULT_SHORTCODE_NAME);
        ?>
        <fieldset>
            <input
                type="text"
                name="<?php echo esc_attr(self::OPTION_PP_SHORTCODE_NAME); ?>"
                value="<?php echo esc_attr($shortcode_name); ?>"
                placeholder="<?php echo esc_attr(self::DEFAULT_SHORTCODE_NAME); ?>"
                class="regular-text"
            />
            <p class="description">
                <?php
                esc_html_e('Use this shortcode on your pages: [', 'tornevalls-tools-for-resurs-bank-payments');
                echo esc_html($shortcode_name);
                esc_html_e(']', 'tornevalls-tools-for-resurs-bank-payments');
                ?>
            </p>
        </fieldset>
        <?php
    }

    /**
     * Sanitize enabled checkbox value.
     */
    public static function sanitize_enabled($value) {
        return (string) $value === '1' ? '1' : '0';
    }

    /**
     * Sanitize shortcode name.
     */
    public static function sanitize_shortcode_name($value) {
        // Allow only alphanumeric, underscore, and hyphen
        $sanitized = sanitize_key($value);

        // Ensure it's not empty
        if (empty($sanitized)) {
            $sanitized = self::DEFAULT_SHORTCODE_NAME;
        }

        return $sanitized;
    }

    /**
     * Save settings from the WooCommerce tab form submission.
     */
    public static function save_from_woocommerce(): void {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $enabledRaw = $_POST[self::OPTION_PP_SHORTCODE_ENABLED] ?? '0';
        $nameRaw = $_POST[self::OPTION_PP_SHORTCODE_NAME] ?? self::DEFAULT_SHORTCODE_NAME;

        $enabled = self::sanitize_enabled(is_string($enabledRaw) ? wp_unslash($enabledRaw) : '0');
        $shortcodeName = self::sanitize_shortcode_name(is_string($nameRaw) ? wp_unslash($nameRaw) : self::DEFAULT_SHORTCODE_NAME);

        update_option(self::OPTION_PP_SHORTCODE_ENABLED, $enabled);
        update_option(self::OPTION_PP_SHORTCODE_NAME, $shortcodeName);
    }

    /**
     * Runtime behavior (remove_action + shortcode rendering) is handled by
     * Modules\Part_Payment_Widget::init().
     */
}
