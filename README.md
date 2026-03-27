# Tornevall Networks Toolbox for Resurs Bank Payments

Independent utility plugin for WooCommerce stores that use Resurs Bank Payments. This plugin is **not** created, maintained, supported, or endorsed by Resurs Bank.

Current release: **1.0.2**

## Disclaimer

Tornevall Networks Toolbox for Resurs Bank Payments is a third-party/community utility plugin. It exists to provide support, inspection, and convenience features around the official Resurs Bank WooCommerce integration.

It does **not** process payments, replace the official Resurs plugin, or make financial decisions on your behalf.

## Description

This plugin adds a WooCommerce toolbox tab with Resurs-related status and helper functionality.

The current implementation includes:

- Resurs plugin status detection for common plugin entry files
- Installed plugin file and version display
- Bitbucket-based version check from the admin UI
- WordPress.com plugin-page link and an upgrade shortcut into the WordPress Plugins screen when a newer Resurs release is detected
- Part Payment Widget settings in the toolbox tab
- Admin CSS/JS loaded through proper WordPress admin enqueue hooks on the toolbox tab only
- Optional shortcode/manual rendering mode for the Resurs part payment widget
- Compatibility handling for legacy option names from the older plugin structure
- Updated bundled Swedish translations for the current plugin name and admin UI

## External Services

This plugin uses the following external service:

### Bitbucket API (Atlassian)

- **Service URL:** `https://api.bitbucket.org/2.0/repositories/resursbankplugins/resursbank-woocommerce/refs/tags`
- **Purpose:** Check the latest available tag for the Resurs Bank Payments for WooCommerce plugin, so administrators can compare the installed version with the latest available upstream version.
- **When requests are made:**
  - Only when an administrator manually clicks **Check for Updates** in the toolbox settings tab. No request is sent on page load.
- **Data sent by this plugin:**
  - HTTP `GET` request only.
  - The site's server IP address is sent as part of the normal HTTPS connection to the Bitbucket API.
  - No customer, order, payment, or personal profile data is sent by this feature.
- **Data received/used:** Public tag/version metadata (version strings only).

Service provider legal pages:
- [Atlassian Cloud Terms of Service](https://www.atlassian.com/legal/cloud-terms-of-service)
- [Atlassian Privacy Policy](https://www.atlassian.com/legal/privacy-policy)

## Requirements

- WordPress 6.0+
- WooCommerce 7.6.0+
- PHP 8.1+
- The official Resurs Bank plugin for full widget-related functionality

## Installation

1. Upload or clone the plugin to:
   `/wp-content/plugins/tornevall-networks-toolbox-for-resurs-bank-payments/`
2. Activate it from the WordPress Plugins screen.
3. In wp-admin, go to:
   **WooCommerce → Settings → Tornevall Networks Toolbox for Resurs Bank Payments**
4. If the official Resurs plugin is installed, the toolbox tab will show detected plugin file, version, and update status.

## Part Payment Widget support

The toolbox can control how the Resurs Part Payment Widget is rendered.

### Default mode

If shortcode rendering is **disabled**, the official Resurs plugin continues to render the widget in its normal automatic position on product pages.

### Shortcode/manual mode

If **Enable Shortcode Rendering** is turned on:

- the default automatic part payment widget rendering is disabled
- a shortcode is registered instead
- you can place the widget manually where needed in your content/templates

Default shortcode:

```text
[resurs_partpayment_widget]
```

The shortcode tag can be changed from the toolbox settings page.

## Admin toolbox overview

The WooCommerce toolbox tab currently contains:

- **About This Plugin** card
- **Resurs-plugin status** card with detected file/version info
- **Check for Updates** button that compares the installed Resurs version with Bitbucket tags
- update result actions for **View Bitbucket Release**, **View on WordPress.com**, and **Upgrade in Plugins**
- **Part Payment Widget** settings card for shortcode/manual rendering

Admin assets for this screen are loaded through WordPress admin enqueue hooks rather than raw inline `<style>`/`<script>` output.

## Security

This plugin implements WordPress security best practices:

- **Nonce Verification:** All form submissions are protected using `check_admin_referer()` and all AJAX endpoints use `check_ajax_referer()` — the WordPress-standard nonce check functions. This prevents Cross-Site Request Forgery (CSRF) attacks and is reliably recognised by WordPress plugin-review tools.
- **Permission Checks:** After nonce verification passes, all admin actions require the `manage_woocommerce` capability via `current_user_can()`, ensuring only authorized administrators can modify settings.
- **Input Sanitization:** All user input from `$_POST` is sanitized using WordPress functions (`sanitize_text_field()`, `sanitize_key()`, and `wp_unslash()`) before any use.
- **AJAX Security:** AJAX endpoints (`wp_ajax_*`) validate nonce first (`check_ajax_referer()`), then user permissions, before processing any data.

Security check order in all handlers:

1. **Nonce Validation** → `check_admin_referer()` / `check_ajax_referer()` — verifies the request is legitimate and not a CSRF attack.
2. **User Permission Check** → `current_user_can('manage_woocommerce')` — verifies only authorised users can proceed.
3. **Input Validation** → Sanitize and validate submitted values.
4. **Data Update** → Only if all checks pass, save to the database.

For more information on WordPress security, see:
- [WordPress Nonces](https://developer.wordpress.org/plugins/security/nonces/)
- [WordPress Data Validation](https://developer.wordpress.org/plugins/security/data-validation/)
- [WordPress Capabilities](https://developer.wordpress.org/plugins/security/capabilities/)

## Frequently Asked Questions

### Do I need the official Resurs Bank plugin?

Yes, for any Resurs-specific runtime behavior such as the Part Payment Widget. The toolbox can be activated without it, but most Resurs-specific functionality will have nothing to hook into.

### Is this supported by Resurs Bank?

No. This is an independent third-party developer/merchant tool.

### Does this plugin replace the official payment plugin?

No. It is only a toolbox layer around the official integration.

### Can I use it in production?

Yes, if you understand what it does. It is intended as a utility and inspection/helper plugin, not as a payment engine.

## Changelog

See [`CHANGELOG.md`](./CHANGELOG.md).

## License

GPLv2 or later. See `LICENSE`.

## Author

Tomas Tornevall  
https://profiles.wordpress.org/tornevall/

---

**Disclaimer**: Not endorsed by Resurs Bank.
