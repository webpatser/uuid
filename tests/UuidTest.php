<?php

use Webpatser\Uuid\Uuid;

describe('generation', function () {
    test('static generation returns Uuid instances', function () {
        expect(Uuid::generate(1))->toBeInstanceOf(Uuid::class);
        expect(Uuid::generate(3, 'example.com', Uuid::NS_DNS))->toBeInstanceOf(Uuid::class);
        expect(Uuid::generate(4))->toBeInstanceOf(Uuid::class);
        expect(Uuid::generate(5, 'example.com', Uuid::NS_DNS))->toBeInstanceOf(Uuid::class);
    });

    test('all supported versions can be generated', function () {
        $versions = [1, 3, 4, 5, 6, 7, 8];

        foreach ($versions as $version) {
            $uuid = match ($version) {
                3, 5 => Uuid::generate($version, 'test', Uuid::NS_DNS),
                default => Uuid::generate($version),
            };

            expect($uuid->version)->toBe($version, "Version $version generation failed")
                ->and($uuid->variant)->toBe(1, "Version $version has wrong variant")
                ->and(Uuid::validate($uuid))->toBeTrue("Version $version validation failed");
        }
    });

    test('generated UUIDs match regex', function () {
        expect((string) Uuid::generate(1))->toMatch('~' . Uuid::VALID_UUID_REGEX . '~')
            ->and((string) Uuid::generate(3, 'example.com', Uuid::NS_DNS))->toMatch('~' . Uuid::VALID_UUID_REGEX . '~')
            ->and((string) Uuid::generate(4))->toMatch('~' . Uuid::VALID_UUID_REGEX . '~')
            ->and((string) Uuid::generate(5, 'example.com', Uuid::NS_DNS))->toMatch('~' . Uuid::VALID_UUID_REGEX . '~');
    });
});

describe('validation', function () {
    test('validates generated UUIDs via string', function () {
        expect(Uuid::validate(Uuid::generate(1)->string))->toBeTrue()
            ->and(Uuid::validate(Uuid::generate(3, 'example.com', Uuid::NS_DNS)->string))->toBeTrue()
            ->and(Uuid::validate(Uuid::generate(4)->string))->toBeTrue()
            ->and(Uuid::validate(Uuid::generate(5, 'example.com', Uuid::NS_DNS)->string))->toBeTrue();
    });

    test('raw bytes do not validate as string format', function () {
        expect(Uuid::validate(Uuid::generate(1)->bytes))->toBeFalse()
            ->and(Uuid::validate(Uuid::generate(4)->bytes))->toBeFalse();
    });

    test('URN format does not validate as plain UUID string', function () {
        expect(Uuid::validate(Uuid::generate(1)->urn))->toBeFalse()
            ->and(Uuid::validate(Uuid::generate(4)->urn))->toBeFalse();
    });

    test('validates UUID objects directly', function () {
        expect(Uuid::validate(Uuid::generate(1)))->toBeTrue()
            ->and(Uuid::validate(Uuid::generate(3, 'example.com', Uuid::NS_DNS)))->toBeTrue()
            ->and(Uuid::validate(Uuid::generate(4)))->toBeTrue()
            ->and(Uuid::validate(Uuid::generate(5, 'example.com', Uuid::NS_DNS)))->toBeTrue();
    });

    test('rejects a UUID with a trailing newline', function () {
        $valid = (string) Uuid::generate(4);

        expect(Uuid::validate($valid))->toBeTrue()
            ->and(Uuid::validate($valid."\n"))->toBeFalse()
            ->and(Uuid::validate($valid."\r\n"))->toBeFalse()
            ->and(Uuid::validate("\n".$valid))->toBeFalse();
    });
});

describe('version and variant', function () {
    test('correct version is set', function () {
        expect(Uuid::generate(1)->version)->toBe(1)
            ->and(Uuid::generate(3, 'example.com', Uuid::NS_DNS)->version)->toBe(3)
            ->and(Uuid::generate(4)->version)->toBe(4)
            ->and(Uuid::generate(5, 'example.com', Uuid::NS_DNS)->version)->toBe(5);
    });

    test('correct variant is set', function () {
        expect(Uuid::generate(1)->variant)->toBe(1)
            ->and(Uuid::generate(3, 'example.com', Uuid::NS_DNS)->variant)->toBe(1)
            ->and(Uuid::generate(4)->variant)->toBe(1)
            ->and(Uuid::generate(5, 'example.com', Uuid::NS_DNS)->variant)->toBe(1);
    });
});

