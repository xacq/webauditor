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
if ($result['summary'] !== ['total' => 4, 'pass' => 1, 'warn' => 1, 'fail' => 2, 'unknown' => 0]) {
    throw new RuntimeException('Unexpected check summary.');
}
if ($result['priorities'][0]['title'] !== '.env') {
    throw new RuntimeException('Highest-weight failure must be first priority.');
}
if (summarize_checks($checks, false)['risk'] !== 'alto') {
    throw new RuntimeException('Invalid TLS must force high risk.');
}

$criticalRating = calculate_rating([
    ['status' => 'pass', 'severity' => 'medium', 'weight' => 165, 'earned' => 165, 'title' => 'Controles'],
    ['status' => 'fail', 'severity' => 'critical', 'weight' => 10, 'earned' => 0, 'title' => '.env'],
], true);
if ($criticalRating['score'] !== 49 || $criticalRating['grade'] !== 'F' || $criticalRating['risk'] !== 'alto') {
    throw new RuntimeException('A critical exposure must cap score at 49, force grade F and high risk.');
}

$highRating = calculate_rating([
    ['status' => 'pass', 'severity' => 'medium', 'weight' => 95, 'earned' => 95, 'title' => 'Controles'],
    ['status' => 'fail', 'severity' => 'high', 'weight' => 5, 'earned' => 0, 'title' => 'CORS'],
], true);
if ($highRating['score'] !== 64 || $highRating['grade'] !== 'D' || $highRating['risk'] !== 'alto') {
    throw new RuntimeException('A high-severity exposure must cap score at 64, grade D and high risk.');
}

$unknownRating = calculate_rating([
    ['status' => 'pass', 'severity' => 'medium', 'weight' => 10, 'earned' => 10, 'title' => 'Verificado'],
    ['status' => 'unknown', 'severity' => 'medium', 'weight' => 90, 'earned' => 0, 'title' => 'No verificable'],
], true);
if ($unknownRating['score'] !== 64 || $unknownRating['grade'] !== 'D' || $unknownRating['coverage'] !== 10 || $unknownRating['summary']['unknown'] !== 1 || $unknownRating['risk'] !== 'medio') {
    throw new RuntimeException('Low verification coverage must be reported and cap the grade without treating unknown checks as failures.');
}

echo "Audit summary checks passed\n";
