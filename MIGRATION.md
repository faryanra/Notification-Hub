# Migration Guide: v1.x → v2.0.0

## Overview

Notification Hub v2.0.0 introduces a **complete architectural refactor** based on:
- ✅ **SOLID Principles**
- ✅ **Dependency Injection (DI)**
- ✅ **Conditional Loading**
- ✅ **Yoast SEO Architecture**

---

## What Changed?

### Old Structure (v1.x)
```
notification-hub/
├── core/
├── modules/
├── integrations/
└── api/
```

### New Structure (v2.0.0)
```
notification-hub/
├── src/                      # New architecture
│   ├── main.php              # DI Container
│   ├── loader.php            # Hook Manager
│   ├── conditionals/         # Conditional loading
│   ├── integrations/         # Feature modules
│   ├── repositories/         # Database CRUD
│   ├── routes/               # Endpoint handlers
│   ├── presenters/           # View layer
│   ├── services/             # Business logic
│   ├── helpers/              # Utilities
│   └── initializers/         # Setup tasks
│
├── core/                     # Legacy (v1.x) - Still active
├── modules/                  # Legacy (v1.x) - Still active
└── integrations/             # Legacy (v1.x) - Still active
```

---

## Key Benefits

### 1. **Testability**
- All classes are unit testable
- No global state
- Easy to mock dependencies

### 2. **Maintainability**
- Single Responsibility: each class does one thing
- Clear separation of concerns
- Easy to find and fix bugs

### 3. **Extensibility**
- Add new features without modifying existing code
- Just create a new Integration class
- Register in Loader

### 4. **Performance**
- Conditional loading: code loads only when needed
- Lazy initialization in DI Container
- Reduced memory footprint

---

## Backward Compatibility

✅ **100% backward compatible**

- Legacy code (v1.x) still runs
- New architecture runs alongside
- No breaking changes
- Gradual migration path

---

## How to Migrate Custom Code

### Example: Custom Event Hook (Old Way)

```php
// Old way (v1.x)
add_action('my_custom_event', function() {
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'nh_notifications',
        array(
            'title' => 'Custom Event',
            'message' => 'Something happened'
        )
    );
});
```

### New Way (v2.0.0)

**Step 1:** Create Integration class
```php
// src/integrations/events/my-plugin/my-custom-event.php
namespace Notification_Hub\Integrations\Events\My_Plugin;

use Notification_Hub\Integrations\Integration_Interface;
use Notification_Hub\Repositories\Notifications;
use Notification_Hub\Services\Notification_Dispatcher;

class My_Custom_Event implements Integration_Interface {
    private $repo;
    private $dispatcher;

    public function __construct( Notifications $repo, Notification_Dispatcher $dispatcher ) {
        $this->repo = $repo;
        $this->dispatcher = $dispatcher;
    }

    public function register() {
        add_action( 'my_custom_event', array( $this, 'handle' ) );
    }

    public function handle() {
        $id = $this->repo->create(
            array(
                'title' => 'Custom Event',
                'message' => 'Something happened',
                'type' => 'custom',
            )
        );

        if ( $id ) {
            do_action( 'nh_notification_created', $id, 'custom' );
        }
    }
}
```

**Step 2:** Register in Loader
```php
// src/loader.php
$this->integrations[] = array(
    'integration' => new My_Custom_Event(
        $this->container->get( 'notifications_repo' ),
        $this->container->get( 'notification_dispatcher' )
    ),
    'conditionals' => array(), // Always load
);
```

Done! 🎉

---

## New Features Enabled

✅ **Conditional Loading**
- Admin integrations load only in admin
- AJAX integrations load only during AJAX
- Premium features load only when premium is active

✅ **Dependency Injection**
- No `new Class()` inside classes
- All dependencies injected via constructor
- Easy to swap implementations

✅ **Repository Pattern**
- Database logic separated from business logic
- Easy to switch to different storage (e.g., custom tables, external API)

✅ **Service Layer**
- Business logic isolated in services
- Reusable across different integrations

---

## What's Next?

### Immediate (v2.1.0)
- [ ] REST API routes migration
- [ ] WooCommerce events integration
- [ ] Contact Form 7 events integration

### Future (v3.0.0)
- [ ] Remove legacy code
- [ ] GraphQL API
- [ ] React-based admin UI
- [ ] Unit tests for all classes

---

## Questions?

Refer to:
- `src/README.md` - Architecture documentation
- [Yoast SEO Plugin](https://github.com/Yoast/wordpress-seo) - Reference implementation
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/) - Best practices

---

**Version:** 2.0.0  
**Date:** February 13, 2026  
**Author:** Faryan Rajabi (HelloCode)
