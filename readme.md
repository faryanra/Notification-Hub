# 🚨 Notification Hub v1.2.0

A flexible and extensible plugin to collect, manage, and route WordPress notifications through Email, Telegram (Pro), and Slack (Pro).  
Built for site admins who want instant, multi-channel alerts and a clear dashboard to manage them.

---

## ✨ Features

### Core Features (Free)
- 🗂 Central dashboard (view recent notifications)
- 📨 Email channel (configurable + test)
- ⚠️ Admin bar badge (real-time count)
- 🧩 Integrations:
  - WooCommerce: new orders, status changes
  - Contact Form 7: form submissions
  - WordPress Core: new comments

### Pro Features
- 💬 Telegram Bot notifications
- 🔔 Slack Webhook alerts
- 🔐 License activation system

---

## 🎛 Settings UI (v1.2.0)

- Tabbed layout (`General`, `Pro Channels`)
- Auto-persistent tabs after save or test
- Visual feedback with admin notices (✅ / ❌)
- Disabled inputs for Pro-only fields with message:
  > 🔒 This field is disabled because it’s only available in the Pro version.

---

## 🛠 Installation

1. Upload to `/wp-content/plugins/notification-hub/`.
2. Activate the plugin.
3. Visit **Notifications > Settings** to configure Email, Telegram, and Slack.
4. For Pro features:
   - Enter license key in the settings.
   - Use "Send Test" buttons to verify connections.

---

## 📁 Folder Structure

- `notification-hub.php`: Main entry point
- `includes/`: Core classes and logic
  - `class-nh-loader.php`
  - `class-nh-collector.php`
  - `class-nh-notifier.php`
  - `class-nh-test-controller.php`
- `templates/setting.php`: Settings UI
- `assets/js/admin.js`: Tab UI + Test actions
- `pro/`: License and Pro logic

---

## 📅 Changelog

### v1.2.0
- Persistent tab fix after Save or Test
- Admin notices after test/send
- Telegram & Slack fields disabled for Free
- Improved JS and CSS separation

### v1.1.0
- WooCommerce + CF7 support
- Email and Slack support
- Logging + Debugging added

### v1.0.0
- MVP release

---

## 👨‍💻 Author

**Faryan Rajabi Jorshari**  
GitHub: [faryanra](https://github.com/faryanra)  
LinkedIn: [@faryan-rajabi](https://linkedin.com/in/faryan-rajabi)

---

## 📜 License
GPL v2 or later — Free to use, extend, and redistribute.
