<?php

declare(strict_types=1);

use Webpatser\Uuid\Uuid;

describe('SQL Server GUID handling', function () {
    test('imports from SQL Server with byte order correction', function () {
        $sqlServerGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';
        $expectedStandardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';

        $uuid = Uuid::importFromSqlServer($sqlServerGuid);

        expect(strtoupper($uuid->string))->toBe($expectedStandardUuid)
            ->and(Uuid::validate($uuid->string))->toBeTrue();
    });

    test('exports to SQL Server format', function () {
        $standardUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';
        $expectedSqlServerGuid = '825B076B-44EC-E511-80DC-00155D0ABC54';

        $uuid = Uuid::import($standardUuid);

        expect($uuid->toSqlServer())->toBe($expectedSqlServerGuid);
    });

    test('round-trip conversion preserves UUID', function () {
        $originalUuid = '6B075B82-EC44-11E5-80DC-00155D0ABC54';

        $uuid = Uuid::import($originalUuid);
        $backToStandard = Uuid::importFromSqlServer($uuid->toSqlServer());

        expect(strtoupper($backToStandard->string))->toBe($originalUuid);
    });

    test('SQL Server binary is 16 bytes and differs from standard', function () {
        $uuid = Uuid::import('6B075B82-EC44-11E5-80DC-00155D0ABC54');
        $sqlServerBinary = $uuid->toSqlServerBinary();

        expect(strlen($sqlServerBinary))->toBe(16)
            ->and($sqlServerBinary)->not->toBe($uuid->bytes);
    });

    test('endianness conversion works with different versions', function () {
        $testUuids = [
            Uuid::generate(1),
            Uuid::v4(),
            Uuid::v7(),
        ];

        foreach ($testUuids as $uuid) {
            $backToOriginal = Uuid::importFromSqlServer($uuid->toSqlServer());
            expect($backToOriginal->string)->toBe($uuid->string);
        }
    });

    test('invalid SQL Server GUID throws exception', function () {
        expect(fn () => Uuid::importFromSqlServer('invalid'))
            ->toThrow(Exception::class, 'Invalid SQL Server GUID format');
    });

    test('byte reversal pattern is correct', function () {
        $knownBytes = "\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10";
        $reflection = new ReflectionClass(Uuid::class);
        $constructor = $reflection->getConstructor();
        $instance = $reflection->newInstanceWithoutConstructor();
        $constructor->invoke($instance, $knownBytes);

        $sqlServerBinary = $instance->toSqlServerBinary();

        // First 4 bytes reversed
        expect(substr($sqlServerBinary, 0, 4))->toBe("\x04\x03\x02\x01")
            // Next 2 bytes reversed
            ->and(substr($sqlServerBinary, 4, 2))->toBe("\x06\x05")
            // Next 2 bytes reversed
            ->and(substr($sqlServerBinary, 6, 2))->toBe("\x08\x07")
            // Last 8 bytes unchanged
            ->and(substr($sqlServerBinary, 8, 8))->toBe("\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10");
    });
});
