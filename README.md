# Notification Hub

**Version:** 2.0.0  
**Requires PHP:** 7.4+  
**WordPress:** 5.9+  

Unified notification system for WordPress. Centralize all notifications from WordPress core, WooCommerce, Contact Form 7, and custom hooks into one dashboard.

---

## 🚀 Features

### Free Version
- ✅ Email notifications
- ✅ Dashboard with filters
- ✅ WooCommerce integration (orders, low stock)
- ✅ Contact Form 7 integration
- ✅ WordPress core events (comments, posts, users)
- ✅ Custom hooks system
- ✅ Priority-based notifications
- ✅ Auto-cleanup (retention policy)
- ✅ REST API

### Premium Version
- 🔥 Telegram notifications
- 🔥 Slack notifications
- 🔥 Advanced filters
- 🔥 Analytics dashboard
- 🔥 Priority support

---

## 📦 Installation

1. Upload to `/wp-content/plugins/notification-hub/`
2. Activate via WordPress admin
3. Navigate to **Notification Hub** menu
4. Configure channels and integrations

---

## 🔧 Configuration

### Email Setup
```php
// Set recipient email
update_option( 'nh_email_to', 'admin@example.com' );
```

### Custom Hooks
```php
// Trigger custom notification
do_action( 'nh_custom_event', array(
    'title'   => 'Custom Event',
    'message' => 'Something happened!',
    'source'  => 'custom',
    'type'    => 'alert',
) );
```

### Programmatic Usage
```php
$container = \Notification_Hub\Core\Container::instance();
$notifier  = $container->get_svc( 'notifier' );

$notifier->queue_send( 'email', array(
    'subject' => 'Test Notification',
    'body'    => 'This is a test message.',
) );
```

---

## 📚 Documentation

- [Developer Guide](docs/developer-guide.md)
- [API Reference](docs/api-reference.md)
- [Hooks & Filters](docs/hooks-filters.md)
- [Migration Guide](docs/migration-2.0.md)

---

## 🛠️ Development

### Requirements
- PHP 7.4+
- Composer (for dev dependencies)
- WordPress 5.9+

### Setup
```bash
git clone https://github.com/faryanra/Notification-Hub.git
cd Notification-Hub
composer install
```

### Standards
- PSR-4 autoloading
- WordPress Coding Standards
- PHPUnit tests

---

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md)

---

## 📄 License

GPLv2 or later

---

## 👤 Author

**FaryaN**  
GitHub: [@faryanra](https://github.com/faryanra)
