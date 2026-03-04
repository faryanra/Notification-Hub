=== Notification Hub ===
Contributors: faryanra
Tags: notifications, dashboard, woocommerce, alerts, admin
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.7.2
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A unified admin dashboard for important WordPress, WooCommerce, and CF7 notifications.

== Description ==

Notification Hub provides a central dashboard for operational events across your WordPress site.

Track comments, user registrations, post events, WooCommerce orders, Contact Form 7 submissions, and custom hooks in one sortable table with quick actions.

The plugin uses a modular architecture designed for scalability and maintainability, allowing notifications to be routed to multiple channels such as Email, Telegram, and Slack.

== What's New in v1.7.2 ==

This release focuses on architecture stability, cleaner boot flow, and repository cleanup.

- Free/Pro boot now follows a dependency-safe model using `nh_loaded`.
- Premium bootstrap no longer depends on legacy loader files.
- Database migration now runs earlier during runtime to ensure schema readiness.
- Settings save flow is split by tab (General vs Premium) to prevent accidental resets.
- General defaults are hardened for retention and uninstall behavior.
- New notifications now default to the "Unread" state.
- Internal technical events (`dispatch_check`, `email_sent`) are hidden from dashboard views.
- Dashboard search and export toolbar improvements.
- Legacy duplicate root assets were removed.
- Legacy integrations folder removed for a cleaner architecture.
- Development smoke-test AJAX routes now load only when `WP_DEBUG` is enabled.

== Features ==

- Unified Notification Table (`WP_List_Table`)
- Email notification channel
- Telegram notification channel (Premium)
- Slack notification channel (Premium)
- Admin bar unread badge
- Modal preview for notification details
- AJAX actions (mark read, mark unread, delete)
- Bulk actions
- Advanced filters
- CSV export
- Custom hooks with channel targeting
- Integrations:
  - WordPress Core
  - WooCommerce
  - Contact Form 7
- Priority system (0–100)

== Installation ==

1. Upload the plugin to `/wp-content/plugins/notification-hub`
2. Activate it from the Plugins screen
3. Open `Notification Hub` from the WordPress admin menu
4. Configure General and Premium settings

== Changelog ==

= 1.7.2 =
* Changed: Free/Pro boot flow migrated to `nh_loaded` dependency model.
* Changed: Premium bootstrap no longer relies on legacy loader files.
* Changed: Database migration runs earlier during runtime.
* Fixed: Settings tab saves no longer reset values across tabs.
* Fixed: General defaults for retention and keep-data settings.
* Changed: New notifications default to unread state.
* Improved: Dashboard hides internal technical events (`dispatch_check`, `email_sent`).
* Changed: Removed duplicate legacy root assets.
* Changed: Removed legacy integrations folder for clean architecture.
* Changed: Development smoke-test AJAX routes restricted to `WP_DEBUG`.

= 1.7.1 =
* Changed: Premium-only classes moved to premium-prefixed files for clean extraction.
* Changed: Settings tab renamed from Pro → Premium.
* Improved: License box UX (lock/edit toggle, saved pill, masked key, warnings).
* Added: Cloudflare/WAF allowlisting note for license verify endpoint.

= 1.7.0 =
* Added: Central NH_License core with normalized state (status, features, domain, last_check, grace_until, message, license_hash).
* Added: Strict Pro key format validation (NH-PRO-XXXX-XXXX).
* Added: Unified License & Pro Features box in Settings → Pro with `nh_save_license_bundle` action.
* Added: Remote verify with TTL, transient lock, and POST→GET fallback.
* Added: Support for extended license statuses: active, inactive, revoked, grace, banned, expired.
* Added: Capability-based checks via NH_License::can() for Pro channels (telegram, slack).
* Improved: Pro channel gating in NH_Notifier now enforces per-feature capabilities (telegram/slack).
* Improved: Pro settings UI now enables/disables fields and test actions based on capabilities.

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
