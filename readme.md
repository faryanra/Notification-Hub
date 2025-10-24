# 🚀 Notification Hub v1.3.3

WordPress notification hub for Email, Telegram (Pro), Slack (Pro), WooCommerce, CF7, and custom hooks —  
with a live dashboard, security layer, unified nonces, and multi-channel integrations.

---

## 🔒 What's new in v1.3.3

### Security Layer
- Added `NH_Security` class for unified `ensure_cap()`, `verify_nonce()`, and `sanitize_channels()`.
- All admin actions now go through centralized nonce + capability validation.
- Prevents "Invalid nonce" and "Sorry, you are not allowed" errors.

### Integrations Rebuild
- Contact Form 7: now sends to Email, Slack, and Telegram (plus DB log).
- WooCommerce: events (new order, low stock) now fire correctly from constructor.
- WP Core: automatically registers comment/post/user hooks and DB-defined custom hooks.
- Loader improved with constructor/init detection for older integrations.

### Admin UX
- Updated forms (`settings.php`, `hooks.php`) with consistent nonce and tab handling.
- Redirects after Save/Test/Delete always restore the same tab + proper notices.
- Full compatibility with `class-nh-admin-actions.php` (v1.3.3).

### Code Improvements
- Input sanitization for `action_name` and channels.
- Unified security across all admin endpoints.
- Simplified notifier logic for multi-channel send (email + slack + telegram).

---

## 🧱 Core Features
- 📊 Dashboard (WP_List_Table): search, pagination, status filters, bulk actions
- 📨 Email notifications (with test)
- 🔗 WooCommerce: new order, low stock
- 📮 Contact Form 7: success/fail events
- 🏷 WP Core: comments, post status, new user, and custom hooks
- 🧱 Custom Hooks Manager: Add / Edit / Test / Delete your own triggers
- 🔔 Admin bar unread badge
- 🔐 Pro channels: Telegram Bot, Slack Webhook
- 🔑 License key validation (Pro only)

---

## 🧩 From previous versions
- v1.3.2: Dashboard counter + REST API hardening  
- v1.3.1: Safe bootstrap, uninstall cleanup, and late textdomain loading

---

## 🧑‍💻 Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 [hellocode.ir](https://www.hellocode.ir)  
🐙 [GitHub](https://github.com/faryanra)  
💼 [LinkedIn](https://linkedin.com/in/reza-rajabi-jorshari)

---

## 📄 License
GPL v3 or later
