# 🚨 Notification Hub (v1.3.8)

A modular, secure, and extensible plugin to collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
Built with a modern UI, real dashboard, REST API & Webhook support — now fully accessible and visually polished.

---

## ✨ What’s New in v1.3.8
**Focus:** UX & Accessibility Polish (A11y + UI Refinement)

- 🪟 Full accessibility support (`role="dialog"`, `aria-*`, `tabindex`)
- ⌨️ Esc key closes modal, focus trap & page scroll lock
- 💅 CSS sections: Layout / Tabs / Table / Modal / Buttons
- 🧠 Improved spacing, alignment, and hover feedback
- 🌍 100% i18n coverage for PHP + JS strings

---

## 🧩 Core Features
- Real dashboard (All / Active / Archived / Search / Bulk actions)
- Email channel with Send Test button
- Admin bar unread counter
- WooCommerce, CF7, and Core integrations
- Custom Hook Manager (CRUD + Test)
- REST API `/nh/v1/test-trigger/{id}`
- Webhook `/nh/v1/inbound`

---

## 📅 Changelog

### v1.3.8 — UX & Accessibility Polish
- Added: A11y modal (`role`, `aria-*`, `tabindex`)
- Added: Esc key closes modal + focus trap
- Improved: CSS structure and layout spacing
- Fixed: Scroll overflow issue on open modal
- Verified: Full textdomain coverage for PHP & JS

### v1.3.7 — REST & Webhook Activation
- Added: REST API `/nh/v1/test-trigger/{id}`
- Added: Webhook `/nh/v1/inbound`
- Safe: Table existence check before REST query
- Secure: Access limited to `manage_options`
- Logging: Debug logs under WP_DEBUG

### v1.3.6 — Settings & i18n Cleanup
- Fixed: Redirect after Send Test
- Unified: `nh_settings` slug for tabs & URLs
- Added: `.pot` translation file

---

## 👨‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 [hellocode.ir](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://linkedin.com/in/reza-rajabi-jorshari)

---

## 📜 License
GPL v3 or later — free to use, modify, and distribute.
