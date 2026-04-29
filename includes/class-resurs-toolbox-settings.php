<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tornevall_Resurs_Toolbox_Settings {
    public const OPTION_PP_SHORTCODE_ENABLED = 'tornevall_resurs_toolbox_part_payment_shortcode_enabled';
    public const OPTION_PP_SHORTCODE_NAME = 'tornevall_resurs_toolbox_part_payment_shortcode_name';
    public const LEGACY_OPTION_PP_SHORTCODE_ENABLED = 'tornevalls_resurs_pp_shortcode_enabled';
    public const LEGACY_OPTION_PP_SHORTCODE_NAME = 'tornevalls_resurs_pp_shortcode_name';
    public const DEFAULT_SHORTCODE_NAME = 'resurs_partpayment_widget';
    public const SETTINGS_NONCE_ACTION = 'tornevall_resurs_toolbox_part_payment_settings';
    public const SETTINGS_NONCE_NAME = 'tornevall_resurs_toolbox_part_payment_settings_nonce';
    public const OPTION_SHOW_WC_SETTINGS_TAB = 'tornevall_resurs_toolbox_show_wc_settings_tab';

    public static function register(): void {
        add_action('admin_init', [self::class, 'maybe_migrate_legacy_options']);
    }

    public static function maybe_migrate_legacy_options(): void {
        $newEnabled = get_option(self::OPTION_PP_SHORTCODE_ENABLED, null);
        $newShortcode = get_option(self::OPTION_PP_SHORTCODE_NAME, null);

        if (null === $newEnabled) {
            $legacyEnabled = get_option(self::LEGACY_OPTION_PP_SHORTCODE_ENABLED, null);
            if (null !== $legacyEnabled) {
                update_option(self::OPTION_PP_SHORTCODE_ENABLED, self::sanitize_enabled($legacyEnabled));
            }
        }

        if (null === $newShortcode) {
            $legacyShortcode = get_option(self::LEGACY_OPTION_PP_SHORTCODE_NAME, null);
            if (null !== $legacyShortcode) {
                update_option(self::OPTION_PP_SHORTCODE_NAME, self::sanitize_shortcode_name($legacyShortcode));
            }
        }
    }

    public static function get_shortcode_enabled(): string {
        $value = get_option(self::OPTION_PP_SHORTCODE_ENABLED, null);

        if (null === $value) {
            $value = get_option(self::LEGACY_OPTION_PP_SHORTCODE_ENABLED, '0');
        }

        return self::sanitize_enabled($value);
    }

    public static function get_shortcode_name(): string {
        $value = get_option(self::OPTION_PP_SHORTCODE_NAME, null);

        if (null === $value) {
            $value = get_option(self::LEGACY_OPTION_PP_SHORTCODE_NAME, self::DEFAULT_SHORTCODE_NAME);
        }

        return self::sanitize_shortcode_name($value);
    }

    public static function get_show_wc_settings_tab(): string {
        $value = get_option(self::OPTION_SHOW_WC_SETTINGS_TAB, '0');
        return self::sanitize_enabled($value);
    }

    public static function render_section_description(): void {
        echo '<p>';
        esc_html_e('Control how the Part Payment widget is rendered on your site.', 'tornevall-networks-toolbox-for-resurs-bank-payments');
        echo '</p>';
    }

    public static function render_enabled_field(): void {
        $enabled = self::get_shortcode_enabled();
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
                <?php esc_html_e('Disable default widget rendering and use shortcode instead', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('When enabled, the Part Payment widget will not render automatically on product pages. Use the shortcode below instead.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
            </p>
        </fieldset>
        <?php
    }

    public static function render_shortcode_name_field(): void {
        $shortcodeName = self::get_shortcode_name();
        ?>
        <fieldset>
            <input
                type="text"
                name="<?php echo esc_attr(self::OPTION_PP_SHORTCODE_NAME); ?>"
                value="<?php echo esc_attr($shortcodeName); ?>"
                placeholder="<?php echo esc_attr(self::DEFAULT_SHORTCODE_NAME); ?>"
                class="regular-text"
            />
            <p class="description">
                <?php
                printf(
                    /* translators: %s: shortcode name */
                    esc_html__('Use this shortcode on your pages: [%s]', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                    esc_html($shortcodeName)
                );
                ?>
            </p>
        </fieldset>
        <?php
    }

    public static function render_show_wc_settings_tab_field(): void {
        $enabled = self::get_show_wc_settings_tab();
        ?>
        <fieldset>
            <label>
                <input
                    type="hidden"
                    name="<?php echo esc_attr(self::OPTION_SHOW_WC_SETTINGS_TAB); ?>"
                    value="0"
                />
                <input
                    type="checkbox"
                    name="<?php echo esc_attr(self::OPTION_SHOW_WC_SETTINGS_TAB); ?>"
                    value="1"
                    <?php checked('1', $enabled); ?>
                />
                <?php esc_html_e('Show toolbox in WooCommerce top settings tabs', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
            </label>
            <p class="description">
                <?php esc_html_e('Keep this disabled to use only the left WooCommerce menu link.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
            </p>
        </fieldset>
        <?php
    }

    public static function sanitize_enabled($value): string {
        return (string) $value === '1' ? '1' : '0';
    }

    public static function sanitize_shortcode_name($value): string {
        $sanitized = sanitize_key((string) $value);

        if ('' === $sanitized) {
            $sanitized = self::DEFAULT_SHORTCODE_NAME;
        }

        return $sanitized;
    }

    public static function save_from_woocommerce(): void {
        // First check: nonce validation (CSRF protection) — must come before reading any input
        check_admin_referer(self::SETTINGS_NONCE_ACTION, self::SETTINGS_NONCE_NAME);

        // Second check: user permissions (authorization)
        if (!current_user_can('manage_woocommerce')) {
            wp_die(
                esc_html__('You do not have permission to save settings.', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                esc_html__('Insufficient Permissions', 'tornevall-networks-toolbox-for-resurs-bank-payments'),
                [
                    'response' => 403,
                    'back_link' => true,
                ]
            );
        }

        // Both checks passed: proceed with data processing
        // Enabled flag: expects '0' or '1' (hidden/checkbox pair) — sanitize immediately on read
        $enabledRaw = isset($_POST[self::OPTION_PP_SHORTCODE_ENABLED])
            ? sanitize_text_field(wp_unslash($_POST[self::OPTION_PP_SHORTCODE_ENABLED]))
            : null;

        // Shortcode name: free text further reduced to slug format by sanitize_shortcode_name()
        $nameRaw = isset($_POST[self::OPTION_PP_SHORTCODE_NAME])
            ? sanitize_text_field(wp_unslash($_POST[self::OPTION_PP_SHORTCODE_NAME]))
            : null;

        $enabled = self::sanitize_enabled($enabledRaw ?? '0');
        $shortcodeName = self::sanitize_shortcode_name($nameRaw ?? self::DEFAULT_SHORTCODE_NAME);

        $showWcTabRaw = isset($_POST[self::OPTION_SHOW_WC_SETTINGS_TAB])
            ? sanitize_text_field(wp_unslash($_POST[self::OPTION_SHOW_WC_SETTINGS_TAB]))
            : null;
        $showWcTab = self::sanitize_enabled($showWcTabRaw ?? '0');

        update_option(self::OPTION_PP_SHORTCODE_ENABLED, $enabled);
        update_option(self::OPTION_PP_SHORTCODE_NAME, $shortcodeName);
        update_option(self::OPTION_SHOW_WC_SETTINGS_TAB, $showWcTab);
    }
}

