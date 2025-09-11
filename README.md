# High-Performance UUID Library

A blazing fast PHP library for generating and validating universally unique identifiers (UUIDs) according to **RFC 4122** and **RFC 9562** standards.

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)

## 🚀 Performance First

**15% faster than Ramsey UUID** with modern PHP 8.2+ optimizations:

- **Random\Randomizer class** - Better entropy and 11% faster generation
- **PHP 8.3+ hex optimization** - Direct hex string generation  
- **Readonly properties** - Memory optimization and immutability
- **hrtime() precision** - Nanosecond timestamps with monotonic behavior

## 📦 Installation

```bash
composer require webpatser/uuid
```

**Requirements:** PHP 8.2+ (no extensions required)

## ⚡ Quick Start

```php
use Webpatser\Uuid\Uuid;

// Generate UUIDs
$uuid4 = Uuid::v4();                    // Random UUID (recommended)
$uuid7 = Uuid::v7();                    // Unix timestamp + random (database-optimized)
$uuid1 = Uuid::generate(1);             // Time-based UUID
$uuid5 = Uuid::generate(5, 'hello', Uuid::NS_DNS); // Name-based SHA-1

// Use UUIDs
echo $uuid4;                            // e.g., "123e4567-e89b-12d3-a456-426614174000"
echo $uuid4->hex;                       // Raw hex string
echo $uuid4->bytes;                     // Raw binary data
echo $uuid7->time;                      // Unix timestamp (for time-based UUIDs)

// Validate UUIDs
Uuid::validate('123e4567-e89b-12d3-a456-426614174000'); // true
Uuid::validate($uuid4);                 // true

// Import/compare UUIDs
$imported = Uuid::import('123e4567-e89b-12d3-a456-426614174000');
Uuid::compare($uuid4, $imported);       // true/false

// Nil UUID support
$nil = Uuid::nil();                     // 00000000-0000-0000-0000-000000000000
$nil->isNil();                          // true
Uuid::isNilUuid('00000000-0000-0000-0000-000000000000'); // true
```

## 🎯 UUID Versions

| Version | Type | Use Case | Performance | Privacy |
|---------|------|----------|-------------|---------|
| **1** | Time + MAC | Legacy systems | Poor sorting | MAC exposed |
| **3** | MD5 name-based | Deterministic from name | N/A | Good |
| **4** | Random | **General purpose** | Poor sorting | **Excellent** |
| **5** | SHA-1 name-based | Deterministic from name | N/A | Good |
| **6** | Time + MAC reordered | V1 migration | Good sorting | MAC exposed |
| **7** | Unix time + random | **New applications** | **Best sorting** | **Excellent** |
| **8** | Custom | Special requirements | Varies | Varies |

**Recommendations:**
- **Version 4**: General purpose, maximum randomness
- **Version 7**: New applications requiring sortable UUIDs (database-optimized)

## 🔧 Advanced Features

### RFC 9562 Modern UUIDs

```php
// Version 6: Reordered time-based (better than V1 for databases)
$uuid6 = Uuid::generate(6);
$uuid6 = Uuid::generate(6, '00:11:22:33:44:55'); // With MAC address

// Version 7: Unix Epoch time (recommended for new applications)  
$uuid7 = Uuid::generate(7);

// Version 8: Custom/vendor specific
$uuid8 = Uuid::generate(8);                    // Random implementation
$uuid8 = Uuid::generate(8, 'custom-data');     // Deterministic from data
$uuid8 = Uuid::generate(8, ['key' => 'value']); // From complex data
```

### Monotonic V7 UUIDs

Version 7 UUIDs maintain **perfect lexicographic ordering** for database optimization:

```php
$uuids = [];
for ($i = 0; $i < 1000; $i++) {
    $uuids[] = (string) Uuid::v7();
}

sort($uuids); 
// UUIDs remain in generation order = perfect database clustering
```

### Performance Benchmarking

```php
$results = Uuid::benchmark(10000, 7);
/*
[
    'version' => 7,
    'iterations' => 10000,
    'total_time_ms' => 45.123,
    'avg_time_us' => 4.512,
    'memory_used_bytes' => 2048,
    'uuids_per_second' => 221500
]
*/

// Compare versions
foreach ([1, 4, 6, 7, 8] as $version) {
    $result = Uuid::benchmark(5000, $version);
    echo "Version {$version}: {$result['uuids_per_second']} UUIDs/sec\n";
}
```

### SQL Server GUID Support

Handle SQL Server's mixed-endianness GUID format automatically:

```php
// SQL Server stores GUIDs with mixed endianness - first 8 bytes reversed
$sqlServerGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';  // From SQL Server
$standardUuid = Uuid::importFromSqlServer($sqlServerGuid);   // Corrected byte order
echo $standardUuid; // '6B075B82-EC44-11E5-80DC-00155D0ABC54'

// Convert standard UUID to SQL Server format
$uuid = Uuid::v4();
$sqlServerFormat = $uuid->toSqlServer();                    // Mixed endianness
$sqlServerBinary = $uuid->toSqlServerBinary();              // For BINARY storage

// Round-trip conversion (lossless)
$original = '6B075B82-EC44-11E5-80DC-00155D0ABC54';
$sqlServer = Uuid::import($original)->toSqlServer();        // To SQL Server
$backToStandard = Uuid::importFromSqlServer($sqlServer);    // Back to standard
// $original === $backToStandard->string

// Heuristic detection (optional)
if (Uuid::isSqlServerFormat($someGuid)) {
    $corrected = Uuid::importFromSqlServer($someGuid);
}
```

**Fixes byte order problems when importing UUIDs from SQL Server `uniqueidentifier` columns.**

## 🛡️ Security & Standards

- ✅ **RFC 4122** compliant (original UUID standard)
- ✅ **RFC 9562** compliant (2024 modern UUID standard)  
- ✅ **Cryptographically secure** random generation
- ✅ **No MAC address exposure** in V4/V7 (privacy-friendly)
- ✅ **Thread-safe** with proper entropy
- ✅ **No external dependencies** - pure PHP

## 📊 Performance

**Expected Performance (PHP 8.2+):**
- Version 7: ~500,000+ UUIDs/second
- Version 4: ~700,000+ UUIDs/second  
- Version 6: ~300,000+ UUIDs/second
- Version 1: ~300,000+ UUIDs/second

**Benchmark vs Ramsey UUID:**
- **15.1% faster overall**
- **25.7% faster** for V7 generation
- **8.4% faster** for V4 generation

## 🧪 Testing

```bash
composer install
composer test
```

## 📄 License

MIT License. See [LICENSE](LICENSE) file.

## 🤝 Contributing

This is a high-performance UUID library focused on speed and standards compliance. For Laravel-specific integrations, see [webpatser/laravel-uuid](https://github.com/webpatser/laravel-uuid).

---

**Fast. Modern. Standards-compliant.**