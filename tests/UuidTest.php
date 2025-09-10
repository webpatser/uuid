<?php

declare(strict_types=1);

namespace Webpatser\PureUuid\Tests;

use PHPUnit\Framework\TestCase;
use Webpatser\PureUuid\Uuid;
use Exception;

class UuidTest extends TestCase
{
    public function testStaticGeneration(): void
    {
        $uuid = Uuid::generate(1);
        $this->assertInstanceOf(Uuid::class, $uuid);

        $uuid = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertInstanceOf(Uuid::class, $uuid);

        $uuid = Uuid::generate(4);
        $this->assertInstanceOf(Uuid::class, $uuid);

        $uuid = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertInstanceOf(Uuid::class, $uuid);
    }

    public function testShorthandMethods(): void
    {
        $uuid4 = Uuid::v4();
        $this->assertInstanceOf(Uuid::class, $uuid4);
        $this->assertEquals(4, $uuid4->version);

        $uuid7 = Uuid::v7();
        $this->assertInstanceOf(Uuid::class, $uuid7);
        $this->assertEquals(7, $uuid7->version);
    }

    public function testGenerationOfValidUuid(): void
    {
        $uuid = Uuid::generate(1);
        $this->assertMatchesRegularExpression('~' . Uuid::VALID_UUID_REGEX . '~', (string) $uuid);

        $uuid = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $this->assertMatchesRegularExpression('~' . Uuid::VALID_UUID_REGEX . '~', (string) $uuid);

        $uuid = Uuid::generate(4);
        $this->assertMatchesRegularExpression('~' . Uuid::VALID_UUID_REGEX . '~', (string) $uuid);

        $uuid = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
        $this->assertMatchesRegularExpression('~' . Uuid::VALID_UUID_REGEX . '~', (string) $uuid);
    }

    public function testUuidValidation(): void
    {
        $uuid = Uuid::generate(4);
        $this->assertTrue(Uuid::validate($uuid->string));
        $this->assertTrue(Uuid::validate($uuid));
        $this->assertFalse(Uuid::validate('invalid-uuid'));
    }

    public function testCorrectVersionUuid(): void
    {
        $versions = [1, 3, 4, 5, 6, 7, 8];
        
        foreach ($versions as $version) {
            $uuid = match ($version) {
                3, 5 => Uuid::generate($version, 'test', Uuid::NS_DNS),
                default => Uuid::generate($version),
            };
            
            $this->assertEquals($version, $uuid->version);
            $this->assertEquals(1, $uuid->variant); // RFC variant
        }
    }

    public function testUuidProperties(): void
    {
        $uuid = Uuid::generate(4);
        
        $this->assertIsString($uuid->bytes);
        $this->assertEquals(16, strlen($uuid->bytes));
        
        $this->assertIsString($uuid->hex);
        $this->assertEquals(32, strlen($uuid->hex));
        
        $this->assertIsString($uuid->string);
        $this->assertEquals(36, strlen($uuid->string));
        
        $this->assertIsString($uuid->urn);
        $this->assertStringStartsWith('urn:uuid:', $uuid->urn);
        
        $this->assertIsInt($uuid->version);
        $this->assertIsInt($uuid->variant);
    }

    public function testNilUuid(): void
    {
        $nil = Uuid::nil();
        
        $this->assertInstanceOf(Uuid::class, $nil);
        $this->assertEquals('00000000-0000-0000-0000-000000000000', (string) $nil);
        $this->assertEquals(Uuid::NIL, (string) $nil);
        $this->assertTrue($nil->isNil());
        $this->assertEquals(0, $nil->version);
        $this->assertEquals(0, $nil->variant);
        $this->assertNull($nil->time);
        $this->assertNull($nil->node);
        
        // Test static method
        $this->assertTrue(Uuid::isNilUuid($nil));
        $this->assertTrue(Uuid::isNilUuid(Uuid::NIL));
        $this->assertFalse(Uuid::isNilUuid(Uuid::generate(4)));
    }

    public function testVersion7Generation(): void
    {
        $uuid = Uuid::generate(7);
        
        $this->assertEquals(7, $uuid->version);
        $this->assertEquals(1, $uuid->variant);
        $this->assertNotNull($uuid->time);
        $this->assertNull($uuid->node); // V7 doesn't have MAC address
        
        // Test timestamp accuracy
        $beforeTime = microtime(true);
        $uuid = Uuid::generate(7);
        $afterTime = microtime(true);
        
        $extractedTime = $uuid->time;
        $this->assertGreaterThanOrEqual($beforeTime - 1, $extractedTime);
        $this->assertLessThanOrEqual($afterTime + 1, $extractedTime);
    }

