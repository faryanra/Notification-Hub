# Notification Hub v2.0.0 - Yoast-Style Architecture

This directory contains the **new architecture** (v2.0.0) built with **SOLID principles** and **Dependency Injection**, inspired by Yoast SEO.

---

## 📁 Directory Structure

```
notification-hub/
│
├── notification-hub.php                         # Bootstrap فقط (هیچ logic نداره)
├── uninstall.php                                # Cleanup on uninstall
├── readme.txt                                   # WordPress.org readme
├── readme.md                                    # GitHub readme
│
├── languages/                                   # Translation files
│   ├── notification-hub.pot
│   └── ...
│
├── src/                                         # PSR-4: Notification_Hub\\
│   │
│   ├── main.php                                 # 🎯 Container/DI (like Yoast's Main.php)
│   ├── loader.php                               # 🎯 Hook Manager (Yoast's Loader)
│   ├── autoloader.php                           # 🎯 PSR-4 Autoloader
│   ├── bootstrap.php                            # 🎯 Bootstrap
│   │
│   ├── conditionals/                            # 🎯 شرایط برای Integrations
│   │   ├── conditional.php                      # Interface
│   │   ├── admin.php                            # is_admin()
│   │   ├── ajax.php                             # wp_doing_ajax()
│   │   ├── cron.php                             # wp_doing_cron()
│   │   ├── woocommerce-active.php               # class_exists('WooCommerce')
│   │   ├── contact-form-7-active.php            # class_exists('WPCF7')
│   │   └── premium-active.php                   # defined('NH_PRO_ACTIVE')
│   │
│   ├── integrations/                            # 🎯 هر Integration = یک ویژگی
│   │   ├── integration-interface.php            # Interface اصلی
│   │   │
│   │   ├── admin/                               # 🔹 Admin UI Features
│   │   │   ├── menu-registration.php            # فقط add_menu_page
│   │   │   ├── settings-registration.php        # فقط register_setting
│   │   │   ├── admin-assets.php                 # فقط wp_enqueue_*
│   │   │   └── admin-bar-badge.php              # فقط admin bar badge
│   │   │
│   │   ├── events/                              # 🔹 Event Listeners (به تفکیک کامل)
│   │   │   │
│   │   │   ├── wordpress/                       # WordPress Core Events
│   │   │   │   ├── comment-posted.php           # wp_insert_comment
│   │   │   │   ├── post-status-changed.php      # transition_post_status
│   │   │   │   ├── user-registered.php          # user_register
│   │   │   │   └── custom-hooks-loader.php      # Custom hooks از DB
│   │   │   │
│   │   │   ├── woocommerce/                     # WooCommerce Events
│   │   │   │   ├── order-created.php            # woocommerce_new_order
│   │   │   │   └── low-stock-alert.php          # woocommerce_low_stock
│   │   │   │
│   │   │   └── contact-form-7/                  # CF7 Events
│   │   │       └── form-submitted.php           # wpcf7_mail_sent
│   │   │
│   │   ├── channels/                            # 🔹 Notification Senders
│   │   │   └── email-sender.php                 # ارسال Email (Free)
│   │   │
│   │   ├── api/                                 # 🔹 REST API
│   │   │   └── rest-routes-registration.php     # ثبت REST routes
│   │   │
│   │   └── cron/                                # 🔹 Background Tasks
│   │       ├── cleanup-old-notifications.php    # Daily cleanup
│   │       └── process-queue.php                # صف ارسال
│   │
│   ├── routes/                                  # 🎯 Endpoint Handlers
│   │   │
│   │   ├── admin/                               # Admin-post handlers
│   │   │   ├── create-custom-hook.php           # AJAX: add hook
│   │   │   ├── update-custom-hook.php           # AJAX: edit hook
│   │   │   ├── delete-custom-hook.php           # AJAX: delete hook
│   │   │   └── test-custom-hook.php             # AJAX: test hook
│   │   │
│   │   └── api/                                 # REST API handlers
│   │       ├── get-notifications.php            # GET /notifications
│   │       └── webhook.php                      # POST /webhook
│   │
│   ├── repositories/                            # 🎯 Database Layer (CRUD only)
│   │   ├── notifications.php                    # Notifications CRUD
│   │   └── custom-hooks.php                     # Custom Hooks CRUD
│   │
│   ├── presenters/                              # 🎯 View/Output Layer
│   │   └── admin/
│   │       ├── dashboard-page.php               # Dashboard page renderer
│   │       ├── settings-page.php                # Settings page renderer
│   │       └── hooks-page.php                   # Hooks page renderer
│   │
│   ├── services/                                # 🎯 Business Logic
│   │   └── notification-dispatcher.php          # توزیع notification به channels
│   │
│   ├── helpers/                                 # 🎯 Utilities
│   │   ├── options.php                          # get/set options با cache
│   │   ├── date.php                             # Date utilities
│   │   ├── human-time.php                       # Human-readable time
│   │   └── security.php                         # Nonce, sanitization
│   │
│   ├── initializers/                            # 🎯 One-time Setup
│   │   └── database-migration.php               # Schema + migrations
│   │
│   └── premium/                                 # 🎯 Premium-Only Code
│       ├── integrations/
│       │   └── channels/
│       │       ├── telegram-sender.php          # Telegram sender
│       │       └── slack-sender.php             # Slack sender
│       └── license/
│           └── bootstrap.php
│
├── assets/
│   ├── css/
│   │   └── admin/
│   │       ├── global.css
│   │       ├── dashboard.css
│   │       └── settings.css
│   └── js/
│       └── admin/
│           ├── global.js
│           ├── dashboard.js
│           └── settings.js
│
└── templates/
    ├── admin/
    │   ├── dashboard.php
    │   ├── hooks.php
    │   └── settings.php
    └── notifications/
        ├── email.php
        ├── telegram.php
        └── slack.php
```

