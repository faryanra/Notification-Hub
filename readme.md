# 🚨 Notification Hub (v1.6.3)

A real-time, AJAX-powered notification manager for WordPress — featuring a unified admin dashboard, multi-channel notifications, and a modular, template-based rendering system.

---

## ✨ Highlights (v1.6.3)

### 🧩 Template Engine (Preview = Real Output)
- ✅ Added a minimal, centralized renderer: `NH_Template::render_notification($channel, $payload)`.
- ✅ New templates live in `templates/notifications/` (email/telegram/slack).
- ✅ Dashboard modal preview now renders using the same templates, so the preview matches real channel output.

### 🏷️ Human Mapping (DB ≠ UI)
- ✅ Added central human-readable mapping for `source` and `type` in `core/class-nh-human.php`.
- ✅ Templates and UI can now show user-friendly labels (e.g., `wp_core → WordPress`, `comment_new → New Comment`) without changing DB values.

### 🔗 Admin Deep-links (Actionable Notifications)
- ✅ Added wp-admin links for key events so notifications are actionable:
  - `comment_new` → Edit comment
  - `post_status_changed` → Edit post
  - `user_registered` → Edit user
  - `order_created` → Edit order
  - `low_stock` → Edit product
  - `form_sent` / `form_failed` → Edit CF7 form

### 📨 Better Email Output
- ✅ HTML email template upgraded with consistent layout + footer.
- ✅ Smart CTA label per notification type (e.g., “View Order”, “Edit Product”, “Edit Form”).

### 👁️ Preview Polishing
- ✅ Modal preview supports switching channel output (Email/Telegram/Slack) and remembers selection.

---

## 📅 Changelog

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