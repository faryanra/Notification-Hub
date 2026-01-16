=== Notification Hub ===
Contributors: faryanra
Tags: notifications, dashboard, woocommerce, alerts, admin
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.6.1
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A unified admin dashboard for all important events happening across your WordPress site.

== Description ==

Notification Hub provides a unified admin dashboard for all important events happening across your site. Whether it's a new WooCommerce order, a comment, or a custom event — you'll see it all in a single, sortable, filterable table.

== What's New in v1.6.1 ==

🏗️ **Major Architecture Refactor**  
The plugin has been completely restructured for better maintainability:
- 📦 Modular file structure with single-responsibility classes
- 🧩 Split large files into focused components (40% code reduction)
- 📊 Enhanced filtering system with 5 filter types
- 💾 CSV export restored and improved
- 🎯 Smart priority system with auto-calculation
- 🏷️ Enhanced tags system with JSON storage
- 🐛 Multiple bug fixes and performance improvements

== Features ==

- 📬 Unified Notification Table (with WP_List_Table)
- 📨 Email Alerts
- 🔔 Admin Bar Badge (Unread Count)
- 💬 Modal Viewer (View Notification Details)
- 🔄 AJAX Interactions: Mark Read/Unread, Important, Delete
- 🔗 WooCommerce / CF7 / Core Event Hooks
- ✅ Export to CSV with customizable columns
- 📦 Custom Hooks with Channel Targets
- 🔍 Advanced Filtering (Time, Source, Type, Priority, Status)
- 🎯 Smart Priority System (0-100 scale with auto-calculation)
- 🏷️ Tags Support with JSON storage

== Installation ==

1. Upload the plugin to `/wp-content/plugins/notification-hub`
2. Activate via "Plugins" menu
3. Open "Notification Hub" from admin menu
4. Configure your sources, channels, and enjoy!

== Changelog ==

= 1.6.1 =
* Added: Modular file structure (11 new focused classes)
* Added: CSV export with full column customization
* Added: Advanced filtering system (time, source, type, priority, status)
* Added: Priority auto-calculation with context awareness (Security: 90, Orders: 80, Comments: 60, Forms: 55)
* Added: Enhanced tags system with JSON storage and tag pills
* Improved: Code organization - 40% code reduction through refactoring
* Improved: File sizes reduced from 400+ to <200 lines average
* Improved: Documentation with standardized DocBlocks
* Fixed: CSV export button missing from dashboard
* Fixed: Priority calculation not applying to new notifications
* Fixed: Tags field JSON encoding inconsistencies
* Fixed: Network policy enforcement in multisite environments
* Refactored: Admin actions split into License, Hooks, and CSV Export modules
* Refactored: Notifier split into Queue, Email, Telegram, and Slack handlers
* Refactored: Dashboard split into Table, Query, Columns, Bulk Actions, Filters, and AJAX Actions

= 1.6.0 =
* Added: Fully AJAX-based Mark as Read / Unread / Important / Delete
* Added: Live sync of table rows and counters after each action
* Added: Loader overlay during table refresh
* Added: Auto-Mark as Read on title click
* Added: Filters (priority/tags)
* Improved: Modal interaction UX (smoother open / close)
* Improved: Row highlighting for new entries
* Fixed: Unread/Important visual conflict
* Cleanup: dashboard.js refactored, actions consolidated

= 1.5.0 =
* Added: `read_at` column + migration logic
* Added: Mark as Read (single + bulk)
* Added: CSV Export (current filter)
* Added: Context-aware "Actions" column
* Added: Real-time badge refresh via AJAX polling
* Fixed: Badge count reset to total after zero
* Fixed: Invalid modal "View" errors
* Improved: uninstall.php (drop/keep data, Action Scheduler cleanup)
* Improved: consistent UI + unified JS structure
* Cleanup: removed logs + redundant debug files

= 1.4.0 =
* Added: Action Scheduler / WP-Cron async queue
* Added: Local License activation (Pro unlock for Telegram, Slack)
* Added: Multisite table isolation (via `$wpdb->prefix`)
* Added: Multi-channel send support (email + telegram + slack)
* Refactored: `NH_Notifier`, `NH_Queue`, and `NH_Loader` architecture
* Fixed: Duplicate class load (Notifier now single source)
* Improved: Log clarity with emoji & structured WP_DEBUG entries
* Cleanup: Removed legacy files and dev logs

= 1.3.9 =
* Added: License validate/deactivate
* Added: uninstall.php safe cleanup (keeps data)
* Security: All forms now nonce-protected
* Security: Full `manage_options` enforcement
* Cleanup: Removed dev logs & redundant comments

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
🌐 [https://www.hellocode.ir](https://www.hellocode.ir)  
🐙 [https://github.com/faryanra](https://github.com/faryanra)  
💼 [https://www.linkedin.com/in/faryan-rajabi/](https://www.linkedin.com/in/faryan-rajabi/)  

== License ==
GPLv3 or later
