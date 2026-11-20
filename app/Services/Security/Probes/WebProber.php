<?php

namespace App\Services\Security\Probes;

use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Throwable;

/**
 * Probes this server's own web endpoints over HTTP and HTTPS, as someone on the network would.
 * Targets: 127.0.0.1, this server's addresses, the APP_URL host and configured extras. Redirects are
 * never followed and TLS is never verified. Bodies are matched against signatures and discarded:
 * only status codes, sizes and "matched" flags leave this class.
 */
class WebProber
{
    /** @var list<string>|null base URLs that answered at all */
    private ?array $liveTargets = null;

    /** @var array<string, list<ProbeResult>> */
    private array $cache = [];

    /**
     * @param  list<string>|null  $targets  base URLs, overridable for tests
     */
    public function __construct(private readonly ?array $targets = null) {}

    /**
     * Every base URL to probe, e.g. ["https://rconfig.example.com", "http://127.0.0.1", "https://127.0.0.1"].
     *
     * @return list<string>
     */
    public function candidateTargets(): array
    {
        if ($this->targets !== null) {
            return $this->targets;
        }

        $hosts = array_merge(['127.0.0.1'], $this->localAddresses(), (array) config('security-checks.probes.extra_targets', []));
        $targets = [];

        foreach (array_unique($hosts) as $host) {
            $host = str_contains($host, ':') && ! str_starts_with($host, '[') ? "[{$host}]" : $host;
            $targets[] = "http://{$host}";
            $targets[] = "https://{$host}";
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if (filter_var($appUrl, FILTER_VALIDATE_URL)) {
            $parts = parse_url($appUrl);
            $base = ($parts['scheme'] ?? 'http') . '://' . ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');
            array_unshift($targets, $base);
        }

        return array_values(array_unique($targets));
    }

    /**
     * Base URLs that answered a request for "/" with any HTTP status.
     *
     * @return list<string>
     */
    public function liveTargets(): array
    {
        if ($this->liveTargets !== null) {
            return $this->liveTargets;
        }

        $live = [];

        foreach ($this->request($this->candidateTargets(), '/', 'GET') as $base => $response) {
            if ($response !== null) {
                $live[] = $base;
            }
        }

        return $this->liveTargets = $live;
    }

    /**
     * Probes one path on every live target.
     *
     * @param  (callable(string $body, Response $response): bool)|null  $signature  true when the body proves the file was served
     * @return list<ProbeResult>
     */
    public function probe(string $path, ?callable $signature = null, string $method = 'GET'): array
    {
        $key = $method . ' ' . $path;

        if ($signature === null && isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $results = [];

        foreach ($this->request($this->liveTargets(), $path, $method) as $base => $response) {
            if ($response === null) {
                continue;
            }

            $matched = false;

            if ($response->successful()) {
                try {
                    $matched = $signature === null || (bool) $signature($response->body(), $response);
                } catch (Throwable) {
                    $matched = false;
                }
            }

            $results[] = new ProbeResult(
                url: $base . $path,
                status: $response->status(),
                length: strlen($response->body()),
                matched: $matched,
            );
        }

        if ($signature === null) {
            $this->cache[$key] = $results;
        }

        return $results;
    }

    /**
     * Probes paths and returns every result whose content matched, i.e. the file was really served.
     *
     * @param  array<string, (callable(string, Response): bool)|null>  $paths
     * @return list<ProbeResult>
     */
    public function exposed(array $paths): array
    {
        $exposed = [];

        foreach ($paths as $path => $signature) {
            foreach ($this->probe($path, $signature) as $result) {
                if ($result->isExposed()) {
                    $exposed[] = $result;
                }
            }
        }

        return $exposed;
    }

    /**
     * @param  list<string>  $bases
     * @return array<string, Response|null>
     */
    private function request(array $bases, string $path, string $method): array
    {
        if ($bases === []) {
            return [];
        }

        $timeout = (int) config('security-checks.probes.timeout', 3);

        try {
            $responses = Http::pool(function (Pool $pool) use ($bases, $path, $method, $timeout): array {
                $requests = [];

                foreach ($bases as $base) {
                    $requests[] = $pool->as($base)
                        ->withOptions(['verify' => false, 'allow_redirects' => false, 'http_errors' => false])
                        ->withUserAgent('rConfig-SecurityCheck/1.0')
                        ->connectTimeout($timeout)
                        ->timeout($timeout)
                        ->send($method, $base . $path);
                }

                return $requests;
            });
        } catch (Throwable) {
            return array_fill_keys($bases, null);
        }

        $out = [];

        foreach ($bases as $base) {
            $response = $responses[$base] ?? null;
            $out[$base] = $response instanceof Response ? $response : null;
        }

        return $out;
    }

    /**
     * This server's own IPv4 and IPv6 addresses, loopback and link-local excluded. At most eight.
     *
     * @return list<string>
     */
    private function localAddresses(): array
    {
        $addresses = [];

        try {
            $result = Process::timeout(3)->run(['ip', '-o', 'addr', 'show', 'up']);

            if ($result->successful()) {
                preg_match_all('/\binet6?\s+([0-9a-fA-F:.]+)\//', $result->output(), $matches);
                $addresses = $matches[1];
            }
        } catch (Throwable) {
            $addresses = [];
        }

        if ($addresses === []) {
            $addresses = gethostbynamel((string) gethostname()) ?: [];
        }

        $addresses = array_filter($addresses, function (string $address): bool {
            if (! filter_var($address, FILTER_VALIDATE_IP)) {
                return false;
            }

            return ! str_starts_with($address, '127.') && $address !== '::1' && ! str_starts_with(strtolower($address), 'fe80:');
        });

        return array_slice(array_values(array_unique($addresses)), 0, 8);
    }
}
