# 🚨 Notification Hub (v1.6.2)

A real-time, AJAX-powered notification manager for WordPress — featuring a unified admin dashboard, multi-channel notifications, and a modular codebase.

---

## ✨ Highlights (v1.6.2)

### 🔐 Security Hardening
- ✅ Hardened dashboard query parameters (whitelist/sanitize `orderby`, `order`, paging)
- ✅ Hardened filter handling (validated keys, numeric priority, sanitized view/status)
- ✅ Hardened bulk actions (allowlist actions, normalized SQL IN placeholders)
- ✅ Hardened AJAX actions (absint IDs, safer update formats, escaped error strings)

### 🧼 Safer Sanitization & Integrations
- ✅ Sanitized notifier settings/inputs (email option, source/type slugs)
- ✅ Sanitized Slack webhook URL
- ✅ Sanitized Telegram chat ID
- ✅ Fixed handler class naming consistency (`NH_Notifier_Telegram`, `NH_Notifier_Slack`)

### 🎨 UI & JS Stability Improvements
- ✅ Scoped admin CSS so it won’t affect other wp-admin pages
- ✅ Centralized modal base styles in `assets/css/admin.css`
- ✅ Fixed dashboard column CSS selectors and removed obsolete CSS rules
- ✅ Dashboard JS: consistent `window.nhAdmin` usage + removed `innerHTML` usage for modal meta
- ✅ Admin JS: consistent `window.nhAdmin` usage + NEW-row persistence via `tr[data-id]`
- ✅ Settings JS: safer URL construction + guards when tabs/panes missing

### 📦 CSV Export Improvements
- ✅ Hardened capability checks
- ✅ Added UTF-8 BOM for Excel compatibility

---

## 📅 Changelog

### v1.6.2 — Security + Stability
- Security: Whitelist + sanitize dashboard orderby/order and paging parameters.
- Security: Harden dashboard filters (validate keys, numeric priority, sanitize view/status).
- Security: Harden bulk actions (allowlist actions, normalize IN placeholders).
- Security: Harden AJAX actions (absint IDs, safer update formats, escaped error strings).
- Security: Sanitize notifier options and handler inputs (email, source/type slugs, Slack webhook, Telegram chat id).
- Fix: Rename Telegram/Slack handler classes to consistent `NH_Notifier_Telegram` / `NH_Notifier_Slack`.
- Fix: Dashboard JS uses `window.nhAdmin` consistently and avoids `innerHTML` for modal meta.
- Fix: Admin JS uses `window.nhAdmin` consistently; NEW-row persistence uses `tr[data-id]`.
- Fix: Settings JS now safely builds URLs for relative links and guards when tabs are missing.
- Style: Scope admin `.wrap` margins to plugin pages only.
- Style: Centralize modal base styles into `assets/css/admin.css`.
- Style: Fix dashboard table column selectors and remove obsolete CSS rules.
- Improved: CSV export hardened (capability checks) and adds UTF-8 BOM for Excel compatibility.

### v1.6.1 — Architecture Refactor
- Added: Modular file structure (11 new focused classes)
- Added: CSV export with full column customization
- Added: Advanced filtering system (time, source, type, priority, status)
- Added: Priority auto-calculation with context awareness
- Added: Enhanced tags system with JSON storage
- Improved: Code organization and maintainability
- Fixed: CSV export button missing from dashboard
- Refactored: Admin actions → 3 focused modules
- Refactored: Notifier → 4 channel handlers
- Refactored: Dashboard → 6 specialized classes

---

## 👨‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 [hellocode.ir](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://www.linkedin.com/in/faryan-rajabi/)

---

## 📜 License
GPL v3 or later — free to use, modify, and distribute.