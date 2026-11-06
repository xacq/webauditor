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
assert_same('transport', check_group_id('Transporte'), 'transport group code');
assert_same('headers', check_group_id('Cabeceras de seguridad'), 'headers group code');
assert_same('other', check_group_id('Grupo desconocido'), 'fallback group code');

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

assert_same(5, timeout_for_deadline(null, 5), 'timeout without deadline');
assert_same(0, timeout_for_deadline(microtime(true) - 1, 5), 'expired deadline');

$dohOk = parse_doh_response('{"Status":0,"Answer":[{"data":"\\"v=spf1 -all\\""}]}', 0, 200);
assert_same(true, $dohOk['ok'], 'successful DoH response');
assert_same(['v=spf1 -all'], $dohOk['records'], 'DoH records');

$dohNxDomain = parse_doh_response('{"Status":3}', 0, 200);
assert_same(true, $dohNxDomain['ok'], 'NXDOMAIN is a conclusive empty response');
assert_same([], $dohNxDomain['records'], 'NXDOMAIN records');

$dohFailure = parse_doh_response('', CURLE_OPERATION_TIMEDOUT, 0);
assert_same(false, $dohFailure['ok'], 'DoH transport failure');

$expiredDns = dns_queries_doh(['spf' => ['example.com', 'TXT'], 'caa' => ['example.com', 'CAA']], microtime(true) - 1);
assert_same(false, $expiredDns['spf']['ok'], 'DNS skipped after deadline');
assert_same(false, $expiredDns['caa']['ok'], 'parallel DNS skipped after deadline');

echo "Security helper checks passed\n";
