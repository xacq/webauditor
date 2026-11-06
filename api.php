<?php
declare(strict_types=1);

/**
 * Green PC · Auditor de seguridad web — API
 * Requiere PHP >= 8.0 con extensiones curl y openssl.
 * POST JSON {"url": "ejemplo.com"}  ->  JSON con score, grade y checks.
 */

const RATE_MAX        = 10;    // análisis por IP...
const RATE_WINDOW     = 3600;  // ...por hora
const TARGET_RATE_MAX = 4;     // auditorías por destino...
const TARGET_RATE_WINDOW = 60; // ...por minuto, compartido entre clientes
const TRUST_CF_HEADER = false; // true solo si el sitio está detrás de Cloudflare
const USER_AGENT      = 'GreenPC-SecurityAuditor/1.0 (+https://greenpc.dev)';
const SUPPORTED_LANGS = ['es', 'en', 'fr', 'it', 'pt-PT', 'de'];
const PROBE_BODY_LIMIT = 4096;
const AUDIT_BUDGET_SECONDS = 50;
const CHECK_GROUP_IDS = [
    'Transporte' => 'transport',
    'Cabeceras de seguridad' => 'headers',
    'Cookies y archivos sensibles' => 'exposure',
    'Buenas prácticas' => 'practices',
    'Seguridad DNS y Correo' => 'dns_mail',
];

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('X-Robots-Tag: noindex, nofollow');
header("Content-Security-Policy: default-src 'none'; frame-ancestors 'none'");

function fail(int $code, string $msg, int $retryAfter = 0): void
{
    http_response_code($code);
    $payload = ['error' => $msg];
    if ($retryAfter > 0) {
        header('Retry-After: ' . $retryAfter);
        $payload['retry_after'] = $retryAfter;
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function check_group_id(string $group): string
{
    return CHECK_GROUP_IDS[$group] ?? 'other';
}

/* ---------- Entrada, origen y rate limit ---------- */

function client_ip(): string
{
    if (TRUST_CF_HEADER && !empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function rate_limit(string $ip): void
{
    $fp = @fopen(sys_get_temp_dir() . '/gpc_audit_rl_' . sha1($ip) . '.json', 'c+');
    if (!$fp) {
        return;
    }
    flock($fp, LOCK_EX);
    $now  = time();
    $hits = json_decode((string) stream_get_contents($fp), true) ?: [];
    $hits = array_values(array_filter($hits, fn($t) => is_int($t) && $t > $now - RATE_WINDOW));
    if (count($hits) >= RATE_MAX) {
        flock($fp, LOCK_UN);
        fclose($fp);
        $retryAfter = max(1, min($hits) + RATE_WINDOW - $now);
        fail(429, 'Límite alcanzado: máximo ' . RATE_MAX . ' análisis por hora.', $retryAfter);
    }
    $hits[] = $now;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($hits));
    flock($fp, LOCK_UN);
    fclose($fp);
}

/** Limita escaneos hacia un mismo destino para reducir el riesgo de abuso contra terceros. */
function target_rate_limit(string $host): void
{
    $fp = @fopen(sys_get_temp_dir() . '/gpc_audit_target_' . sha1($host) . '.json', 'c+');
    if (!$fp) {
        return;
    }
    flock($fp, LOCK_EX);
    $now = time();
    $hits = json_decode((string) stream_get_contents($fp), true) ?: [];
    $hits = array_values(array_filter($hits, fn($t) => is_int($t) && $t > $now - TARGET_RATE_WINDOW));
    if (count($hits) >= TARGET_RATE_MAX) {
        flock($fp, LOCK_UN);
        fclose($fp);
        $retryAfter = max(1, min($hits) + TARGET_RATE_WINDOW - $now);
        fail(429, 'El destino alcanzó el límite temporal de análisis.', $retryAfter);
    }
    $hits[] = $now;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($hits));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

/* ---------- Módulos internos ---------- */
require_once __DIR__ . '/lib/Target.php';
require_once __DIR__ . '/lib/Http.php';
require_once __DIR__ . '/lib/Tls.php';

/* ---------- Consultas DNS y Sondaje Seguro ---------- */

function parse_doh_response(string $response, int $curlError, int $httpStatus): array
{
    if ($curlError !== 0 || $response === '' || $httpStatus !== 200) {
        return ['ok' => false, 'records' => []];
    }
    $json = json_decode($response, true);
    if (!isset($json['Answer']) || !is_array($json['Answer'])) {
        $dnsStatus = isset($json['Status']) ? (int) $json['Status'] : -1;
        return ['ok' => in_array($dnsStatus, [0, 3], true), 'records' => []];
    }
    $records = [];
    foreach ($json['Answer'] as $ans) {
        if (isset($ans['data'])) {
            $records[] = trim((string) $ans['data'], '"');
        }
    }
    return ['ok' => true, 'records' => $records];
}

/** Ejecuta varias consultas DNS independientes en paralelo mediante DoH. */
function dns_queries_doh(array $queries, ?float $deadline = null): array
{
    $timeout = timeout_for_deadline($deadline, 3);
    if ($timeout < 1) {
        return array_fill_keys(array_keys($queries), ['ok' => false, 'records' => []]);
    }

    $multi = curl_multi_init();
    $handles = [];
    foreach ($queries as $key => $query) {
        [$name, $type] = $query;
        $url = 'https://cloudflare-dns.com/dns-query?name=' . urlencode($name) . '&type=' . urlencode($type);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/dns-json'],
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => min(2, $timeout),
            CURLOPT_USERAGENT      => USER_AGENT,
        ]);
        curl_multi_add_handle($multi, $ch);
        $handles[$key] = $ch;
    }

    $running = null;
    do {
        curl_multi_exec($multi, $running);
        if ($running > 0 && curl_multi_select($multi, 0.5) === -1) {
            usleep(10000);
        }
    } while ($running > 0);

    $results = [];
    foreach ($handles as $key => $ch) {
        $results[$key] = parse_doh_response(
            (string) curl_multi_getcontent($ch),
            curl_errno($ch),
            (int) curl_getinfo($ch, CURLINFO_HTTP_CODE)
        );
        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);
    }
    curl_multi_close($multi);
    return $results;
}

/** Consulta DNS individual conservada para consumidores internos y pruebas. */
function dns_query_doh(string $name, string $type, ?float $deadline = null): array
{
    return dns_queries_doh(['query' => [$name, $type]], $deadline)['query'];
}

