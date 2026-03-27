<?php
/**
 * Plugin Name: Tornevalls Toolbox for Resurs Bank
 * Plugin URI: https://www.tornevall.se/
 * Description: Independent utility plugin for WooCommerce with Resurs Bank integration (NOT official/endorsed by Resurs Bank)
 * Version: 1.0.0
 * Author: Tomas Tornevall
 * Author URI: https://www.tornevall.se/
 * Text Domain: tornevalls-tools-for-resurs-bank-payments
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
define('TORNEVALLS_RESURS_VERSION', '1.0.0');
define('TORNEVALLS_RESURS_PLUGIN_FILE', __FILE__);
define('TORNEVALLS_RESURS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TORNEVALLS_RESURS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TORNEVALLS_RESURS_PLUGIN_BASENAME', plugin_basename(__FILE__));


// Load core classes
require_once TORNEVALLS_RESURS_PLUGIN_DIR . 'includes/class-bitbucket-version-checker.php';
require_once TORNEVALLS_RESURS_PLUGIN_DIR . 'includes/class-tornevall-toolbox-resurs-admin-page.php';
require_once TORNEVALLS_RESURS_PLUGIN_DIR . 'includes/class-tornevall-toolbox-resurs-settings-tab.php';
require_once TORNEVALLS_RESURS_PLUGIN_DIR . 'includes/class-tornevall-toolbox-resurs.php';
require_once TORNEVALLS_RESURS_PLUGIN_DIR . 'includes/class-ajax-handler.php';

// Initialize AJAX handlers
if (is_admin()) {
    Tornevall_Resurs_AJAX_Handler::init();
}

// Initialize plugin
function tornevall_toolbox_resurs() {
    return Tornevall_Toolbox_Resurs::get_instance();
}

// Start the plugin
tornevall_toolbox_resurs();
