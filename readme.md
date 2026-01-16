# 🚨 Notification Hub (v1.6.1 — Architecture Refactor)

A real-time, AJAX-powered notification manager for WordPress — now featuring modular architecture, enhanced performance, and cleaner codebase.

---

## ✨ Highlights (v1.6.1)

### 🏗️ Major Architecture Refactor
- ✅ **Modular File Structure**: Split large monolithic files into focused, single-responsibility classes
- ✅ **Admin Actions Refactored**:
  - `admin-actions/class-nh-admin-license.php` (License management)
  - `admin-actions/class-nh-admin-hooks.php` (Hook CRUD + Testing)
  - `admin-actions/class-nh-admin-csv-export.php` (CSV export handler)
- ✅ **Notifier Channel Split**:
  - `notifier/class-nh-notifier-queue.php` (Queue + DB logging)
  - `notifier/class-nh-notifier-email.php` (Email handler)
  - `notifier/class-nh-notifier-telegram.php` (Telegram - Pro)
  - `notifier/class-nh-notifier-slack.php` (Slack - Pro)
- ✅ **Dashboard Modularization**:
  - `dashboard/class-nh-notifications-table.php` (Main table)
  - `dashboard/class-nh-table-query.php` (Query builder)
  - `dashboard/class-nh-table-columns.php` (Column rendering)
  - `dashboard/class-nh-table-bulk-actions.php` (Bulk operations)
  - `dashboard/class-nh-table-filters.php` (Filters + Export UI)
  - `dashboard/class-nh-dashboard-actions.php` (AJAX handlers)

### 📦 CSV Export Restored
- ✅ Full CSV export functionality with customizable columns
- ✅ Export button integrated into dashboard toolbar
- ✅ Supports all notification fields (id, source, type, title, message, status, priority, tags, context, timestamps)
- ✅ Proper encoding for tags (JSON → pipe-separated) and context (pretty JSON)

### 🎯 Enhanced Filtering System
- ✅ Filter by **Time Range** (Today, Yesterday, Last 7/30 days, Last year)
- ✅ Filter by **Source** (WooCommerce, CF7, Comments, etc.)
- ✅ Filter by **Type** (Order, Comment, Form, etc.)
- ✅ Filter by **Priority** (0-100 scale)
- ✅ Filter by **Read Status** (Read, Unread, Important)
- ✅ Clear Filters button for easy reset
- ✅ JavaScript-powered filter application (no page reload)

### ⚡ Priority System Enhancement
- ✅ Auto-calculation based on notification context:
  - 🔴 Security alerts: 90
  - 🟠 WooCommerce orders: 80
  - 🟡 Comments: 60
  - 🟢 Forms (CF7): 55
  - ⚪ Default: 50
- ✅ Priority normalization (clamped to 0-100 range)
- ✅ Sortable priority column in dashboard

### 🏷️ Tags System Improvements
- ✅ JSON array storage for tags
- ✅ Auto-tagging based on source + type
- ✅ Tag pills rendering in dashboard
- ✅ CSV export with pipe-separated tags

### 📉 Code Quality Improvements
- ✅ **40% code reduction** through refactoring
- ✅ Average file size reduced from 400+ to <200 lines
- ✅ Standardized DocBlock comments
- ✅ Improved separation of concerns
- ✅ Better error handling and logging
- ✅ Cleaner method naming conventions

### 🐛 Bug Fixes
- 🐛 Fixed CSV export missing from dashboard
- 🐛 Fixed priority not applying correctly for new notifications
- 🐛 Fixed tags JSON encoding issues
- 🐛 Fixed network policy not enforcing multisite restrictions
- 🐛 Fixed missing translation wrappers

---

## 📅 Changelog

### v1.6.1 — Architecture Refactor
- **Added**: Modular file structure (11 new focused classes)
- **Added**: CSV export with full column customization
- **Added**: Advanced filtering system (time, source, type, priority, status)
- **Added**: Priority auto-calculation with context awareness
- **Added**: Enhanced tags system with JSON storage
- **Improved**: Code organization and maintainability
- **Improved**: File sizes reduced by 40% on average
- **Improved**: Documentation with standardized DocBlocks
- **Fixed**: CSV export button missing from dashboard
- **Fixed**: Priority calculation not applying to new notifications
- **Fixed**: Tags field JSON encoding inconsistencies
- **Fixed**: Network policy enforcement in multisite environments
- **Refactored**: `class-nh-admin-actions.php` → 3 focused modules
- **Refactored**: `class-nh-notifier.php` → 4 channel handlers
- **Refactored**: Dashboard components → 6 specialized classes

### v1.6.0 — AJAX Actions + UX Sync
- Added: Fully AJAX-based Mark as Read / Unread / Important / Delete
- Added: Live sync of table rows and counters after each action
- Added: Loader overlay during table refresh
- Added: Auto-Mark as Read on title click
- Added: Filters (priority/tags)
- Improved: Modal interaction UX (smoother open / close)
- Improved: Row highlighting for new entries
- Fixed: Unread/Important visual conflict
- Cleanup: dashboard.js refactored, actions consolidated, filters disabled

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