describe('import', function () {
    test('imports all-zero UUID', function () {
        $uuid = Uuid::import('00000000-0000-0000-0000-000000000000');

        expect($uuid)->toBeInstanceOf(Uuid::class)
            ->and((string) $uuid)->toBe('00000000-0000-0000-0000-000000000000');
    });

    test('imported UUID preserves version', function () {
        $uuidOne = Uuid::generate(1);
        $uuidThree = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
        $uuidFour = Uuid::generate(4);
        $uuidFive = Uuid::generate(5, 'example.com', Uuid::NS_DNS);

        expect(Uuid::import((string) $uuidOne)->version)->toBe($uuidOne->version)
            ->and(Uuid::import((string) $uuidThree)->version)->toBe($uuidThree->version)
            ->and(Uuid::import((string) $uuidFour)->version)->toBe($uuidFour->version)
            ->and(Uuid::import((string) $uuidFive)->version)->toBe($uuidFive->version);
    });

    test('imported UUID preserves time', function () {
        $uuidOne = Uuid::generate(1);
        expect(Uuid::import((string) $uuidOne)->time)->toBe($uuidOne->time);

        expect(Uuid::import((string) Uuid::generate(3, 'example.com', Uuid::NS_DNS))->time)->toBeEmpty()
            ->and(Uuid::import((string) Uuid::generate(4))->time)->toBeEmpty()
            ->and(Uuid::import((string) Uuid::generate(5, 'example.com', Uuid::NS_DNS))->time)->toBeEmpty();
    });
});

describe('node', function () {
    test('correct node for generated UUID', function () {
        $macAddress = '01:23:45:67:89:ab';

        $v1 = Uuid::generate(1, $macAddress);
        expect($v1->node)->toBe(strtolower(str_replace(':', '', $macAddress)));

        expect(Uuid::generate(3, $macAddress, Uuid::NS_DNS)->node)->toBeNull()
            ->and(Uuid::generate(4, $macAddress)->node)->toBeNull()
            ->and(Uuid::generate(5, $macAddress, Uuid::NS_DNS)->node)->toBeNull();
    });
});

describe('compare', function () {
    test('compares UUIDs correctly', function () {
        $uuid1 = (string) Uuid::generate(1);
        $uuid2 = (string) Uuid::generate(1);

        expect(Uuid::compare($uuid1, $uuid1))->toBeTrue()
            ->and(Uuid::compare($uuid1, $uuid2))->toBeFalse();
    });

    test('two unrelated invalid strings never compare as equal', function () {
        expect(Uuid::compare('not-a-uuid', 'also-not-a-uuid'))->toBeFalse()
            ->and(Uuid::compare('garbage', 'garbage'))->toBeFalse()
            ->and(Uuid::compare('', ''))->toBeFalse()
            ->and(Uuid::compare((string) Uuid::generate(4), 'not-a-uuid'))->toBeFalse();
    });
});

describe('nil UUID', function () {
    test('creates nil UUID', function () {
        $nil = Uuid::nil();

        expect($nil)->toBeInstanceOf(Uuid::class)
            ->and((string) $nil)->toBe('00000000-0000-0000-0000-000000000000')
            ->and((string) $nil)->toBe(Uuid::NIL);
    });

    test('nil UUID has correct properties', function () {
        $nil = Uuid::nil();

        expect($nil->version)->toBe(0)
            ->and($nil->variant)->toBe(0)
            ->and($nil->time)->toBeNull()
            ->and($nil->node)->toBeNull();
    });

    test('isNil method works', function () {
        expect(Uuid::nil()->isNil())->toBeTrue()
            ->and(Uuid::generate(4)->isNil())->toBeFalse()
            ->and(Uuid::import('00000000-0000-0000-0000-000000000000')->isNil())->toBeTrue();
    });

    test('isNilUuid static method works', function () {
        expect(Uuid::isNilUuid(Uuid::nil()))->toBeTrue()
            ->and(Uuid::isNilUuid(Uuid::generate(4)))->toBeFalse()
            ->and(Uuid::isNilUuid('00000000-0000-0000-0000-000000000000'))->toBeTrue()
            ->and(Uuid::isNilUuid(Uuid::NIL))->toBeTrue()
            ->and(Uuid::isNilUuid('123e4567-e89b-12d3-a456-426614174000'))->toBeFalse()
            ->and(Uuid::isNilUuid('00000000000000000000000000000000'))->toBeTrue();
    });

    test('nil UUID constant', function () {
        expect(Uuid::NIL)->toBe('00000000-0000-0000-0000-000000000000');
    });

    test('nil UUID validates', function () {
        expect(Uuid::validate(Uuid::nil()))->toBeTrue()
            ->and(Uuid::validate(Uuid::NIL))->toBeTrue()
            ->and(Uuid::validate('00000000-0000-0000-0000-000000000000'))->toBeTrue();
    });
});

