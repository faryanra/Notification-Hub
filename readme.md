# 🚨 Notification Hub v1.3.0

A modular, secure, and extensible plugin to collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
Includes a real dashboard, custom hook system, and REST API — built for developers and admins who want total control over notifications.

---

## ✨ Features

### 🧩 Core (Free)
- 🗂 **Dashboard:** Real-time notification table (Active / Archived / Search / Bulk actions)
- 📨 **Email channel:** Native wp_mail() integration + test button
- ⚙️ **Admin bar badge:** Shows unread (active) count in real-time
- 🔗 **Integrations:**
  - WooCommerce → New orders & low stock alerts
  - Contact Form 7 → Form success/failure tracking
  - WordPress Core → New comments, post status, new user registration
- 🧱 **Custom Hooks Manager:**  
  - Add / Edit / Delete / Test your own actions  
  - Store hook metadata in `nh_hooks` table  
  - Trigger via REST or programmatically (`do_action()`)

### 💎 Pro Features
- 💬 **Telegram Bot integration**
- 🔔 **Slack Webhook integration**
- 🔐 **License activation system** (enables Pro fields + unlocks multi-channel sending)

---

## 🖥 Dashboard (v1.3.0)

A modern **WP_List_Table**-based dashboard with:
- Filter tabs (All / Active / Archived)
- Search box & pagination
- Bulk actions (Archive / Unarchive / Delete)
- AJAX-based "View" modal for previewing notifications
- Admin bar counter (Active alerts)
- Clean, responsive, native WordPress layout

---

## ⚙️ Settings UI (Unified)

Two tabs:  
- **General:** Retention, Email settings  
- **Pro Channels:** Telegram, Slack, License key  

✅ Tab persistence after save or test  
✅ Inline test buttons (Email / Telegram / Slack)  
✅ Locked inputs for Free version (🔒 message displayed)

---

## 🛠 Developer API

### REST Endpoint
Trigger custom hooks remotely:
```
POST /wp-json/nh/v1/test-trigger/{id}
```

### Dynamic Hook Registration
All saved hooks are automatically registered via:
```php
do_action('my_custom_hook', [
  'title' => 'Example Event',
  'body'  => 'Triggered via NH system',
  'source'=> 'plugin-x'
]);
```
---

## 📅 Changelog

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
- Persistent tab fix after Save or Test
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
🔗 [Website](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://linkedin.com/in/reza-rajabi-jorshari)

---

## 📜 License
GPL v3 or later — Free to use, extend, and redistribute.
