<?php
declare(strict_types=1);

/* ---------- Validación de destino (anti-SSRF) ---------- */

/** Normaliza y valida la entrada. Solo http/https, puertos 80/443, sin credenciales. */
function parse_target(string $input): array
{
    $input = trim($input);
    if ($input === '' || strlen($input) > 2048) {
        throw new RuntimeException('Ingresa una URL o dominio.');
    }
    if (!preg_match('#^https?://#i', $input)) {
        $input = 'https://' . $input;
    }
    $p = parse_url($input);
    if (!$p || empty($p['host']) || isset($p['user']) || isset($p['pass'])) {
        throw new RuntimeException('La URL no es válida.');
    }
    $scheme = strtolower($p['scheme'] ?? 'https');
    $defaultPort = $scheme === 'https' ? 443 : 80;
    if (isset($p['port']) && (int) $p['port'] !== $defaultPort) {
        throw new RuntimeException('Solo se permite HTTP en el puerto 80 y HTTPS en el puerto 443.');
    }
    $port = (int) ($p['port'] ?? $defaultPort);
    $host = strtolower(rtrim($p['host'], '.'));
    if (function_exists('idn_to_ascii') && preg_match('/[^\x20-\x7e]/', $host)) {
        $host = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46) ?: $host;
    }
    $isIp = (bool) filter_var($host, FILTER_VALIDATE_IP);
    if ($isIp && str_contains($host, ':')) {
        throw new RuntimeException('Usa un dominio para destinos IPv6; se resolverá de forma segura.');
    }
    if (!$isIp && (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) || !str_contains($host, '.'))) {
        throw new RuntimeException('El dominio no es válido.');
    }
    $path = ($p['path'] ?? '') === '' ? '/' : $p['path'];
    if (isset($p['query'])) {
        $path .= '?' . $p['query'];
    }
    return ['scheme' => $scheme, 'host' => $host, 'port' => $port, 'path' => $path];
}

/** Formatea un host para usarlo dentro de una URL, incluido IPv6. */
function url_host(string $host): string
{
    return str_contains($host, ':') ? '[' . $host . ']' : $host;
}

/** Formatea una dirección para stream_socket_client. */
function socket_endpoint(string $ip, int $port = 443): string
{
    return 'ssl://' . (str_contains($ip, ':') ? '[' . $ip . ']' : $ip) . ':' . $port;
}

/** Ajusta un timeout individual al presupuesto restante del análisis. */
function timeout_for_deadline(?float $deadline, int $maximum): int
{
    if ($deadline === null) {
        return $maximum;
    }
    return max(0, min($maximum, (int) ceil($deadline - microtime(true))));
}

/** Resuelve A y AAAA y exige que TODAS las direcciones sean públicas. */
function resolve_public(string $host): string
{
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $ips = [$host];
    } else {
        $records = @dns_get_record($host, DNS_A | DNS_AAAA) ?: [];
        $ips = [];
        foreach ($records as $record) {
            if (isset($record['ip'])) {
                $ips[] = $record['ip'];
            } elseif (isset($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }
    }
    $ips = array_values(array_unique($ips));
    if (!$ips) {
        throw new RuntimeException('No se pudo resolver el dominio.');
    }
    foreach ($ips as $ip) {
        $public = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        $long = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? ip2long($ip) : false;
        $cgnat = $long !== false && ($long >> 22) === (ip2long('100.64.0.0') >> 22);
        if (!$public || $cgnat) {
            throw new RuntimeException('El destino apunta a una red privada o reservada; no se analiza.');
        }
    }
    return $ips[0];
}

