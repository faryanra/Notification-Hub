# Notification Hub (v1.7.2)

Notification Hub is a modular notification management plugin for WordPress that centralizes important operational events into a single admin dashboard and routes them to multiple delivery channels.

The plugin is designed with a scalable architecture so that integrations, channels, and routes can evolve independently.

---

## Highlights (v1.7.2)

### Stable Free/Pro Boot Flow
Premium now boots only after the Free plugin has fully initialized.

This is implemented through the `nh_loaded` hook to ensure dependency-safe loading similar to large WordPress plugins.

---

### Safer Data Migration
Database migrations now run earlier during runtime to ensure notification tables exist before events are processed.

This prevents failures when events occur before an admin page is opened.

---

### Reliable Settings Handling
General and Premium settings are saved independently.

Saving one tab will no longer reset values in the other tab.

Stable defaults are now enforced for:

- Notification retention
- Keep data on uninstall

---

### Cleaner Notification Behavior
New notifications now default to **Unread**.

Internal technical events are hidden from dashboard views and badge counters:

- `dispatch_check`
- `email_sent`

---

### Clean Repository Structure
Version 1.7.2 completes a major internal cleanup.

Removed legacy components:

- Duplicate root CSS files
- Duplicate root JavaScript files
- Legacy integrations folder

This results in a cleaner and more maintainable codebase.

---

### Development Safety
Smoke-test AJAX routes used during development now load only when `WP_DEBUG` is enabled.

---

## Core Features

- Unified notification dashboard (`WP_List_Table`)
- Email notification delivery
- Telegram notifications (Premium)
- Slack notifications (Premium)
- Admin bar unread badge
- Notification detail preview modal
- AJAX actions for notification management
- Bulk actions
- Advanced filtering
- CSV export
- Custom hooks with channel routing
- Integrations:
  - WordPress Core
  - WooCommerce
  - Contact Form 7

---

## Architecture

The plugin uses a modular internal structure with:

- Container-based boot process
- Integration modules
- Event listeners
- Channel dispatchers
- Repository-based data layer
- Presenters for UI rendering

This architecture allows new integrations and channels to be added without affecting the core system.

---

## Changelog

### v1.7.2

- Changed: Free/Pro boot now uses `nh_loaded` dependency model.
- Changed: Premium bootstrap no longer depends on legacy loaders.
- Changed: Database migration runs earlier during runtime.
- Fixed: Settings tabs no longer reset values across sections.
- Fixed: Stable defaults for retention and uninstall behavior.
- Changed: New notifications default to unread.
- Improved: Dashboard hides internal system events.
- Changed: Removed legacy duplicate assets and integrations folder.
- Changed: Development smoke routes limited to `WP_DEBUG`.

### v1.7.1

- Reorganized Premium classes.
- Renamed settings tab from Pro to Premium.
- Improved license UX and editing flow.

### v1.7.0

- Introduced central license state model.
- Added license key format validation.
- Added unified license bundle saving.
- Added remote verification with TTL caching.

---

## Author

Faryan Rajabi Jorshari  
HelloCode

https://www.hellocode.ir  
https://github.com/faryanra  
https://www.linkedin.com/in/faryan-rajabi-jorshari/

---

## License

GPL v3 or later