/** Petición rápida y acotada para verificar exposición de archivos manteniendo anti-SSRF con la IP validada. */
function probe_path(string $host, string $ip, string $path, bool $https = true): array
{
    $port    = $https ? 443 : 80;
    $scheme  = $https ? 'https' : 'http';
    $url     = $scheme . '://' . url_host($host) . $path;
    $headers = [];
    $body    = '';
    $status  = 0;
    $ch      = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RESOLVE        => [$host . ':' . $port . ':' . $ip],
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT        => 3,
        CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_USERAGENT      => USER_AGENT,
        CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$headers, &$status) {
            $len     = strlen($line);
            $trimmed = trim($line);
            if (stripos($trimmed, 'HTTP/') === 0) {
                $status = (int) (explode(' ', $trimmed)[1] ?? 0);
            } elseif (str_contains($trimmed, ':')) {
                [$k, $v] = explode(':', $trimmed, 2);
                $headers[strtolower(trim($k))] = trim($v);
            }
            return $len;
        },
        CURLOPT_WRITEFUNCTION  => function ($ch, $data) use (&$body) {
            $body .= $data;
            if (strlen($body) >= 1024) {
                return 0; // aborta tras 1KB para ahorrar recursos
            }
            return strlen($data);
        },
    ]);
    curl_exec($ch);
    curl_close($ch);
    return ['status' => $status, 'headers' => $headers, 'body' => $body];
}

/** Lanza múltiples probe_path en paralelo con curl_multi. Devuelve array asociativo ruta => resultado. */
function probe_paths_parallel(string $host, string $ip, array $paths, bool $https = true, ?float $deadline = null): array
{
    $timeout = timeout_for_deadline($deadline, 4);
    if ($timeout < 1) {
        return array_fill_keys($paths, ['status' => 0, 'headers' => [], 'body' => '', 'truncated' => false, 'error' => 'presupuesto de tiempo agotado']);
    }
    $port   = $https ? 443 : 80;
    $scheme = $https ? 'https' : 'http';
    $mh     = curl_multi_init();
    $handles = [];
    $results = [];
    $responseHeaders = [];
    $responseBodies = [];
    $responseStatuses = [];
    $truncated = [];

    foreach ($paths as $path) {
        $url = $scheme . '://' . url_host($host) . $path;
        $responseHeaders[$path] = [];
        $responseBodies[$path] = '';
        $responseStatuses[$path] = 0;
        $truncated[$path] = false;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RESOLVE        => [$host . ':' . $port . ':' . $ip],
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => min(2, $timeout),
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => USER_AGENT,
            CURLOPT_HEADERFUNCTION => function ($ch, $line) use ($path, &$responseHeaders, &$responseStatuses) {
                $len = strlen($line);
                $line = trim($line);
                if (stripos($line, 'HTTP/') === 0) {
                    $responseHeaders[$path] = [];
                    $responseStatuses[$path] = (int) (explode(' ', $line)[1] ?? 0);
                } elseif (str_contains($line, ':')) {
                    [$key, $value] = explode(':', $line, 2);
                    $responseHeaders[$path][strtolower(trim($key))] = trim($value);
                }
                return $len;
            },
            CURLOPT_WRITEFUNCTION => function ($ch, $data) use ($path, &$responseBodies, &$truncated) {
                $remaining = PROBE_BODY_LIMIT - strlen($responseBodies[$path]);
                if ($remaining <= 0) {
                    $truncated[$path] = true;
                    return 0;
                }
                if (strlen($data) > $remaining) {
                    $responseBodies[$path] .= substr($data, 0, $remaining);
                    $truncated[$path] = true;
                    return 0;
                }
                $responseBodies[$path] .= $data;
                return strlen($data);
            },
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[(int) $ch] = ['ch' => $ch, 'path' => $path];
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        if ($running > 0) {
            curl_multi_select($mh, 0.5);
        }
    } while ($running > 0);

    foreach ($handles as $info) {
        $ch   = $info['ch'];
        $path = $info['path'];
        $errno = curl_errno($ch);
        $intentionalAbort = $errno === CURLE_WRITE_ERROR && $truncated[$path];
        $results[$path] = [
            'status' => $responseStatuses[$path] ?: (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'headers' => $responseHeaders[$path],
            'body' => $responseBodies[$path],
            'truncated' => $truncated[$path],
            'error' => ($errno !== 0 && !$intentionalAbort) ? (curl_error($ch) ?: 'error de conexión') : null,
        ];
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }

    curl_multi_close($mh);
    return $results;
}

function probe_is_unknown(array $result): bool
{
    return !empty($result['error']) || (int) ($result['status'] ?? 0) === 0;
}

function probe_is_soft_404(array $result, array $baseline): bool
{
    if (probe_is_unknown($result) || probe_is_unknown($baseline) || ($result['status'] ?? 0) !== ($baseline['status'] ?? 0)) {
        return false;
    }
    $body = preg_replace('/\s+/', ' ', trim((string) ($result['body'] ?? '')));
    $baselineBody = preg_replace('/\s+/', ' ', trim((string) ($baseline['body'] ?? '')));
    if ($body === '' || $baselineBody === '') {
        return false;
    }
    similar_text($body, $baselineBody, $similarity);
    return hash_equals(hash('sha256', $baselineBody), hash('sha256', $body)) || $similarity >= 95.0;
}

function probe_exposes_sensitive_file(string $path, array $result, array $baseline): bool
{
    if (probe_is_unknown($result) || (int) ($result['status'] ?? 0) !== 200 || probe_is_soft_404($result, $baseline)) {
        return false;
    }
    $body = (string) ($result['body'] ?? '');
    $contentType = strtolower((string) ($result['headers']['content-type'] ?? ''));
    $isHtml = str_contains($contentType, 'html') || stripos($body, '<!doctype') !== false || stripos($body, '<html') !== false;

    if ($path === '/.env') {
        return !$isHtml && (bool) preg_match('/^[A-Z0-9_]{2,}\s*=|APP_KEY|DB_PASSWORD|SECRET/m', $body);
    }
    if ($path === '/.git/HEAD') {
        return str_starts_with(trim($body), 'ref: refs/') || (bool) preg_match('/^[0-9a-f]{40}$/i', trim($body));
    }
    if ($path === '/phpinfo.php') {
        return stripos($body, 'phpinfo()') !== false || (bool) preg_match('/<title>phpinfo\(\)<\/title>|PHP Version\s*[0-9]/i', $body);
    }
    if (str_starts_with($path, '/wp-config')) {
        return (bool) preg_match('/DB_(?:NAME|USER|PASSWORD|HOST)|AUTH_KEY|SECURE_AUTH_KEY/i', $body);
    }
    if (str_ends_with($path, '.sql')) {
        return (bool) preg_match('/(?:CREATE\s+TABLE|INSERT\s+INTO|SQL\s+dump|PostgreSQL\s+database\s+dump)/i', $body);
    }
    return false;
}

/* ---------- Auditoría ---------- */

/** Ordena las correcciones de mayor impacto y resume el riesgo sin persistir resultados. */
function summarize_checks(array $checks, bool $tlsOk): array
{
    $priorities = array_values(array_filter($checks, fn($check) => $check['status'] !== 'pass'));
    usort($priorities, function (array $a, array $b): int {
        $statusRank = ['fail' => 3, 'warn' => 2, 'unknown' => 1];
        $severityRank = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
        return (($statusRank[$b['status']] ?? 0) <=> ($statusRank[$a['status']] ?? 0))
            ?: (($severityRank[$b['severity'] ?? 'medium'] ?? 0) <=> ($severityRank[$a['severity'] ?? 'medium'] ?? 0))
            ?: ($b['weight'] <=> $a['weight']);
    });
    $counts = ['pass' => 0, 'warn' => 0, 'fail' => 0, 'unknown' => 0];
    foreach ($checks as $check) {
        $counts[$check['status']]++;
    }
    $severeFailure = array_filter($checks, fn($check) => $check['status'] === 'fail' && in_array($check['severity'] ?? 'medium', ['critical', 'high'], true));
    $risk = !$tlsOk || $severeFailure ? 'alto' : ($counts['fail'] > 0 || $counts['warn'] >= 4 || $counts['unknown'] > 0 ? 'medio' : 'bajo');
    return ['risk' => $risk, 'summary' => ['total' => count($checks)] + $counts, 'priorities' => array_slice($priorities, 0, 3)];
}

/** Calcula una nota honesta: lo no verificable no suma ni resta y los fallos graves limitan la calificación. */
function calculate_rating(array $checks, bool $tlsOk): array
{
    $scoredChecks = array_values(array_filter($checks, fn($check) => $check['status'] !== 'unknown'));
    $totalWeight = array_sum(array_column($checks, 'weight'));
    $maxScore = array_sum(array_column($scoredChecks, 'weight'));
    $earnedScore = array_sum(array_column($scoredChecks, 'earned'));
    $score = $maxScore > 0 ? (int) round(($earnedScore / $maxScore) * 100) : 0;
    $coverage = $totalWeight > 0 ? (int) round(($maxScore / $totalWeight) * 100) : 0;

    $criticalFailure = array_filter($checks, fn($check) => $check['status'] === 'fail' && ($check['severity'] ?? 'medium') === 'critical');
    $highFailure = array_filter($checks, fn($check) => $check['status'] === 'fail' && ($check['severity'] ?? 'medium') === 'high');
    if (!$tlsOk || $criticalFailure) {
        $score = min($score, 49);
    } elseif ($highFailure) {
        $score = min($score, 64);
    }
    if ($coverage < 75) {
        $score = min($score, 64);
    }

    $grade = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 65 ? 'C' : ($score >= 50 ? 'D' : 'F')));
    return ['score' => $score, 'grade' => $grade, 'coverage' => $coverage] + summarize_checks($checks, $tlsOk);
}