    public function testVersion7Sortability(): void
    {
        $uuids = [];
        $timestamps = [];
        
        // Generate UUIDs with small delays
        for ($i = 0; $i < 5; $i++) {
            if ($i > 0) {
                usleep(1000); // 1ms delay
            }
            $uuid = Uuid::generate(7);
            $uuids[] = (string) $uuid;
            $timestamps[] = $uuid->time;
        }
        
        // Sort UUIDs lexicographically
        $sortedUuids = $uuids;
        sort($sortedUuids);
        
        // Sort timestamps numerically
        $sortedTimestamps = $timestamps;
        sort($sortedTimestamps);
        
        // V7 should be sortable
        $this->assertEquals($uuids, $sortedUuids, 'V7 UUIDs should be naturally sortable by creation time');
        $this->assertEquals($timestamps, $sortedTimestamps, 'Timestamps should be in ascending order');
    }

    public function testVersion6Generation(): void
    {
        $uuid = Uuid::generate(6);
        
        $this->assertEquals(6, $uuid->version);
        $this->assertEquals(1, $uuid->variant);
        $this->assertNotNull($uuid->time);
        $this->assertNotNull($uuid->node); // V6 has MAC address
        $this->assertEquals(12, strlen($uuid->node)); // 12 hex chars = 6 bytes
    }

    public function testVersion8Generation(): void
    {
        $uuid = Uuid::generate(8);
        
        $this->assertEquals(8, $uuid->version);
        $this->assertEquals(1, $uuid->variant);
        $this->assertNull($uuid->time); // V8 doesn't have time
        $this->assertNull($uuid->node); // V8 doesn't have node
        
        // Test deterministic generation
        $data = 'test-data';
        $uuid1 = Uuid::generate(8, $data);
        $uuid2 = Uuid::generate(8, $data);
        
        $this->assertEquals((string) $uuid1, (string) $uuid2);
    }

    public function testUuidComparison(): void
    {
        $uuid1 = Uuid::generate(4);
        $uuid2 = Uuid::generate(4);
        
        $this->assertTrue(Uuid::compare((string) $uuid1, (string) $uuid1));
        $this->assertFalse(Uuid::compare((string) $uuid1, (string) $uuid2));
    }

    public function testUuidImport(): void
    {
        $original = Uuid::generate(4);
        $imported = Uuid::import((string) $original);
        
        $this->assertEquals($original->version, $imported->version);
        $this->assertEquals($original->variant, $imported->variant);
        $this->assertEquals((string) $original, (string) $imported);
    }

    public function testUuidUniqueness(): void
    {
        $uuids = [];
        
        for ($i = 0; $i < 1000; $i++) {
            $uuid = (string) Uuid::generate(4);
            $this->assertNotContains($uuid, $uuids, 'Generated duplicate UUID: ' . $uuid);
            $uuids[] = $uuid;
        }
    }

    public function testBenchmarkMethod(): void
    {
        $result = Uuid::benchmark(1000, 7);
        
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('iterations', $result);
        $this->assertArrayHasKey('total_time_ms', $result);
        $this->assertArrayHasKey('avg_time_us', $result);
        $this->assertArrayHasKey('memory_used_bytes', $result);
        $this->assertArrayHasKey('uuids_per_second', $result);
        
        $this->assertEquals(7, $result['version']);
        $this->assertEquals(1000, $result['iterations']);
        $this->assertIsNumeric($result['total_time_ms']);
        $this->assertIsNumeric($result['avg_time_us']);
        $this->assertGreaterThan(0, $result['uuids_per_second']);
    }

    public function testInvalidVersionThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Selected version is invalid or unsupported.');
        
        Uuid::generate(99);
    }

    public function testVersion2ThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Version 2 is unsupported.');
        
        Uuid::generate(2);
    }

    public function testInvalidConstructorInput(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Input must be a 128-bit integer.');
        
        new class('invalid') extends Uuid {
            public function __construct(string $uuid)
            {
                parent::__construct($uuid);
            }
        };
    }

    public function testNameBasedUuidRequirements(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('A name-string is required for Version 3 or 5 UUIDs.');
        
        Uuid::generate(3, '', Uuid::NS_DNS);
    }

    public function testRandomizerOptimizations(): void
    {
        // Test that we're using the optimized Randomizer class
        $uuid1 = Uuid::generate(4);
        $uuid2 = Uuid::generate(4);
        
        // Should be different (randomness test)
        $this->assertNotEquals((string) $uuid1, (string) $uuid2);
        
        // Test that randomBytes works
        $bytes = Uuid::randomBytes(16);
        $this->assertEquals(16, strlen($bytes));
    }
}