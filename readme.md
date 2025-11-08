# 🚨 Notification Hub (v1.5.0 — UX Polish + Real-Time Release)

A professional-grade notification manager for WordPress — now featuring a real-time dashboard, admin bar sync, database migration, and uninstall customization.

---

## ✨ Highlights (v1.5.0)

### 🧱 Database & Migration
- Added `read_at DATETIME NULL` column to `nh_notifications`
- Auto-migration with `ALTER TABLE` on upgrade
- Rewritten queries to rely on `read_at IS NULL` instead of `status`
- Added safe schema update detection via `NH_Database::maybe_upgrade()`

### 🧩 Dashboard & UX
- Added “Mark as Read” button (single + bulk)
- Added **Export CSV** for current filtered view
- Added **Actions column** with contextual links:
  - 🛒 WooCommerce Orders
  - 💬 Comments
  - 📝 Posts
  - 📩 CF7 Forms
  - Tooltip fallback → “No context”
- Polished table visuals and consistent row heights
- Updated “NEW” / “Archived” status badges

### 🔔 Admin Bar Badge
- Fully rewritten badge logic (uses `read_at IS NULL`)
- Tracks `nh_badge_last_seen_at` when Dashboard opened
- Now live-syncs across all admin pages
- Fixed reset-to-total bug after hitting 0 unread
- Always visible — shows “0 New” instead of hiding

### ⚙️ Live Refresh (JS Polling)
- Real-time updates every 15s via `/nh/v1/notifications?since=last_ts`
- Smooth refresh animation for new rows
- Added “⏳ Updating…” indicator
- Badge + table synchronized seamlessly

### 🧹 Cleanup & Structure
- Rewritten `uninstall.php` with options:
  - Drop/keep data
  - Delete all `nh_*` options
  - Unschedule Action Scheduler
  - Multisite-safe handling
- Split `admin.js` (global badge) and `dashboard.js` (table refresh)
- Modal redesigned: icon-only eye button (`dashicons-visibility`)
- Cleaned debug logs, unified emoji markers in logs

---

## 📅 Changelog

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
