<?php
declare(strict_types=1);

require dirname(__DIR__) . '/api.php';

function assert_same(mixed $expected, mixed $actual, string $label): void
{
    if ($expected !== $actual) {
        throw new RuntimeException("$label: expected " . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

assert_same('example.com', url_host('example.com'), 'IPv4/domain URL host');
assert_same('[2001:db8::42]', url_host('2001:db8::42'), 'IPv6 URL host');
assert_same('ssl://203.0.113.1:443', socket_endpoint('203.0.113.1'), 'IPv4 socket endpoint');
assert_same('ssl://[2001:db8::42]:443', socket_endpoint('2001:db8::42'), 'IPv6 socket endpoint');

$target = parse_target('https://example.com:443/path?x=1');
assert_same('https', $target['scheme'], 'scheme');
assert_same(443, $target['port'], 'HTTPS port');
assert_same('/path?x=1', $target['path'], 'path');

$literalIpv6Rejected = false;
try {
    parse_target('https://[2001:db8::42]/');
} catch (RuntimeException) {
    $literalIpv6Rejected = true;
}
if (!$literalIpv6Rejected) {
    throw new RuntimeException('Literal IPv6 input must be rejected in favor of a domain.');
}

echo "Security helper checks passed\n";
