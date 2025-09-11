# UUID Library

High-performance PHP UUID library generating UUIDs according to RFC 4122 and RFC 9562 standards. **15% faster than Ramsey UUID**.

## Installation

```bash
composer require webpatser/uuid
```

**Requirements:** PHP 8.2+ (no extensions required)

## Quick Start

```php
use Webpatser\Uuid\Uuid;

// Generate UUIDs
$uuid4 = Uuid::v4();                    // Random UUID (recommended)
$uuid7 = Uuid::v7();                    // Unix timestamp + random (database-optimized)
$uuid1 = Uuid::generate(1);             // Time-based UUID
$uuid5 = Uuid::generate(5, 'hello', Uuid::NS_DNS); // Name-based SHA-1

echo $uuid4; // e.g., "123e4567-e89b-12d3-a456-426614174000"
```

## Documentation

For complete documentation, examples, and API reference, visit:

**https://documentation.downsized.nl/uuid**

## License

MIT License.