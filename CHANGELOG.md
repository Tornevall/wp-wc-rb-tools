# Changelog

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
