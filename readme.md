# 🚨 Notification Hub v1.3.1

A modular, secure, and extensible plugin to collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
Includes a real dashboard, custom hook system, and REST API — built for admins and developers who want total control.

---

## ✨ Key Features

### 🧩 Core (Free)
- 🗂 **Dashboard:** Notification table (Active / Archived / Search / Bulk actions)
- 📨 **Email channel:** wp_mail() integration + test button
- ⚙️ **Admin bar badge:** Unread (active) counter
- 🔗 **Integrations:**
  - WooCommerce → New order & low stock alerts
  - Contact Form 7 → Success / failure tracking
  - WordPress Core → New comments, post status changes, new user registration
- 🧱 **Custom Hooks Manager:**
  - Add / Edit / Delete / Test your own hooks
  - Stored in `nh_hooks` table
  - Trigger via REST or `do_action()`

### 💎 Pro Features
- 💬 Telegram Bot integration
- 🔔 Slack Webhook integration
- 🔐 License activation system (unlocks multi-channel sending)

---

## 🧠 What's New in v1.3.1

- ✅ Safe bootstrap (no fatal if a file is missing)
- ✅ `class-nh-test-controller.php` moved out of `/core` → now `modules/class-nh-admin-actions.php`
- ✅ Restored admin redirects and notices after:
  - Send Test Email / Telegram / Slack
  - Create / Update / Archive custom hooks
- ✅ Added `uninstall.php` for cleanup of cron + options
- ✅ Proper `load_plugin_textdomain()` on `init` for translations
- ✅ Loader now wires Registry, Services, Admin UI, Integrations, REST API/Webhook with graceful fallbacks
- ✅ Updated plugin header to `Version: 1.3.1`

This release stabilizes the architecture: core is now truly "core", admin logic is in modules, and the plugin can safely load even if some optional files are missing.

---

## Changelog

### v1.3.1
- Safe bootstrap & registry-driven loader
- Admin Actions moved to `modules/` (UI responsibility, not core)
- Fixed redirect flow and notices after Send Test / Save Hook / Archive
- Added `uninstall.php`
- Added late textdomain loading
- Prepared scaffolding for REST API + Webhook without fatal errors

### v1.3.0
- Dashboard overhaul (filters, search, bulk actions)
- Custom Hooks Manager (CRUD + Test)
- REST API endpoint `/nh/v1/test-trigger/{id}`
- Admin Bar Badge (unread counter)
- Security pass: Nonce / Capability checks
- Refactored WooCommerce, CF7, WP Core integrations

---

## Author

**Faryan Rajabi Jorshari (HelloCode)**  
🌐 https://www.hellocode.ir  
🐙 https://github.com/faryanra  
💼 https://linkedin.com/in/reza-rajabi-jorshari

---

## License
GPL v3 or later
