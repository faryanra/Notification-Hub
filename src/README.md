# Notification Hub v2.0.0 - Yoast-Style Architecture

**Complete refactor** based on **SOLID principles**, **Dependency Injection**, and **Yoast SEO architecture**.

---

## 📁 Project Structure

```
notification-hub/
├── notification-hub.php                     # Main bootstrap
├── uninstall.php                            # Cleanup
├── readme.txt                               # WordPress.org
│
├── src/                                     # PSR-4: Notification_Hub\
│   ├── main.php                             # DI Container
│   ├── loader.php                           # Hook Manager
│   ├── autoloader.php                       # PSR-4 Autoloader
│   ├── bootstrap.php                        # Bootstrap
│   │
│   ├── conditionals/                        # Conditional loading
│   │   ├── conditional.php
│   │   ├── admin.php
│   │   ├── ajax.php
│   │   ├── cron.php
│   │   ├── woocommerce-active.php
│   │   ├── contact-form-7-active.php
│   │   └── premium-active.php
│   │
│   ├── integrations/                        # Feature modules
│   │   ├── integration-interface.php
│   │   ├── admin/
│   │   │   ├── menu-registration.php
│   │   │   ├── settings-registration.php
│   │   │   ├── admin-assets.php
│   │   │   └── admin-bar-badge.php
│   │   ├── events/
│   │   │   └── wordpress/
│   │   │       ├── comment-posted.php
│   │   │       ├── post-status-changed.php
│   │   │       ├── user-registered.php
│   │   │       └── custom-hooks-loader.php
│   │   ├── channels/
│   │   │   └── email-sender.php
│   │   ├── api/
│   │   │   └── rest-routes-registration.php
│   │   └── cron/
│   │       ├── cleanup-old-notifications.php
│   │       └── process-queue.php
│   │
│   ├── routes/                              # Endpoint handlers
│   │   ├── admin/
│   │   │   ├── create-custom-hook.php
│   │   │   ├── update-custom-hook.php
│   │   │   ├── delete-custom-hook.php
│   │   │   └── test-custom-hook.php
│   │   └── api/
│   │       ├── get-notifications.php
│   │       └── webhook.php
│   │
│   ├── repositories/                        # Database CRUD
│   │   ├── notifications.php
│   │   └── custom-hooks.php
│   │
│   ├── presenters/                          # View layer
│   │   └── admin/
│   │       ├── dashboard-page.php
│   │       ├── hooks-page.php
│   │       └── settings-page.php
│   │
│   ├── services/                            # Business logic
│   │   └── notification-dispatcher.php
│   │
│   ├── helpers/                             # Utilities
│   │   ├── options.php
│   │   ├── date.php
│   │   ├── human-time.php
│   │   └── security.php
│   │
│   ├── initializers/                        # One-time setup
│   │   └── database-migration.php
│   │
│   └── premium/                             # Premium features
│       ├── integrations/
│       │   └── channels/
│       │       ├── telegram-sender.php
│       │       └── slack-sender.php
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

## 🎯 Key Architecture Principles

### 1. **Dependency Injection**
- All dependencies injected via constructor
- No `new Class()` inside classes
- Managed by `Main` DI Container

### 2. **Single Responsibility**
- Each class has **one job**
- Example: `Menu_Registration` only registers admin menus

### 3. **Conditional Loading**
- Integrations load **only when conditions are met**
- Example: Admin integrations load only when `is_admin()` is true

### 4. **Interface Segregation**
- `Integration_Interface`: only `register()` method
- `Conditional`: only `is_met()` method

### 5. **Open/Closed Principle**
- Add new features **without modifying existing code**
- Just create a new Integration class

---

## 🚀 How It Works

### Step 1: Bootstrap (`notification-hub.php`)
```php
require_once plugin_dir_path( __FILE__ ) . 'src/bootstrap.php';
```

### Step 2: DI Container (`src/main.php`)
```php
$this->services['notifications_repo'] = function() {
    return new Notifications();
};
```

### Step 3: Hook Manager (`src/loader.php`)
```php
$this->integrations[] = array(
    'integration' => new Menu_Registration( ... ),
    'conditionals' => array( Admin::class ),
);
```

### Step 4: Integration Example
```php
class Comment_Posted implements Integration_Interface {
    public function __construct(
        Notifications $repo,
        Notification_Dispatcher $dispatcher
    ) {
        $this->repo       = $repo;
        $this->dispatcher = $dispatcher;
    }

    public function register() {
        add_action( 'wp_insert_comment', array( $this, 'handle' ), 10, 2 );
    }
}
```

---

## ➕ Adding New Features

### Example: Add Telegram Channel

**1. Create integration:**
```php
// src/integrations/channels/telegram-sender.php
namespace Notification_Hub\Integrations\Channels;

class Telegram_Sender implements Integration_Interface {
    public function register() {
        add_action( 'nh_send_telegram', array( $this, 'send' ) );
    }

    public function send( $payload ) {
        // Send logic
    }
}
```

**2. Register in Loader:**
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

- All classes are **unit testable**
- No global state
- All dependencies injected
- Easy to mock

---

## 📚 Inspired By

- [Yoast SEO Plugin](https://github.com/Yoast/wordpress-seo)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- SOLID Principles by Uncle Bob

---

## 🔄 Backward Compatibility

New architecture **coexists** with legacy code for seamless migration.

---

## 📝 License

GPLv3 or later
