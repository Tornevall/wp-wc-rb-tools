# Changelog

## Unreleased

## 1.0.1

### Fixed

- Replaced raw inline `<style>` and `<script>` tags on the WooCommerce toolbox admin tab with proper WordPress admin asset loading via `admin_enqueue_scripts`, `wp_register_*`, `wp_enqueue_*`, and `wp_add_inline_script()`.
- Scoped toolbox admin CSS/JS so they only load on `WooCommerce → Settings → Tornevall Networks Toolbox for Resurs Bank Payments`.
- Fixed hardcoded filesystem path in `class-resurs-toolbox-admin-page.php` by replacing `WP_PLUGIN_DIR . '/' . $slug` with `trailingslashit(WP_PLUGIN_DIR) . $slug` for proper WordPress path handling across different server configurations.
- Enhanced security in `class-resurs-toolbox-settings.php::save_from_woocommerce()` by using explicit `wp_die()` with user-friendly error messages for failed nonce and permission checks, ensuring unauthorized requests are clearly rejected rather than silently ignored.
- Improved AJAX security in `class-resurs-toolbox-ajax-handler.php` by reordering security checks: permission check now runs before nonce validation (per WordPress best practices), with clear comments documenting each security layer.
- Replaced `filter_input(..., FILTER_UNSAFE_RAW)` in `save_from_woocommerce()` with WordPress-idiomatic `sanitize_text_field(wp_unslash($_POST[...]))` for both the enabled flag and shortcode name fields, satisfying WordPress "sanitize early" requirements and eliminating `FILTER_UNSAFE_RAW` / `FILTER_DEFAULT` usage.
- Fixed unescaped output in `woocommerce_missing_notice()`: wrapped `__()` with `wp_kses_post()` so the translated format string is escaped before being passed to `printf()`. `esc_html__()` is not applicable here because the `%s` placeholder is replaced with a `<strong>` tag that must be allowed through; `wp_kses_post()` permits safe HTML tags while stripping unsafe markup.
- Stopped dual-writing legacy option keys (`tornevalls_resurs_pp_shortcode_*`) during settings save. Legacy keys remain read-only for migration/fallback, while canonical writes now only target `tornevall_resurs_toolbox_*` options.

## 1.0.0

### Added

- Initial release
- WooCommerce settings tab with Resurs plugin status
- Bitbucket version check via admin UI
- Realized we had two different releases in two different repositories. Restored the order.
- Restored the Part Payment Widget module in the current plugin structure.
- Added Part Payment Widget settings to the WooCommerce toolbox tab.
- Added shortcode/manual rendering mode for the Resurs Part Payment Widget.
- Added support for a configurable shortcode tag, defaulting to `resurs_partpayment_widget`.
- Added compatibility handling for legacy shortcode option names from the older plugin structure.
- Added a WordPress.com plugin-page link in the Resurs update result UI.
- Added an "Upgrade in Plugins" action that opens the WordPress plugin manager for the detected Resurs plugin.


### Changed

- Renamed the plugin to **Tornevall Networks Toolbox for Resurs Bank Payments**.
- Updated the plugin slug-facing package path and main bootstrap naming.
- Standardized internal constants, class names, AJAX action names, and nonce names.
- Refreshed admin labels and documentation branding to match the new product name.
- Updated the admin toolbox layout to include the About card, Resurs-plugin status card, and Part Payment Widget settings card.
- Improved Resurs plugin detection to support multiple known plugin entry files.
- Updated `README.md` and `readme.txt` so they reflect the current plugin name, admin path, and widget behavior.

### Fixed

- Applied plugin-check-oriented cleanups and validation updates during the rename/cleanup pass.
- Restored saving of Part Payment Widget settings from the WooCommerce settings tab.
- Restored disabling of the official automatic widget rendering when shortcode/manual mode is enabled.
- Fixed the Resurs plugin file detection used when placing the toolbox tab relative to the official Resurs settings tab.
- Fixed bundled translation loading for the current plugin text domain.
- Rebuilt the bundled Swedish translation files to match the current plugin name and UI text.
