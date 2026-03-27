<?php

if (!defined('ABSPATH')) {
    exit;
}


class Tornevall_Toolbox_Resurs_Admin_Page {
    public static function render() {
        // Initialize all variables first
        $resurs_plugin_file = '';
        $is_active = false;
        $is_installed = false;
        $resurs_version = '';

        // Common Resurs Bank plugin slugs
        $resurs_slugs = [
            'resurs-bank-payments-for-woocommerce/init.php',
            'resursbank/init.php',
            'resursbank-woocommerce/init.php',
        ];

        // Check each slug for installed/active status
        foreach ($resurs_slugs as $slug) {
            $plugin_path = WP_PLUGIN_DIR . '/' . $slug;
            if (file_exists($plugin_path)) {
                $is_installed = true;
                $resurs_plugin_file = $slug;

                // Get plugin version
                $plugin_data = get_plugin_data($plugin_path);
                $resurs_version = isset($plugin_data['Version']) ? $plugin_data['Version'] : '';

                // Check if active
                if (is_plugin_active($slug)) {
                    $is_active = true;
                }
                break;
            }
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

            <div class="notice notice-warning">
                <p>
                    <strong>⚠️ DISCLAIMER:</strong>
                    This plugin is NOT created, maintained, or endorsed by Resurs Bank.
                    It is an independent third-party utility tool.
                </p>
            </div>

            <div class="card">
                <h2><?php _e('About This Plugin', 'tornevalls-tools-for-resurs-bank-payments'); ?></h2>
                <p><?php _e('Tornevalls Toolbox for Resurs provides utility functions and inspection tools for WooCommerce implementations that use Resurs Bank.', 'tornevalls-tools-for-resurs-bank-payments'); ?></p>
            </div>

            <div class="card">
                <h2><?php _e('What This Does', 'tornevalls-tools-for-resurs-bank-payments'); ?></h2>
                <ul>
                    <li>✓ <?php _e('Currently nothing.', 'tornevalls-tools-for-resurs-bank-payments'); ?></li>
                </ul>
            </div>

            <div class="card">
                <h2><?php _e('What This Does NOT Do', 'tornevalls-tools-for-resurs-bank-payments'); ?></h2>
                <ul>
                    <li>✗ <?php _e('Process or handle real payments', 'tornevalls-tools-for-resurs-bank-payments'); ?></li>
                    <li>✗ <?php _e('Store financial or payment credentials', 'tornevalls-tools-for-resurs-bank-payments'); ?></li>
                    <li>✗ <?php _e('Replace or supplement the official Resurs plugin', 'tornevalls-tools-for-resurs-bank-payments'); ?></li>
                    <li>✗ <?php _e('Make decisions on refunds, captures, or payment status', 'tornevalls-tools-for-resurs-bank-payments'); ?></li>
                </ul>
            </div>

            <div class="card">
                <h2><?php _e('Resurs Plugin Status', 'tornevalls-tools-for-resurs-bank-payments'); ?></h2>
                <?php if ($is_active): ?>
                    <p><strong><?php _e('Status:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <span style="color: green;">✓ <?php _e('Active', 'tornevalls-tools-for-resurs-bank-payments'); ?></span></p>
                <?php elseif ($is_installed): ?>
                    <p><strong><?php _e('Status:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <span style="color: orange;">⚠ <?php _e('Installed (inactive)', 'tornevalls-tools-for-resurs-bank-payments'); ?></span></p>
                <?php else: ?>
                    <p><strong><?php _e('Status:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <span style="color: red;">✗ <?php _e('Not installed', 'tornevalls-tools-for-resurs-bank-payments'); ?></span></p>
                <?php endif; ?>

                <?php if (!empty($resurs_plugin_file)): ?>
                    <p><strong><?php _e('Plugin file:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <?php echo esc_html($resurs_plugin_file); ?></p>
                <?php endif; ?>

                <?php if (!empty($resurs_version)): ?>
                    <p><strong><?php _e('Version:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <?php echo esc_html($resurs_version); ?></p>

                    <hr style="margin: 15px 0;">

                    <div id="version-check-container">
                        <button type="button" class="button button-primary" id="check-version-btn" data-installed-version="<?php echo esc_attr($resurs_version); ?>">
                            🔄 <?php _e('Check for Updates', 'tornevalls-tools-for-resurs-bank-payments'); ?>
                        </button>
                        <span id="version-check-spinner" style="display: none; margin-left: 10px;">
                            <span class="spinner" style="float: none; margin: 0;"></span>
                        </span>
                    </div>

                    <div id="version-check-result" style="margin-top: 15px; display: none;"></div>
                <?php endif; ?>
            </div>

            <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#check-version-btn').on('click', function(e) {
                    e.preventDefault();

                    var btn = $(this);
                    var installedVersion = btn.data('installed-version');
                    var resultDiv = $('#version-check-result');
                    var spinner = $('#version-check-spinner');

                    // Show spinner
                    spinner.show();
                    resultDiv.hide();
                    btn.prop('disabled', true);

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'resurs_check_version',
                            nonce: '<?php echo wp_create_nonce('resurs_toolbox_nonce'); ?>',
                            installed_version: installedVersion
                        },
                        success: function(response) {
                            var html = '';

                            if (response.success) {
                                var data = response.data;
                                html += '<div class="notice" style="padding: 10px; border-left: 4px solid #0073aa;">';
                                html += '<p><strong><?php _e('Latest version on Bitbucket:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> ' + data.latest + '</p>';

                                if (data.is_outdated) {
                                    // Current version is outdated
                                    html = html.replace('notice-info', 'notice-warning');
                                    html += '<p style="color: #d63638;"><strong>⚠️ <?php _e('An update is available!', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong></p>';
                                    html += '<p><?php _e('Your version:', 'tornevalls-tools-for-resurs-bank-payments'); ?> ' + data.installed + '</p>';
                                    html += '<p><a href="https://bitbucket.org/resursbankplugins/resursbank-woocommerce/src/' + data.latest + '/" target="_blank" class="button button-secondary"><?php _e('View Release', 'tornevalls-tools-for-resurs-bank-payments'); ?></a></p>';
                                } else if (data.is_dev_version) {
                                    // Current version is newer than latest tagged - development/pre-release version
                                    html = html.replace('notice-info', 'notice-success');
                                    html += '<p style="color: #2f7c31;"><strong>📦 <?php _e('Development Version Detected', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong></p>';
                                    html += '<p><?php _e('You are running version', 'tornevalls-tools-for-resurs-bank-payments'); ?> <strong>' + data.installed + '</strong>, which is newer than the latest stable release (' + data.latest + ').</p>';
                                    html += '<p><?php _e('This could be:', 'tornevalls-tools-for-resurs-bank-payments'); ?></p>';
                                    html += '<ul style="margin-left: 20px;">';
                                    html += '<li><?php _e('A development/pre-release version', 'tornevalls-tools-for-resurs-bank-payments'); ?></li>';
                                    html += '<li><?php _e('A hotfix not yet tagged on Bitbucket', 'tornevalls-tools-for-resurs-bank-payments'); ?></li>';
                                    html += '<li><?php _e('An unreleased update from Resurs Bank', 'tornevalls-tools-for-resurs-bank-payments'); ?></li>';
                                    html += '</ul>';
                                    html += '<p><?php _e('If you did not intentionally upgrade, please verify your installation.', 'tornevalls-tools-for-resurs-bank-payments'); ?></p>';
                                } else {
                                    // Versions match - latest stable
                                    html = html.replace('notice-info', 'notice-success');
                                    html += '<p style="color: #1d8f1f;"><strong>✓ <?php _e('You are running the latest stable version!', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong></p>';
                                }

                                html += '</div>';
                            } else {
                                html += '<div class="notice notice-error" style="padding: 10px; border-left: 4px solid #dc3545;">';
                                html += '<p><strong><?php _e('Error checking version:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> ' + response.data.error + '</p>';
                                html += '</div>';
                            }

                            resultDiv.html(html).show();
                        },
                        error: function() {
                            var html = '<div class="notice notice-error" style="padding: 10px; border-left: 4px solid #dc3545;">';
                            html += '<p><strong><?php _e('Error:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <?php _e('Failed to connect to the server', 'tornevalls-tools-for-resurs-bank-payments'); ?></p>';
                            html += '</div>';
                            resultDiv.html(html).show();
                        },
                        complete: function() {
                            spinner.hide();
                            btn.prop('disabled', false);
                        }
                    });
                });
            });
            </script>
        </div>
        <?php
    }
}
