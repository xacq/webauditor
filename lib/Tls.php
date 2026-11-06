<?php
declare(strict_types=1);

/* ---------- TLS ---------- */

/** Prueba explícitamente los protocolos que el runtime PHP/OpenSSL puede forzar. */
function tls_protocol_matrix(string $host, string $ip, ?float $deadline = null): array
{
    $methods = [
        'TLSv1.0' => 'STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT',
        'TLSv1.1' => 'STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT',
        'TLSv1.2' => 'STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT',
        'TLSv1.3' => 'STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT',
    ];
    $results = [];
    foreach ($methods as $label => $constant) {
        $timeout = timeout_for_deadline($deadline, 3);
        if ($timeout < 1) {
            $results[$label] = null;
            continue;
        }
        if (!defined($constant)) {
            $results[$label] = null;
            continue;
        }
        $ctx = stream_context_create(['ssl' => [
            'crypto_method' => constant($constant),
            'verify_peer' => false,
            'verify_peer_name' => false,
            'peer_name' => $host,
            'SNI_enabled' => true,
            'SNI_server_name' => $host,
        ]]);
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client(socket_endpoint($ip), $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
        $results[$label] = $fp !== false;
        if ($fp) {
            fclose($fp);
        }
    }
    return $results;
}

function tls_probe(string $host, string $ip, ?float $deadline = null): array
{
    $out = ['ok' => false, 'valid' => false, 'error' => null, 'supported_protocols' => []];
    foreach ([true, false] as $verify) {
        $timeout = timeout_for_deadline($deadline, 6);
        if ($timeout < 1) {
            $out['error'] = 'Se agotó el tiempo total del análisis.';
            break;
        }
        $ctx = stream_context_create(['ssl' => [
            'capture_peer_cert'  => true,
            'verify_peer'        => $verify,
            'verify_peer_name'   => $verify,
            'peer_name'          => $host,
            'SNI_enabled'        => true,
            'SNI_server_name'    => $host,
        ]]);
        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client(socket_endpoint($ip), $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $ctx);
        if (!$fp) {
            if ($verify) {
                $out['error'] = $errstr ?: 'fallo en el handshake TLS';
            }
            continue; // reintenta sin verificar para poder leer el certificado
        }
        $meta   = stream_get_meta_data($fp);
        $params = stream_context_get_params($fp);
        fclose($fp);

        $out['ok']       = true;
        $out['valid']    = $verify;
        $out['protocol'] = $meta['crypto']['protocol'] ?? null;
        $out['cipher']   = $meta['crypto']['cipher_name'] ?? null;

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        $x    = $cert ? openssl_x509_parse($cert) : null;
        if ($x) {
            $out['subject']   = $x['subject']['CN'] ?? '';
            $out['issuer']    = $x['issuer']['O'] ?? ($x['issuer']['CN'] ?? 'desconocido');
            $out['valid_to']  = $x['validTo_time_t'];
            $out['days_left'] = (int) floor(($x['validTo_time_t'] - time()) / 86400);
            $out['san_count'] = isset($x['extensions']['subjectAltName'])
                ? substr_count($x['extensions']['subjectAltName'], 'DNS:') : 0;
        }
        if ($verify) {
            $out['error'] = null;
        }
        break;
    }
    if ($out['ok']) {
        $out['supported_protocols'] = tls_protocol_matrix($host, $ip, $deadline);
    }
    return $out;
}

