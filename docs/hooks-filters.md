# Hooks & Filters

## Actions

### `nh_notification_queued`

Fired when a notification is queued.

```php
add_action( 'nh_notification_queued', function( $channel, $payload ) {
    error_log( 'Notification queued for: ' . $channel );
}, 10, 2 );
```

### `nh_notification_sent`

Fired after a notification is sent.

```php
add_action( 'nh_notification_sent', function( $channel, $payload, $success ) {
    if ( $success ) {
        error_log( 'Notification sent successfully' );
    }
}, 10, 3 );
```

### `nh_before_send`

Fired before sending notification.

```php
add_action( 'nh_before_send', function( $channel, &$payload ) {
    // Modify payload
    $payload['custom_field'] = 'value';
}, 10, 2 );
```

---

## Filters

### `nh_notification_payload`

Filter notification payload before processing.

```php
add_filter( 'nh_notification_payload', function( $payload, $channel ) {
    if ( $channel === 'email' ) {
        $payload['subject'] = '[CUSTOM] ' . $payload['subject'];
    }
    return $payload;
}, 10, 2 );
```

### `nh_email_recipient`

Filter email recipient.

```php
add_filter( 'nh_email_recipient', function( $to, $payload ) {
    // Send to different email based on source
    if ( $payload['source'] === 'woocommerce' ) {
        return 'sales@example.com';
    }
    return $to;
}, 10, 2 );
```

### `nh_notification_priority`

Filter calculated priority.

```php
add_filter( 'nh_notification_priority', function( $priority, $source, $type ) {
    if ( $source === 'custom' ) {
        return 95; // High priority
    }
    return $priority;
}, 10, 3 );
```

### `nh_retention_days`

Filter retention period.

```php
add_filter( 'nh_retention_days', function( $days ) {
    return 180; // Keep for 6 months
} );
```

---

## Custom Events

### Trigger Custom Notification

```php
do_action( 'nh_custom_event', array(
    'title'    => 'Custom Event',
    'message'  => 'Something happened',
    'source'   => 'my_plugin',
    'type'     => 'info',
    'priority' => 70,
    'context'  => array(
        'user_id' => 123,
        'action'  => 'clicked',
    ),
) );
```
