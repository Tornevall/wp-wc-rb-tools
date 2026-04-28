<?php
/**
 * Plugin Name: Tornevall Networks Toolbox for Resurs Bank Payments
 * Description: Independent utility plugin for WooCommerce with Resurs Bank Payments integration (not official or endorsed by Resurs Bank)
 * Version: 1.0.4
 * Author: Thomas Tornevall
 * Author URI: https://www.tornevalls.se/
 * Text Domain: tornevall-networks-toolbox-for-resurs-bank-payments
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
 * Resurs Required: 1.2.30
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * DISCLAIMER: This plugin is NOT created, maintained, or endorsed by Resurs Bank.
 * It is an independent third-party utility tool.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants — version is read directly from this file's header
// so readme.txt Stable tag and the header Version: are the only places to update.
$tornevall_resurs_toolbox_header = get_file_data(__FILE__, ['Version' => 'Version'], 'plugin');
if (!defined('TORNEVALL_RESURS_TOOLBOX_VERSION')) {
    define('TORNEVALL_RESURS_TOOLBOX_VERSION', $tornevall_resurs_toolbox_header['Version'] ?: '0.0.0');
}
if (!defined('TORNEVALL_RESURS_TOOLBOX_PLUGIN_FILE')) {
    define('TORNEVALL_RESURS_TOOLBOX_PLUGIN_FILE', __FILE__);
}
if (!defined('TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR')) {
    define('TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('TORNEVALL_RESURS_TOOLBOX_PLUGIN_URL')) {
    define('TORNEVALL_RESURS_TOOLBOX_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('TORNEVALL_RESURS_TOOLBOX_PLUGIN_BASENAME')) {
    define('TORNEVALL_RESURS_TOOLBOX_PLUGIN_BASENAME', plugin_basename(__FILE__));
}
unset($tornevall_resurs_toolbox_header);

// Load core classes
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-version-checker.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-admin-page.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-settings-tab.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-settings.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/modules/class-resurs-toolbox-part-payment-widget.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/modules/class-resurs-toolbox-order-status-tester.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-plugin.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-ajax-handler.php';

// Initialize AJAX handlers
if (is_admin()) {
    Tornevall_Resurs_Toolbox_Ajax_Handler::init();
}

// Initialize plugin
function tornevall_resurs_toolbox_plugin() {
    return Tornevall_Resurs_Toolbox_Plugin::get_instance();
}

// Start the plugin
tornevall_resurs_toolbox_plugin();
