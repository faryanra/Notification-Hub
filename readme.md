# 🚀 Notification Hub (v1.5.1 — Stability & UX Refinement)

A professional-grade notification manager for WordPress — now with fully reliable Mark-as-Read, persistent highlighting, and smoother live updates.

---

## ✨ Highlights (v1.5.1)

### 🧩 Dashboard
- Fixed: “Mark as Read” now functions perfectly (single + AJAX)
- Fixed: Live refresh now syncs badge + table correctly
- Fixed: CSV Export now outputs valid file (no blank screen)
- Fixed: New notifications stay highlighted until manually read
- Improved: Dashboard PHP fully commented and modular
- Improved: Table counts accuracy (All / Active / Archived)
- Cleaned: Removed redundant event listeners and duplicate logic

### 🔔 Admin Bar Badge
- Fixed: No longer resets count after visiting dashboard
- Refreshes every 15 seconds automatically via AJAX
- Works globally on all admin pages

### ⚙️ Code Quality
- Rewritten `admin.js` and `dashboard.js` (modular, no duplication)
- Fully documented code (English comments only)
- Consistent nonce + capability verification
- Finalized CSV export and error handling
- Removed deprecated or inline code blocks

---

## 🧱 Core Features
- Unified dashboard (All / Active / Archived)
- Real-time unread badge
- Bulk and single Mark-as-Read
- Multi-channel notifications (Email / Slack / Telegram)
- Custom Hooks Manager (CRUD + Test)
- REST & Webhook APIs
- CSV Export (filtered view)
- Secure nonce-based actions
- Multisite-ready database logic

---

## 📅 Changelog

### v1.5.1 — Stability & UX Refinement
- Fixed: Mark-as-Read AJAX handler and nonce logic
- Fixed: Badge + live sync consistency
- Fixed: CSV Export blank page
- Improved: Table UX, counts, and row highlight logic
- Improved: Code clarity and file documentation
- Cleanup: Removed duplicate events and inline JS

### v1.5.0 — UX Polish + Real-Time Release 
- Added: Live unread badge + dashboard refresh sync
- Added: Mark as Read (single + bulk) + CSV Export
- Added: read_at column with migration handler
- Added: Context-aware Actions column
- Fixed: badge count reset to total on 0 unread
- Fixed: Invalid Request errors in modal view
- Improved: uninstall.php with full cleanup / keep-data mode
- Improved: visual consistency, code structure, and JS logic
- Cleanup: removed old logs and unused test functions

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

## 👨‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 [hellocode.ir](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://www.linkedin.com/in/faryan-rajabi/)

---

## 📜 License
GPL v3 or later — free to use, modify, and distribute.
