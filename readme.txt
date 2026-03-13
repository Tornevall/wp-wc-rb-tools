=== Tornevall Networks Toolbox for Resurs Bank Payments ===
Contributors: tornevall
Tags: resurs bank, woocommerce, utilities
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
WC requires at least: 7.6.0
WC Tested up to: 10.6.1
Requires Plugins: woocommerce
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Independent utility plugin for WooCommerce with Resurs Bank Payments integration.

== Description ==

Tornevall Networks Toolbox for Resurs Bank Payments is an independent developer and merchant utility plugin—**not officially affiliated with or endorsed by Resurs Bank**.

This is a third-party tool built to support WooCommerce implementations that use Resurs Bank Payments as a payment provider.

**⚠️ DISCLAIMER**: This plugin is NOT created, maintained, or supported by Resurs Bank. It is a community/independent utility.

Current toolbox features include:

* Resurs plugin status detection for common plugin file variants.
* Installed plugin file and version display in the WooCommerce toolbox tab.
* Bitbucket-based version checking from the admin interface.
* Part Payment Widget settings with optional shortcode/manual rendering mode.
* Support for a configurable shortcode tag, defaulting to `[resurs_partpayment_widget]`.

== Installation ==

1. Upload to `/wp-content/plugins/tornevall-networks-toolbox-for-resurs-bank-payments/`
2. Activate via Plugins screen
3. Go to **WooCommerce > Settings > Tornevall Networks Toolbox for Resurs Bank Payments**
4. Review the detected Resurs plugin status and configure widget behavior if needed

== Frequently Asked Questions ==

= Do I need Resurs Bank official plugin? =

This plugin is designed to work alongside the official Resurs Bank plugin for WooCommerce.
You can install it without the official plugin, but it will be an empty shell without Resurs Bank integration features.

= Is this supported by Resurs Bank? =

No. Third-party developer tool. Not official.

= Can I use this in production? =

This is a utility/inspection tool. Use at your own discretion.

= What does the shortcode setting do? =

If shortcode rendering is enabled, the plugin disables the default automatic Part Payment Widget rendering from the official Resurs plugin and registers a shortcode instead.
The default shortcode is `[resurs_partpayment_widget]`, but the shortcode name can be changed from the toolbox tab.

== Changelog ==

= Unreleased =
* Restored the Part Payment Widget module in the current plugin structure.
* Added shortcode/manual rendering mode for the Part Payment Widget.
* Added configurable shortcode name handling in the toolbox settings.
* Restored the widget settings card in the WooCommerce toolbox tab.
* Improved Resurs plugin status detection for multiple known plugin file variants.
* Kept compatibility with legacy shortcode option names during migration to the new naming convention.

= 1.0.0 =
* Initial release
* WooCommerce toolbox/settings tab integration.
* Resurs plugin status view.
* Bitbucket version check UI in admin.
* Renamed the plugin to Tornevall Networks Toolbox for Resurs Bank Payments.
* Updated the plugin slug-facing package path and main bootstrap naming.
* Standardized internal constants, class names, AJAX action names, and nonce names.
* Refreshed admin labels and readme branding to match the new product name.
* Applied WordPress plugin-check compliance fixes and validation updates.


== License ==

GPLv2 or later. See LICENSE file.

== Author ==

Tomas Tornevall — [www.tornevall.se](https://www.tornevall.se/)

---

**⚠️ Disclaimer**: Not endorsed by Resurs Bank.
