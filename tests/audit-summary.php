<?php
declare(strict_types=1);

require dirname(__DIR__) . '/api.php';

$checks = [
    ['status' => 'pass', 'weight' => 20, 'title' => 'Certificado'],
    ['status' => 'warn', 'weight' => 15, 'title' => 'CSP'],
    ['status' => 'fail', 'weight' => 5, 'title' => 'Cookies'],
    ['status' => 'fail', 'weight' => 10, 'title' => '.env'],
];

$result = summarize_checks($checks, true);
if ($result['risk'] !== 'medio') {
    throw new RuntimeException('Expected medium risk for two failures.');
}
if ($result['summary'] !== ['total' => 4, 'pass' => 1, 'warn' => 1, 'fail' => 2]) {
    throw new RuntimeException('Unexpected check summary.');
}
if ($result['priorities'][0]['title'] !== '.env') {
    throw new RuntimeException('Highest-weight failure must be first priority.');
}
if (summarize_checks($checks, false)['risk'] !== 'alto') {
    throw new RuntimeException('Invalid TLS must force high risk.');
}

echo "Audit summary checks passed\n";
