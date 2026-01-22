# 🚨 Notification Hub (v1.7.1)

A real-time, AJAX-powered notification manager for WordPress — featuring a unified admin dashboard, multi-channel notifications, and a modular, template-based rendering system.

---

## ✨ Highlights (v1.7.1)

### 🔑 License & Premium Features
- ✅ Premium-only license UI (Settings → Premium) with a single, unified form.
- ✅ Save **License Server URL** + **License Key** together via one action (`nh_save_license_bundle`).
- ✅ Polished UX: masked key preview, “Saved” pill, lock/edit toggle, one-time warning notices.

### 🧱 Central License Policy (NH_License)
- ✅ Normalized state: `status`, `features`, `domain`, `last_check`, `grace_until`, `message`, `license_hash`.
- ✅ Strict key format validation: `NH-PRO-XXXX-XXXX` (A–Z / 0–9).
- ✅ Grace-mode support so temporary license server outages don’t instantly lock Premium.

### 🛡️ Cloudflare / Anti‑Bot Notes (License Endpoint)
If your license endpoint is behind Cloudflare/WAF and you see non‑JSON responses (challenge pages), allowlist the verification URL so the plugin receives clean JSON.

Recommended setup:
- Put your verify endpoint on a dedicated path, for example: `/license/verify.php`.
- Cloudflare → Security/WAF: create an allow rule for that exact path.
- Disable/skip Bot Fight Mode / JS Challenge for that path.
- If you use rate limiting, set a reasonable window (the plugin caches checks for a few hours).

### 🧩 Real “two‑plugin” model
- ✅ Premium capabilities are gated by both:
  - Premium addon presence (`NH_PRO_ACTIVE`), and
  - license state/capabilities (`NH_License::can('telegram')`, `NH_License::can('slack')`).

---

## 📅 Changelog

### v1.7.1 — Premium Packaging + UX
- Changed: Premium-only classes moved to premium-prefixed files for clean extraction.
- Changed: Settings tab renamed from Pro → Premium.
- Improved: License box UX (lock/edit toggle, saved pill, masked key, warnings).
- Fixed: Free plugin no longer loads license classes when Premium addon is not active.

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
- Improved: Notification payload normalization (standard keys: `title`, `summary`, `source`, `type`, `context`, `link`).

---

## 👨‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 https://www.hellocode.ir  
🐙 https://github.com/faryanra  
💼 https://www.linkedin.com/in/faryan-rajabi/

---

## 📜 License
GPL v3 or later — free to use, modify, and distribute.
