<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Webpatser\Uuid\Uuid;

echo "=== Basic UUID Generation ===\n";

// Generate a v4 UUID (random)
$uuid4 = Uuid::v4();
echo "UUID v4: " . (string) $uuid4 . "\n";

// Generate a v7 UUID (time-ordered, recommended for databases)
$uuid7 = Uuid::v7();
echo "UUID v7: " . (string) $uuid7 . "\n";

// Generate other versions
$uuid1 = Uuid::generate(1);
echo "UUID v1: " . (string) $uuid1 . "\n";

$uuid3 = Uuid::generate(3, 'example.com', Uuid::NS_DNS);
echo "UUID v3: " . (string) $uuid3 . "\n";

$uuid5 = Uuid::generate(5, 'example.com', Uuid::NS_DNS);
echo "UUID v5: " . (string) $uuid5 . "\n";

$uuid6 = Uuid::generate(6);
echo "UUID v6: " . (string) $uuid6 . "\n";

$uuid8 = Uuid::generate(8);
echo "UUID v8: " . (string) $uuid8 . "\n";

echo "\n=== Import & Validate ===\n";

// Import an existing UUID string
$imported = Uuid::import('6ba7b810-9dad-11d1-80b4-00c04fd430c8');
echo "Imported: " . (string) $imported . "\n";
echo "Version:  " . $imported->version . "\n";
echo "Variant:  " . $imported->variant . "\n";

// Validate UUID strings
$valid = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
echo "\nIs valid UUID: " . (Uuid::validate($valid) ? 'yes' : 'no') . "\n";

$invalid = 'not-a-uuid';
echo "Is valid UUID: " . (Uuid::validate($invalid) ? 'yes' : 'no') . "\n";

echo "\n=== UUID Properties ===\n";

$uuid = Uuid::generate(1);
echo "String: " . (string) $uuid . "\n";
echo "Hex:    " . $uuid->hex . "\n";
echo "URN:    " . $uuid->urn . "\n";
echo "Node:   " . $uuid->node . "\n";

echo "\n=== Nil UUID ===\n";

$nil = Uuid::nil();
echo "Nil UUID: " . (string) $nil . "\n";
echo "Is nil:   " . ($nil->isNil() ? 'yes' : 'no') . "\n";
