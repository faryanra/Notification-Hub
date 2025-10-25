# 🚨 Notification Hub (v1.3.9 — Final Cleanup & Pro Ready)

A modular, secure, and extensible plugin to collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
This version finalizes the branch with complete security, structure, and Pro-ready foundation.

---

## ✨ Highlights (v1.3.9)

### 🧩 Core Improvements
- Standardized file headers and version numbers (`@version 1.3.9`)
- Folder structure finalized and stable for long-term maintenance
- All forms verified for nonce + capability checks
- Security class (`NH_Security`) fully reviewed and cleaned
- License class upgraded (now supports `validate()` and `deactivate()`)
- uninstall.php now safely removes settings but keeps data tables for users who may reinstall

### 🔐 Security Review
- Every form now has `wp_nonce_field()` in templates
- Every handler calls `NH_Security::verify_nonce()` before DB operations
- Capability checks enforced via `NH_Security::ensure_cap()`
- Safe sanitization added for all user inputs and request params

### 🪪 License System
- `NH_License::validate($key)` sanitizes, checks length, and stores keys
- `NH_License::deactivate()` removes key on request
- Future-proof structure for Pro remote validation (coming in v1.4.0)

### 🧹 Cleanup
- Removed all leftover dev/test logs
- WP_DEBUG logs restricted only to errors (`❌`), not successes (`✅`)
- Removed redundant comments and unused variables

### 🧾 uninstall.php (Safe Mode)
- Removes plugin options (`nh_*`)
- Unschedules cleanup cron job
- Keeps `nh_hooks` and `nh_notifications` tables
- Ready for future “Delete all data?” confirmation & feedback form

---

## 📅 Changelog

### v1.3.9 — Final Cleanup & Pro Ready
- Cleanup: Removed debug/test logs
- Security: Full nonce & capability enforcement
- Added: License `validate()` and `deactivate()` methods
- Added: uninstall.php safe cleanup (keeps user data)
- Finalized: Folder structure and file versions
- Marked: End of v1.x branch — stable and production-ready

### v1.3.8 — UX & Accessibility Polish
- Added: A11y modal support (`role`, `aria-*`, `tabindex`)
- Improved: CSS layout and structure
- Verified: Full i18n coverage for PHP & JS

### v1.3.7 — REST & Webhook Activation
- Added: REST API `/nh/v1/test-trigger/{id}` and `/nh/v1/inbound`
- Secure: Access limited to `manage_options`
- Safe: Table existence checks before queries

### v1.3.6 — Settings & i18n Cleanup
- Fixed: Redirect after Send Test Email/Telegram/Slack
- Added: `.pot` translation file
- Unified: Tab slug consistency (`nh_settings`)

---

## 👨‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 [hellocode.ir](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://linkedin.com/in/reza-rajabi-jorshari)

---

## 📜 License
GPL v3 or later — free to use, modify, and distribute.