describe('version 7', function () {
    test('generates valid V7 UUID', function () {
        $uuid = Uuid::generate(7);

        expect($uuid)->toBeInstanceOf(Uuid::class)
            ->and($uuid->version)->toBe(7)
            ->and($uuid->variant)->toBe(1);
    });

    test('validates V7 UUID', function () {
        $uuid = Uuid::generate(7);

        expect(Uuid::validate($uuid))->toBeTrue()
            ->and((string) $uuid)->toMatch('~' . Uuid::VALID_UUID_REGEX . '~');
    });

    test('V7 UUID has correct properties', function () {
        $beforeTime = microtime(true);
        $uuid = Uuid::generate(7);
        $afterTime = microtime(true);

        expect($uuid->version)->toBe(7)
            ->and($uuid->variant)->toBe(1)
            ->and($uuid->time)->toBeFloat()
            ->and($uuid->time)->toBeGreaterThanOrEqual($beforeTime - 1)
            ->and($uuid->time)->toBeLessThanOrEqual($afterTime + 1)
            ->and($uuid->node)->toBeNull();
    });

    test('V7 UUIDs are unique', function () {
        $uuids = [];
        for ($i = 0; $i < 1000; $i++) {
            $uuid = (string) Uuid::generate(7);
            expect($uuids)->not->toContain($uuid);
            $uuids[] = $uuid;
        }
    });

    test('V7 UUIDs are sortable', function () {
        $uuids = [];
        $timestamps = [];

        for ($i = 0; $i < 5; $i++) {
            if ($i > 0) {
                usleep(1000);
            }
            $uuid = Uuid::generate(7);
            $uuids[] = (string) $uuid;
            $timestamps[] = $uuid->time;
        }

        $sortedUuids = $uuids;
        sort($sortedUuids);
        $sortedTimestamps = $timestamps;
        sort($sortedTimestamps);

        expect($uuids)->toBe($sortedUuids)
            ->and($timestamps)->toBe($sortedTimestamps);
    });

    test('V7 timestamp is accurate', function () {
        $currentTime = microtime(true);
        $uuid = Uuid::generate(7);
        $timeDiff = abs($uuid->time - $currentTime);

        expect($timeDiff)->toBeLessThan(0.01);
    });

    test('V7 import preserves properties', function () {
        $original = Uuid::generate(7);
        $imported = Uuid::import((string) $original);

        expect($imported->version)->toBe($original->version)
            ->and($imported->variant)->toBe($original->variant)
            ->and($imported->time)->toBe($original->time)
            ->and((string) $imported)->toBe((string) $original);
    });
});

