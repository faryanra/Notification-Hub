# 🚀 Notification Hub v1.3.4

Centralized notification manager for WordPress with multi-channel support, live dashboard, AJAX modal preview, REST API, and admin badge.

---

## ✨ What’s New in v1.3.4

### Modal Preview
- New `View` button in dashboard list opens modal via AJAX.
- Includes Title, Message, Source, and Created Date.
- Clean UX with backdrop and close button (ESC-supported).

### Toolbar Badge
- 🔔 Bell icon added to Admin Bar with live unread count.
- Click to open dashboard.
- Available on every admin page.

### Code & UX Improvements
- Clean separation of concerns across PHP and JS files.
- No inline JS or CSS.
- dashboard.js fully modularized, handles modal + refresh only.
- `render_dashboard()` now includes fallback modal load.

### Security
- All AJAX and admin POST requests validated via:
  - `current_user_can('manage_options')`
  - `wp_verify_nonce()`
  - `wp_send_json_success/error()` with error context

---

## 🧱 Core Features
- 📊 Unified Dashboard: search, pagination, filters, bulk archive/delete
- 📨 Email notifications
- 🔗 WooCommerce: new orders, low stock
- 📮 Contact Form 7: submit success/failure
- 🏷 WordPress Core: post/comment/user hooks + DB-defined triggers
- 🎛 Custom Hook Manager
- 🔔 Admin Bar Badge (🔔 NH: 7)
- 🧪 Test buttons for each channel
- 🧩 REST API `/nh/v1/test-trigger/{id}`
- 💬 Pro Channels: Telegram Bot + Slack Webhook
- 🔑 License system (Free + Pro)

---

## 🧑‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 [hellocode.ir](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://linkedin.com/in/reza-rajabi-jorshari)

---

## 📄 License
GPL v3 or later
