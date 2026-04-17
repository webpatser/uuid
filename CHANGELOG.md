# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2026-03-20

### Breaking Changes
- PHP requirement raised to `^8.5`
- Removed `isSqlServerFormat()` (stub that never worked, was identical to `validate()`)
- Switched test suite from PHPUnit to Pest 4

### Added
- `#[\NoDiscard]` attribute on all factory and query methods
- PHP 8.5 `array_first()` native function usage
- Expanded test coverage: error handling, property assertions, V6 import round-trip, case-insensitive compare, V3/V5 benchmarks (62 tests, 1309 assertions)
- `examples/benchmark.php` for reproducible performance measurements
- Safety throw in `mintName()` default branch to prevent null dereference

### Removed
- `isSqlServerFormat()` method and its test
- Redundant `(int)` cast on already-typed `$ver` parameter
- `method_exists` compatibility guards
- Deprecated `setAccessible()` call in tests

### Fixed
- Misleading comment in `mintTime()` claiming hrtime usage (uses `microtime(true)`)
- `mintName()` could silently produce null on unexpected hash algorithm
- `compare()` simplified from verbose if/else to single return
- Example files used wrong namespace and non-existent methods
- README: corrected PHP version requirement, removed unsubstantiated performance claims

### Changed
- `.gitignore` comment updated from "PHPUnit" to "Testing"

## [1.3.0] - 2025-09-11

### Added
- **SQL Server GUID Support**: New methods for handling SQL Server's mixed-endianness GUID format
  - `importFromSqlServer()` - Import UUID from SQL Server with automatic byte order correction
  - `toSqlServer()` - Export UUID to SQL Server GUID format with proper endianness
  - `toSqlServerBinary()` - Get binary representation for SQL Server uniqueidentifier storage
  - `isSqlServerFormat()` - Heuristic detection of SQL Server GUID format
- Comprehensive test suite for SQL Server GUID conversion (8 tests, 17 assertions)
- Documentation for SQL Server GUID endianness handling with examples

### Fixed
- SQL Server GUID byte order problems when importing from uniqueidentifier columns
- Mixed endianness conversion for proper cross-database UUID compatibility

### Changed
- Improved code formatting and documentation standards
- Enhanced PHPDoc annotations for better IDE support

## [1.2.2] - 2025-09-10

### Fixed
- Minor performance optimizations
- Documentation improvements

## [1.2.1] - 2025-09-10

### Added
- Initial release with RFC 4122 and RFC 9562 compliance
- Support for UUID versions 1, 3, 4, 5, 6, 7, and 8
- High-performance generation (15% faster than Ramsey UUID)
- Modern PHP 8.2+ optimizations with Random\Randomizer
- Monotonic V7 UUIDs for database optimization
- Performance benchmarking methods
- Nil UUID support and validation

### Features
- **Version 4**: Random UUIDs (recommended for general use)
- **Version 7**: Unix timestamp + random (database-optimized)
- **Version 1**: Time-based UUIDs with MAC address
- **Version 6**: Reordered time-based (better than V1 for databases)
- **Version 3/5**: Name-based UUIDs (MD5/SHA-1)
- **Version 8**: Custom/vendor-specific UUIDs

### Performance
- Version 4: ~700,000+ UUIDs/second
- Version 7: ~500,000+ UUIDs/second
- ~40% faster than ramsey/uuid on V4
- ~45% faster than ramsey/uuid on V7
- Cryptographically secure via `Random\Randomizer` (OS CSPRNG)
- No external dependencies, pure PHP

[2.0.0]: https://github.com/webpatser/uuid/compare/v1.3.0...v2.0.0
[1.3.0]: https://github.com/webpatser/uuid/compare/v1.2.2...v1.3.0
[1.2.2]: https://github.com/webpatser/uuid/compare/v1.2.1...v1.2.2
[1.2.1]: https://github.com/webpatser/uuid/releases/tag/v1.2.1