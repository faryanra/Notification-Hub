=== Notification Hub ===
Contributors: faryanra
Tags: notifications, telegram, slack, email, woocommerce, contact form 7, hooks, rest api
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.0
Stable tag: 1.3.2
License: GPLv3 or later

A powerful notification manager for WordPress. Collects alerts from WordPress Core, WooCommerce, and Contact Form 7, and sends them via Email, Telegram, and Slack.
Now includes a full dashboard, custom hook manager, and REST API.

== Description ==
Notification Hub centralizes all your WordPress notifications in one place.

== Features ==
* Unified dashboard with filters and bulk actions
* Email notifications (configurable + test)
* Admin bar unread badge
* Custom Hooks Manager (Add / Edit / Test / Delete)
* REST API endpoint /nh/v1/test-trigger/{id}
* Integrations: WooCommerce, Contact Form 7, WP Core
* Telegram + Slack channels (Pro)
* License system (Free + Pro)

== Installation ==
1. Upload the plugin to /wp-content/plugins/notification-hub/.
2. Activate via the Plugins menu.
3. Go to “Notifications > Settings”.
4. Configure your Email, Telegram, and Slack options.

== Changelog ==
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