function audit(string $input, string $lang = 'es'): array
{
    $startedAt = microtime(true);
    $deadline = $startedAt + AUDIT_BUDGET_SECONDS;
    $t    = parse_target($input);
    $host = $t['host'];
    $ip   = resolve_public($host);
    $tls  = tls_probe($host, $ip, $deadline);
    $tlsOk = $tls['ok'] && $tls['valid'];

    $main = fetch_follow(($tls['ok'] ? 'https' : 'http') . '://' . url_host($host) . $t['path'], $ip, $tlsOk, $deadline);

    // Comprueba CORS con un origen ajeno controlado; detecta políticas que reflejan Origin.
    try {
        $corsProbe = fetch_once($main['final_url'], ['Origin: https://gpc-audit.invalid'], $main['ip'], $tlsOk, $deadline);
    } catch (RuntimeException $e) {
        $corsProbe = ['headers' => []];
    }

    // ¿http:// redirige a https://?
    $plain = null;
    try {
        $plain = fetch_once('http://' . url_host($host) . '/', [], null, false, $deadline);
    } catch (RuntimeException $e) {
        $plain = null;
    }

    $H          = $main['headers'];
    $get        = fn(string $n) => $H[$n][0] ?? null;
    $finalHttps = str_starts_with($main['final_url'], 'https://');

    $checks = [];
    $add = function (string $group, string $id, string $title, int $w, string $status, string $detail, string $fix = '', string $severity = 'medium', string $messageCode = '', array $messageParams = []) use (&$checks) {
        $earned   = $status === 'pass' ? $w : ($status === 'warn' ? (int) round($w / 2) : 0);
        $checks[] = compact('group', 'id', 'title', 'status', 'detail', 'fix', 'severity') + [
            'group_id' => check_group_id($group),
            'message_code' => $messageCode,
            'message_params' => $messageParams,
            'weight' => $w,
            'earned' => $earned,
        ];
    };

    /* --- Transporte --- */
    $G = 'Transporte';
    if ($tlsOk) {
        $add($G, 'cert', 'Certificado válido', 20, 'pass', 'Certificado de confianza para ' . $host . ', emitido por ' . $tls['issuer'] . '.', '', 'medium', 'cert.valid', [
            'host' => $host,
            'issuer' => $tls['issuer'],
        ]);
    } elseif ($tls['ok']) {
        $add($G, 'cert', 'Certificado válido', 20, 'fail', 'El certificado no es de confianza: ' . $tls['error'] . '.',
            "Instala un certificado válido que cubra el dominio exacto y envía la cadena intermedia completa.
Let's Encrypt es gratuito: certbot --apache -d $host", 'critical', 'cert.invalid', [
                'host' => $host,
                'error' => $tls['error'],
            ]);
    } else {
        $add($G, 'cert', 'Certificado válido', 20, 'fail', 'No hay HTTPS disponible en el puerto 443.',
            "Activa HTTPS con un certificado (Let's Encrypt) y redirige todo el tráfico HTTP a HTTPS.", 'critical', 'cert.unavailable', ['host' => $host]);
    }

    if ($tls['ok'] && isset($tls['days_left'])) {
        $d = $tls['days_left'];
        $s = $d < 0 ? 'fail' : ($d < 30 ? 'warn' : 'pass');
        $expiryCode = $d < 0 ? 'expiry.expired' : ($d < 30 ? 'expiry.expiring' : 'expiry.valid');
        $add($G, 'expiry', 'Vigencia del certificado', 5, $s,
            $d < 0 ? 'El certificado expiró hace ' . abs($d) . ' días.' : "Vence en $d días.",
            $s === 'pass' ? '' : 'Renueva el certificado y automatiza la renovación (certbot renew en cron o systemd timer).', $d < 0 ? 'high' : 'medium', $expiryCode, [
                'days' => abs($d),
            ]);
    } else {
        $add($G, 'expiry', 'Vigencia del certificado', 5, 'fail', 'No hay certificado que evaluar.', '', 'critical', 'expiry.unavailable');
    }

    $proto = $tls['protocol'] ?? null;
    if ($proto) {
        $ok = in_array($proto, ['TLSv1.2', 'TLSv1.3'], true);
        $add($G, 'tls', 'Versión de TLS', 5, $ok ? 'pass' : 'fail', 'Negociado: ' . $proto . (isset($tls['cipher']) ? ' (' . $tls['cipher'] . ')' : '') . '.',
            $ok ? '' : "Desactiva TLS 1.0/1.1.
Nginx: ssl_protocols TLSv1.2 TLSv1.3;
Apache: SSLProtocol -all +TLSv1.2 +TLSv1.3", $ok ? 'medium' : 'high', $ok ? 'tls.modern' : 'tls.legacy', [
                'protocol' => $proto,
                'cipher' => $tls['cipher'] ?? '',
                'cipher_suffix' => isset($tls['cipher']) ? ' (' . $tls['cipher'] . ')' : '',
            ]);
    } else {
        $add($G, 'tls', 'Versión de TLS', 5, 'fail', 'No se pudo negociar TLS.', '', 'critical', 'tls.unavailable');
    }

    $protocols = $tls['supported_protocols'] ?? [];
    $testedProtocols = array_filter($protocols, fn($supported) => $supported !== null);
    $legacyProtocols = [];
    foreach (['TLSv1.0', 'TLSv1.1'] as $legacy) {
        if (($protocols[$legacy] ?? false) === true) {
            $legacyProtocols[] = $legacy;
        }
    }
    if (!$testedProtocols) {
        $add($G, 'legacy_tls', 'Protocolos TLS heredados', 5, 'warn',
            'El runtime no permite comprobar versiones TLS de forma explícita.',
            'Ejecuta la herramienta con PHP/OpenSSL actualizado o verifica el servidor con testssl.sh.', 'medium', 'legacy_tls.unverified');
    } elseif ($legacyProtocols) {
        $add($G, 'legacy_tls', 'Protocolos TLS heredados', 5, 'fail',
            'El servidor aún acepta ' . implode(' y ', $legacyProtocols) . '.',
            "Desactiva TLS 1.0/1.1.
Nginx: ssl_protocols TLSv1.2 TLSv1.3;
Apache: SSLProtocol -all +TLSv1.2 +TLSv1.3", 'high', 'legacy_tls.exposed', ['protocols' => $legacyProtocols]);
    } else {
        $checked = implode(', ', array_keys($testedProtocols));
        $add($G, 'legacy_tls', 'Protocolos TLS heredados', 5, 'pass',
            'No se aceptaron protocolos heredados en las pruebas disponibles (' . $checked . ').', '', 'medium', 'legacy_tls.safe', ['protocols' => array_keys($testedProtocols)]);
    }

    if ($plain === null) {
        $add($G, 'redirect', 'Redirección HTTP → HTTPS', 10, 'pass', 'El puerto 80 no responde; el sitio solo se sirve por HTTPS.', '', 'medium', 'redirect.port_closed');
    } else {
        $loc = $plain['headers']['location'][0] ?? '';
        $ok  = $plain['status'] >= 300 && $plain['status'] < 400 && stripos($loc, 'https://') === 0;
        $add($G, 'redirect', 'Redirección HTTP → HTTPS', 10, $ok ? 'pass' : 'fail',
            $ok ? 'http:// redirige a HTTPS (' . $plain['status'] . ').' : 'http:// responde ' . $plain['status'] . ' y no redirige a HTTPS.',
            $ok ? '' : "Apache (.htaccess):
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

Nginx:
return 301 https://\$host\$request_uri;", 'medium', $ok ? 'redirect.enabled' : 'redirect.missing', [
                'status' => $plain['status'],
                'location' => $loc,
            ]);
    }

    $hsts = $get('strict-transport-security');
    $hstsFix = "Apache: Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains\"
