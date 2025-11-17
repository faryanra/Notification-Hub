== Description ==

Notification Hub provides a unified admin dashboard for all important events happening across your site. Whether it's a new WooCommerce order, a comment, or a custom event — you'll see it all in a single, sortable, filterable table.

== What's New in v1.6.0 ==

🆕 **AJAX Actions & Optimized UX**  
The dashboard is now smarter, smoother, and more dynamic:
- 🔄 Mark as Read / Unread directly with AJAX
- ⭐ Mark as Important / Remove Important via one click
- 🧼 Row animations + loading indicator for table updates
- 🧠 Title click = Mark as Read + follow the link
- 🔁 Live tab count updates for All / Unread / Archived / Important
- 🔐 Full nonce protection and role checks
- 🚫 add broken filters (priority/tags)

== Features ==

- 📬 Unified Notification Table (with WP_List_Table)
- 📨 Email Alerts
- 🔔 Admin Bar Badge (Unread Count)
- 💬 Modal Viewer (View Notification Details)
- 🔄 AJAX Interactions: Mark Read/Unread, Important, Delete
- 🔗 WooCommerce / CF7 / Core Event Hooks
- ✅ Export to CSV
- 📦 Custom Hooks with Channel Targets

== Installation ==

1. Upload the plugin to `/wp-content/plugins/notification-hub`
2. Activate via "Plugins" menu
3. Open "Notification Hub" from admin menu
4. Configure your sources, channels, and enjoy!

== Changelog ==

= 1.6.0 =
* Added: Fully AJAX-based Mark as Read / Unread / Important / Delete
* Added: Live sync of table rows and counters after each action
* Added: Loader overlay during table refresh
* Added: Auto-Mark as Read on title click
* Added:Filters (priority/tags)
* Improved: Modal interaction UX (smoother open / close)
* Improved: Row highlighting for new entries
* Fixed: Unread/Important visual conflict
* Cleanup: dashboard.js refactored, actions consolidated, filters disabled

= 1.5.0 — UX Polish + Real-Time Release =
* Added: `read_at` column + migration logic
* Added: Mark as Read (single + bulk)
* Added: CSV Export (current filter)
* Added: Context-aware “Actions” column
* Added: Real-time badge refresh via AJAX polling
* Fixed: Badge count reset to total after zero
* Fixed: Invalid modal “View” errors
* Improved: uninstall.php (drop/keep data, Action Scheduler cleanup)
* Improved: consistent UI + unified JS structure
* Cleanup: removed logs + redundant debug files

= 1.4.0 — Async & Pro Edition =
* Added: Action Scheduler / WP-Cron async queue
* Added: Local License activation (Pro unlock for Telegram, Slack)
* Added: Multisite table isolation (via `$wpdb->prefix`)
* Added: Multi-channel send support (email + telegram + slack)
* Refactored: `NH_Notifier`, `NH_Queue`, and `NH_Loader` architecture
* Refactored: `NH_Admin_Actions` unified for test/send/CRUD
* Fixed: Duplicate class load (Notifier now single source)
* Improved: Log clarity with emoji & structured WP_DEBUG entries
* Cleanup: Removed legacy files and dev logs

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
Developed by **Faryan Rajabi Jorshari (HelloCode)**  
🌐 https://www.hellocode.ir  
🐙 https://github.com/faryanra  
💼 https://www.linkedin.com/in/faryan-rajabi/  

== License ==
GPLv3 or later
