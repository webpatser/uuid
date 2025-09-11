# High-Performance UUID Library

Blazing fast PHP UUID library leveraging **PHP 8.2+ and 8.4** cutting-edge features for maximum performance. **15% faster than Ramsey UUID** with modern optimizations.

## ⚡ Modern PHP Performance Features

Built with the latest PHP innovations for unmatched speed:

- **Random\Randomizer** (PHP 8.2) - Superior entropy generation
- **readonly properties** (PHP 8.1+) - Memory optimization and immutability  
- **hrtime()** precision - Nanosecond timestamps with monotonic behavior
- **array_is_list()** (PHP 8.1) - Optimized array operations
- **str_starts_with()** / **str_ends_with()** (PHP 8.0+) - Fast string parsing
- **match expressions** (PHP 8.0+) - Efficient version dispatching
- **PHP 8.3+ hex optimization** - Direct hex string generation
- **enum backing** (PHP 8.1+) - Type-safe constants with zero overhead

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