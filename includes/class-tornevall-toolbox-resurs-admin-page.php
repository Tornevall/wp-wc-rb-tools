<?php

namespace Tornevalls\ToolboxResurs;

if (!defined('ABSPATH')) {
    exit;
}


class Tornevall_Toolbox_Resurs_Admin_Page {
    public static function render() {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

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
            <style>
                /* Hide the generic WooCommerce Settings title on this specific tab. */
                .wrap > h1 {
                    display: none;
                }
            </style>
            <h1 class="tornevalls-resurs-title">
                <?php esc_html_e('Tornevalls Toolbox for Resurs Bank', 'tornevalls-tools-for-resurs-bank-payments'); ?>
            </h1>

            <div class="notice notice-warning">
                <p>
                    <strong>⚠️ <?php esc_html_e('DISCLAIMER:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong>
                    <?php esc_html_e('This plugin is NOT created, maintained, or endorsed by Resurs Bank.', 'tornevalls-tools-for-resurs-bank-payments'); ?>
                    <?php esc_html_e('It is an independent third-party utility tool.', 'tornevalls-tools-for-resurs-bank-payments'); ?>
                </p>
            </div>

            <div class="tornevalls-resurs-cards">
                <div class="card">
                    <h2><?php esc_html_e('About This Plugin', 'tornevalls-tools-for-resurs-bank-payments'); ?></h2>
                    <p><?php esc_html_e('Tornevalls Toolbox for Resurs provides utility and status tools for WooCommerce implementations that use Resurs Bank.', 'tornevalls-tools-for-resurs-bank-payments'); ?></p>
                </div>


                <div class="card">
                    <h2><?php esc_html_e('Resurs Plugin Status', 'tornevalls-tools-for-resurs-bank-payments'); ?></h2>
                    <?php if ($is_active): ?>
                        <p><strong><?php esc_html_e('Status:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <span style="color: green;">✓ <?php esc_html_e('Active', 'tornevalls-tools-for-resurs-bank-payments'); ?></span></p>
                    <?php elseif ($is_installed): ?>
                        <p><strong><?php esc_html_e('Status:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <span style="color: orange;">⚠ <?php esc_html_e('Installed (inactive)', 'tornevalls-tools-for-resurs-bank-payments'); ?></span></p>
                    <?php else: ?>
                        <p><strong><?php esc_html_e('Status:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <span style="color: red;">✗ <?php esc_html_e('Not installed', 'tornevalls-tools-for-resurs-bank-payments'); ?></span></p>
                    <?php endif; ?>

                    <?php if (!empty($resurs_plugin_file)): ?>
                        <p><strong><?php esc_html_e('Plugin file:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <?php echo esc_html($resurs_plugin_file); ?></p>
                    <?php endif; ?>

                    <?php if (!empty($resurs_version)): ?>
                        <p><strong><?php esc_html_e('Version:', 'tornevalls-tools-for-resurs-bank-payments'); ?></strong> <?php echo esc_html($resurs_version); ?></p>

                        <hr style="margin: 15px 0;">

                        <div id="version-check-container">
                            <button
                                type="button"
                                class="button button-primary tornevalls-resurs-check-btn"
                                id="check-version-btn"
                                data-installed-version="<?php echo esc_attr($resurs_version); ?>"
                                data-nonce="<?php echo esc_attr(wp_create_nonce('resurs_toolbox_nonce')); ?>"
                            >
                                <span class="tornevalls-resurs-check-label">
                                    <?php esc_html_e('Check for Updates', 'tornevalls-tools-for-resurs-bank-payments'); ?>
                                </span>
                                <span class="spinner" id="version-check-spinner" aria-hidden="true"></span>
                            </button>
                        </div>

                        <div id="version-check-result" style="margin-top: 15px; display: none;"></div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h2><?php esc_html_e('Part Payment Widget', 'tornevalls-tools-for-resurs-bank-payments'); ?></h2>
                    <form method="POST" action="">
                        <?php
                        settings_fields('tornevalls_resurs_pp_shortcode_settings');
                        do_settings_sections('tornevalls_resurs_pp_shortcode_settings');
                        submit_button();
                        ?>
                    </form>
                </div>
            </div>

            <style>
                .tornevalls-resurs-cards {
                    display: grid;
                    gap: 20px;
                    grid-template-columns: 1fr 1fr;
                    margin-top: 20px;
                }
                .tornevalls-resurs-cards .card {
                    margin: 0;
                    max-width: none;
                    width: 100%;
                    box-sizing: border-box;
                }
                .tornevalls-resurs-cards .card:nth-child(3) {
                    grid-column: 1 / -1;
                    max-width: 50%;
                }
                @media (max-width: 768px) {
                    .tornevalls-resurs-cards {
                        grid-template-columns: 1fr;
                    }
                    .tornevalls-resurs-cards .card:nth-child(3) {
                        max-width: 100%;
                    }
                }
                .tornevalls-resurs-check-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    line-height: 1;
                }
                .tornevalls-resurs-check-btn .spinner {
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
                .tornevalls-resurs-check-btn .spinner.is-active {
                    visibility: visible;
                }
                .tornevalls-resurs-check-btn .tornevalls-resurs-check-label {
                    line-height: 1;
                }
            </style>

            <script type="text/javascript">
            (function() {
                var btn = document.getElementById('check-version-btn');
                if (!btn) {
                    return;
                }

                var messages = {
                    latestVersion: <?php echo wp_json_encode(__('Latest version on Bitbucket:', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    updateAvailable: <?php echo wp_json_encode(__('An update is available!', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    yourVersion: <?php echo wp_json_encode(__('Your version:', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    viewRelease: <?php echo wp_json_encode(__('View Release', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    devDetected: <?php echo wp_json_encode(__('Development Version Detected', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    runningVersion: <?php echo wp_json_encode(__('You are running version', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    newerThanStable: <?php echo wp_json_encode(__('which is newer than the latest stable release', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    thisCouldBe: <?php echo wp_json_encode(__('This could be:', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    devPreRelease: <?php echo wp_json_encode(__('A development/pre-release version', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    hotfixNotTagged: <?php echo wp_json_encode(__('A hotfix not yet tagged on Bitbucket', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    unreleasedUpdate: <?php echo wp_json_encode(__('An unreleased update from Resurs Bank', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    verifyInstall: <?php echo wp_json_encode(__('If you did not intentionally upgrade, please verify your installation.', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    latestStable: <?php echo wp_json_encode(__('You are running the latest stable version!', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    errorChecking: <?php echo wp_json_encode(__('Error checking version:', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    errorLabel: <?php echo wp_json_encode(__('Error:', 'tornevalls-tools-for-resurs-bank-payments')); ?>,
                    failedConnect: <?php echo wp_json_encode(__('Failed to connect to the server', 'tornevalls-tools-for-resurs-bank-payments')); ?>
                };

                var resultDiv = document.getElementById('version-check-result');
                var spinner = document.getElementById('version-check-spinner');

                function setLoading(isLoading) {
                    if (spinner) {
                        spinner.classList.toggle('is-active', isLoading);
                    }
                    if (btn) {
                        btn.disabled = isLoading;
                        btn.classList.toggle('is-busy', isLoading);
                    }
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

                function renderResponse(response) {
                    var html = '';

                    if (response && response.success) {
                        var data = response.data || {};
                        html += '<div class="notice" style="padding: 10px; border-left: 4px solid #0073aa;">';
                        html += '<p><strong>' + messages.latestVersion + '</strong> ' + (data.latest || '') + '</p>';

                        if (data.is_outdated) {
                            html += '<p style="color: #d63638;"><strong>⚠️ ' + messages.updateAvailable + '</strong></p>';
                            html += '<p>' + messages.yourVersion + ' ' + (data.installed || '') + '</p>';
                            html += '<p><a href="https://bitbucket.org/resursbankplugins/resursbank-woocommerce/src/' + (data.latest || '') + '/" target="_blank" class="button button-secondary">' + messages.viewRelease + '</a></p>';
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
                        html += '<p><strong>' + messages.errorChecking + '</strong> ' + (response && response.data && response.data.error ? response.data.error : '') + '</p>';
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
                    var installedVersion = btn.getAttribute('data-installed-version') || '';
                    var nonce = btn.getAttribute('data-nonce') || '';
                    var url = window.ajaxurl || '';

                    setLoading(true);

                    if (window.jQuery && typeof window.jQuery.post === 'function') {
                        window.jQuery.post(url, {
                            action: 'resurs_check_version',
                            nonce: nonce,
                            installed_version: installedVersion
                        })
                        .done(function(response) {
                            renderResponse(response);
                        })
                        .fail(function() {
                            renderError();
                        })
                        .always(function() {
                            setLoading(false);
                        });
                        return;
                    }

                    var payload = new URLSearchParams();
                    payload.append('action', 'resurs_check_version');
                    payload.append('nonce', nonce);
                    payload.append('installed_version', installedVersion);

                    fetch(url, {
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

                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    sendRequest();
                });
            })();
            </script>
        </div>
        <?php
    }
}
