=== Notification Hub ===
Contributors: faryanra
Tags: notifications, admin alerts, Slack, Telegram, Email, WooCommerce, CF7
Requires at least: 5.6
Tested up to: 6.5.3
Stable tag: 1.3.7
Requires PHP: 7.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A powerful, modular notification manager for WordPress. Collects alerts from Core, WooCommerce, and Contact Form 7, then routes them via Email, Telegram, or Slack.  
Includes a real admin dashboard, custom hook manager, REST API, and webhook support.

== Description ==
**Notification Hub** centralizes all your WordPress notifications into one clean dashboard — and lets you send them to multiple channels.  
Track, archive, and test notifications directly from your admin panel.

**New in v1.3.7:**
- ✅ REST API `/nh/v1/test-trigger/{id}` is now active
- ✅ Webhook `/nh/v1/inbound` can receive external POSTs
- ✅ Database table existence check prevents REST-related fatals
- ✅ Loader safely skips REST/Webhook if database is missing
- ✅ Standardized JSON REST responses (`ok`, `msg`)
- ✅ Debug logging for REST/Webhook startup under WP_DEBUG

**From previous versions (1.3.6 & 1.3.5):**
- Fixed redirect after Send Test (no more "Sorry, you are not allowed…")
- Unified Settings tab slug (`nh_settings`)
- Localized JS alerts with `wp_localize_script`
- Added `.pot` file for translation
- Added `$wpdb->last_error` logging
- Moved admin actions into `class-nh-admin-actions.php`
- Improved dashboard filtering, counts, and modal previews

== Installation ==
1. Upload `notification-hub` to `/wp-content/plugins/`
2. Activate via WordPress → Plugins
3. Access **Notification Hub** in the admin menu

== Usage ==
- **Dashboard:** View, search, and archive notifications  
- **Hooks:** Create custom actions and trigger them programmatically  
- **Settings:** Configure Email and (Pro) Telegram / Slack channels  
- **REST:** POST to `/wp-json/nh/v1/test-trigger/{id}` to trigger saved hooks remotely  
- **Webhook:** POST to `/wp-json/nh/v1/inbound` to send external alerts into your system

== Changelog ==

= 1.3.7 =
* Added: REST API endpoint `/nh/v1/test-trigger/{id}` (secure trigger)
* Added: Webhook `/nh/v1/inbound` (external POST)
* Added: Table existence check before REST queries
* Improved: Loader no longer causes 500s on first install
* Improved: REST response structure (`ok`, `msg`)
* Internal: Added debug logs for REST/Webhook boot
* Security: REST access limited to `manage_options` capability

= 1.3.6 =
* Fixed: “Sorry, you are not allowed…” bug after Send Test
* Fixed: Unified `nh_settings` slug (tabs, menu, redirects)
* Improved: Localized JS alerts (`nh_i18n`)
* Added: Translation file `/languages/notification-hub.pot`

= 1.3.5 =
* Fixed: Accurate tab counts for All / Active / Archived
* Moved: CRUD actions to `class-nh-admin-actions.php`
* Added: `$wpdb->last_error` logging
* Improved: URL consistency (`add_query_arg` + `wp_nonce_url`)

= 1.3.4 =
* Finalized AJAX view modal
* Added bell icon to admin bar + “X Unread” count
* Cleaned up dashboard output and nonce handling
* Visual improvements and security updates

= 1.3.3 =
* Added centralized security layer (`core/class-nh-security.php`) with unified nonce + capability checks.
* Rewritten `class-nh-admin-actions.php` — consistent redirects, safe DB operations, and proper notices.
* Updated all forms (`templates/settings.php` + `templates/hooks.php`) to use standardized nonces and matching action names.
* Rebuilt integrations:
  * CF7: now triggers Email, Telegram, and Slack notifications.
  * WooCommerce: hooks registered directly in constructor, sends multi-channel notifications.
  * WP Core: hooks now active automatically, includes custom hooks from DB.
* Improved `NH_Loader` fallback for constructor-only integrations.
* Added validation for `action_name` and channel sanitization.
* Fixed rare permission and nonce mismatch issues during Send Test / Save / Update / Delete.
* Fully compatible with WordPress 6.6.

= 1.3.2 =
* Fixed Dashboard counters: "All" now shows total (Active + Archived)
* Fixed filter logic for Active / Archived tabs in Dashboard
* Unified action URLs in Dashboard using wp_nonce_url for consistency
* Added graceful DB checks to REST API (no fatal on fresh install)
* Added accessibility attributes (role="dialog", aria-*) to the Preview modal
* Limited Telegram/Slack token logging to WP_DEBUG only

= 1.3.1 =
* Safe bootstrap (no fatal if a file is missing)
* Admin actions moved from /core to /modules
* Restored admin redirects + notices after Send Test / Save Hook / Archive
* uninstall.php added for cleanup of cron + options
* Late textdomain loading (init)
* Loader now wires Registry, Services, UI, Integrations, REST API/Webhook with graceful fallbacks
* Version header updated to 1.3.1

= 1.3.0 =
* Rebuilt Dashboard (All / Active / Archived)
* Added Custom Hooks Manager (CRUD + Test)
* Added REST API endpoint
* Added admin bar unread counter
* Refactored integrations (WooCommerce, CF7, Core)
* Improved security, performance, and UI

= 1.2.0 =
* Persistent tabs after save/test
* Admin notices
* Pro-only fields with disabled message

= 1.1.0 =
* WooCommerce + CF7 integration
* Slack + Email support

= 1.0.0 =
* Initial release

== Author ==
Developed by Faryan Rajabi Jorshari (HelloCode)
Website: https://www.hellocode.ir
GitHub: https://github.com/faryanra
LinkedIn: https://linkedin.com/in/reza-rajabi-jorshari

== License ==
GPLv3 or later
