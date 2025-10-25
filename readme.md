# 🚨 Notification Hub (v1.3.6)

A modular, secure, and extensible plugin to collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
Includes a real dashboard, custom hook system, REST API layer (prepared for activation), and multi-channel test tools — built for developers and admins who want total control over notifications.

---

## ✨ Features

### 🧩 Core (Free)
- 🗂 **Dashboard:** Real-time notification table (All / Active / Archived / Search / Bulk actions)
- 📨 **Email channel:** Native wp_mail() integration + "Send Test" button
- ⚙️ **Admin bar badge:** Shows unread (active) count in real-time
- 🔗 **Integrations:**
  - WooCommerce → New orders & low stock alerts
  - Contact Form 7 → Form success/failure tracking
  - WordPress Core → New comments, post status changes, and new user registration
- 🧱 **Custom Hooks Manager:**  
  - Add / Edit / Delete / Test your own actions  
  - Store hook metadata in `nh_hooks` table  
  - Trigger via REST or programmatically (`do_action()`)

### 💎 Pro Features
- 💬 **Telegram Bot integration**
- 🔔 **Slack Webhook integration**
- 🔐 **License activation system** (enables Pro fields + unlocks multi-channel sending)

---

## 🖥 Dashboard (v1.3.6)

A modern **WP_List_Table**-based dashboard with:
- Accurate filter tabs (All / Active / Archived) → “All” truly includes both statuses
- Status filtering logic refactored for consistency and accuracy
- Bulk actions moved to central handler (`class-nh-admin-actions.php`)
- Secure URLs: All actions use `add_query_arg()` + `wp_nonce_url()`
- AJAX-based "View" modal with nonce protection
- Admin bar counter (Active alerts)
- Clean, responsive, native WordPress layout
- ✅ Persistent redirect for archive/delete actions with admin notices
- ✅ Accessible modal container foundation (role/aria coming in v1.3.8 roadmap)

---

## ⚙️ Settings UI (Updated in v1.3.6)

Two tabs:  
- **General:** Retention, Email settings  
- **Pro Channels:** Telegram, Slack, License key  

What’s new in 1.3.6:
- ✅ Tab slug is now unified: `nh_settings` everywhere (menu, redirects, JS, URLs)
- ✅ After clicking “Send Test Email / Telegram / Slack”, you are safely redirected back to the correct tab without hitting a `Sorry, you are not allowed to access this page.` error
- ✅ Inline success / error notices now appear at the top of Settings (`?success=1` / `?success=0`)
- ✅ JS keeps the selected tab visible without page breakage
- ✅ All strings now use the `notification-hub` text domain for translation
- ✅ JS alerts (like "Failed to load notification") are now localized via `wp_localize_script()` for i18n

Also unchanged from 1.3.5:
- 🔒 Pro-only fields (Telegram / Slack) are visible but disabled if no valid license key is active
- 🧪 One-click test buttons for each channel (email / telegram / slack)

---

## 🔧 Backend & Infrastructure Fixes (v1.3.6)

- ✅ `NH_Admin_UI`: fixed submenu slug (`nh_settings`) to match URLs like `?page=nh_settings&tab=...`
- ✅ `NH_Admin_Actions`: safe redirect back to Settings after test send, with status flags
- ✅ `NH_Loader`: REST API + Webhook loader is now *intentionally disabled* in v1.3.6 for safety — they’ll activate in `v1.3.7` after table existence checks and error hardening
- ✅ `dashboard.js`: all user-facing alerts now go through localized strings (`nh_i18n`) instead of hardcoded English
- ✅ `load_plugin_textdomain()` added (or confirmed) so translations load from `/languages`
- ✅ `.pot` file scaffold created under `/languages/notification-hub.pot` to prepare for full translation workflow

Carried forward from 1.3.5:
- `dbDelta()` cleanup: removed stray `...` merge junk that caused fatals
- `$wpdb->last_error` logging after insert/update so silent DB failures are debuggable
- `handle_delete()` / `handle_toggle()` moved into `class-nh-admin-actions.php` for cleaner separation

---

## 🛠 Developer API

### REST Endpoint (Disabled by default in 1.3.6, becomes active in 1.3.7)
Prepared endpoint to trigger custom hooks remotely:
```text
POST /wp-json/nh/v1/test-trigger/{id}
```
Once active, this will allow remote/manual triggering of saved hooks in `nh_hooks`.  
In 1.3.6 the class `NH_REST_API` is shipped but not booted automatically to avoid 500s on first install if the DB tables aren't ready.

### Dynamic Hook Registration
All saved hooks are automatically registered via:
```php
do_action('my_custom_hook', [
  'title'  => 'Example Event',
  'body'   => 'Triggered via NH system',
  'source' => 'plugin-x'
]);
```

You can map any WordPress action to any channel (Email / Telegram / Slack).

---

## 📅 Changelog

### v1.3.6
- Fix: “Sorry, you are not allowed to access this page.” after Send Test is gone — redirect now returns to `nh_settings` with success/fail state
- Fix: Settings submenu and tab URLs are now unified under `nh_settings`
- Fix: JS tab persistence + safe test buttons (`.nh-test-btn`) now keep you on the correct tab after reload
- Improvement: All Settings / Dashboard strings use the `notification-hub` text domain for translation
- Improvement: Localized JS messages via `wp_localize_script()` (`nh_i18n`), no more hardcoded English alerts in dashboard actions
- Improvement: `NH_Loader` now defers REST/Webhook boot until v1.3.7 for safer first activation on fresh installs
- Internal: Added `/languages/notification-hub.pot` so translators and future .mo files can start
- Internal: Centralized admin redirects in `NH_Admin_Actions`, including `nh_test_channel`, `nh_delete_notification`, and archive/unarchive flow

### v1.3.5
- Fix: Corrected `All` tab count to show all notifications, not just active ones
- Fix: Accurate status filtering (all / active / archived) for dashboard list
- Fix: Unified action URLs using `add_query_arg()` + `wp_nonce_url()`
- Change: Moved `handle_delete()` and `handle_toggle()` to `class-nh-admin-actions.php`
- Fix: Removed `...` literals in code that caused PHP fatal errors
- Improvement: Logging added for `$wpdb->last_error` on DB operations

### v1.3.4
- Modal Preview system finalized (AJAX + JSON + i18n)
- UX polish: Button styles, badges, refresh indicator

### v1.3.3
- Refactored admin UI separation (dashboard, hooks, settings)
- WP Admin Bar badge added

### v1.3.2
- Initial view modal system with nonce-protected AJAX

### v1.3.1
- Bulk action fixes and list count bugs addressed

### v1.3.0
- Rebuilt Dashboard (filters, search, bulk actions)
- Added Custom Hooks Manager (CRUD + Test)
- Added REST API endpoint `/nh/v1/test-trigger/{id}`
- Admin Bar Badge (unread counter)
- Refactored integrations (WooCommerce, CF7, WP Core)
- Security overhaul: Nonce checks, sanitization, capability checks
- UI/UX improvements: Modals, badges, notices
- Code cleanup & modular structure

### v1.2.0
- Persistent tab fix after Save/Test
- Admin notices
- Pro field locking and layout polish

### v1.1.0
- Added WooCommerce & CF7 integrations
- Slack & Email support

### v1.0.0
- MVP release

---

## 👨‍💻 Author

**Faryan Rajabi Jorshari (HelloCode)**  
🔗 https://www.hellocode.ir  
🐙 https://github.com/faryanra  
💼 https://linkedin.com/in/reza-rajabi-jorshari

---

## 📜 License
GPL v3 or later — Free to use, extend, and redistribute.