Nginx: add_header Strict-Transport-Security \"max-age=31536000; includeSubDomains\" always;";
    preg_match('/max-age=(\d+)/i', (string) $hsts, $m);
    $hstsAge = (int) ($m[1] ?? 0);
    $hstsSubdomains = $hsts && stripos($hsts, 'includesubdomains') !== false;
    $hstsPreload = $hsts && stripos($hsts, 'preload') !== false;
    $hstsBase = $finalHttps && $hsts && $hstsAge >= 15552000;
    $hstsMaxCode = $hstsBase ? 'hsts.valid' : (!$finalHttps ? 'hsts.non_https' : (!$hsts ? 'hsts.missing' : 'hsts.short'));
    $add($G, 'hsts_max_age', 'HSTS: vigencia', 8, $hstsBase ? 'pass' : 'fail',
        $hstsBase ? "max-age=$hstsAge: los navegadores forzarán HTTPS." : (!$finalHttps ? 'La respuesta final no usa HTTPS.' : ($hsts ? "max-age=$hstsAge es inferior a 6 meses." : 'Falta Strict-Transport-Security.')),
        $hstsBase ? '' : "Usa al menos max-age=15552000; idealmente 31536000.
" . $hstsFix, 'medium', $hstsMaxCode, ['max_age' => $hstsAge]);
    $add($G, 'hsts_subdomains', 'HSTS: subdominios', 4, $hstsSubdomains ? 'pass' : 'warn',
        $hstsSubdomains ? 'includeSubDomains protege también los subdominios.' : 'No incluye subdominios; uno inseguro podría debilitar la protección del dominio.',
        $hstsSubdomains ? '' : $hstsFix, 'medium', $hstsSubdomains ? 'hsts_subdomains.enabled' : 'hsts_subdomains.missing');
    $add($G, 'hsts_preload', 'HSTS: preparación para preload', 3, $hstsPreload ? 'pass' : 'warn',
        $hstsPreload ? 'La directiva preload está presente.' : 'No incluye preload. Es opcional, pero aporta protección desde la primera visita si todo el dominio soporta HTTPS.',
        $hstsPreload ? '' : 'Solo cuando todos los subdominios usen HTTPS: añade preload y registra el dominio en hstspreload.org.', 'low', $hstsPreload ? 'hsts_preload.enabled' : 'hsts_preload.missing');

    /* --- Cabeceras --- */
    $G    = 'Cabeceras de seguridad';
    $dirs = [];
    $csp  = $get('content-security-policy');
    $cspIssueCodes = [];
    $cspFix = "Empieza con una política mínima y ajústala:
Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'";
    if (!$csp) {
        $add($G, 'csp', 'Content-Security-Policy', 15, 'fail', 'No hay CSP: nada limita qué scripts pueden ejecutarse (mitigación clave contra XSS).', $cspFix, 'medium', 'csp.missing');
    } else {
        foreach (explode(';', $csp) as $d) {
            $parts = preg_split('/\s+/', trim($d), -1, PREG_SPLIT_NO_EMPTY);
            if ($parts) {
                $dirs[strtolower(array_shift($parts))] = $parts;
            }
        }
        $script = $dirs['script-src'] ?? $dirs['default-src'] ?? null;
        $issues = [];
        if ($script === null) {
            $issues[] = 'no restringe scripts (falta script-src o default-src)';
            $cspIssueCodes[] = 'missing_script_source';
        } else {
            $strict = preg_grep("/^'(nonce-|sha(256|384|512)-)/", $script) || in_array("'strict-dynamic'", $script, true);
            foreach (["'unsafe-inline'", "'unsafe-eval'", '*', 'data:', 'http:'] as $bad) {
                if (in_array($bad, $script, true) && !($bad === "'unsafe-inline'" && $strict)) {
                    $issues[] = "permite $bad en scripts";
                    $cspIssueCodes[] = match ($bad) {
                        "'unsafe-inline'" => 'unsafe_inline',
                        "'unsafe-eval'" => 'unsafe_eval',
                        '*' => 'wildcard',
                        'data:' => 'data_scheme',
                        'http:' => 'http_scheme',
                    };
                }
            }
        }
        $add($G, 'csp', 'Content-Security-Policy', 15, $issues ? 'warn' : 'pass',
            $issues ? 'CSP presente pero débil: ' . implode('; ', $issues) . '.' : 'CSP presente y restringe la ejecución de scripts.',
            $issues ? "Elimina 'unsafe-inline'/'unsafe-eval' usando nonces o hashes, y evita comodines en script-src." : '', 'medium', $issues ? 'csp.weak' : 'csp.strong', [
                'issue_codes' => $cspIssueCodes,
            ]);
    }

    $xfo = strtolower((string) $get('x-frame-options'));
    $ok  = in_array($xfo, ['deny', 'sameorigin'], true) || isset($dirs['frame-ancestors']);
    $add($G, 'framing', 'Protección contra clickjacking', 5, $ok ? 'pass' : 'fail',
        $ok ? 'El sitio controla en qué páginas puede incrustarse.' : 'Cualquier sitio puede incrustar esta página en un iframe.',
        $ok ? '' : "Header always set X-Frame-Options \"SAMEORIGIN\"
(o frame-ancestors 'self' dentro de la CSP)", 'medium', $ok ? 'framing.protected' : 'framing.missing', ['value' => $xfo]);

    $ok = strtolower((string) $get('x-content-type-options')) === 'nosniff';
    $add($G, 'nosniff', 'X-Content-Type-Options', 5, $ok ? 'pass' : 'fail',
        $ok ? 'nosniff activo.' : 'Falta nosniff: el navegador puede adivinar tipos MIME.',
        $ok ? '' : 'Header always set X-Content-Type-Options "nosniff"', 'medium', $ok ? 'nosniff.enabled' : 'nosniff.missing');

    $rp = strtolower((string) $get('referrer-policy'));
    if ($rp === '') {
        $add($G, 'referrer', 'Referrer-Policy', 5, 'warn', 'No definida; se usa el valor por defecto del navegador.', 'Header always set Referrer-Policy "strict-origin-when-cross-origin"', 'low', 'referrer.missing');
    } else {
        $ok = in_array($rp, ['no-referrer', 'same-origin', 'strict-origin', 'strict-origin-when-cross-origin', 'origin-when-cross-origin'], true);
        $add($G, 'referrer', 'Referrer-Policy', 5, $ok ? 'pass' : 'fail', "Valor: $rp.",
            $ok ? '' : 'Header always set Referrer-Policy "strict-origin-when-cross-origin"', $ok ? 'low' : 'medium', $ok ? 'referrer.safe' : 'referrer.unsafe', ['value' => $rp]);
    }

    $pp = $get('permissions-policy');
    $add($G, 'permissions', 'Permissions-Policy', 5, $pp ? 'pass' : 'warn',
        $pp ? 'Restringe el acceso a funciones del navegador.' : 'No definida: cámara, micrófono y geolocalización no están restringidos explícitamente.',
        $pp ? '' : 'Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"', 'low', $pp ? 'permissions.configured' : 'permissions.missing', ['value' => $pp ?? '']);

    $coop = strtolower((string) $get('cross-origin-opener-policy'));
    $okCoop = in_array($coop, ['same-origin', 'same-origin-allow-popups'], true);
    $add($G, 'coop', 'Cross-Origin-Opener-Policy (COOP)', 5, $okCoop ? 'pass' : 'warn',
        $okCoop ? "Aislamiento activo ($coop): previene ataques como Spectre y fugas cross-window." : ($coop ? "Valor permisivo: $coop." : 'No definida: otras pestañas maliciosas podrían interactuar con la ventana.'),
        $okCoop ? '' : 'Header always set Cross-Origin-Opener-Policy "same-origin"', 'low', $okCoop ? 'coop.safe' : ($coop ? 'coop.weak' : 'coop.missing'), ['value' => $coop]);

    $corp = strtolower((string) $get('cross-origin-resource-policy'));
    $okCorp = in_array($corp, ['same-origin', 'same-site'], true);
    $add($G, 'corp', 'Cross-Origin-Resource-Policy (CORP)', 5, $okCorp ? 'pass' : 'warn',
        $okCorp ? "CORP activo ($corp): restringe la carga de recursos a este origen." : ($corp ? "Valor actual: $corp." : 'No definida: sitios externos podrían embeber tus recursos multimedia o scripts.'),
        $okCorp ? '' : 'Header always set Cross-Origin-Resource-Policy "same-origin"', 'low', $okCorp ? 'corp.safe' : ($corp ? 'corp.weak' : 'corp.missing'), ['value' => $corp]);

    /* --- Exposición y Archivos Sensibles --- */
    $G       = 'Cookies y archivos sensibles';
    $cookies = $H['set-cookie'] ?? [];
    if (!$cookies) {
        $add($G, 'cookies', 'Cookies', 5, 'pass', 'La respuesta no establece cookies.', '', 'low', 'cookies.none');
    } else {
        $noSecure = $noHttp = $noSame = [];
        foreach ($cookies as $c) {
            $name = trim(explode('=', $c, 2)[0]);
            if (!preg_match('/;\s*secure/i', $c))   { $noSecure[] = $name; }
            if (!preg_match('/;\s*httponly/i', $c)) { $noHttp[]   = $name; }
            if (!preg_match('/;\s*samesite=/i', $c)) { $noSame[]   = $name; }
        }
        $probs = [];
        if ($noSecure) { $probs[] = 'sin Secure: ' . implode(', ', $noSecure); }
        if ($noHttp)   { $probs[] = 'sin HttpOnly: ' . implode(', ', $noHttp); }
        if ($noSame)   { $probs[] = 'sin SameSite: ' . implode(', ', $noSame); }
        $add($G, 'cookies', 'Cookies', 5, !$probs ? 'pass' : ($noSecure ? 'fail' : 'warn'),
            !$probs ? count($cookies) . ' cookie(s) con Secure, HttpOnly y SameSite.' : 'Cookies ' . implode(' | ', $probs) . '.',
            !$probs ? '' : "PHP: session.cookie_secure=1, session.cookie_httponly=1, session.cookie_samesite=Lax
WordPress/otros: define los atributos al emitir la cookie.", $noSecure ? 'medium' : 'low', !$probs ? 'cookies.safe' : 'cookies.unsafe', [
                'count' => count($cookies),
                'no_secure' => $noSecure,
                'no_http_only' => $noHttp,
                'no_same_site' => $noSame,
            ]);
    }

    $leaks = [];
    $srv   = $get('server');
    if ($srv && preg_match('/\d+\.\d+/', $srv)) { $leaks[] = "Server: $srv"; }
    if ($get('x-powered-by'))                   { $leaks[] = 'X-Powered-By: ' . $get('x-powered-by'); }
    $add($G, 'leak', 'Divulgación de tecnología', 5, $leaks ? 'warn' : 'pass',
        $leaks ? 'Se exponen versiones: ' . implode('; ', $leaks) . '.' : 'No se exponen versiones de servidor ni de lenguaje.',
        $leaks ? "PHP (php.ini): expose_php = Off
Apache: ServerTokens Prod
Nginx: server_tokens off;" : '', 'low', $leaks ? 'leak.exposed' : 'leak.safe', ['values' => $leaks]);

    // Una ruta aleatoria sirve como referencia para distinguir archivos reales de páginas soft-404.
    $baselinePath = '/.gpc-audit-' . bin2hex(random_bytes(8));
    $probePaths = ['/.env', '/.git/HEAD', '/wp-config.php.bak', '/wp-config.old', '/backup.sql', '/dump.sql', '/db.sql', '/phpinfo.php', '/.well-known/security.txt', $baselinePath];
    $probeResults = probe_paths_parallel($host, $ip, $probePaths, $tlsOk, $deadline);
    $baselineProbe = $probeResults[$baselinePath];

    // Verificación de exposición de .env
    $envProbe  = $probeResults['/.env'];
    $envStatus = $envProbe['status'];
    $envUnknown = probe_is_unknown($envProbe);
    $envLeak = probe_exposes_sensitive_file('/.env', $envProbe, $baselineProbe);
    $envCheckStatus = $envUnknown ? 'unknown' : ($envLeak ? 'fail' : 'pass');
    $envMessageCode = $envUnknown ? 'env.unknown' : ($envLeak ? 'env.exposed' : 'env.safe');
    $add($G, 'env', 'Protección de archivo .env', 10, $envCheckStatus,
        $envUnknown ? 'No se pudo verificar el archivo .env por un error de conexión.' : ($envLeak ? '¡PELIGRO CRÍTICO! El archivo .env está expuesto públicamente, filtrando credenciales y claves de la aplicación.' : 'El archivo .env no es accesible públicamente (' . $envStatus . ').'),
        $envLeak ? "Bloquea el acceso a archivos dotfiles en tu servidor web:
Apache: <Files \".*\">
  Require all denied
</Files>
Nginx: location ~ /\\. { deny all; }" : '', 'critical', $envMessageCode, ['status' => $envStatus]);

    // Verificación de exposición de .git
    $gitProbe  = $probeResults['/.git/HEAD'];
    $gitStatus = $gitProbe['status'];
    $gitUnknown = probe_is_unknown($gitProbe);
    $gitLeak = probe_exposes_sensitive_file('/.git/HEAD', $gitProbe, $baselineProbe);
    $gitCheckStatus = $gitUnknown ? 'unknown' : ($gitLeak ? 'fail' : 'pass');
    $gitMessageCode = $gitUnknown ? 'git.unknown' : ($gitLeak ? 'git.exposed' : 'git.safe');
    $add($G, 'git', 'Protección de repositorio .git', 10, $gitCheckStatus,
        $gitUnknown ? 'No se pudo verificar el directorio .git por un error de conexión.' : ($gitLeak ? '¡PELIGRO CRÍTICO! El directorio .git está expuesto públicamente, permitiendo reconstruir el código fuente del proyecto.' : 'El repositorio .git no es accesible públicamente (' . $gitStatus . ').'),
        $gitLeak ? "Bloquea el acceso al directorio .git:
Apache: RedirectMatch 404 /\\.git
Nginx: location ~ /\\.git { deny all; }" : '', 'critical', $gitMessageCode, ['status' => $gitStatus]);

    // Verificación de archivos de respaldo y configuración
    $backupPaths = ['/wp-config.php.bak', '/wp-config.old', '/backup.sql', '/dump.sql', '/db.sql', '/phpinfo.php'];
    $exposedFiles = [];
    $unknownFiles = [];
    foreach ($backupPaths as $bp) {
        $bpRes = $probeResults[$bp];
        if (probe_is_unknown($bpRes)) {
            $unknownFiles[] = $bp;
            continue;
        }
        if (probe_exposes_sensitive_file($bp, $bpRes, $baselineProbe)) {
            $exposedFiles[] = $bp;
        }
    }
    $backupStatus = $exposedFiles ? 'fail' : ($unknownFiles ? 'unknown' : 'pass');
    $backupMessageCode = $exposedFiles ? 'backups.exposed' : ($unknownFiles ? 'backups.unknown' : 'backups.safe');
    $backupDetail = $exposedFiles
        ? '¡PELIGRO! Archivos sensibles accesibles públicamente: ' . implode(', ', $exposedFiles) . '.'
        : ($unknownFiles ? 'No se pudieron verificar todos los archivos sensibles: ' . implode(', ', $unknownFiles) . '.' : 'No se encontraron archivos de respaldo o configuración expuestos.');
    $add($G, 'backups', 'Archivos de respaldo expuestos', 10, $backupStatus, $backupDetail,
        $exposedFiles ? "Elimina los archivos de respaldo del servidor público o bloquea su acceso:
Apache: <FilesMatch \"\\.(bak|old|sql)$\">
  Require all denied
</FilesMatch>
Nginx: location ~* \\.(bak|old|sql)$ { deny all; }" : '', 'critical', $backupMessageCode, [
            'files' => $exposedFiles,
            'unknown_files' => $unknownFiles,
        ]);

    /* --- Buenas prácticas --- */
    $G = 'Buenas prácticas';

    // security.txt (RFC 9116): Contact y Expires futuro son obligatorios.
    $secTxt = $probeResults['/.well-known/security.txt'];
    $secBody = $secTxt['body'] ?? '';
    preg_match('/^Expires:\s*(.+)$/im', $secBody, $expiresMatch);
    $expiresAt = isset($expiresMatch[1]) ? strtotime(trim($expiresMatch[1])) : false;
    $hasContact = (bool) preg_match('/^Contact:\s*\S+/im', $secBody);
    $secTxtOk = $secTxt['status'] === 200 && $hasContact && $expiresAt !== false && $expiresAt > time();
    $secTxtUnknown = probe_is_unknown($secTxt) || (!empty($secTxt['truncated']) && !$secTxtOk);
    $secTxtCode = $secTxtUnknown ? 'sectxt.unknown' : ($secTxtOk ? 'sectxt.valid' : 'sectxt.invalid');
    $add($G, 'sectxt', 'security.txt (RFC 9116)', 5, $secTxtUnknown ? 'unknown' : ($secTxtOk ? 'pass' : 'warn'),
        $secTxtUnknown ? 'No se pudo verificar por completo security.txt.' : ($secTxtOk ? 'security.txt tiene un contacto y una fecha de expiración vigente.' : 'security.txt debe incluir Contact y Expires con una fecha futura.'),
        ($secTxtUnknown || $secTxtOk) ? '' : "Crea /.well-known/security.txt con al menos:
Contact: mailto:seguridad@$host
Expires: " . gmdate('Y-m-d\\TH:i:s\\Z', time() + 365 * 86400) . "
Preferred-Languages: es, en", 'low', $secTxtCode, [
            'host' => $host,
            'expires' => gmdate('Y-m-d\\TH:i:s\\Z', time() + 365 * 86400),
        ]);

    // Verificación de CORS
    $corsOrigin = $corsProbe['headers']['access-control-allow-origin'][0] ?? $get('access-control-allow-origin');
    $corsCreds  = strtolower((string) ($corsProbe['headers']['access-control-allow-credentials'][0] ?? $get('access-control-allow-credentials')));
    $reflectedOrigin = trim((string) $corsOrigin) === 'https://gpc-audit.invalid';
    if ($corsOrigin === null) {
        $add($G, 'cors', 'Configuración CORS', 5, 'pass', 'No se envían cabeceras CORS: el navegador aplica la política de mismo origen por defecto.', '', 'medium', 'cors.none');
    } else {
        $corsWild = trim($corsOrigin) === '*';
        $corsCredsAllowed = $corsCreds === 'true';
        if ($reflectedOrigin && $corsCredsAllowed) {
            $add($G, 'cors', 'Configuración CORS', 5, 'fail',
                '¡PELIGRO! El servidor refleja un origen externo no confiable y permite credenciales: una web maliciosa podría leer respuestas autenticadas.',
                'Valida el encabezado Origin contra una lista explícita de orígenes permitidos; no lo reflejes automáticamente.', 'high', 'cors.reflected_credentials');
        } elseif ($reflectedOrigin) {
            $add($G, 'cors', 'Configuración CORS', 5, 'warn',
                'El servidor refleja un origen externo no confiable. Aunque no permite credenciales en esta respuesta, la política es demasiado amplia.',
                'Valida el encabezado Origin contra una lista explícita de orígenes permitidos.', 'medium', 'cors.reflected');
        } elseif ($corsWild && $corsCredsAllowed) {
            $add($G, 'cors', 'Configuración CORS', 5, 'fail',
                '¡PELIGRO! CORS permite cualquier origen (*) con credenciales: los navegadores rechazan esta combinación, pero indica una mala configuración que podría derivar en fugas de datos.',
                "Nunca combines Access-Control-Allow-Origin: * con Allow-Credentials: true.
Restringe el origen a los dominios autorizados:
Access-Control-Allow-Origin: https://tu-frontend.com", 'high', 'cors.wildcard_credentials');
        } elseif ($corsWild) {
            $add($G, 'cors', 'Configuración CORS', 5, 'warn',
                "CORS permite cualquier origen (Access-Control-Allow-Origin: *). Si el recurso es público (CDN, API abierta) esto es intencional, pero si maneja datos privados es un riesgo.",
                "Restringe a los dominios que necesiten acceso:
Access-Control-Allow-Origin: https://tu-app.com", 'medium', 'cors.wildcard');
        } else {
            $add($G, 'cors', 'Configuración CORS', 5, 'pass',
                'CORS restringe el acceso al origen: ' . $corsOrigin . '.', '', 'medium', 'cors.restricted', ['origin' => $corsOrigin]);
        }
    }

    // Evaluación de la suite de cifrado TLS
    $cipher = $tls['cipher'] ?? null;
    if ($cipher) {
        $weakCiphers = ['RC4', 'DES', '3DES', 'MD5', 'NULL', 'EXPORT', 'anon'];
        $isWeak = false;
        foreach ($weakCiphers as $wc) {
            if (stripos($cipher, $wc) !== false) {
                $isWeak = true;
                break;
            }
        }
        $isModern = (bool) preg_match('/(?:AES[_-]?(?:128|256)[_-]GCM|CHACHA20)/i', $cipher);
        if ($isWeak) {
            $add($G, 'cipher', 'Fortaleza del cifrado TLS', 5, 'fail',
                "Cifrado débil detectado: $cipher. Vulnerable a ataques criptográficos conocidos.",
                "Configura suites de cifrado modernas:
Nginx: ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305';
Apache: SSLCipherSuite ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384", 'high', 'cipher.weak', ['cipher' => $cipher]);
        } elseif ($isModern) {
            $add($G, 'cipher', 'Fortaleza del cifrado TLS', 5, 'pass',
                "Suite de cifrado moderna activa: $cipher.", '', 'medium', 'cipher.modern', ['cipher' => $cipher]);
        } else {
            $add($G, 'cipher', 'Fortaleza del cifrado TLS', 5, 'warn',
                "Cifrado aceptable pero no óptimo: $cipher. Considera priorizar AES-256-GCM o ChaCha20.",
                "Prioriza suites modernas en tu configuración SSL:
ssl_prefer_server_ciphers on;
ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305';", 'medium', 'cipher.acceptable', ['cipher' => $cipher]);
        }
    } else {
        $add($G, 'cipher', 'Fortaleza del cifrado TLS', 5, 'unknown',
            'No se pudo determinar la suite de cifrado negociada.', '', 'medium', 'cipher.unavailable');
    }

    /* --- Seguridad DNS y Correo --- */
    $G = 'Seguridad DNS y Correo';
    $dnsQueries = dns_queries_doh([
        'spf' => [$host, 'TXT'],
        'dmarc' => ['_dmarc.' . $host, 'TXT'],
        'caa' => [$host, 'CAA'],
    ], $deadline);

    // SPF: RFC 7208 permite un único registro por dominio.
    $txtQuery = $dnsQueries['spf'];
    $txtRecords = $txtQuery['records'];
    $spfRecords = array_values(array_filter($txtRecords, fn($rec) => stripos($rec, 'v=spf1') === 0));
    if (!$txtQuery['ok']) {
        $add($G, 'spf', 'Registro SPF (Antisuplantación)', 5, 'unknown',
            'No se pudo consultar el registro SPF por un error del servicio DNS.', '', 'medium', 'spf.unknown');
    } elseif (count($spfRecords) > 1) {
        $add($G, 'spf', 'Registro SPF (Antisuplantación)', 5, 'fail',
            'Hay varios registros SPF; los servidores receptores pueden devolver PermError y no aplicar la política.',
            'Consolida todos los mecanismos autorizados en un único registro TXT SPF.', 'medium', 'spf.multiple', ['count' => count($spfRecords)]);
    } elseif ($spfRecords) {
        $spf = $spfRecords[0];
        preg_match('/(?:^|\s)([+~?\-])?all(?:\s|$)/i', $spf, $allMatch);
        $qualifier = $allMatch[1] ?? '+';
        $status = $qualifier === '-' ? 'pass' : ($qualifier === '~' ? 'warn' : 'fail');
        $detail = $qualifier === '-' ? "SPF estricto activo: $spf" : ($qualifier === '~' ? "SPF en softfail: $spf" : "SPF permisivo o incompleto: $spf");
        $fix = $qualifier === '-' ? '' : "Después de verificar todos los servicios que envían correo, finaliza con -all:
v=spf1 mx include:tu-proveedor.example -all";
        $spfCode = $qualifier === '-' ? 'spf.strict' : ($qualifier === '~' ? 'spf.softfail' : 'spf.permissive');
        $add($G, 'spf', 'Registro SPF (Antisuplantación)', 5, $status, $detail, $fix, 'medium', $spfCode, ['record' => $spf]);
    } else {
        $add($G, 'spf', 'Registro SPF (Antisuplantación)', 5, 'warn',
            'No se encontró registro SPF en el dominio: terceros podrían falsificar envíos de correo en su nombre.',
            "Publica un registro TXT en el dominio, por ejemplo:
v=spf1 mx ~all
(o incluye tu proveedor de correo, ej. include:_spf.google.com).", 'medium', 'spf.missing', ['host' => $host]);
    }

    // DMARC: valida política y porcentaje de aplicación.
    $dmarcQuery = $dnsQueries['dmarc'];
    $dmarcRecords = $dmarcQuery['records'];
    $dmarc = null;
    foreach ($dmarcRecords as $rec) {
        if (stripos($rec, 'v=DMARC1') === 0) {
            $dmarc = $rec;
            break;
        }
    }
    if (!$dmarcQuery['ok']) {
        $add($G, 'dmarc', 'Política DMARC (Protección de identidad)', 5, 'unknown',
            'No se pudo consultar DMARC por un error del servicio DNS.', '', 'medium', 'dmarc.unknown');
    } elseif ($dmarc) {
        preg_match('/(?:^|;)\s*p=([a-z]+)/i', $dmarc, $pm);
        preg_match('/(?:^|;)\s*pct=(\d{1,3})/i', $dmarc, $pctMatch);
        $policy = strtolower($pm[1] ?? 'none');
        $pct = min(100, (int) ($pctMatch[1] ?? 100));
        $enforced = in_array($policy, ['reject', 'quarantine'], true);
        $status = $enforced && $pct === 100 ? 'pass' : ($enforced ? 'warn' : 'fail');
        $detail = $enforced ? "DMARC p=$policy con aplicación al $pct%." : 'DMARC en modo monitoreo (p=none): no bloquea la suplantación de identidad.';
        $fix = $status === 'pass' ? '' : "Eleva gradualmente la aplicación hasta 100%:
v=DMARC1; p=quarantine; pct=100; rua=mailto:dmarc-reports@$host";
        $dmarcCode = $status === 'pass' ? 'dmarc.enforced' : ($enforced ? 'dmarc.partial' : 'dmarc.monitoring');
        $add($G, 'dmarc', 'Política DMARC (Protección de identidad)', 5, $status, $detail, $fix, 'medium', $dmarcCode, [
            'policy' => $policy,
            'percent' => $pct,
            'host' => $host,
        ]);
    } else {
        $add($G, 'dmarc', 'Política DMARC (Protección de identidad)', 5, 'warn',
            'Falta registro DMARC en _dmarc.' . $host . ': no hay directivas de autenticación para servidores receptores de correo.',
            "Publica un registro TXT en _dmarc.$host:
v=DMARC1; p=quarantine; rua=mailto:dmarc-reports@$host", 'medium', 'dmarc.missing', ['host' => $host]);
    }

    // CAA
    $caaQuery = $dnsQueries['caa'];
    $caaRecords = $caaQuery['records'];
    if (!$caaQuery['ok']) {
        $add($G, 'caa', 'Registros CAA (Autoridad de Certificación)', 5, 'unknown',
            'No se pudieron consultar los registros CAA por un error del servicio DNS.', '', 'low', 'caa.unknown');
    } elseif (!empty($caaRecords)) {
        $add($G, 'caa', 'Registros CAA (Autoridad de Certificación)', 5, 'pass',
            'Registros CAA configurados: restringe qué ACs pueden emitir certificados para este dominio (' . implode(', ', array_slice($caaRecords, 0, 3)) . ').', '', 'low', 'caa.configured', [
                'records' => array_slice($caaRecords, 0, 3),
            ]);
    } else {
        $add($G, 'caa', 'Registros CAA (Autoridad de Certificación)', 5, 'warn',
            'Sin registros CAA: cualquier AC pública confiable en los navegadores podría emitir certificados para este dominio si fuera engañada.',
            "Añade registros CAA en tu zona DNS para autorizar únicamente a tu proveedor:
0 issue \"letsencrypt.org\"
0 issuewild \"letsencrypt.org\"", 'low', 'caa.missing');
    }

    /* --- Puntuación --- */
    $rating = calculate_rating($checks, $tlsOk);

    return [
        'schema_version' => 2,
        'host'      => $host,
        'ip'        => $main['ip'],
        'final_url' => $main['final_url'],
        'status'    => $main['status'],
        'ms'        => $main['ms'],
        'analysis_ms'=> (int) round((microtime(true) - $startedAt) * 1000),
        'score'     => $rating['score'],
        'grade'     => $rating['grade'],
        'coverage'  => $rating['coverage'],
        'risk'      => $rating['risk'],
        'summary'   => $rating['summary'],
        'priorities'=> $rating['priorities'],
        'checks'    => $checks,
        'tls'       => array_intersect_key($tls, array_flip(['protocol', 'cipher', 'issuer', 'subject', 'days_left', 'san_count', 'supported_protocols'])),
        'chain'     => $main['chain'],
        'at'        => gmdate('c'),
        'report_id' => 'GPC-' . gmdate('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4))),
        'lang'      => $lang,
    ];
}

/* ---------- Punto de entrada ---------- */

// Permite incluir las funciones desde pruebas sin ejecutar el endpoint HTTP.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== __FILE__) {
    return;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    fail(405, 'Método no permitido.');
}
$contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
if (!str_starts_with($contentType, 'application/json')) {
    fail(415, 'Content-Type debe ser application/json.');
}
if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 4096) {
    fail(413, 'Solicitud demasiado grande.');
}
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '' && parse_url($origin, PHP_URL_HOST) !== explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]) {
    fail(403, 'Origen no permitido.');
}
$body = json_decode((string) file_get_contents('php://input', false, null, 0, 4096), true);
if (!is_array($body) || !isset($body['url']) || !is_string($body['url'])) {
    fail(400, 'Solicitud inválida.');
}

set_time_limit(60);
rate_limit(client_ip());

try {
    $target = parse_target($body['url']);
    target_rate_limit($target['host']);
    $lang = isset($body['lang']) && is_string($body['lang']) ? $body['lang'] : 'es';
    if (!in_array($lang, SUPPORTED_LANGS, true)) {
        fail(400, 'Idioma no admitido.');
    }
    echo json_encode(audit($body['url'], $lang), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (RuntimeException $e) {
    fail(422, $e->getMessage());
} catch (Throwable $e) {
    error_log('auditor: ' . $e->getMessage());
    fail(500, 'Error interno al analizar el sitio.');
}
