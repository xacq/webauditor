<?php
declare(strict_types=1);

$root = dirname(__DIR__);
require $root . '/api.php';
$api = (string) file_get_contents($root . '/api.php');
$index = (string) file_get_contents($root . '/assets/app.js');

preg_match_all('/\$add\(\$G,\s*[\'\"]([a-z0-9_]+)[\'\"]/', $api, $matches);
$checkIds = array_values(array_unique($matches[1]));
sort($checkIds);

$catalogStart = strpos($index, 'const CHECK_LABELS = {');
$catalogEnd = strpos($index, 'const catalogLabel', $catalogStart ?: 0);
if ($catalogStart === false || $catalogEnd === false) {
    throw new RuntimeException('CHECK_LABELS catalog was not found.');
}
$catalog = substr($index, $catalogStart, $catalogEnd - $catalogStart);
$languages = ['es:', 'en:', 'fr:', 'it:', "'pt-PT':", 'de:'];

foreach ($checkIds as $id) {
    if (!preg_match('/^\s*' . preg_quote($id, '/') . ':\{([^}]+)\}/m', $catalog, $entry)) {
        throw new RuntimeException("Missing translated check title: $id");
    }
    foreach ($languages as $language) {
        if (!str_contains($entry[1], $language)) {
            throw new RuntimeException("Missing $language translation for check: $id");
        }
    }
}

foreach (CHECK_GROUP_IDS as $groupId) {
    if (!preg_match('/^\s*' . preg_quote($groupId, '/') . ':\{([^}]+)\}/m', $index, $entry)) {
        throw new RuntimeException("Missing translated group title: $groupId");
    }
    foreach ($languages as $language) {
        if (!str_contains($entry[1], $language)) {
            throw new RuntimeException("Missing $language translation for group: $groupId");
        }
    }
}

$messageCodes = [
    'env.unknown', 'env.exposed', 'env.safe',
    'git.unknown', 'git.exposed', 'git.safe',
    'backups.exposed', 'backups.unknown', 'backups.safe',
    'cert.valid', 'cert.invalid', 'cert.unavailable',
    'tls.modern', 'tls.legacy', 'tls.unavailable',
    'legacy_tls.unverified', 'legacy_tls.exposed', 'legacy_tls.safe',
    'cors.none', 'cors.reflected_credentials', 'cors.reflected',
    'cors.wildcard_credentials', 'cors.wildcard', 'cors.restricted',
    'hsts.valid', 'hsts.non_https', 'hsts.missing', 'hsts.short',
    'hsts_subdomains.enabled', 'hsts_subdomains.missing',
    'hsts_preload.enabled', 'hsts_preload.missing',
    'csp.missing', 'csp.weak', 'csp.strong',
    'framing.protected', 'framing.missing',
    'nosniff.enabled', 'nosniff.missing',
    'referrer.missing', 'referrer.safe', 'referrer.unsafe',
    'permissions.configured', 'permissions.missing',
    'coop.safe', 'coop.weak', 'coop.missing',
    'corp.safe', 'corp.weak', 'corp.missing',
    'cookies.none', 'cookies.safe', 'cookies.unsafe',
    'leak.exposed', 'leak.safe',
    'expiry.valid', 'expiry.expiring', 'expiry.expired', 'expiry.unavailable',
    'redirect.port_closed', 'redirect.enabled', 'redirect.missing',
    'sectxt.unknown', 'sectxt.valid', 'sectxt.invalid',
    'cipher.weak', 'cipher.modern', 'cipher.acceptable', 'cipher.unavailable',
    'spf.unknown', 'spf.multiple', 'spf.strict', 'spf.softfail', 'spf.permissive', 'spf.missing',
    'dmarc.unknown', 'dmarc.enforced', 'dmarc.partial', 'dmarc.monitoring', 'dmarc.missing',
    'caa.unknown', 'caa.configured', 'caa.missing',
];
foreach ($messageCodes as $messageCode) {
    if (!str_contains($api, "'$messageCode'")) {
        throw new RuntimeException("API does not emit message code: $messageCode");
    }
    $pattern = "/^\\s*'" . preg_quote($messageCode, '/') . "':\\{([\\s\\S]*?)(?=^\\s*'[a-z]+\\.[a-z]+':\\{|^\\s*\\};)/m";
    if (!preg_match($pattern, $index, $entry)) {
        throw new RuntimeException("Missing message catalog entry: $messageCode");
    }
    foreach ($languages as $language) {
        if (!str_contains($entry[1], $language)) {
            throw new RuntimeException("Missing $language message for: $messageCode");
        }
    }
}

echo 'I18n catalog checks passed (' . count($checkIds) . ' controls, ' . count($messageCodes) . " structured messages)\n";
