=== Notification Hub ===
Contributors: faryanra
Tags: notifications, WordPress admin, Slack, Telegram, Email
Requires at least: 5.6
Tested up to: 6.5.3
Stable tag: 1.3.5
Requires PHP: 7.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful notification manager for WordPress. Collects alerts from WordPress Core, WooCommerce, and Contact Form 7, and sends them via Email, Telegram, and Slack.
Now includes a full dashboard, custom hook manager, and REST API.

== Description ==
Notification Hub lets you centralize your WordPress notifications and send them to multiple channels (email, Slack, Telegram).

**New in v1.3.5:**
- ✅ Fixed incorrect tab counts (All / Active / Archived now accurate)
- ⚙️ Improved filtering logic across dashboard (status filters + counts)
- 🔁 Unified all URLs (Delete, Archive) for security and testability
- 🛡 Moved all action handlers to `class-nh-admin-actions.php` for cleaner codebase
- 🐛 Fixed fatal merge bugs (literal `...` in `class-nh-database.php`, etc.)
- 🧪 Added `$wpdb->last_error` logging after DB operations
- 🔄 Refactored `handle_delete()` and `handle_toggle()` (more secure + modular)
- 📊 Channels column in `nh_hooks` can now support LONGTEXT in future

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Visit “Notification Hub” in the WordPress admin menu

== Changelog ==
= 1.3.5 =
* Fixed: Tab counters now reflect correct notification statuses
* Fixed: Filtering logic now fully supports 'all', 'active', and 'archived'
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
