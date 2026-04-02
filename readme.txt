=== Tornevall Networks Toolbox for Resurs Bank Payments ===
Contributors: tornevall
Tags: resurs bank, woocommerce, utilities
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
WC requires at least: 7.6.0
WC Tested up to: 10.6.1
Resurs Required: 1.2.30
Requires Plugins: woocommerce
Stable tag: 1.0.3
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
* WordPress.com plugin-page link and an "Upgrade in Plugins" shortcut when a newer Resurs release is detected.
* Part Payment Widget settings with optional shortcode/manual rendering mode.
* Admin CSS/JS loaded through proper WordPress admin enqueue hooks on the toolbox tab only.
* Support for a configurable shortcode tag, defaulting to `[resurs_partpayment_widget]`.
* Updated bundled Swedish translations for the current plugin name and UI text.

== External Services ==

This plugin connects to one external service:

**Service:** Bitbucket REST API (Atlassian)
**Endpoint:** `https://api.bitbucket.org/2.0/repositories/resursbankplugins/resursbank-woocommerce/refs/tags`

This plugin connects to the Bitbucket REST API to check the latest available tag for the Resurs Bank Payments for WooCommerce plugin. This is used to help administrators compare the installed version with the latest available upstream version.

A request is sent only when an administrator manually clicks "Check for Updates" in the toolbox settings tab.

The request sends the current site's server IP address as part of the normal HTTPS request to the Bitbucket API. No customer or order data is sent by this feature.

Bitbucket is provided by Atlassian.
Terms of Service: https://www.atlassian.com/legal/cloud-terms-of-service
Privacy Policy: https://www.atlassian.com/legal/privacy-policy


== Installation ==

1. Upload to `/wp-content/plugins/tornevall-networks-toolbox-for-resurs-bank-payments/`
2. Activate via Plugins screen
3. Go to **WooCommerce > Settings > Tornevall Networks Toolbox for Resurs Bank Payments**
4. Review the detected Resurs plugin status and configure widget behavior if needed

== Security ==

This plugin implements WordPress security best practices:

* **Nonce Verification**: All form submissions (POST requests) are protected with WordPress nonces to prevent CSRF attacks.
* **Permission Checks**: All admin actions require the `manage_woocommerce` capability, ensuring only authorized administrators can modify settings.
* **Input Sanitization**: All user input is properly sanitized using WordPress functions.
* **AJAX Security**: AJAX endpoints validate both user permissions and nonce tokens before processing data.

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

== Screenshots ==

1. Toolbox tab overview in WooCommerce settings with plugin status and controls.
2. Resurs plugin version-check result panel in the toolbox admin UI.
3. Part Payment Widget shortcode/manual mode settings in the toolbox tab.
4. Before: shortcode inserted in WooCommerce product description editor (`[resurs_partpayment_widget]`).
5. After: rendered Resurs Part Payment widget visible on the product page description.

== Changelog ==

= 1.0.3 =
* Reworked Part Payment shortcode context detection so the shortcode works in WooCommerce product description content and across more template/block flows.
* Improved widget bootstrap/runtime context handling for shortcode/manual mode.
* Added/updated handoff through the `resursbank_pp_price_data` filter so module-resolved amount data can be passed to the Resurs widget when available.
* Improved shortcode stability when standard single-product globals are missing.
* Kept automatic summary rendering disabled in shortcode/manual mode to avoid double-render conflicts.

= 1.0.2 =
* Adjusted plugin path resolution to use plugin constants defined from the main plugin file, ensuring compatibility with WordPress directory handling across installations.
* Corrected external-service disclosure text for the Bitbucket API integration: the version check request is only sent when an administrator manually clicks "Check for Updates" — not on settings page load.
* Replaced manual `wp_verify_nonce()` conditional in settings save handler with `check_admin_referer()`, the WordPress-standard single-call nonce function recognised by plugin-review scanners.
* Replaced manual `wp_verify_nonce()` conditional in the version-check AJAX handler with `check_ajax_referer()` for the same reason.
* Reordered security checks in both handlers so that nonce validation runs before capability checks, following WordPress plugin-review recommendations.

= 1.0.1 =
* Replaced raw inline `<style>` and `<script>` tags on the toolbox admin page with proper WordPress admin enqueue usage.
* Scoped toolbox admin CSS/JS so they only load on the WooCommerce toolbox settings tab.
* Fixed hardcoded filesystem path in the Resurs plugin detector by using proper WordPress path helper functions instead of manual string concatenation.
* Hardened settings save flow with explicit capability + nonce failure handling.
* Improved AJAX security ordering and validation flow for version checks.
* Replaced unsafe raw input reads with early WordPress sanitization in settings save.
* Fixed late escaping of translated output in the WooCommerce missing-plugin admin notice.
* Stopped writing legacy option keys on save; canonical option keys are now the only write target.

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
* Restored the Part Payment Widget module in the current plugin structure.
* Added shortcode/manual rendering mode for the Part Payment Widget.
* Added configurable shortcode name handling in the toolbox settings.
* Restored the widget settings card in the WooCommerce toolbox tab.
* Improved Resurs plugin status detection for multiple known plugin file variants.
* Kept compatibility with legacy shortcode option names during migration to the new naming convention.
* Added a WordPress.com plugin-page link in the Resurs update result UI.
* Added an "Upgrade in Plugins" action that opens the WordPress plugin manager for the detected Resurs plugin.
* Fixed bundled translation loading for the current plugin text domain.
* Rebuilt the bundled Swedish translation files to match the current plugin name and UI text.

== License ==

GPLv2 or later. See LICENSE file.

== Author ==

Thomas Tornevall — [www.tornevalls.se](https://www.tornevalls.se/)

---

**⚠️ Disclaimer**: Not endorsed by Resurs Bank.