describe('version 6', function () {
    test('generates valid V6 UUID', function () {
        $uuid = Uuid::generate(6);

        expect($uuid)->toBeInstanceOf(Uuid::class)
            ->and($uuid->version)->toBe(6)
            ->and($uuid->variant)->toBe(1);
    });

    test('V6 with MAC address', function () {
        $uuid = Uuid::generate(6, '00:11:22:33:44:55');

        expect($uuid->version)->toBe(6)
            ->and($uuid->variant)->toBe(1)
            ->and($uuid->node)->toBe('001122334455')
            ->and($uuid->time)->not->toBeNull();
    });

    test('V6 UUID has correct properties', function () {
        $beforeTime = microtime(true);
        $uuid = Uuid::generate(6);
        $afterTime = microtime(true);

        expect($uuid->version)->toBe(6)
            ->and($uuid->variant)->toBe(1)
            ->and($uuid->time)->toBeFloat()
            ->and($uuid->time)->toBeGreaterThanOrEqual($beforeTime - 1)
            ->and($uuid->time)->toBeLessThanOrEqual($afterTime + 1)
            ->and($uuid->node)->not->toBeNull()
            ->and(strlen($uuid->node))->toBe(12);
    });

    test('V6 UUIDs are sortable', function () {
        $uuids = [];
        $timestamps = [];

        for ($i = 0; $i < 5; $i++) {
            if ($i > 0) {
                usleep(1000);
            }
            $uuid = Uuid::generate(6);
            $uuids[] = (string) $uuid;
            $timestamps[] = $uuid->time;
        }

        $sortedUuids = $uuids;
        sort($sortedUuids);
        $sortedTimestamps = $timestamps;
        sort($sortedTimestamps);

        expect($uuids)->toBe($sortedUuids)
            ->and($timestamps)->toBe($sortedTimestamps);
    });

    test('validates V6 UUID', function () {
        $uuid = Uuid::generate(6);

        expect(Uuid::validate($uuid))->toBeTrue()
            ->and((string) $uuid)->toMatch('~' . Uuid::VALID_UUID_REGEX . '~');
    });
});

describe('version 8', function () {
    test('generates valid V8 UUID', function () {
        $uuid = Uuid::generate(8);

        expect($uuid)->toBeInstanceOf(Uuid::class)
            ->and($uuid->version)->toBe(8)
            ->and($uuid->variant)->toBe(1);
    });

    test('V8 with custom data is deterministic', function () {
        $uuid1 = Uuid::generate(8, 'test-data-123');
        $uuid2 = Uuid::generate(8, 'test-data-123');

        expect($uuid1->version)->toBe(8)
            ->and($uuid2->version)->toBe(8)
            ->and((string) $uuid1)->toBe((string) $uuid2);
    });

    test('V8 with binary data sets version bits correctly', function () {
        $uuid = Uuid::generate(8, random_bytes(16));

        expect($uuid->version)->toBe(8)
            ->and($uuid->variant)->toBe(1)
            ->and(ord($uuid->bytes[6]) >> 4)->toBe(8);
    });

    test('V8 UUID has correct properties', function () {
        $uuid = Uuid::generate(8);

        expect($uuid->version)->toBe(8)
            ->and($uuid->variant)->toBe(1)
            ->and($uuid->time)->toBeNull()
            ->and($uuid->node)->toBeNull();
    });

    test('validates V8 UUID', function () {
        $uuid = Uuid::generate(8);

        expect(Uuid::validate($uuid))->toBeTrue()
            ->and((string) $uuid)->toMatch('~' . Uuid::VALID_UUID_REGEX . '~');
    });

    test('V8 UUIDs without custom data are unique', function () {
        $uuids = [];
        for ($i = 0; $i < 100; $i++) {
            $uuid = (string) Uuid::generate(8);
            expect($uuids)->not->toContain($uuid);
            $uuids[] = $uuid;
        }
    });

    test('V8 with different data types', function () {
        $arrayData = ['key' => 'value', 'number' => 123];
        expect((string) Uuid::generate(8, $arrayData))->toBe((string) Uuid::generate(8, $arrayData));

        $object = (object) ['prop' => 'value'];
        expect(Uuid::generate(8, $object)->version)->toBe(8);

        expect((string) Uuid::generate(8, 42))->toBe((string) Uuid::generate(8, 42));
    });
});

describe('benchmark', function () {
    test('returns expected metrics', function () {
        $result = Uuid::benchmark(1000, 7);

        expect($result)
            ->toHaveKeys(['version', 'iterations', 'total_time_ms', 'avg_time_us', 'memory_used_bytes', 'uuids_per_second'])
            ->and($result['version'])->toBe(7)
            ->and($result['iterations'])->toBe(1000)
            ->and($result['total_time_ms'])->toBeNumeric()
            ->and($result['avg_time_us'])->toBeNumeric()
            ->and($result['uuids_per_second'])->toBeGreaterThan(10000);
    });
});

describe('randomizer', function () {
    test('generates different UUIDs', function () {
        expect((string) Uuid::generate(4))->not->toBe((string) Uuid::generate(4));
    });

    test('getBytesFromString optimization works', function () {
        $uuid = Uuid::generate(8, 'hex-test');
        expect($uuid->version)->toBe(8);
    });
});

