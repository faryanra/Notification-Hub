# Notification Hub v2.0.0 - Yoast-Style Architecture

This directory contains the **new architecture** (v2.0.0) built with **SOLID principles** and **Dependency Injection**, inspired by Yoast SEO.

---

## 📁 Directory Structure

```
src/
├── autoloader.php              # PSR-4 autoloader
├── bootstrap.php               # Bootstrap for new architecture
├── main.php                    # DI Container
├── loader.php                  # Hook Manager (Integration Manager)
│
├── conditionals/               # Conditional loading logic
│   ├── conditional.php         # Interface
│   ├── admin.php               # is_admin()
│   ├── ajax.php                # wp_doing_ajax()
│   ├── woocommerce-active.php  # WooCommerce check
│   ├── contact-form-7-active.php
│   └── premium-active.php      # Premium addon check
│
├── helpers/                    # Utility helpers
│   ├── security.php            # Nonce, caps, sanitization
│   ├── date.php                # Date utilities
│   ├── human-time.php          # Human-readable time
│   └── options.php             # Options API wrapper
│
├── integrations/               # All integrations
│   ├── integration-interface.php  # Interface for all integrations
│   │
│   ├── admin/                  # Admin-only integrations
│   │   ├── menu-registration.php
│   │   ├── settings-registration.php
│   │   ├── admin-assets.php
│   │   ├── admin-bar-badge.php
│   │   └── routes-registration.php
│   │
│   ├── events/                 # Event listeners
│   │   └── wordpress/
│   │       ├── comment-posted.php
│   │       ├── post-status-changed.php
│   │       ├── user-registered.php
│   │       └── custom-hooks-loader.php
│   │
│   └── channels/               # Notification channels
│       └── email-sender.php
│
├── presenters/                 # Page renderers (View layer)
│   └── admin/
│       ├── dashboard-page.php
│       ├── hooks-page.php
│       └── settings-page.php
│
├── repositories/               # Database CRUD
│   ├── notifications.php       # Notifications table CRUD
│   └── custom-hooks.php        # Custom hooks table CRUD
│
├── routes/                     # Route handlers (admin_post)
│   └── admin/
│       ├── create-custom-hook.php
│       ├── update-custom-hook.php
│       ├── delete-custom-hook.php
│       └── test-custom-hook.php
│
├── services/                   # Business logic services
│   └── notification-dispatcher.php
│
└── initializers/               # One-time setup tasks
    └── database-migration.php  # Schema setup
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

### 1. Bootstrap (`src/bootstrap.php`)
```php
// Load autoloader
require 'autoloader.php';

// Initialize DI Container
$container = new Main();

// Load integrations via Hook Manager
$loader = new Loader( $container );
$loader->load();
```

### 2. DI Container (`src/main.php`)
```php
// Registers all services
$this->set( 'notifications_repo', function() {
    return new Notifications();
});
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
