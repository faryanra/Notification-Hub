# API Reference

## REST API

### Base URL
```
/wp-json/notification-hub/v1/
```

### Authentication
Requires `manage_options` capability.

---

## Endpoints

### GET /notifications

Get list of notifications.

**Parameters:**
- `page` (int, optional): Page number (default: 1)
- `per_page` (int, optional): Items per page (default: 20)

**Response:**
```json
[
  {
    "id": 1,
    "source": "woocommerce",
    "type": "order_created",
    "title": "New Order",
    "message": "Order #123",
    "status": 0,
    "priority": 80,
    "created_at": "2026-02-13 12:00:00"
  }
]
```

---

### GET /notifications/{id}

Get single notification.

**Response:**
```json
{
  "id": 1,
  "source": "woocommerce",
  "type": "order_created",
  "title": "New Order",
  "message": "Order #123",
  "status": 0,
  "priority": 80,
  "context": {"order_id": 123},
  "created_at": "2026-02-13 12:00:00"
}
```

---

### POST /notifications

Create a notification.

**Body:**
```json
{
  "source": "custom",
  "type": "alert",
  "title": "Test Alert",
  "message": "This is a test"
}
```

**Response:**
```json
{
  "id": 456,
  "message": "Notification created successfully"
}
```

---

## PHP API

### Notifier Service

```php
$container = \Notification_Hub\Core\Container::instance();
$notifier  = $container->get_svc( 'notifier' );

// Queue notification (recommended)
$notifier->queue_send( 'email', array(
    'subject' => 'Hello',
    'body'    => 'World',
) );

// Send immediately
$notifier->send_now( 'email', array(
    'subject' => 'Urgent',
    'body'    => 'Send now!',
) );
```

### Builders

```php
use Notification_Hub\Builders\Notification_Builder;
use Notification_Hub\Builders\Email_Builder;

$notification = ( new Notification_Builder() )
    ->source( 'custom' )
    ->type( 'alert' )
    ->title( 'Alert' )
    ->message( 'Something happened' )
    ->priority( 90 )
    ->build();

$email = ( new Email_Builder() )
    ->to( 'user@example.com' )
    ->subject( 'Subject' )
    ->body( '<p>HTML body</p>' )
    ->html( true )
    ->build();
```
