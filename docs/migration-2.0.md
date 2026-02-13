# Migration Guide: 1.x → 2.0

## Overview

Version 2.0 is a **major refactor** with breaking changes in file structure and class names. However, **backward compatibility layers** ensure most existing code continues to work.

---

## Breaking Changes

### 1. File Structure

**Old (1.x):**
```
modules/
├── class-nh-*.php
├── admin-actions/
├── notifier/
```

**New (2.0):**
```
src/
├── core/
├── integrations/
├── services/
├── repositories/
```

### 2. Class Names

**Old:**
```php
NH_Notifier
NH_Dashboard
NH_Database
```

**New:**
```php
Notification_Hub\Services\Notifier
Notification_Hub\Presenters\Dashboard_Presenter
Notification_Hub\Repositories\Notifications
```

---

## Compatibility

### Legacy Class Aliases

2.0 includes aliases for old class names:

```php
// Still works in 2.0
$notifier = new NH_Notifier();
$notifier->send( 'email', $payload );
```

### Hook Compatibility

All existing hooks preserved:

```php
// Still works
add_action( 'woocommerce_new_order', 'my_custom_handler' );
```

---

## Recommended Updates

### 1. Update Class References

**Before:**
```php
$notifier = new NH_Notifier();
```

**After:**
```php
$container = \Notification_Hub\Core\Container::instance();
$notifier  = $container->get_svc( 'notifier' );
```

### 2. Use Builders

**Before:**
```php
$payload = array(
    'source'  => 'custom',
    'type'    => 'alert',
    'title'   => 'Test',
    'message' => 'Message',
);
```

**After:**
```php
use Notification_Hub\Builders\Notification_Builder;

$payload = ( new Notification_Builder() )
    ->source( 'custom' )
    ->type( 'alert' )
    ->title( 'Test' )
    ->message( 'Message' )
    ->build();
```

### 3. Use Repositories

**Before:**
```php
global $wpdb;
$wpdb->get_results( "SELECT * FROM {$wpdb->prefix}nh_notifications" );
```

**After:**
```php
$repo  = new \Notification_Hub\Repositories\Notifications();
$items = $repo->get_list();
```

---

## Database

No schema changes. Data migrates automatically on activation.

---

## Testing Checklist

- [ ] Activate plugin
- [ ] Check admin pages load
- [ ] Test email notifications
- [ ] Verify WooCommerce integration
- [ ] Test CF7 integration
- [ ] Check custom hooks
- [ ] Test REST API endpoints
- [ ] Verify multisite (if applicable)

---

## Support

If you encounter issues, please open a GitHub issue with:
- WordPress version
- PHP version
- Active plugins list
- Error logs
