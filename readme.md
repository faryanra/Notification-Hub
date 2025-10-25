# 🚨 Notification Hub (v1.3.7)

A modular, secure, and extensible plugin to collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
Includes a real dashboard, custom hook system, REST API & Webhook endpoints — built for developers and admins who want total control over notifications.

---

## ✨ Features

### 🧩 Core (Free)
- 🗂 **Dashboard:** Real-time notification table (All / Active / Archived / Search / Bulk actions)
- 📨 **Email channel:** Native wp_mail() integration + “Send Test” button
- ⚙️ **Admin bar badge:** Shows unread (active) count in real-time
- 🔗 **Integrations:**
  - WooCommerce → New orders & low stock alerts
  - Contact Form 7 → Form success/failure tracking
  - WordPress Core → Comments, post status changes, and user registration
- 🧱 **Custom Hooks Manager:**  
  - Add / Edit / Delete / Test your own actions  
  - Store hook metadata in `nh_hooks`  
  - Trigger via REST or programmatically (`do_action()`)

### 💎 Pro Features
- 💬 **Telegram Bot integration**
- 🔔 **Slack Webhook integration**
- 🔐 **License activation system** (unlocks Pro channels)

---

## 🌐 REST API (v1.3.7)
The REST API is now **fully active** and safe for use.

### Test Trigger Endpoint
Trigger custom hooks remotely:
```
POST /wp-json/nh/v1/test-trigger/{id}
```
**Access:** Requires `manage_options` (Admin)  
**Response Example:**
```json
{ "ok": true, "msg": "Hook triggered" }
```

Includes:
- ✅ Table existence check (no fatal errors if plugin freshly installed)
- ✅ Safe permission callback
- ✅ JSON structured responses
- ✅ Error logging under WP_DEBUG

---

## 🪝 Webhook Receiver (v1.3.7)
External systems can now post messages to:
```
POST /wp-json/nh/v1/inbound
```
**Payload Example:**
```json
{ "message": "Test from external app" }
```

This automatically triggers an internal Email notification:
- Title: “Inbound Webhook”
- Body: message content
- Source: “webhook”

---

## 📅 Changelog

### v1.3.7 — REST & Webhook Activation
- Added: REST API `/nh/v1/test-trigger/{id}` (secure trigger endpoint)
- Added: Webhook receiver `/nh/v1/inbound` for external POSTs
- Added: Database table existence check before any REST query
- Fix: Loader now safely skips REST/Webhook if DB not ready
- Improvement: Standardized REST JSON responses (`ok`, `msg`)
- Internal: Debug logging added for REST/Webhook initialization

### v1.3.6 — Settings & i18n Cleanup
- Fixed: Redirect after Send Test now works correctly
- Fixed: Unified `nh_settings` slug across tabs/menus
- Improved: Localized JS alerts with `nh_i18n`
- Added: `.pot` translation file and verified text domain

### v1.3.5 — Dashboard Accuracy
- Fixed: Tab counters and filters fully synchronized
- Added: `$wpdb->last_error` logging
- Moved: Bulk and delete actions to `class-nh-admin-actions.php`

---

## 👨‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 [hellocode.ir](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://linkedin.com/in/reza-rajabi-jorshari)

---

## 📜 License
GPL v3 or later — free to use, modify, and distribute.
