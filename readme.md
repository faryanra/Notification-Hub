# 🚨 Notification Hub — Full Changelog (v1.0.0 → v1.3.5)

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

## 🖥 Dashboard (v1.3.5)

A modern **WP_List_Table**-based dashboard with:
- Accurate filter tabs (All / Active / Archived) → All now truly includes both statuses
- Status filtering logic refactored for consistency and accuracy
- Bulk actions moved to central handler (`class-nh-admin-actions.php`)
- Secure URLs: All actions use `add_query_arg` + `wp_nonce_url`
- AJAX-based "View" modal with nonce protection
- Admin bar counter (Active alerts)
- Clean, responsive, native WordPress layout

---

## 🔧 Backend Fixes (v1.3.5)

- ✅ `dbDelta()` fixed: Removed broken `...` segments that caused PHP fatal errors
- ✅ `channels` column in `nh_hooks` remains `TEXT`, but ready for `LONGTEXT` if needed
- ✅ `$wpdb->last_error` logging added for debugging insert/update
- ✅ CRUD logic for notifications moved to `class-nh-admin-actions.php`

---

## ⚙️ Settings UI

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

### v1.3.5
- Fix: Corrected `All` tab count to show all notifications, not just active ones
- Fix: Accurate status filtering (all / 0 / 1) for dashboard list
- Fix: Unified action URLs using `add_query_arg()` and `wp_nonce_url()`
- Change: Moved `handle_delete()` and `handle_toggle()` to `class-nh-admin-actions.php`
- Fix: Removed `...` literals in code that caused PHP errors
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