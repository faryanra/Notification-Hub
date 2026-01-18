# 🚨 Notification Hub (v1.7.0)

A real-time, AJAX-powered notification manager for WordPress — featuring a unified admin dashboard, multi-channel notifications, and a modular, template-based rendering system.

---

## ✨ Highlights (v1.7.0)

### 🔑 License & Pro Features
- ✅ Redesigned **License & Pro Features** box under *Settings → Pro* with a single, unified form.
- ✅ Save **License Server URL** + **License Key** together via one action (`nh_save_license_bundle`).
- ✅ Masked key preview (only first + last 4 characters visible) and “Saved” pill for quick feedback.

### 🧱 Central License Policy (NH_License)
- ✅ New `NH_License` core with normalized state: `status`, `features`, `domain`, `last_check`, `grace_until`, `message`, `license_hash`.
- ✅ Strict key format validation: `NH-PRO-XXXX-XXXX` (A–Z / 0–9). Invalid format is rejected and shown as an admin notice.
- ✅ Grace-mode support so temporary license server outages don’t instantly lock Pro.

### 🛡️ Safer Remote Verify + Anti‑Bot Detection
- ✅ Remote verification expects JSON, supports POST and GET fallback, and records detailed debug context when WP_DEBUG is enabled.
- ✅ Detects common JS/WAF “anti-bot challenge” pages and surfaces a clear, actionable message.

### 🧠 Capability-based Pro Checks
- ✅ New `NH_License::can($capability)` API (`telegram`, `slack`, …) as the single source of truth for Pro capabilities.
- ✅ Settings UI enables/disables Telegram/Slack fields (and test buttons) based on per-feature capabilities.
- ✅ `NH_Notifier` enforces Pro channels using `NH_License::can()`.

---

## 📅 Changelog

### v1.7.0 — License Server + Pro Policy
- Added: Central `NH_License` core with normalized state.
- Added: Strict Pro key format validation: `NH-PRO-XXXX-XXXX`.
- Added: Unified License box in *Settings → Pro* with `nh_save_license_bundle` action.
- Added: Remote verify with TTL, transient lock, and POST→GET fallback.
- Added: Support for extended statuses: `active`, `inactive`, `revoked`, `grace`, `banned`, `expired`.
- Added: Capability-based checks via `NH_License::can()` for Pro channels.
- Improved: Pro channel gating in `NH_Notifier` now uses `NH_License::can('telegram')` / `NH_License::can('slack')`.
- Improved: Pro settings UI now enables/disables fields and test actions based on capabilities.

### v1.6.3 — Template Rendering + Action Links
- Added: `templates/notifications/` channel templates (email/telegram/slack).
- Added: Central renderer `NH_Template` to unify channel rendering.
- Added: Human mapping helpers (`nh_human_source`, `nh_human_type`) for consistent UI labels.
- Improved: Notification payload normalization (standard keys: `title`, `summary`, `source`, `type`, `context`, `link`).
- Improved: WooCommerce + CF7 integrations now send richer payloads (type/context/link) for templates.
- Improved: WordPress core events now include wp-admin deep-links for post/user.
- Improved: Dashboard modal preview uses template renderer (Preview = output).
- Improved: Email CTA label is type-aware.
- Added: Channel switcher inside modal preview.

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
🌐 https://www.hellocode.ir  
🐙 https://github.com/faryanra  
💼 https://www.linkedin.com/in/faryan-rajabi/

---

## 📜 License
GPL v3 or later — free to use, modify, and distribute.