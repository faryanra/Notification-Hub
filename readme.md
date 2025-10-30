# 🚨 Notification Hub (v1.4.0 — Async & Pro Edition)

A modular, secure, and extensible plugin to collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
Now powered by an async queue, local license system, and full multisite compatibility.

---

## ✨ Highlights (v1.4.0)

### ⚙️ Core / Async System
- Added background queue with Action Scheduler (fallback to WP-Cron)
- Async execution prevents admin request blocking and timeouts
- `NH_Notifier` redesigned for dual-mode: `queue_send()` + `send_now()`

### 🔑 License System (Pro Activation)
- Introduced `NH_License::is_pro()` and local activation logic
- Telegram and Slack channels now gated under Pro mode
- Future-ready for remote validation (Envato / Freemius)

### 🌐 Multisite Support
- Each site uses isolated tables via `$wpdb->prefix`
- Options stored per-blog; safe for large multisite installs

### 🔗 Integrations Expansion
Four native integrations, all now async and multi-channel:
1. **WordPress Core** → comments, post updates, user registrations  
2. **WooCommerce** → new orders, low stock alerts  
3. **Contact Form 7** → form submissions  
4. **Email (base)** → for system messages  

### 🧱 Refactor & Cleanup
- `NH_Notifier` moved from `/core/` to `/modules/` (single definition)
- Loader rebuilt for dependency injection via `NH_Core_Registry`
- Unified admin actions (CRUD + test) in `NH_Admin_Actions`
- Removed redundant logs and unused dev comments
- Clean, readable debug output with emojis for trace clarity

---

## 📅 Changelog

### v1.4.0 — Async & Pro Edition
- Added: Queue system (Action Scheduler + fallback)
- Added: Local license validation
- Added: Multisite database prefix support
- Added: Multi-channel async send (Email, Telegram, Slack)
- Refactored: Core, Loader, Notifier, and Admin Actions
- Fixed: Duplicate Notifier class issue
- Cleaned: All legacy logs and comments

### v1.3.9 — Final Cleanup & Pro Ready
- Nonce + capability enforcement
- License validate/deactivate
- Safe uninstall
- Clean folder structure

### v1.3.0 — Dashboard & Custom Hooks
- Added hooks CRUD manager
- REST API endpoints
- Dashboard Active / Archived views
- Security & performance upgrade

### 1.2.0 
* Persistent tabs after save/test
* Admin notices
* Pro-only fields with disabled message

### 1.1.0 
* WooCommerce + CF7 integration
* Slack + Email support

### 1.0.0 
* Initial release

---

## 🧩 Technical Overview

| Component | Purpose |
|------------|----------|
| **NH_Core_Registry** | Central dependency container |
| **NH_Database** | Handles CRUD for notifications & hooks |
| **NH_Queue** | Async worker (Action Scheduler / WP-Cron) |
| **NH_Notifier** | Main delivery system for all channels |
| **NH_License** | License validation & Pro gating |
| **NH_Admin_Actions** | Unified admin post actions (send/test/save) |
| **NH_Loader** | Boot sequence & integration loader |

---

## 👨‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 [hellocode.ir](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://www.linkedin.com/in/faryan-rajabi/)

---

## 📜 License
GPL v3 or later — free to use, modify, and distribute.
