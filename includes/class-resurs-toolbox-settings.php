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
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $nonce = isset($_POST[self::SETTINGS_NONCE_NAME]) ? sanitize_text_field(wp_unslash($_POST[self::SETTINGS_NONCE_NAME])) : '';
        if ('' === $nonce || !wp_verify_nonce($nonce, self::SETTINGS_NONCE_ACTION)) {
            return;
        }

        $enabledRaw = $_POST[self::OPTION_PP_SHORTCODE_ENABLED] ?? '0';
        $nameRaw = $_POST[self::OPTION_PP_SHORTCODE_NAME] ?? self::DEFAULT_SHORTCODE_NAME;

        $enabled = self::sanitize_enabled(is_string($enabledRaw) ? wp_unslash($enabledRaw) : '0');
        $shortcodeName = self::sanitize_shortcode_name(is_string($nameRaw) ? wp_unslash($nameRaw) : self::DEFAULT_SHORTCODE_NAME);

        update_option(self::OPTION_PP_SHORTCODE_ENABLED, $enabled);
        update_option(self::OPTION_PP_SHORTCODE_NAME, $shortcodeName);

        update_option(self::LEGACY_OPTION_PP_SHORTCODE_ENABLED, $enabled);
        update_option(self::LEGACY_OPTION_PP_SHORTCODE_NAME, $shortcodeName);
    }
}

