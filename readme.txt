=== Notification Hub ===
Contributors: faryanra
Tags: notifications, WordPress admin, Slack, Telegram, Email
Requires at least: 5.6
Tested up to: 6.5.3
Stable tag: 1.3.6
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful notification manager for WordPress. Collects alerts from WordPress Core, WooCommerce, and Contact Form 7, and routes them to Email, Telegram, and Slack. Includes a full dashboard, custom hook manager, REST API layer (prepared), and unified Settings UI.

== Description ==
Notification Hub centralizes how alerts are captured, viewed, archived, and delivered.

**New in v1.3.6:**
- ✅ Fixed redirect after Send Test (Email / Telegram / Slack). No more "Sorry, you are not allowed to access this page."
- 🔁 Settings tab slug is now unified: `nh_settings`. All links, redirects, and JS agree.
- 🧭 Tab persistence and test buttons keep you on the correct tab after reload
- 🌍 All dashboard/settings UI strings are now properly translatable under the `notification-hub` text domain
- 🗣 JS alerts in the dashboard (AJAX fail, load fail, etc.) are localized using `wp_localize_script()` instead of hardcoded English
- 🧱 REST API and Webhook classes are bundled but NOT auto-booted in 1.3.6. They will be enabled in 1.3.7 with safety checks so fresh installs don't 500 if the DB table doesn't exist yet
- 📝 `/languages/notification-hub.pot` added to prep translation workflow

Carried forward from v1.3.5:
- 🗂 Real dashboard (WP_List_Table) with All / Active / Archived filters
- 📨 Email channel with "Send Test" button
- 🔒 Pro-only Telegram/Slack settings (locked unless a valid license is present)
- 🪝 Custom Hook Manager (CRUD + Test)
- 🚨 Admin bar unread counter
- 🧼 Safe CRUD + redirects moved into `class-nh-admin-actions.php`
- 🛠 `$wpdb->last_error` logging across DB ops for debug visibility
- 🧨 Removed leftover `...` fragments that caused fatals in earlier merges

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the “Plugins” menu in WordPress
3. Go to “Notification Hub” in the WordPress admin menu

== Screenshots ==
1. Dashboard with Active / Archived filters and unread counter
2. Settings screen with Email and Pro channels
3. Hook Manager for custom trigger actions
4. Modal preview of an incoming alert

== Changelog ==
= 1.3.6 =
* Fix: No more "Sorry, you are not allowed to access this page." after hitting Send Test in Settings
* Fix: Settings submenu + tab URLs standardized on `nh_settings` instead of mixed slugs
* Fix: JS-powered tab switching now syncs with redirect and preserves active tab
* Improvement: Localized all dashboard/JS alert strings via `wp_localize_script()` (`nh_i18n`)
* Improvement: Added/confirmed `load_plugin_textdomain()` and created `/languages/notification-hub.pot`
* Internal: REST API / Webhook boot disabled in loader; officially deferred to 1.3.7 so first-time activation cannot fatal on missing tables
* Internal: Centralized admin redirects (test channel, archive/unarchive, delete) into `class-nh-admin-actions.php`

= 1.3.5 =
* Fixed: Tab counters now reflect correct notification statuses
* Fixed: Filtering logic now fully supports "all", "active", and "archived"
* Fixed: Moved delete/archive handlers out of dashboard class to avoid mixing concerns
* Fixed: Replaced literal `...` leftovers from previous diffs
* Fixed: `prepare_items()` and `calculate_counts()` now in sync
* Added: `$wpdb->last_error` logging after DB insert/update/delete
* Improved: Unified URL generation using `add_query_arg()` and `wp_nonce_url()`
* Improved: Dashboard logic cleaned, safer redirects and access checks

= 1.3.4 =
* Finalized AJAX view modal
* Added bell icon to admin bar + “X Unread” count
* Cleaned up dashboard output and nonce handling
* Visual improvements and security updates

= 1.3.3 =
* Added centralized security layer (`core/class-nh-security.php`) with unified nonce + capability checks
* Rewritten `class-nh-admin-actions.php` — consistent redirects, safe DB operations, and proper notices
* Updated all forms (`templates/settings.php` + `templates/hooks.php`) to use standardized nonces and matching action names
* Rebuilt integrations:
  * CF7: now triggers Email, Telegram, and Slack notifications
  * WooCommerce: hooks registered directly in constructor, sends multi-channel notifications
  * WP Core: hooks now active automatically, includes custom hooks from DB
* Improved `NH_Loader` fallback for constructor-only integrations
* Added validation for `action_name` and channel sanitization
* Fixed rare permission and nonce mismatch issues during Send Test / Save / Update / Delete
* Fully compatible with WordPress 6.6

= 1.3.2 – 1.3.1 – 1.3.0 =
* Rebuilt Dashboard (All / Active / Archived)
* Added Custom Hooks Manager (CRUD + Test)
* Added REST API endpoint `/nh/v1/test-trigger/{id}` (will be re-enabled with safety in 1.3.7)
* Added admin bar unread counter
* Refactored WooCommerce, CF7, and Core integrations
* Improved security, performance, and UI

= 1.2.0 =
* Persistent tabs after save/test
* Admin notices
* Pro field locking and layout polish

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
