<?php

declare(strict_types=1);

namespace Webpatser\Uuid\Tests;

use PHPUnit\Framework\TestCase;
use Webpatser\Uuid\Uuid;

/**
 * Tests for SQL Server GUID endianness handling
 */
class SqlServerTest extends TestCase
{
    /**
     * Test SQL Server GUID import with byte order correction
     */
    public function test_import_from_sql_server(): void
    {
        // The exact case from GitHub issue #11
        $sqlServerGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';
        $expectedStandardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';

        $uuid = Uuid::importFromSqlServer($sqlServerGuid);

        $this->assertEquals($expectedStandardUuid, strtoupper($uuid->string));
        $this->assertTrue(Uuid::validate($uuid->string));
    }

    /**
     * Test SQL Server GUID export with byte order conversion
     */
    public function test_to_sql_server(): void
    {
        $standardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';
        $expectedSqlServerGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';

        $uuid = Uuid::import($standardUuid);
        $sqlServerFormat = $uuid->toSqlServer();

        $this->assertEquals($expectedSqlServerGuid, $sqlServerFormat);
    }

    /**
     * Test round-trip conversion
     */
    public function test_round_trip_conversion(): void
    {
        $originalUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';

        $uuid = Uuid::import($originalUuid);
        $sqlServerFormat = $uuid->toSqlServer();
        $backToStandard = Uuid::importFromSqlServer($sqlServerFormat);

        $this->assertEquals($originalUuid, strtoupper($backToStandard->string));
    }

    /**
     * Test SQL Server binary format
     */
    public function test_sql_server_binary(): void
    {
        $standardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';
        $uuid = Uuid::import($standardUuid);

        $sqlServerBinary = $uuid->toSqlServerBinary();
        $this->assertEquals(16, strlen($sqlServerBinary));

        // The binary should be different from standard binary due to endianness
        $this->assertNotEquals($uuid->bytes, $sqlServerBinary);
    }

    /**
     * Test endianness conversion with different UUID versions
     */
    public function test_endianess_with_different_versions(): void
    {
        $testUuids = [
            Uuid::generate(1),  // V1 time-based
            Uuid::v4(),         // V4 random
            Uuid::v7(),         // V7 time-ordered
        ];

        foreach ($testUuids as $uuid) {
            $sqlServerFormat = $uuid->toSqlServer();
            $backToOriginal = Uuid::importFromSqlServer($sqlServerFormat);

            $this->assertEquals($uuid->string, $backToOriginal->string);
        }
    }

    /**
     * Test validation of SQL Server format detection
     */
    public function test_sql_server_format_detection(): void
    {
        // Valid GUID format should pass validation
        $validGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';
        $this->assertTrue(Uuid::isSqlServerFormat($validGuid));

        // Invalid format should fail
        $invalidGuid = 'not-a-guid';
        $this->assertFalse(Uuid::isSqlServerFormat($invalidGuid));
    }

    /**
     * Test error handling for invalid input
     */
    public function test_invalid_sql_server_guid(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid SQL Server GUID format');

        Uuid::importFromSqlServer('invalid');
    }

    /**
     * Test the exact byte reversal pattern
     */
    public function test_byte_reversal_pattern(): void
    {
        // Create a UUID with known byte pattern for testing
        $knownBytes = "\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10";
        $uuid = new \ReflectionClass(Uuid::class);
        $constructor = $uuid->getConstructor();
        $constructor->setAccessible(true);
        $instance = $uuid->newInstanceWithoutConstructor();
        $constructor->invoke($instance, $knownBytes);

        $sqlServerBinary = $instance->toSqlServerBinary();

        // First 4 bytes should be reversed: 0x01020304 -> 0x04030201
        $this->assertEquals("\x04\x03\x02\x01", substr($sqlServerBinary, 0, 4));

        // Next 2 bytes should be reversed: 0x0506 -> 0x0605
        $this->assertEquals("\x06\x05", substr($sqlServerBinary, 4, 2));

        // Next 2 bytes should be reversed: 0x0708 -> 0x0807
        $this->assertEquals("\x08\x07", substr($sqlServerBinary, 6, 2));

        // Last 8 bytes should remain unchanged
        $this->assertEquals("\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10", substr($sqlServerBinary, 8, 8));
    }
}
