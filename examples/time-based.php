<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Webpatser\Uuid\Uuid;

echo "=== Time-Based UUIDs ===\n";

// Generate multiple v7 UUIDs to show time ordering
echo "Generating 5 time-ordered v7 UUIDs:\n";

for ($i = 1; $i <= 5; $i++) {
    $uuid = Uuid::v7();
    echo "UUID #{$i}: " . (string) $uuid . "\n";

    // Small delay to show time progression
    usleep(1000); // 1ms
}

echo "\n=== UUID Comparison ===\n";

$uuid1 = Uuid::v7();
$uuid2 = Uuid::v7();

echo "UUID 1: " . (string) $uuid1 . "\n";
echo "UUID 2: " . (string) $uuid2 . "\n";
echo "Are equal: " . (Uuid::compare((string) $uuid1, (string) $uuid2) ? 'yes' : 'no') . "\n";
echo "UUID 1 == UUID 1: " . (Uuid::compare((string) $uuid1, (string) $uuid1) ? 'yes' : 'no') . "\n";

echo "\n=== Time Extraction ===\n";

$v1 = Uuid::generate(1);
echo "V1 UUID:     " . (string) $v1 . "\n";
echo "V1 Time:     " . $v1->time . "\n";

$v6 = Uuid::generate(6);
echo "V6 UUID:     " . (string) $v6 . "\n";
echo "V6 Time:     " . $v6->time . "\n";

$v7 = Uuid::v7();
echo "V7 UUID:     " . (string) $v7 . "\n";
echo "V7 Time:     " . $v7->time . "\n";
echo "V7 DateTime: " . date('Y-m-d H:i:s', (int) $v7->time) . "\n";
