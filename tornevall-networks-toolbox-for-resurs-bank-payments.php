<?php
/**
 * Plugin Name: Tornevall Networks Toolbox for Resurs Bank Payments
 * Plugin URI: https://www.tornevall.se/
 * Description: Independent utility plugin for WooCommerce with Resurs Bank Payments integration (not official or endorsed by Resurs Bank)
 * Version: 1.0.0
 * Author: Tomas Tornevall
 * Author URI: https://www.tornevall.se/
 * Text Domain: tornevall-networks-toolbox-for-resurs-bank-payments
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.1
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

// Define plugin constants
define('TORNEVALL_RESURS_TOOLBOX_VERSION', '1.0.0');
define('TORNEVALL_RESURS_TOOLBOX_PLUGIN_FILE', __FILE__);
define('TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TORNEVALL_RESURS_TOOLBOX_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TORNEVALL_RESURS_TOOLBOX_PLUGIN_BASENAME', plugin_basename(__FILE__));


// Load core classes
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-version-checker.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-admin-page.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-settings-tab.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/class-resurs-toolbox-settings.php';
require_once TORNEVALL_RESURS_TOOLBOX_PLUGIN_DIR . 'includes/modules/class-resurs-toolbox-part-payment-widget.php';
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
