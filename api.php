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

/** Consulta registros DNS vía DoH (Cloudflare) con timeout estricto. */
function dns_query_doh(string $name, string $type): array
{
    $url = 'https://cloudflare-dns.com/dns-query?name=' . urlencode($name) . '&type=' . urlencode($type);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/dns-json'],
        CURLOPT_TIMEOUT        => 3,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_USERAGENT      => USER_AGENT,
    ]);
    $res = curl_exec($ch);
    $err = curl_errno($ch);
    curl_close($ch);
    if ($err !== 0 || !$res) {
        return [];
    }
    $json = json_decode((string) $res, true);
    if (!isset($json['Answer']) || !is_array($json['Answer'])) {
        return [];
    }
    $records = [];
    foreach ($json['Answer'] as $ans) {
        if (isset($ans['data'])) {
            $records[] = trim((string) $ans['data'], '"');
        }
    }
    return $records;
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
function probe_paths_parallel(string $host, string $ip, array $paths, bool $https = true): array
{
    $port   = $https ? 443 : 80;
    $scheme = $https ? 'https' : 'http';
    $mh     = curl_multi_init();
    $handles = [];
    $results = [];

    foreach ($paths as $path) {
        $url     = $scheme . '://' . url_host($host) . $path;
        $hdr     = [];
        $body    = '';
        $status  = 0;
        $ch      = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RESOLVE        => [$host . ':' . $port . ':' . $ip],
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_TIMEOUT        => 4,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => USER_AGENT,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_NOBODY         => false,
            CURLOPT_MAXFILESIZE    => 2048,
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[(int) $ch] = ['ch' => $ch, 'path' => $path];
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh, 0.5);
    } while ($running > 0);

    foreach ($handles as $info) {
        $ch   = $info['ch'];
        $path = $info['path'];
        $raw  = (string) curl_multi_getcontent($ch);
        $hSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerBlock = substr($raw, 0, $hSize);
        $body = substr($raw, $hSize, 1024);

        $headers = [];
        foreach (explode("\r
", $headerBlock) as $line) {
            $line = trim($line);
            if ($line !== '' && str_contains($line, ':') && stripos($line, 'HTTP/') !== 0) {
                [$k, $v] = explode(':', $line, 2);
                $headers[strtolower(trim($k))] = trim($v);
            }
        }

        $results[$path] = ['status' => $status, 'headers' => $headers, 'body' => $body];
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }

    curl_multi_close($mh);
    return $results;
}

/* ---------- Auditoría ---------- */

/** Ordena las correcciones de mayor impacto y resume el riesgo sin persistir resultados. */
function summarize_checks(array $checks, bool $tlsOk): array
{
    $priorities = array_values(array_filter($checks, fn($check) => $check['status'] !== 'pass'));
    usort($priorities, function (array $a, array $b): int {
        $severity = ['fail' => 2, 'warn' => 1];
        return ($severity[$b['status']] <=> $severity[$a['status']]) ?: ($b['weight'] <=> $a['weight']);
    });
    $counts = ['pass' => 0, 'warn' => 0, 'fail' => 0];
    foreach ($checks as $check) {
        $counts[$check['status']]++;
    }
    $risk = !$tlsOk || $counts['fail'] >= 3 ? 'alto' : ($counts['fail'] > 0 || $counts['warn'] >= 4 ? 'medio' : 'bajo');
    return ['risk' => $risk, 'summary' => ['total' => count($checks)] + $counts, 'priorities' => array_slice($priorities, 0, 3)];
}

