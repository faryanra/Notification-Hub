# 🚨 Notification Hub v1.3.2

WordPress notification hub for Email, Telegram (Pro), Slack (Pro), WooCommerce, CF7, and custom hooks — with a live dashboard, bulk actions, REST API, and admin bar badge.

---

## 🔄 What's new in v1.3.2

### Dashboard polish
- "All" tab now truly shows ALL notifications (Active + Archived)
- Active / Archived filters are correct and consistent
- Action URLs (Archive / Unarchive / Delete) now all use `wp_nonce_url()` for consistency

### API hardening
- REST API now checks DB tables gracefully to avoid fatals on fresh installs
- Telegram/Slack tokens no longer get logged unless `WP_DEBUG` is true

### Accessibility
- The notification preview modal now includes `role="dialog"`, `aria-modal="true"`, and `aria-labelledby` for better a11y

---

## From v1.3.1
- Safe bootstrap (no white screen if a file is missing)
- `class-nh-test-controller.php` moved out of `/core` → now `/modules/class-nh-admin-actions.php`
- Fixed admin redirects and success notices after:
  - Send Test Email / Telegram / Slack
  - Save / Update / Archive custom hooks
- Added `uninstall.php` (cleans cron + options)
- Late `load_plugin_textdomain()` on `init`
- Central `NH_Loader` wires Registry, UI, Integrations, REST API/Webhook with graceful fallbacks

---

## Core Features
- 📊 Dashboard (WP_List_Table): search, pagination, status filters, bulk actions
- 📨 Email notifications (with test)
- 🔗 WooCommerce: new order, low stock
- 📮 Contact Form 7: success/fail events
- 🏷 WP Core: comments, post status, new user
- 🧱 Custom Hooks Manager: define your own `do_action` triggers and send them to multi-channels
- 🔔 Admin bar unread badge
- 🔐 Pro channels: Telegram Bot, Slack Webhook
- 🔑 License key gate for Pro features

---

## Author
**Faryan Rajabi Jorshari (HelloCode)**  
🌐 https://www.hellocode.ir  
🐙 https://github.com/faryanra  
💼 https://linkedin.com/in/reza-rajabi-jorshari

---

## License
GPL v3 or later
