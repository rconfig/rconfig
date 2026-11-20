<?php

namespace App\Services\Security\Probes;

use Throwable;

/**
 * Reads the certificate this server presents. Never verifies the chain: the point is what is served.
 */
class TlsInspector
{
    /** @var array<string, array{valid_to: int, days_left: int}|null> */
    private array $certificates = [];

    /**
     * Host and port to inspect: the APP_URL host when it uses https, else 127.0.0.1:443.
     *
     * @return array{host: string, port: int, server_name: string}
     */
    public function target(): array
    {
        $parts = parse_url((string) config('app.url')) ?: [];

        if (($parts['scheme'] ?? '') === 'https' && ! empty($parts['host'])) {
            return ['host' => $parts['host'], 'port' => (int) ($parts['port'] ?? 443), 'server_name' => $parts['host']];
        }

        return ['host' => '127.0.0.1', 'port' => 443, 'server_name' => (string) ($parts['host'] ?? 'localhost')];
    }

    /**
     * @return array{valid_to: int, days_left: int}|null
     */
    public function certificate(): ?array
    {
        $target = $this->target();
        $key = "{$target['host']}:{$target['port']}";

        if (array_key_exists($key, $this->certificates)) {
            return $this->certificates[$key];
        }

        return $this->certificates[$key] = $this->fetchCertificate($target);
    }

    /**
     * @param  array{host: string, port: int, server_name: string}  $target
     * @return array{valid_to: int, days_left: int}|null
     */
    private function fetchCertificate(array $target): ?array
    {
        $context = stream_context_create(['ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
            'SNI_enabled' => true,
            'peer_name' => $target['server_name'],
            'capture_peer_cert' => true,
        ]]);

        try {
            $stream = @stream_socket_client(
                "ssl://{$target['host']}:{$target['port']}",
                $errno,
                $errstr,
                (float) config('security-checks.probes.timeout', 3),
                STREAM_CLIENT_CONNECT,
                $context,
            );
        } catch (Throwable) {
            return null;
        }

        if ($stream === false) {
            return null;
        }

        $params = stream_context_get_params($stream);
        fclose($stream);

        $cert = $params['options']['ssl']['peer_certificate'] ?? null;
        $parsed = $cert === null ? false : openssl_x509_parse($cert);

        if (! is_array($parsed)) {
            return null;
        }

        $validTo = (int) ($parsed['validTo_time_t'] ?? 0);

        return ['valid_to' => $validTo, 'days_left' => (int) floor(($validTo - time()) / 86400)];
    }
}