function audit(string $input, string $lang = 'es'): array
{
    $t    = parse_target($input);
    $host = $t['host'];
    $ip   = resolve_public($host);
    $tls  = tls_probe($host, $ip);
    $tlsOk = $tls['ok'] && $tls['valid'];

    $main = fetch_follow(($tls['ok'] ? 'https' : 'http') . '://' . url_host($host) . $t['path'], $ip, $tlsOk);

    // Comprueba CORS con un origen ajeno controlado; detecta políticas que reflejan Origin.
    try {
        $corsProbe = fetch_once($main['final_url'], ['Origin: https://gpc-audit.invalid'], $main['ip'], $tlsOk);
    } catch (RuntimeException $e) {
        $corsProbe = ['headers' => []];
    }

    // ¿http:// redirige a https://?
    $plain = null;
    try {
        $plain = fetch_once('http://' . url_host($host) . '/');
    } catch (RuntimeException $e) {
        $plain = null;
    }

    $H          = $main['headers'];
    $get        = fn(string $n) => $H[$n][0] ?? null;
    $finalHttps = str_starts_with($main['final_url'], 'https://');

    $checks = [];
    $add = function (string $group, string $id, string $title, int $w, string $status, string $detail, string $fix = '') use (&$checks) {
        $earned   = $status === 'pass' ? $w : ($status === 'warn' ? (int) round($w / 2) : 0);
        $checks[] = compact('group', 'id', 'title', 'status', 'detail', 'fix') + ['weight' => $w, 'earned' => $earned];
    };

    /* --- Transporte --- */
    $G = 'Transporte';
    if ($tlsOk) {
        $add($G, 'cert', 'Certificado válido', 20, 'pass', 'Certificado de confianza para ' . $host . ', emitido por ' . $tls['issuer'] . '.');
    } elseif ($tls['ok']) {
        $add($G, 'cert', 'Certificado válido', 20, 'fail', 'El certificado no es de confianza: ' . $tls['error'] . '.',
            "Instala un certificado válido que cubra el dominio exacto y envía la cadena intermedia completa.
Let's Encrypt es gratuito: certbot --apache -d $host");
    } else {
        $add($G, 'cert', 'Certificado válido', 20, 'fail', 'No hay HTTPS disponible en el puerto 443.',
            "Activa HTTPS con un certificado (Let's Encrypt) y redirige todo el tráfico HTTP a HTTPS.");
    }

    if ($tls['ok'] && isset($tls['days_left'])) {
        $d = $tls['days_left'];
        $s = $d < 0 ? 'fail' : ($d < 30 ? 'warn' : 'pass');
        $add($G, 'expiry', 'Vigencia del certificado', 5, $s,
            $d < 0 ? 'El certificado expiró hace ' . abs($d) . ' días.' : "Vence en $d días.",
            $s === 'pass' ? '' : 'Renueva el certificado y automatiza la renovación (certbot renew en cron o systemd timer).');
    } else {
        $add($G, 'expiry', 'Vigencia del certificado', 5, 'fail', 'No hay certificado que evaluar.');
    }

    $proto = $tls['protocol'] ?? null;
    if ($proto) {
        $ok = in_array($proto, ['TLSv1.2', 'TLSv1.3'], true);
        $add($G, 'tls', 'Versión de TLS', 5, $ok ? 'pass' : 'fail', 'Negociado: ' . $proto . (isset($tls['cipher']) ? ' (' . $tls['cipher'] . ')' : '') . '.',
            $ok ? '' : "Desactiva TLS 1.0/1.1.
Nginx: ssl_protocols TLSv1.2 TLSv1.3;
Apache: SSLProtocol -all +TLSv1.2 +TLSv1.3");
    } else {
        $add($G, 'tls', 'Versión de TLS', 5, 'fail', 'No se pudo negociar TLS.');
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
            'Ejecuta la herramienta con PHP/OpenSSL actualizado o verifica el servidor con testssl.sh.');
    } elseif ($legacyProtocols) {
        $add($G, 'legacy_tls', 'Protocolos TLS heredados', 5, 'fail',
            'El servidor aún acepta ' . implode(' y ', $legacyProtocols) . '.',
            "Desactiva TLS 1.0/1.1.
Nginx: ssl_protocols TLSv1.2 TLSv1.3;
Apache: SSLProtocol -all +TLSv1.2 +TLSv1.3");
    } else {
        $checked = implode(', ', array_keys($testedProtocols));
        $add($G, 'legacy_tls', 'Protocolos TLS heredados', 5, 'pass',
            'No se aceptaron protocolos heredados en las pruebas disponibles (' . $checked . ').');
    }

    if ($plain === null) {
        $add($G, 'redirect', 'Redirección HTTP → HTTPS', 10, 'pass', 'El puerto 80 no responde; el sitio solo se sirve por HTTPS.');
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
return 301 https://\$host\$request_uri;");
    }

    $hsts = $get('strict-transport-security');
    $hstsFix = "Apache: Header always set Strict-Transport-Security \"max-age=31536000; includeSubDomains\"
Nginx: add_header Strict-Transport-Security \"max-age=31536000; includeSubDomains\" always;";
    preg_match('/max-age=(\d+)/i', (string) $hsts, $m);
    $hstsAge = (int) ($m[1] ?? 0);
    $hstsSubdomains = $hsts && stripos($hsts, 'includesubdomains') !== false;
    $hstsPreload = $hsts && stripos($hsts, 'preload') !== false;
    $hstsBase = $finalHttps && $hsts && $hstsAge >= 15552000;
    $add($G, 'hsts_max_age', 'HSTS: vigencia', 8, $hstsBase ? 'pass' : 'fail',
        $hstsBase ? "max-age=$hstsAge: los navegadores forzarán HTTPS." : (!$finalHttps ? 'La respuesta final no usa HTTPS.' : ($hsts ? "max-age=$hstsAge es inferior a 6 meses." : 'Falta Strict-Transport-Security.')),
        $hstsBase ? '' : "Usa al menos max-age=15552000; idealmente 31536000.
" . $hstsFix);
    $add($G, 'hsts_subdomains', 'HSTS: subdominios', 4, $hstsSubdomains ? 'pass' : 'warn',
        $hstsSubdomains ? 'includeSubDomains protege también los subdominios.' : 'No incluye subdominios; uno inseguro podría debilitar la protección del dominio.',
        $hstsSubdomains ? '' : $hstsFix);
    $add($G, 'hsts_preload', 'HSTS: preparación para preload', 3, $hstsPreload ? 'pass' : 'warn',
        $hstsPreload ? 'La directiva preload está presente.' : 'No incluye preload. Es opcional, pero aporta protección desde la primera visita si todo el dominio soporta HTTPS.',
        $hstsPreload ? '' : 'Solo cuando todos los subdominios usen HTTPS: añade preload y registra el dominio en hstspreload.org.');

    /* --- Cabeceras --- */
    $G    = 'Cabeceras de seguridad';
    $dirs = [];
    $csp  = $get('content-security-policy');
    $cspFix = "Empieza con una política mínima y ajústala:
Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'";
    if (!$csp) {
        $add($G, 'csp', 'Content-Security-Policy', 15, 'fail', 'No hay CSP: nada limita qué scripts pueden ejecutarse (mitigación clave contra XSS).', $cspFix);
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
        } else {
            $strict = preg_grep("/^'(nonce-|sha(256|384|512)-)/", $script) || in_array("'strict-dynamic'", $script, true);
            foreach (["'unsafe-inline'", "'unsafe-eval'", '*', 'data:', 'http:'] as $bad) {
                if (in_array($bad, $script, true) && !($bad === "'unsafe-inline'" && $strict)) {
                    $issues[] = "permite $bad en scripts";
                }
            }
        }
        $add($G, 'csp', 'Content-Security-Policy', 15, $issues ? 'warn' : 'pass',
            $issues ? 'CSP presente pero débil: ' . implode('; ', $issues) . '.' : 'CSP presente y restringe la ejecución de scripts.',
            $issues ? "Elimina 'unsafe-inline'/'unsafe-eval' usando nonces o hashes, y evita comodines en script-src." : '');
    }

    $xfo = strtolower((string) $get('x-frame-options'));
    $ok  = in_array($xfo, ['deny', 'sameorigin'], true) || isset($dirs['frame-ancestors']);
    $add($G, 'framing', 'Protección contra clickjacking', 5, $ok ? 'pass' : 'fail',
        $ok ? 'El sitio controla en qué páginas puede incrustarse.' : 'Cualquier sitio puede incrustar esta página en un iframe.',
        $ok ? '' : "Header always set X-Frame-Options \"SAMEORIGIN\"
(o frame-ancestors 'self' dentro de la CSP)");

    $ok = strtolower((string) $get('x-content-type-options')) === 'nosniff';
    $add($G, 'nosniff', 'X-Content-Type-Options', 5, $ok ? 'pass' : 'fail',
        $ok ? 'nosniff activo.' : 'Falta nosniff: el navegador puede adivinar tipos MIME.',
        $ok ? '' : 'Header always set X-Content-Type-Options "nosniff"');

    $rp = strtolower((string) $get('referrer-policy'));
    if ($rp === '') {
        $add($G, 'referrer', 'Referrer-Policy', 5, 'warn', 'No definida; se usa el valor por defecto del navegador.', 'Header always set Referrer-Policy "strict-origin-when-cross-origin"');
    } else {
        $ok = in_array($rp, ['no-referrer', 'same-origin', 'strict-origin', 'strict-origin-when-cross-origin', 'origin-when-cross-origin'], true);
        $add($G, 'referrer', 'Referrer-Policy', 5, $ok ? 'pass' : 'fail', "Valor: $rp.",
            $ok ? '' : 'Header always set Referrer-Policy "strict-origin-when-cross-origin"');
    }

    $pp = $get('permissions-policy');
    $add($G, 'permissions', 'Permissions-Policy', 5, $pp ? 'pass' : 'warn',
        $pp ? 'Restringe el acceso a funciones del navegador.' : 'No definida: cámara, micrófono y geolocalización no están restringidos explícitamente.',
        $pp ? '' : 'Header always set Permissions-Policy "camera=(), microphone=(), geolocation=()"');

    $coop = strtolower((string) $get('cross-origin-opener-policy'));
    $okCoop = in_array($coop, ['same-origin', 'same-origin-allow-popups'], true);
    $add($G, 'coop', 'Cross-Origin-Opener-Policy (COOP)', 5, $okCoop ? 'pass' : 'warn',
        $okCoop ? "Aislamiento activo ($coop): previene ataques como Spectre y fugas cross-window." : ($coop ? "Valor permisivo: $coop." : 'No definida: otras pestañas maliciosas podrían interactuar con la ventana.'),
        $okCoop ? '' : 'Header always set Cross-Origin-Opener-Policy "same-origin"');

    $corp = strtolower((string) $get('cross-origin-resource-policy'));
    $okCorp = in_array($corp, ['same-origin', 'same-site'], true);
    $add($G, 'corp', 'Cross-Origin-Resource-Policy (CORP)', 5, $okCorp ? 'pass' : 'warn',
        $okCorp ? "CORP activo ($corp): restringe la carga de recursos a este origen." : ($corp ? "Valor actual: $corp." : 'No definida: sitios externos podrían embeber tus recursos multimedia o scripts.'),
        $okCorp ? '' : 'Header always set Cross-Origin-Resource-Policy "same-origin"');

    /* --- Exposición y Archivos Sensibles --- */
    $G       = 'Cookies y archivos sensibles';
    $cookies = $H['set-cookie'] ?? [];
    if (!$cookies) {
        $add($G, 'cookies', 'Cookies', 5, 'pass', 'La respuesta no establece cookies.');
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
WordPress/otros: define los atributos al emitir la cookie.");
    }

    $leaks = [];
    $srv   = $get('server');
    if ($srv && preg_match('/\d+\.\d+/', $srv)) { $leaks[] = "Server: $srv"; }
    if ($get('x-powered-by'))                   { $leaks[] = 'X-Powered-By: ' . $get('x-powered-by'); }
    $add($G, 'leak', 'Divulgación de tecnología', 5, $leaks ? 'warn' : 'pass',
        $leaks ? 'Se exponen versiones: ' . implode('; ', $leaks) . '.' : 'No se exponen versiones de servidor ni de lenguaje.',
        $leaks ? "PHP (php.ini): expose_php = Off
Apache: ServerTokens Prod
Nginx: server_tokens off;" : '');

    // Sondeo paralelo de todos los archivos sensibles y security.txt
    $probePaths = ['/.env', '/.git/HEAD', '/wp-config.php.bak', '/wp-config.old', '/backup.sql', '/dump.sql', '/db.sql', '/phpinfo.php', '/.well-known/security.txt'];
    $probeResults = probe_paths_parallel($host, $ip, $probePaths, $tlsOk);

    // Helper para detectar si la respuesta es una página HTML genérica (404 personalizada)
    $isHtmlResponse = function(array $res): bool {
        $ct = strtolower($res['headers']['content-type'] ?? '');
        $b  = $res['body'];
        return str_contains($ct, 'html') || stripos($b, '<!doctype') !== false || stripos($b, '<html') !== false;
    };

    // Verificación de exposición de .env
    $envProbe  = $probeResults['/.env'];
    $envStatus = $envProbe['status'];
    $envBody   = $envProbe['body'];
    $envLeak   = $envStatus === 200 && !$isHtmlResponse($envProbe) && (
        preg_match('/^[A-Z0-9_]{2,}\s*=/m', $envBody) ||
        str_contains($envBody, 'APP_KEY') ||
        str_contains($envBody, 'DB_PASSWORD') ||
        str_contains($envBody, 'SECRET')
    );
    $add($G, 'env', 'Protección de archivo .env', 10, $envLeak ? 'fail' : 'pass',
        $envLeak ? '¡PELIGRO CRÍTICO! El archivo .env está expuesto públicamente, filtrando credenciales y claves de la aplicación.' : 'El archivo .env no es accesible públicamente (' . ($envStatus ?: 'bloqueado') . ').',
        $envLeak ? "Bloquea el acceso a archivos dotfiles en tu servidor web:
Apache: <Files \".*\">
  Require all denied
</Files>
Nginx: location ~ /\\. { deny all; }" : '');

    // Verificación de exposición de .git
    $gitProbe  = $probeResults['/.git/HEAD'];
    $gitStatus = $gitProbe['status'];
    $gitBody   = $gitProbe['body'];
    $gitLeak   = $gitStatus === 200 && (str_starts_with(trim($gitBody), 'ref: refs/') || preg_match('/^[0-9a-f]{40}$/i', trim($gitBody)));
    $add($G, 'git', 'Protección de repositorio .git', 10, $gitLeak ? 'fail' : 'pass',
        $gitLeak ? '¡PELIGRO CRÍTICO! El directorio .git está expuesto públicamente, permitiendo reconstruir el código fuente del proyecto.' : 'El repositorio .git no es accesible públicamente (' . ($gitStatus ?: 'bloqueado') . ').',
        $gitLeak ? "Bloquea el acceso al directorio .git:
Apache: RedirectMatch 404 /\\.git
Nginx: location ~ /\\.git { deny all; }" : '');

    // Verificación de archivos de respaldo y configuración
    $backupPaths = ['/wp-config.php.bak', '/wp-config.old', '/backup.sql', '/dump.sql', '/db.sql', '/phpinfo.php'];
    $exposedFiles = [];
    foreach ($backupPaths as $bp) {
        $bpRes = $probeResults[$bp];
        if ($bpRes['status'] === 200 && !$isHtmlResponse($bpRes)) {
            $exposedFiles[] = $bp;
        }
    }
    $add($G, 'backups', 'Archivos de respaldo expuestos', 10, $exposedFiles ? 'fail' : 'pass',
        $exposedFiles ? '¡PELIGRO! Archivos sensibles accesibles públicamente: ' . implode(', ', $exposedFiles) . '.' : 'No se encontraron archivos de respaldo o configuración expuestos.',
        $exposedFiles ? "Elimina los archivos de respaldo del servidor público o bloquea su acceso:
Apache: <FilesMatch \"\\.(bak|old|sql)$\">
  Require all denied
</FilesMatch>
Nginx: location ~* \\.(bak|old|sql)$ { deny all; }" : '');

    /* --- Buenas prácticas --- */
    $G = 'Buenas prácticas';

    // security.txt (RFC 9116): Contact y Expires futuro son obligatorios.
    $secTxt = $probeResults['/.well-known/security.txt'];
    $secBody = $secTxt['body'] ?? '';
    preg_match('/^Expires:\s*(.+)$/im', $secBody, $expiresMatch);
    $expiresAt = isset($expiresMatch[1]) ? strtotime(trim($expiresMatch[1])) : false;
    $hasContact = (bool) preg_match('/^Contact:\s*\S+/im', $secBody);
    $secTxtOk = $secTxt['status'] === 200 && $hasContact && $expiresAt !== false && $expiresAt > time();
    $add($G, 'sectxt', 'security.txt (RFC 9116)', 5, $secTxtOk ? 'pass' : 'warn',
        $secTxtOk ? 'security.txt tiene un contacto y una fecha de expiración vigente.' : 'security.txt debe incluir Contact y Expires con una fecha futura.',
        $secTxtOk ? '' : "Crea /.well-known/security.txt con al menos:
Contact: mailto:seguridad@$host
Expires: " . gmdate('Y-m-d\\TH:i:s\\Z', time() + 365 * 86400) . "
Preferred-Languages: es, en");

    // Verificación de CORS
    $corsOrigin = $corsProbe['headers']['access-control-allow-origin'][0] ?? $get('access-control-allow-origin');
    $corsCreds  = strtolower((string) ($corsProbe['headers']['access-control-allow-credentials'][0] ?? $get('access-control-allow-credentials')));
    $reflectedOrigin = trim((string) $corsOrigin) === 'https://gpc-audit.invalid';
    if ($corsOrigin === null) {
        $add($G, 'cors', 'Configuración CORS', 5, 'pass', 'No se envían cabeceras CORS: el navegador aplica la política de mismo origen por defecto.');
    } else {
        $corsWild = trim($corsOrigin) === '*';
        $corsCredsAllowed = $corsCreds === 'true';
        if ($reflectedOrigin && $corsCredsAllowed) {
            $add($G, 'cors', 'Configuración CORS', 5, 'fail',
                '¡PELIGRO! El servidor refleja un origen externo no confiable y permite credenciales: una web maliciosa podría leer respuestas autenticadas.',
                'Valida el encabezado Origin contra una lista explícita de orígenes permitidos; no lo reflejes automáticamente.');
        } elseif ($reflectedOrigin) {
            $add($G, 'cors', 'Configuración CORS', 5, 'warn',
                'El servidor refleja un origen externo no confiable. Aunque no permite credenciales en esta respuesta, la política es demasiado amplia.',
                'Valida el encabezado Origin contra una lista explícita de orígenes permitidos.');
        } elseif ($corsWild && $corsCredsAllowed) {
            $add($G, 'cors', 'Configuración CORS', 5, 'fail',
                '¡PELIGRO! CORS permite cualquier origen (*) con credenciales: los navegadores rechazan esta combinación, pero indica una mala configuración que podría derivar en fugas de datos.',
                "Nunca combines Access-Control-Allow-Origin: * con Allow-Credentials: true.
Restringe el origen a los dominios autorizados:
Access-Control-Allow-Origin: https://tu-frontend.com");
        } elseif ($corsWild) {
            $add($G, 'cors', 'Configuración CORS', 5, 'warn',
                "CORS permite cualquier origen (Access-Control-Allow-Origin: *). Si el recurso es público (CDN, API abierta) esto es intencional, pero si maneja datos privados es un riesgo.",
                "Restringe a los dominios que necesiten acceso:
Access-Control-Allow-Origin: https://tu-app.com");
        } else {
            $add($G, 'cors', 'Configuración CORS', 5, 'pass',
                'CORS restringe el acceso al origen: ' . $corsOrigin . '.');
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
        $isModern = (stripos($cipher, 'AES_256_GCM') !== false || stripos($cipher, 'CHACHA20') !== false);
        if ($isWeak) {
            $add($G, 'cipher', 'Fortaleza del cifrado TLS', 5, 'fail',
                "Cifrado débil detectado: $cipher. Vulnerable a ataques criptográficos conocidos.",
                "Configura suites de cifrado modernas:
Nginx: ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305';
Apache: SSLCipherSuite ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384");
        } elseif ($isModern) {
            $add($G, 'cipher', 'Fortaleza del cifrado TLS', 5, 'pass',
                "Suite de cifrado moderna activa: $cipher.");
        } else {
            $add($G, 'cipher', 'Fortaleza del cifrado TLS', 5, 'warn',
                "Cifrado aceptable pero no óptimo: $cipher. Considera priorizar AES-256-GCM o ChaCha20.",
                "Prioriza suites modernas en tu configuración SSL:
ssl_prefer_server_ciphers on;
ssl_ciphers 'ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305';");
        }
    }

    /* --- Seguridad DNS y Correo --- */
    $G = 'Seguridad DNS y Correo';

    // SPF: RFC 7208 permite un único registro por dominio.
    $txtRecords = dns_query_doh($host, 'TXT');
    $spfRecords = array_values(array_filter($txtRecords, fn($rec) => stripos($rec, 'v=spf1') === 0));
    if (count($spfRecords) > 1) {
        $add($G, 'spf', 'Registro SPF (Antisuplantación)', 5, 'fail',
            'Hay varios registros SPF; los servidores receptores pueden devolver PermError y no aplicar la política.',
            'Consolida todos los mecanismos autorizados en un único registro TXT SPF.');
    } elseif ($spfRecords) {
        $spf = $spfRecords[0];
        preg_match('/(?:^|\s)([+~?\-])?all(?:\s|$)/i', $spf, $allMatch);
        $qualifier = $allMatch[1] ?? '+';
        $status = $qualifier === '-' ? 'pass' : ($qualifier === '~' ? 'warn' : 'fail');
        $detail = $qualifier === '-' ? "SPF estricto activo: $spf" : ($qualifier === '~' ? "SPF en softfail: $spf" : "SPF permisivo o incompleto: $spf");
        $fix = $qualifier === '-' ? '' : "Después de verificar todos los servicios que envían correo, finaliza con -all:
v=spf1 mx include:tu-proveedor.example -all";
        $add($G, 'spf', 'Registro SPF (Antisuplantación)', 5, $status, $detail, $fix);
    } else {
        $add($G, 'spf', 'Registro SPF (Antisuplantación)', 5, 'warn',
            'No se encontró registro SPF en el dominio: terceros podrían falsificar envíos de correo en su nombre.',
            "Publica un registro TXT en el dominio, por ejemplo:
v=spf1 mx ~all
(o incluye tu proveedor de correo, ej. include:_spf.google.com).");
    }

    // DMARC: valida política y porcentaje de aplicación.
    $dmarcRecords = dns_query_doh('_dmarc.' . $host, 'TXT');
    $dmarc = null;
    foreach ($dmarcRecords as $rec) {
        if (stripos($rec, 'v=DMARC1') === 0) {
            $dmarc = $rec;
            break;
        }
    }
    if ($dmarc) {
        preg_match('/(?:^|;)\s*p=([a-z]+)/i', $dmarc, $pm);
        preg_match('/(?:^|;)\s*pct=(\d{1,3})/i', $dmarc, $pctMatch);
        $policy = strtolower($pm[1] ?? 'none');
        $pct = min(100, (int) ($pctMatch[1] ?? 100));
        $enforced = in_array($policy, ['reject', 'quarantine'], true);
        $status = $enforced && $pct === 100 ? 'pass' : ($enforced ? 'warn' : 'fail');
        $detail = $enforced ? "DMARC p=$policy con aplicación al $pct%." : 'DMARC en modo monitoreo (p=none): no bloquea la suplantación de identidad.';
        $fix = $status === 'pass' ? '' : "Eleva gradualmente la aplicación hasta 100%:
v=DMARC1; p=quarantine; pct=100; rua=mailto:dmarc-reports@$host";
        $add($G, 'dmarc', 'Política DMARC (Protección de identidad)', 5, $status, $detail, $fix);
    } else {
        $add($G, 'dmarc', 'Política DMARC (Protección de identidad)', 5, 'warn',
            'Falta registro DMARC en _dmarc.' . $host . ': no hay directivas de autenticación para servidores receptores de correo.',
            "Publica un registro TXT en _dmarc.$host:
v=DMARC1; p=quarantine; rua=mailto:dmarc-reports@$host");
    }

    // CAA
    $caaRecords = dns_query_doh($host, 'CAA');
    if (!empty($caaRecords)) {
        $add($G, 'caa', 'Registros CAA (Autoridad de Certificación)', 5, 'pass',
            'Registros CAA configurados: restringe qué ACs pueden emitir certificados para este dominio (' . implode(', ', array_slice($caaRecords, 0, 3)) . ').');
    } else {
        $add($G, 'caa', 'Registros CAA (Autoridad de Certificación)', 5, 'warn',
            'Sin registros CAA: cualquier AC pública confiable en los navegadores podría emitir certificados para este dominio si fuera engañada.',
            "Añade registros CAA en tu zona DNS para autorizar únicamente a tu proveedor:
0 issue \"letsencrypt.org\"
0 issuewild \"letsencrypt.org\"");
    }

    /* --- Puntuación --- */
    $maxScore = array_sum(array_column($checks, 'weight'));
    $earnedScore = array_sum(array_column($checks, 'earned'));
    $score = $maxScore > 0 ? (int) round(($earnedScore / $maxScore) * 100) : 0;
    $grade = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 65 ? 'C' : ($score >= 50 ? 'D' : 'F')));
    if (!$tlsOk) {
        $grade = 'F'; // sin HTTPS confiable no hay nota aprobatoria
    }

    $summaryData = summarize_checks($checks, $tlsOk);

    return [
        'host'      => $host,
        'ip'        => $main['ip'],
        'final_url' => $main['final_url'],
        'status'    => $main['status'],
        'ms'        => $main['ms'],
        'score'     => $score,
        'grade'     => $grade,
        'risk'      => $summaryData['risk'],
        'summary'   => $summaryData['summary'],
        'priorities'=> $summaryData['priorities'],
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
