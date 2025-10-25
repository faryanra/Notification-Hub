=== Notification Hub ===
Contributors: faryanra
Tags: notifications, admin alerts, Slack, Telegram, Email, WooCommerce, CF7
Requires at least: 5.6
Tested up to: 6.5.3
Stable tag: 1.3.9
Requires PHP: 7.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Notification Hub centralizes all WordPress notifications into one dashboard — now fully secure, stable, and Pro-ready.

== Description ==
Collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
Includes a complete dashboard, REST API, and webhook support.

**New in v1.3.9 — Final Cleanup & Pro Ready**
- ✅ Standardized file versions and headers
- 🔐 Enforced full nonce + capability checks across admin forms
- 🧱 Cleaned and stabilized folder structure
- 🪪 License system ready (`validate()`, `deactivate()`)
- 🧹 Removed leftover debug/test logs
- 🗂 uninstall.php now keeps user data safely

== Installation ==
1. Upload `notification-hub` to `/wp-content/plugins/`
2. Activate via Plugins menu
3. Visit “Notification Hub” in the WordPress admin

== Changelog ==

= 1.3.9 =
* Final Cleanup & Security Review
* Added: License validate/deactivate
* Added: uninstall.php safe cleanup (keeps data)
* Security: All forms now nonce-protected
* Security: Full `manage_options` enforcement
* Cleanup: Removed dev logs & redundant comments

= 1.3.8 =
* Added: Full accessibility modal (role, aria-*)
* Added: Keyboard Esc close & focus management
* Improved: CSS structure and layout spacing
* Fixed: Scroll overflow issue with modal open
* Verified: Full localization coverage

= 1.3.7 =
* Added: REST API `/nh/v1/test-trigger/{id}`
* Added: Webhook `/nh/v1/inbound`
* Secure: REST only for admins, table check before query

= 1.3.6 =
* Fixed: Redirect issue after Send Test
* Unified: Settings slug `nh_settings`
* Added: Translation `.pot` file

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