---

## 🎯 Key Principles

### 1. **Single Responsibility Principle**
- Each class has **one job**.
- Example: `Menu_Registration` only registers menus.

### 2. **Dependency Injection**
- No `new Class()` inside classes.
- All dependencies injected via constructor.
- Managed by `Main` DI Container.

### 3. **Open/Closed Principle**
- Add new integrations **without modifying existing code**.
- Just create a new class implementing `Integration_Interface`.

### 4. **Interface Segregation**
- `Integration_Interface` has only one method: `register()`.
- `Conditional` interface has only one method: `is_met()`.

### 5. **Conditional Loading**
- Integrations load only when conditions are met.
- Example: Admin integrations load only when `is_admin()` is true.

---

## 🚀 How It Works

### 1. Bootstrap (`notification-hub.php`)
```php
require_once plugin_dir_path( __FILE__ ) . 'src/bootstrap.php';
```

### 2. DI Container (`src/main.php`)
```php
// Registers all services
$this->services['notifications_repo'] = function() {
    return new Notifications();
};
```

### 3. Hook Manager (`src/loader.php`)
```php
// Registers integrations with conditionals
$this->integrations[] = array(
    'integration' => new Menu_Registration( ... ),
    'conditionals' => array( Admin::class ),
);
```

### 4. Integration Example
```php
class Comment_Posted implements Integration_Interface {
    public function __construct(
        Notifications $repo,
        Notification_Dispatcher $dispatcher
    ) {
        // Dependencies injected
    }

    public function register() {
        add_action( 'wp_insert_comment', array( $this, 'on_comment' ), 10, 2 );
    }
}
```

---

## 📦 Adding New Integrations

### Example: Add Telegram Channel

1. **Create integration:**
```php
// src/integrations/channels/telegram-sender.php
namespace Notification_Hub\\Integrations\\Channels;

class Telegram_Sender implements Integration_Interface {
    public function register() {
        add_action( 'nh_send_telegram', array( $this, 'send' ) );
    }

    public function send( $payload ) {
        // Send logic
    }
}
```

2. **Register in Loader:**
```php
// src/loader.php
$this->integrations[] = array(
    'integration' => new Telegram_Sender(),
    'conditionals' => array( Premium_Active::class ),
);
```

Done! 🎉

---

## 🧪 Testing

All classes are **unit testable** because:
- No global state
- All dependencies injected
- Interfaces for easy mocking

---

## 📚 Inspired By

- [Yoast SEO Plugin](https://github.com/Yoast/wordpress-seo)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- SOLID Principles by Uncle Bob

---

## 🔄 Backward Compatibility

The new architecture **coexists** with legacy code (`core/`, `modules/`) for backward compatibility. No breaking changes.

---

## 📝 License

GPLv3 or later
