# UUID Library for PHP

[![Latest Version](https://img.shields.io/packagist/v/webpatser/uuid.svg)](https://packagist.org/packages/webpatser/uuid)
[![Total Downloads](https://img.shields.io/packagist/dt/webpatser/uuid.svg)](https://packagist.org/packages/webpatser/uuid)
[![License](https://img.shields.io/packagist/l/webpatser/uuid.svg)](https://packagist.org/packages/webpatser/uuid)

A pure PHP library to generate and validate universally unique identifiers (UUIDs) according to RFC 4122 and RFC 9562 standards. Supports UUID versions 1, 3, 4, 5, 6, 7, and 8.

**Requirements:** PHP ^8.5 (no extensions required)

## Installation

```bash
composer require webpatser/uuid
```

## Quick Start

```php
use Webpatser\Uuid\Uuid;

// Generate UUIDs
$uuid4 = Uuid::v4();                                    // Random (recommended for general use)
$uuid7 = Uuid::v7();                                    // Time-ordered (recommended for databases)
$uuid1 = Uuid::generate(1);                             // Time-based with MAC address
$uuid3 = Uuid::generate(3, 'example.com', Uuid::NS_DNS); // Name-based (MD5)
$uuid5 = Uuid::generate(5, 'example.com', Uuid::NS_DNS); // Name-based (SHA-1)
$uuid6 = Uuid::generate(6);                             // Reordered time-based
$uuid8 = Uuid::generate(8);                             // Custom/vendor-specific

echo (string) $uuid4; // e.g., "550e8400-e29b-41d4-a716-446655440000"
```

## API Reference

### Generation

| Method | Description |
|--------|-------------|
| `Uuid::v4()` | Generate random UUID (version 4) |
| `Uuid::v7()` | Generate time-ordered UUID (version 7) |
| `Uuid::generate(int $ver, mixed $node = null, ?string $ns = null)` | Generate any version (1, 3, 4, 5, 6, 7, 8) |
| `Uuid::nil()` | Create nil UUID (all zeros) |

### Import & Validation

| Method | Description |
|--------|-------------|
| `Uuid::import(string $uuid)` | Import a UUID string |
| `Uuid::validate(mixed $uuid)` | Check if string is valid UUID format |
| `Uuid::compare(string $a, string $b)` | Compare two UUIDs (case-insensitive) |
| `Uuid::isNilUuid(mixed $uuid)` | Check if UUID is nil |

### Properties

```php
$uuid = Uuid::v7();

$uuid->string;       // "019d05e8-dfa1-7009-a8f7-4e1c868ccfa4"
$uuid->hex;          // "019d05e8dfa17009a8f74e1c868ccfa4"
$uuid->bytes;        // 16-byte binary string
$uuid->urn;          // "urn:uuid:019d05e8-dfa1-7009-a8f7-4e1c868ccfa4"
$uuid->version;      // 7
$uuid->variant;      // 1 (RFC 4122)
$uuid->time;         // 1773920640.936 (Unix timestamp, V1/V6/V7 only)
$uuid->node;         // MAC address hex (V1/V6 only, null for others)
```

### SQL Server GUID Support

SQL Server stores GUIDs with mixed endianness. These methods handle the byte-order conversion:

```php
// Import from SQL Server
$uuid = Uuid::importFromSqlServer('825B076B-44EC-E511-80DC-00155D0ABC54');

// Export to SQL Server format
$sqlGuid = $uuid->toSqlServer();          // String format
$sqlBin  = $uuid->toSqlServerBinary();    // 16-byte binary
```

### Benchmarking

```php
$result = Uuid::benchmark(10000, 7);
// Returns: version, iterations, total_time_ms, avg_time_us, memory_used_bytes, uuids_per_second
```

Run the benchmark script to compare versions:

```bash
php examples/benchmark.php 10000
```

### Name-Based UUID Namespaces

```php
Uuid::NS_DNS;   // 6ba7b810-9dad-11d1-80b4-00c04fd430c8
Uuid::NS_URL;   // 6ba7b811-9dad-11d1-80b4-00c04fd430c8
Uuid::NS_OID;   // 6ba7b812-9dad-11d1-80b4-00c04fd430c8
Uuid::NS_X500;  // 6ba7b814-9dad-11d1-80b4-00c04fd430c8
```

## Features

- UUID versions 1, 3, 4, 5, 6, 7, and 8 (RFC 4122 + RFC 9562)
- SQL Server GUID byte-order conversion
- Monotonic V7 UUIDs with 12-bit sub-millisecond sequence
- `#[\NoDiscard]` attribute on all factory and query methods
- `array_first()` PHP 8.5 native function
- `Random\Randomizer` for cryptographic random bytes
- `readonly` properties for immutability
- `match` expressions for efficient dispatching
- ~40% faster than ramsey/uuid on V4, ~45% on V7
- Zero dependencies, pure PHP

## License

MIT License.
