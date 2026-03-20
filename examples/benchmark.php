<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Webpatser\Uuid\Uuid;

echo "=== UUID Benchmark ===\n";
echo "PHP " . PHP_VERSION . " on " . php_uname('s') . " " . php_uname('m') . "\n\n";

$iterations = (int) ($argv[1] ?? 10000);
echo "Running {$iterations} iterations per version...\n\n";

$versions = [1, 3, 4, 5, 6, 7, 8];

printf("%-10s %12s %12s %16s\n", 'Version', 'Total (ms)', 'Avg (µs)', 'UUIDs/sec');
echo str_repeat('-', 54) . "\n";

foreach ($versions as $version) {
    $result = Uuid::benchmark($iterations, $version);
    printf(
        "%-10s %12.2f %12.3f %16s\n",
        "V{$version}",
        $result['total_time_ms'],
        $result['avg_time_us'],
        number_format($result['uuids_per_second']),
    );
}

echo "\n";

// Compare with ramsey/uuid if available
if (class_exists(\Ramsey\Uuid\Uuid::class)) {
    echo "=== Comparison with ramsey/uuid ===\n\n";

    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        \Ramsey\Uuid\Uuid::uuid4();
    }
    $ramseyV4 = (hrtime(true) - $start) / 1_000_000;

    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        \Ramsey\Uuid\Uuid::uuid7();
    }
    $ramseyV7 = (hrtime(true) - $start) / 1_000_000;

    $webpatserV4 = Uuid::benchmark($iterations, 4)['total_time_ms'];
    $webpatserV7 = Uuid::benchmark($iterations, 7)['total_time_ms'];

    printf("%-20s %12s %12s\n", '', 'webpatser', 'ramsey');
    echo str_repeat('-', 46) . "\n";
    printf("%-20s %10.2f ms %10.2f ms\n", "V4 ({$iterations}x)", $webpatserV4, $ramseyV4);
    printf("%-20s %10.2f ms %10.2f ms\n", "V7 ({$iterations}x)", $webpatserV7, $ramseyV7);
} else {
    echo "Install ramsey/uuid to compare: composer require --dev ramsey/uuid\n";
}
