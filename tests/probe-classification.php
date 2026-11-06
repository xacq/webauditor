<?php
declare(strict_types=1);

require dirname(__DIR__) . '/api.php';

function probe_result(int $status, string $body, string $contentType = 'text/plain', ?string $error = null): array
{
    return [
        'status' => $status,
        'headers' => ['content-type' => $contentType],
        'body' => $body,
        'truncated' => false,
        'error' => $error,
    ];
}

$baseline = probe_result(200, '<html><title>Not found</title></html>', 'text/html');

if (!probe_is_soft_404(probe_result(200, '<html><title>Not found</title></html>', 'text/html'), $baseline)) {
    throw new RuntimeException('An identical catch-all response must be classified as soft-404.');
}
if (probe_exposes_sensitive_file('/.env', $baseline, $baseline)) {
    throw new RuntimeException('A generic HTML response must not be classified as an exposed .env file.');
}
if (!probe_exposes_sensitive_file('/.env', probe_result(200, "APP_KEY=base64:secret\nDB_PASSWORD=secret"), $baseline)) {
    throw new RuntimeException('A real environment file signature must be detected.');
}
if (!probe_exposes_sensitive_file('/.git/HEAD', probe_result(200, 'ref: refs/heads/main'), $baseline)) {
    throw new RuntimeException('An exposed Git HEAD must be detected.');
}
if (!probe_exposes_sensitive_file('/phpinfo.php', probe_result(200, '<html><title>phpinfo()</title><h1>PHP Version 8.3</h1></html>', 'text/html'), $baseline)) {
    throw new RuntimeException('An HTML phpinfo page must be detected.');
}
if (!probe_exposes_sensitive_file('/backup.sql', probe_result(200, '-- SQL dump\nCREATE TABLE users (id int);'), $baseline)) {
    throw new RuntimeException('An SQL dump signature must be detected.');
}
if (!probe_is_unknown(probe_result(0, '', 'text/plain', 'timeout'))) {
    throw new RuntimeException('A connection error must be classified as unknown.');
}

$expiredProbes = probe_paths_parallel('example.com', '93.184.216.34', ['/.env', '/.git/HEAD'], true, microtime(true) - 1);
if (!probe_is_unknown($expiredProbes['/.env']) || !probe_is_unknown($expiredProbes['/.git/HEAD'])) {
    throw new RuntimeException('Probes skipped after the global deadline must be classified as unknown.');
}

echo "Probe classification checks passed\n";
