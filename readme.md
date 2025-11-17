# 🚨 Notification Hub (v1.6.0 — AJAX Actions + UX Sync)

A real-time, AJAX-powered notification manager for WordPress — now featuring inline actions (Read / Important / Delete), smoother UX sync, and performance updates across dashboard and modal views.

---

## ✨ Highlights (v1.6.0)

### ⚡ AJAX Inline Actions (Mark Read / Important)
- ✅ Converted all **row actions** (Mark as Read / Unread / Important / Remove) to **AJAX-based handlers**
- ✅ No page reload — instant feedback and visual refresh
- ✅ Cleaner interaction model, no URL jumping or full form posts
- ✅ Used `wp_ajax_*` with reusable `nh_ajax_handle()` closure
- ✅ Full capability + nonce verification per action

### 🌀 Real-Time Dashboard Sync (Live Refresh)
- ✅ Integrated action response with `nh_refresh_force` event
- ✅ Fully synced:
  - ✅ Table content
  - ✅ Status tags (All / Unread / Archived / Important)
  - ✅ Modal logic (marks item as read when clicked)
  - ✅ Time indicators (auto-updating human timestamps)

### 🧪 Title-Based Auto Read
- ✅ Clicking on title now automatically marks the notification as read before navigating to admin context (post / order / comment)
- ✅ Preserves existing `make_admin_link_from_context()` logic
- ✅ Graceful fallback when no context link exists

### 🎯 UX & Visual Feedback
- ✅ “⏳ Table Loader Overlay” on AJAX action to indicate change in progress
- ✅ Smarter **row highlighting** when new items arrive (`.nh-row-anim`)
- ✅ Admin Bar Badge remains synced (`read_at IS NULL`)
- ✅ Tooltip fallback on missing values

### 💥 Bugfixes
- 🐛 Fixed **Unread priority** conflict with Important:
  - Now both statuses are shown independently with correct classes
- 🐛 Fixed issue where **modal view didn’t update read status** in table
- 🐛 Fixed **title row** remaining unread after context click

### 🧹 Code Cleanup
- ✅ Removed broken filter form (min_priority, tags, only_important) — deferred to future version
- ✅ Clean separation between:
  - `admin.js` → global badge + common UI
  - `dashboard.js` → table, modal, polling, AJAX actions

---

## 📅 Changelog

### v1.6.0 — AJAX Actions + UX Sync
- Added: Fully AJAX-based Mark as Read / Unread / Important / Delete
- Added: Live sync of table rows and counters after each action
- Added: Loader overlay during table refresh
- Added: Auto-Mark as Read on title click
- Added:Filters (priority/tags)
- Improved: Modal interaction UX (smoother open / close)
- Improved: Row highlighting for new entries
- Fixed: Unread/Important visual conflict
- Cleanup: dashboard.js refactored, actions consolidated, filters disabled

---

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
