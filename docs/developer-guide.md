# Developer Guide

## Architecture Overview

Notification Hub 2.0 uses a modern, layered architecture:

```
src/
├── core/           # Core plugin files (bootstrap, container, loader)
├── conditionals/   # Feature detection
├── helpers/        # Utility functions
├── repositories/   # Data access layer
├── services/       # Business logic
├── integrations/   # Event listeners & channels
├── routes/         # Admin AJAX handlers
├── presenters/     # UI rendering
├── builders/       # Object construction
├── factories/      # Instance creation
├── utilities/      # General utilities
└── templates/      # View templates
```

## Container (DI)

```php
$container = \Notification_Hub\Core\Container::instance();

// Register a service
$container->register( 'my_service', function( $c ) {
    return new My_Service( $c );
}, true ); // true = singleton

// Retrieve a service
$service = $container->get_svc( 'my_service' );
```

## Creating Custom Integrations

### 1. Create Integration Class

```php
namespace My_Plugin\Integrations;

use Notification_Hub\Integrations\Integration_Interface;

class My_Integration implements Integration_Interface {
    
    private $container;
    
    public function __construct( $container ) {
        $this->container = $container;
    }
    
    public function register(): void {
        add_action( 'my_custom_action', array( $this, 'handle' ) );
    }
    
    public function handle() {
        $notifier = $this->container->get_svc( 'notifier' );
        
        $notifier->queue_send( 'email', array(
            'subject' => 'My Event',
            'body'    => 'Something happened!',
            'source'  => 'my_plugin',
            'type'    => 'alert',
        ) );
    }
}
```

### 2. Register in Bootstrap

```php
add_action( 'plugins_loaded', function() {
    $container = \Notification_Hub\Core\Container::instance();
    $integration = new \My_Plugin\Integrations\My_Integration( $container );
    $integration->register();
}, 20 );
```

## Using Helpers

```php
use Notification_Hub\Helpers\Date;
use Notification_Hub\Helpers\Sanitization;
use Notification_Hub\Helpers\Formatters\String_Formatter;

$human_time = Date::human_time_diff( $timestamp );
$clean_text = Sanitization::sanitize_text( $input );
$excerpt = String_Formatter::excerpt( $long_text, 30 );
```

## Repository Pattern

```php
$repo = new \Notification_Hub\Repositories\Notifications();

// Insert
$id = $repo->insert( array(
    'source'  => 'custom',
    'type'    => 'alert',
    'title'   => 'Test',
    'message' => 'Message',
) );

// Get list
$items = $repo->get_list(
    array( 'status' => 0 ), // filters
    1,                       // page
    20                       // per_page
);

// Update
$repo->update( $id, array( 'status' => 1 ) );

// Delete
$repo->delete( $id );
```

## Factory Pattern

```php
use Notification_Hub\Factories\Notification_Factory;

$notification = Notification_Factory::from_event( 'order_created', array(
    'source'  => 'woocommerce',
    'title'   => 'New Order',
    'message' => 'Order #123 received',
    'context' => array( 'order_id' => 123 ),
) );
```

## Testing

Unit tests coming soon.
