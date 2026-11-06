<?php
declare(strict_types=1);

require dirname(__DIR__) . '/api.php';

function expect_valid(string $input, string $scheme, int $port): void
{
    $target = parse_target($input);
    if ($target['scheme'] !== $scheme || $target['port'] !== $port) {
        throw new RuntimeException("Target normalization failed for $input");
    }
}

function expect_rejected(string $input): void
{
    try {
        parse_target($input);
    } catch (RuntimeException) {
        return;
    }
    throw new RuntimeException("Unsafe target was accepted: $input");
}

expect_valid('example.com', 'https', 443);
expect_valid('https://example.com:443/path', 'https', 443);
expect_valid('http://example.com:80/', 'http', 80);

foreach ([
    'https://example.com:80/',
    'http://example.com:443/',
    'https://example.com:8080/',
    'file:///etc/passwd',
    'https://user:pass@example.com/',
] as $unsafe) {
    expect_rejected($unsafe);
}

echo "Security regression checks passed\n";
