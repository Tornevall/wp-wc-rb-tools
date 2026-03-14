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

        // Check each slug for installed/active status
        foreach ($resurs_slugs as $slug) {
            $plugin_path = WP_PLUGIN_DIR . '/' . $slug;
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
            <style>
                .wrap > h1 {
                    display: none;
                }
                .tornevall-resurs-toolbox-cards {
                    display: grid;
                    gap: 20px;
                    grid-template-columns: 1fr 1fr;
                    margin-top: 20px;
                }
                .tornevall-resurs-toolbox-cards .card {
                    margin: 0;
                    max-width: none;
                    width: 100%;
                    box-sizing: border-box;
                }
                .tornevall-resurs-toolbox-cards .card--wide {
                    grid-column: 1 / -1;
                    max-width: 50%;
                }
                .tornevall-resurs-toolbox-check-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    line-height: 1;
                }
                .tornevall-resurs-toolbox-check-btn .spinner {
                    display: inline-block;
                    float: none;
                    margin: 0 0 0 4px;
                    width: 12px;
                    height: 12px;
                    visibility: hidden;
                    vertical-align: middle;
                    line-height: 12px;
                    background-size: 12px 12px;
                }
                .tornevall-resurs-toolbox-check-btn .spinner.is-active {
                    visibility: visible;
                }
                .tornevall-resurs-toolbox-submit {
                    margin-top: 16px;
                }
                @media (max-width: 960px) {
                    .tornevall-resurs-toolbox-cards {
                        grid-template-columns: 1fr;
                    }
                    .tornevall-resurs-toolbox-cards .card--wide {
                        max-width: 100%;
                    }
                }
            </style>

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
                    <div class="tornevall-resurs-toolbox-submit">
                        <?php submit_button(__('Save Changes', 'tornevall-networks-toolbox-for-resurs-bank-payments'), 'primary', 'save', false); ?>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
            (function() {
                var btn = document.getElementById('check-version-btn');
                if (!btn) {
                    return;
                }

                var messages = {
                    latestVersion: <?php echo wp_json_encode(__('Latest version on Bitbucket:', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    updateAvailable: <?php echo wp_json_encode(__('An update is available!', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    yourVersion: <?php echo wp_json_encode(__('Your version:', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    viewRelease: <?php echo wp_json_encode(__('View Bitbucket Release', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    viewOnWordPress: <?php echo wp_json_encode(__('View on WordPress.com', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    upgradeInPlugins: <?php echo wp_json_encode(__('Upgrade in Plugins', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    devDetected: <?php echo wp_json_encode(__('Development Version Detected', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    runningVersion: <?php echo wp_json_encode(__('You are running version', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    newerThanStable: <?php echo wp_json_encode(__('which is newer than the latest stable release', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    thisCouldBe: <?php echo wp_json_encode(__('This could be:', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    devPreRelease: <?php echo wp_json_encode(__('A development/pre-release version', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    hotfixNotTagged: <?php echo wp_json_encode(__('A hotfix not yet tagged on Bitbucket', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    unreleasedUpdate: <?php echo wp_json_encode(__('An unreleased update from Resurs Bank', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    verifyInstall: <?php echo wp_json_encode(__('If you did not intentionally upgrade, please verify your installation.', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    latestStable: <?php echo wp_json_encode(__('You are running the latest stable version!', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    errorChecking: <?php echo wp_json_encode(__('Error checking version:', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    errorLabel: <?php echo wp_json_encode(__('Error:', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>,
                    failedConnect: <?php echo wp_json_encode(__('Failed to connect to the server', 'tornevall-networks-toolbox-for-resurs-bank-payments')); ?>
                };

                var resultDiv = document.getElementById('version-check-result');
                var spinner = document.getElementById('version-check-spinner');
                var wordpressPluginUrl = btn.getAttribute('data-wordpress-plugin-url') || '';
                var pluginsManagerUrl = btn.getAttribute('data-plugins-manager-url') || '';

                function buildActionButton(url, label, isExternal) {
                    if (!url) {
                        return '';
                    }

                    var attrs = ' class="button button-secondary"';
                    if (isExternal) {
                        attrs += ' target="_blank" rel="noopener noreferrer"';
                    }

                    return '<a href="' + url + '"' + attrs + '>' + label + '</a>';
                }

                function buildActionButtons(data) {
                    var buttons = [];
                    var releaseUrl = 'https://bitbucket.org/resursbankplugins/resursbank-woocommerce/src/' + encodeURIComponent(data.latest || '') + '/';

                    buttons.push(buildActionButton(releaseUrl, messages.viewRelease, true));

                    if (wordpressPluginUrl) {
                        buttons.push(buildActionButton(wordpressPluginUrl, messages.viewOnWordPress, true));
                    }

                    if (pluginsManagerUrl) {
                        buttons.push(buildActionButton(pluginsManagerUrl, messages.upgradeInPlugins, false));
                    }

                    buttons = buttons.filter(function(button) {
                        return button !== '';
                    });

                    if (!buttons.length) {
                        return '';
                    }

                    return '<p style="display:flex; gap:8px; flex-wrap:wrap; margin-top:12px;">' + buttons.join('') + '</p>';
                }

                function setLoading(isLoading) {
                    if (spinner) {
                        spinner.classList.toggle('is-active', isLoading);
                    }

                    btn.disabled = isLoading;

                    if (resultDiv && isLoading) {
                        resultDiv.style.display = 'none';
                    }
                }

                function renderHtml(html) {
                    if (!resultDiv) {
                        return;
                    }

                    resultDiv.innerHTML = html;
                    resultDiv.style.display = 'block';
                }

                function extractError(response) {
                    if (!response || !response.data) {
                        return '';
                    }

                    return response.data.error || response.data.message || '';
                }

                function renderResponse(response) {
                    var html = '';

                    if (response && response.success) {
                        var data = response.data || {};
                        html += '<div class="notice" style="padding: 10px; border-left: 4px solid #0073aa;">';
                        html += '<p><strong>' + messages.latestVersion + '</strong> ' + (data.latest || '') + '</p>';

                        if (data.is_outdated) {
                            html += '<p style="color: #d63638;"><strong>⚠️ ' + messages.updateAvailable + '</strong></p>';
                            html += '<p>' + messages.yourVersion + ' ' + (data.installed || '') + '</p>';
                            html += buildActionButtons(data);
                        } else if (data.is_dev_version) {
                            html += '<p style="color: #2f7c31;"><strong>📦 ' + messages.devDetected + '</strong></p>';
                            html += '<p>' + messages.runningVersion + ' <strong>' + (data.installed || '') + '</strong>, ' + messages.newerThanStable + ' (' + (data.latest || '') + ').</p>';
                            html += '<p>' + messages.thisCouldBe + '</p>';
                            html += '<ul style="margin-left: 20px;">';
                            html += '<li>' + messages.devPreRelease + '</li>';
                            html += '<li>' + messages.hotfixNotTagged + '</li>';
                            html += '<li>' + messages.unreleasedUpdate + '</li>';
                            html += '</ul>';
                            html += '<p>' + messages.verifyInstall + '</p>';
                        } else {
                            html += '<p style="color: #1d8f1f;"><strong>✓ ' + messages.latestStable + '</strong></p>';
                        }

                        html += '</div>';
                    } else {
                        html += '<div class="notice notice-error" style="padding: 10px; border-left: 4px solid #dc3545;">';
                        html += '<p><strong>' + messages.errorChecking + '</strong> ' + extractError(response) + '</p>';
                        html += '</div>';
                    }

                    renderHtml(html);
                }

                function renderError() {
                    var html = '<div class="notice notice-error" style="padding: 10px; border-left: 4px solid #dc3545;">';
                    html += '<p><strong>' + messages.errorLabel + '</strong> ' + messages.failedConnect + '</p>';
                    html += '</div>';
                    renderHtml(html);
                }

                function sendRequest() {
                    var payload = new URLSearchParams();
                    payload.append('action', 'tornevall_resurs_toolbox_check_version');
                    payload.append('nonce', btn.getAttribute('data-nonce') || '');
                    payload.append('installed_version', btn.getAttribute('data-installed-version') || '');

                    setLoading(true);

                    fetch(window.ajaxurl || '', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: payload.toString()
                    })
                        .then(function(response) { return response.json(); })
                        .then(function(response) { renderResponse(response); })
                        .catch(function() { renderError(); })
                        .finally(function() { setLoading(false); });
                }

                btn.addEventListener('click', function(event) {
                    event.preventDefault();
                    sendRequest();
                });
            })();
            </script>
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