describe('error handling', function () {
    test('invalid version throws exception', function () {
        expect(fn () => Uuid::generate(99))
            ->toThrow(Exception::class, 'Selected version is invalid or unsupported.');
    });

    test('version 2 throws exception', function () {
        expect(fn () => Uuid::generate(2))
            ->toThrow(Exception::class, 'Version 2 is unsupported.');
    });

    test('invalid import throws exception', function () {
        expect(fn () => Uuid::import('not-a-uuid'))
            ->toThrow(\TypeError::class);
    });

    test('empty string constructor throws exception', function () {
        $reflection = new ReflectionClass(Uuid::class);
        $constructor = $reflection->getConstructor();
        $instance = $reflection->newInstanceWithoutConstructor();

        expect(fn () => $constructor->invoke($instance, ''))
            ->toThrow(Exception::class, 'Input must be a 128-bit integer.');
    });

    test('empty name for V3 throws exception', function () {
        expect(fn () => Uuid::generate(3, '', Uuid::NS_DNS))
            ->toThrow(Exception::class, 'A name-string is required for Version 3 or 5 UUIDs.');
    });

    test('empty name for V5 throws exception', function () {
        expect(fn () => Uuid::generate(5, '', Uuid::NS_DNS))
            ->toThrow(Exception::class, 'A name-string is required for Version 3 or 5 UUIDs.');
    });

    test('invalid namespace for V5 throws exception', function () {
        expect(fn () => Uuid::generate(5, 'test', 'bad'))
            ->toThrow(Exception::class, 'A binary namespace is required for Version 3 or 5 UUIDs.');
    });
});

describe('properties', function () {
    test('hex property returns 32-char hex string', function () {
        $uuid = Uuid::generate(4);

        expect($uuid->hex)->toMatch('/^[0-9a-f]{32}$/')
            ->and(strlen($uuid->hex))->toBe(32);
    });

    test('urn property starts with urn:uuid:', function () {
        $uuid = Uuid::generate(4);

        expect($uuid->urn)->toStartWith('urn:uuid:')
            ->and(strlen($uuid->urn))->toBe(45); // "urn:uuid:" (9) + UUID (36)
    });
});

describe('V6 import', function () {
    test('V6 import preserves version and time', function () {
        $original = Uuid::generate(6);
        $imported = Uuid::import((string) $original);

        expect($imported->version)->toBe(6)
            ->and($imported->variant)->toBe($original->variant)
            ->and($imported->time)->toBe($original->time)
            ->and((string) $imported)->toBe((string) $original);
    });
});

describe('compare edge cases', function () {
    test('compare is case-insensitive', function () {
        $uuid = (string) Uuid::generate(4);
        $upper = strtoupper($uuid);
        $lower = strtolower($uuid);

        expect(Uuid::compare($upper, $lower))->toBeTrue();
    });
});

describe('benchmark versions', function () {
    test('benchmark works for V3', function () {
        $result = Uuid::benchmark(100, 3);

        expect($result)
            ->toHaveKeys(['version', 'iterations', 'total_time_ms', 'avg_time_us', 'memory_used_bytes', 'uuids_per_second'])
            ->and($result['version'])->toBe(3)
            ->and($result['iterations'])->toBe(100);
    });

    test('benchmark works for V5', function () {
        $result = Uuid::benchmark(100, 5);

        expect($result)
            ->toHaveKeys(['version', 'iterations', 'total_time_ms', 'avg_time_us', 'memory_used_bytes', 'uuids_per_second'])
            ->and($result['version'])->toBe(5)
            ->and($result['iterations'])->toBe(100);
    });
});

describe('monotonic timestamps', function () {
    test('V7 UUIDs maintain ordering', function () {
        $uuids = [];
        $timestamps = [];

        for ($i = 0; $i < 10; $i++) {
            if ($i > 0) {
                usleep(100);
            }
            $uuid = Uuid::generate(7);
            $uuids[] = (string) $uuid;
            $timestamps[] = $uuid->time;
        }

        $sortedTimestamps = $timestamps;
        sort($sortedTimestamps, SORT_NUMERIC);
        $sortedUuids = $uuids;
        sort($sortedUuids);

        expect($timestamps)->toBe($sortedTimestamps)
            ->and($uuids)->toBe($sortedUuids);
    });
});
