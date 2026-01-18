=== Notification Hub ===
Contributors: faryanra
Tags: notifications, dashboard, woocommerce, alerts, admin
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.6.3
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A unified admin dashboard for all important events happening across your WordPress site.

== Description ==

Notification Hub provides a unified admin dashboard for all important events happening across your WordPress site.

Whether it's a new WooCommerce order, a comment, a form submission, or a custom event — you'll see it all in a single, sortable, filterable table.

== What's New in v1.6.3 ==

This release introduces a template-based rendering layer so **Preview = real channel output**, plus human-readable mapping and actionable admin deep-links.

- 🧩 Template Engine: Central renderer + `templates/notifications/` for email/telegram/slack.
- 👁️ Real Preview: Dashboard modal uses the same renderer, so what you preview is exactly what channels send.
- 🏷️ Human Mapping: Central mapping for source/type labels (DB values remain unchanged).
- 🔗 Action links: wp-admin deep-links for key events (comments, posts, users, orders, products, CF7 forms).
- 📨 Improved email template: consistent HTML layout + smart CTA per notification type.
- 🎛️ Preview channel switcher: quickly toggle Email/Telegram/Slack in modal preview.

== Features ==

- 📬 Unified Notification Table (WP_List_Table)
- 📨 Email alerts
- 📣 Telegram alerts
- 💬 Slack alerts
- 🔔 Admin bar unread badge (live refresh)
- 👁️ Modal viewer (template-based preview)
- 🔄 AJAX interactions: Mark Read/Unread, Important, Delete
- 📦 Bulk actions support
- 🔍 Advanced filtering (time, source, type, priority, status)
- ✅ Export to CSV (supports current filters)
- 📦 Custom hooks with channel targets
- 🧩 Integrations: WooCommerce / CF7 / Core event hooks
- 🎯 Smart priority system (0–100 scale)

== Installation ==

1. Upload the plugin to `/wp-content/plugins/notification-hub`
2. Activate via "Plugins" menu
3. Open "Notification Hub" from admin menu
4. Configure channels and settings

== Changelog ==

= 1.6.3 =
* Added: Template Engine (`NH_Template::render_notification`) and new channel templates in `templates/notifications/`.
* Added: Human mapping helpers for consistent UI labels: `nh_human_source()` and `nh_human_type()`.
* Improved: Standardized notification payload keys (`title`, `summary`, `source`, `type`, `context`, `link`) for all channels.
* Improved: Dashboard modal preview now renders via templates (Preview = output).
* Improved: WooCommerce + CF7 payloads now include type/context/link so templates can build outcome-focused output.
* Improved: WordPress core events now include wp-admin deep-links (Edit Post / Edit User).
* Improved: Email HTML template with smart CTA label per event type.
* Added: Modal preview channel switcher (Email/Telegram/Slack).

= 1.6.2 =
* Security: Whitelist + sanitize dashboard orderby/order and paging parameters.
* Security: Harden dashboard filters (validate keys, numeric priority, sanitize view/status).
* Security: Harden bulk actions (allowlist actions, normalize IN placeholders).
* Security: Harden AJAX actions (absint ids, safer update formats, escaped error strings).
* Security: Sanitize notifier options and handler inputs (email, source/type slugs, Slack webhook, Telegram chat id).
* Fix: Rename Telegram/Slack handler classes to consistent `NH_Notifier_Telegram` / `NH_Notifier_Slack`.
* Fix: Dashboard JS uses `window.nhAdmin` consistently and avoids `innerHTML` for modal meta.
* Fix: Admin JS uses `window.nhAdmin` consistently; NEW-row persistence uses `tr[data-id]`.
* Fix: Settings JS now safely builds URLs for relative links and guards when tabs are missing.
* Style: Scope admin `.wrap` margins to plugin pages only.
* Style: Centralize modal base styles into `assets/css/admin.css`.
* Style: Fix dashboard table column selectors and remove obsolete CSS rules.
* Improved: CSV export hardened (capability checks) and adds UTF-8 BOM for Excel compatibility.

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
* Improved: uninstall.php safe cleanup (keeps data, Action Scheduler cleanup)
* Improved: consistent UI + unified JS structure
* Cleanup: removed logs + redundant debug files

= 1.4.0 =
* Added: Action Scheduler / WP-Cron async queue
* Added: Local License activation (Pro unlock for Telegram, Slack)
* Added: Multisite table isolation (via `$wpdb->prefix`)
* Added: Multi-channel send support (email + telegram + slack)
* Refactored: `NH_Notifier`, `NH_Queue`, and `NH_Loader` architecture
* Fixed: Duplicate class load (Notifier now single source)
* Improved: Log clarity & structured WP_DEBUG entries
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
🌐 https://www.hellocode.ir
🐙 https://github.com/faryanra
💼 https://www.linkedin.com/in/faryan-rajabi-jorshari/

== License ==
GPLv3 or later