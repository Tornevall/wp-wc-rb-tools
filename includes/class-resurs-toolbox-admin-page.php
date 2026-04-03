<?php

if (!defined('ABSPATH')) {
    exit;
}


class Tornevall_Resurs_Toolbox_Admin_Page {
    public static function render() {
        if (!function_exists('get_plugin_data') || !function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Initialize all variables first
        $resurs_plugin_file = '';
        $resurs_plugin_data = [];
        $is_active = false;
        $is_installed = false;
        $resurs_version = '';

        // Common Resurs Bank plugin slugs
        $resurs_slugs = [
            'resurs-bank-payments-for-woocommerce/init.php',
            'resurs-bank-payments-for-woocommerce/resurs-bank-payments-for-woocommerce.php',
            'resursbank/init.php',
            'resursbank-woocommerce/init.php',
        ];

        // Resolve plugins root using WordPress helpers + plugin main file constant.
        $plugins_root = trailingslashit(dirname(plugin_dir_path(TORNEVALL_RESURS_TOOLBOX_PLUGIN_FILE)));

        // Check each slug for installed/active status
        foreach ($resurs_slugs as $slug) {
            $plugin_path = $plugins_root . ltrim($slug, '/');
            if (file_exists($plugin_path)) {
                $is_installed = true;
                $resurs_plugin_file = $slug;

                // Get plugin version
                $plugin_data = get_plugin_data($plugin_path);
                $resurs_plugin_data = is_array($plugin_data) ? $plugin_data : [];
                $resurs_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '';

                // Check if active
                if (is_plugin_active($slug)) {
                    $is_active = true;
                }
                break;
            }
        }

        $wordpress_plugin_url = self::get_wordpress_plugin_url($resurs_plugin_file, $resurs_plugin_data);
        $plugins_manager_url = self::get_plugins_manager_url($resurs_plugin_file);
        ?>
        <div class="wrap">
            <h1 class="tornevall-resurs-toolbox-title">
                <?php esc_html_e('Tornevall Networks Toolbox for Resurs Bank Payments', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
            </h1>

            <div class="notice notice-warning">
                <p>
                    <strong>⚠️ <?php esc_html_e('DISCLAIMER:', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></strong>
                    <?php esc_html_e('This plugin is NOT created, maintained, or endorsed by Resurs Bank.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                    <?php esc_html_e('It is an independent third-party utility tool.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?>
                </p>
            </div>

            <div class="tornevall-resurs-toolbox-cards">
                <div class="card">
                    <h2><?php esc_html_e('About This Plugin', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></h2>
                    <p><?php esc_html_e('Tornevall Networks Toolbox for Resurs Bank Payments provides utility and status tools for WooCommerce implementations that use Resurs Bank Payments.', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></p>
                </div>

                <div class="card">
                    <h2><?php esc_html_e('Resurs Plugin Status', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></h2>
                    <?php if ($is_active): ?>
                        <p><strong><?php esc_html_e('Status:', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></strong> <span style="color: green;">✓ <?php esc_html_e('Active', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></span></p>
                    <?php elseif ($is_installed): ?>
                        <p><strong><?php esc_html_e('Status:', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></strong> <span style="color: orange;">⚠ <?php esc_html_e('Installed (inactive)', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></span></p>
                    <?php else: ?>
                        <p><strong><?php esc_html_e('Status:', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></strong> <span style="color: red;">✗ <?php esc_html_e('Not installed', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></span></p>
                    <?php endif; ?>

                    <?php if (!empty($resurs_plugin_file)): ?>
                        <p><strong><?php esc_html_e('Plugin file:', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></strong> <?php echo esc_html($resurs_plugin_file); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($resurs_version)): ?>
                        <p><strong><?php esc_html_e('Version:', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></strong> <?php echo esc_html($resurs_version); ?></p>

                        <hr style="margin: 15px 0;">

                        <div id="version-check-container">
                            <button
                                type="button"
                                class="button button-primary tornevall-resurs-toolbox-check-btn"
                                id="check-version-btn"
                                data-installed-version="<?php echo esc_attr($resurs_version); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('tornevall_resurs_toolbox_nonce')); ?>"
                                data-wordpress-plugin-url="<?php echo esc_url($wordpress_plugin_url); ?>"
                                data-plugins-manager-url="<?php echo esc_url($plugins_manager_url); ?>"
                            >
                                <span><?php esc_html_e('Check for Updates', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></span>
                                <span class="spinner" id="version-check-spinner" aria-hidden="true"></span>
                            </button>
                        </div>

                        <div id="version-check-result" style="margin-top: 15px; display: none;"></div>
                    <?php endif; ?>
                </div>

                <div class="card card--wide">
                    <h2><?php esc_html_e('Part Payment Widget', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></h2>
                    <?php wp_nonce_field(Tornevall_Resurs_Toolbox_Settings::SETTINGS_NONCE_ACTION, Tornevall_Resurs_Toolbox_Settings::SETTINGS_NONCE_NAME); ?>
                    <h3><?php esc_html_e('Part Payment Widget Settings', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></h3>
                    <?php Tornevall_Resurs_Toolbox_Settings::render_section_description(); ?>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row"><?php esc_html_e('Enable Shortcode Rendering', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></th>
                            <td><?php Tornevall_Resurs_Toolbox_Settings::render_enabled_field(); ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Shortcode Name', 'tornevall-networks-toolbox-for-resurs-bank-payments'); ?></th>
                            <td><?php Tornevall_Resurs_Toolbox_Settings::render_shortcode_name_field(); ?></td>
                        </tr>
                    </table>
                    <!--
                    <div class="tornevall-resurs-toolbox-submit">
                        <?php submit_button(__('Save Changes', 'tornevall-networks-toolbox-for-resurs-bank-payments'), 'primary', 'save', false); ?>
                    </div>
                    -->
                </div>
            </div>
        </div>
        <?php
    }

    private static function get_wordpress_plugin_url(string $resurs_plugin_file, array $plugin_data = []): string {
        if (0 === strpos($resurs_plugin_file, 'resurs-bank-payments-for-woocommerce/')) {
            return 'https://wordpress.com/plugins/resurs-bank-payments-for-woocommerce';
        }

        if (!empty($plugin_data['PluginURI']) && is_string($plugin_data['PluginURI'])) {
            return $plugin_data['PluginURI'];
        }

        return '';
    }

    private static function get_plugins_manager_url(string $resurs_plugin_file): string {
        if ('' === $resurs_plugin_file) {
            return '';
        }

        $search_term = dirname($resurs_plugin_file);
        if ('' === $search_term || '.' === $search_term) {
            $search_term = $resurs_plugin_file;
        }

        return add_query_arg(
            [
                'plugin_status' => 'all',
                's' => $search_term,
            ],
            self_admin_url('plugins.php')
        );
    }
}

