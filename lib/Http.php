<?php
declare(strict_types=1);

/* ---------- HTTP ---------- */

/** Una petición GET sin seguir redirecciones; se fija la IP validada y se descarta el cuerpo. */
function fetch_once(string $url, array $requestHeaders = [], ?string $pinnedIp = null, bool $verifyTls = false, ?float $deadline = null): array
{
    $timeout = timeout_for_deadline($deadline, 10);
    if ($timeout < 1) {
        throw new RuntimeException('Se agotó el tiempo total del análisis.');
    }
    $t     = parse_target($url);
    $https = $t['scheme'] === 'https';
    $port  = $t['port'];
    $ip    = $pinnedIp ?? resolve_public($t['host']);

    $headers = [];
    $status  = 0;
    $ch      = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RESOLVE        => [$t['host'] . ':' . $port . ':' . $ip],
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        CURLOPT_SSL_VERIFYPEER => $https && $verifyTls,
        CURLOPT_SSL_VERIFYHOST => ($https && $verifyTls) ? 2 : 0,
        CURLOPT_USERAGENT      => USER_AGENT,
        CURLOPT_HTTPHEADER     => array_merge(['Accept: text/html,*/*;q=0.8'], $requestHeaders),
        CURLOPT_HEADERFUNCTION => function ($ch, $line) use (&$headers, &$status) {
            $len  = strlen($line);
            $line = trim($line);
            if (stripos($line, 'HTTP/') === 0) {
                $headers = [];
                $status  = (int) (explode(' ', $line)[1] ?? 0);
            } elseif (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $headers[strtolower(trim($k))][] = trim($v);
            }
            return $len;
        },
        CURLOPT_WRITEFUNCTION  => fn($ch, $data) => 0, // no necesitamos el cuerpo
    ]);
    $t0 = microtime(true);
    curl_exec($ch);
    $errno = curl_errno($ch);
    $err   = curl_error($ch);
    curl_close($ch);

    if ($status === 0 || ($errno !== 0 && $errno !== 23)) { // 23 = abortamos la escritura a propósito
        throw new RuntimeException($errno === 28 ? 'Tiempo de espera agotado.' : 'No se pudo conectar (' . ($err ?: 'sin respuesta') . ').');
    }
    return ['status' => $status, 'headers' => $headers, 'ip' => $ip, 'ms' => (int) round((microtime(true) - $t0) * 1000)];
}

function absolutize(string $base, string $loc): string
{
    if (preg_match('#^https?://#i', $loc)) {
        return $loc;
    }
    $b      = parse_url($base);
    $origin = $b['scheme'] . '://' . url_host($b['host']) . (isset($b['port']) ? ':' . $b['port'] : '');
    if (str_starts_with($loc, '//')) {
        return $b['scheme'] . ':' . $loc;
    }
    if (str_starts_with($loc, '/')) {
        return $origin . $loc;
    }
    return $origin . rtrim(dirname($b['path'] ?? '/'), '/') . '/' . $loc;
}

/** Sigue hasta 5 redirecciones revalidando cada salto. */
function fetch_follow(string $url, ?string $firstIp = null, bool $verifyTls = false, ?float $deadline = null): array
{
    $chain = [];
    for ($i = 0; $i <= 5; $i++) {
        $r       = fetch_once($url, [], $i === 0 ? $firstIp : null, $verifyTls, $deadline);
        $chain[] = ['url' => $url, 'status' => $r['status']];
        $loc     = $r['headers']['location'][0] ?? null;
        if ($r['status'] >= 300 && $r['status'] < 400 && $loc) {
            $url = absolutize($url, $loc);
            parse_target($url); // valida esquema, puerto y host del siguiente salto
            continue;
        }
        return $r + ['chain' => $chain, 'final_url' => $url];
    }
    throw new RuntimeException('Demasiadas redirecciones.');
}

