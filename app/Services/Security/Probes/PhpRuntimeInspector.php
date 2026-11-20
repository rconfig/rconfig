<?php

namespace App\Services\Security\Probes;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Throwable;

/**
 * php.ini values as the web server's PHP sees them. From the CLI they come from the signed,
 * loopback-only security.php-runtime route; if that fails the CLI's own values are used and the source says so.
 */
class PhpRuntimeInspector
{
    /**
     * The only ini keys the runtime route ever returns.
     */
    public const KEYS = ['display_errors', 'display_startup_errors', 'log_errors'];

    /** @var array{source: string, sapi: string, version: string, values: array<string, string|false>}|null */
    private ?array $snapshot;

    /**
     * @param  array{source: string, sapi: string, version: string, values: array<string, string|false>}|null  $snapshot  overridable for tests
     */
    public function __construct(?array $snapshot = null)
    {
        $this->snapshot = $snapshot;
    }

    /**
     * @return array{source: string, sapi: string, version: string, values: array<string, string|false>}
     */
    public function snapshot(): array
    {
        if ($this->snapshot !== null) {
            return $this->snapshot;
        }

        if (PHP_SAPI !== 'cli') {
            return $this->snapshot = self::local('web');
        }

        return $this->snapshot = $this->fetchWeb() ?? self::local('cli');
    }

    /**
     * ini booleans as PHP reads them: "1", "On", "true", "yes".
     */
    public function enabled(string $key): bool
    {
        return filter_var($this->snapshot()['values'][$key] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function source(): string
    {
        return $this->snapshot()['source'];
    }

    public function sapi(): string
    {
        return $this->snapshot()['sapi'];
    }

    /**
     * Appended to php.* messages when the web server's PHP could not be read.
     */
    public function sourceNote(): string
    {
        return $this->source() === 'cli' ? ' (read from the command-line PHP, because the web server PHP could not be reached; it may differ)' : '';
    }

    /**
     * This process's own values, limited to KEYS.
     *
     * @return array{source: string, sapi: string, version: string, values: array<string, string|false>}
     */
    public static function local(string $source): array
    {
        $values = [];

        foreach (self::KEYS as $key) {
            $values[$key] = ini_get($key);
        }

        return ['source' => $source, 'sapi' => PHP_SAPI, 'version' => PHP_VERSION, 'values' => $values];
    }

    /**
     * @return array{source: string, sapi: string, version: string, values: array<string, string|false>}|null
     */
    private function fetchWeb(): ?array
    {
        try {
            $relative = URL::temporarySignedRoute('security.php-runtime', now()->addMinute(), [], false);
            $timeout = (int) config('security-checks.probes.timeout', 3);

            foreach ($this->loopbackTargets() as [$base, $resolve]) {
                $options = ['verify' => false, 'allow_redirects' => false];

                if ($resolve !== null) {
                    $options['curl'] = [CURLOPT_RESOLVE => [$resolve]];
                }

                $response = Http::withOptions($options)
                    ->connectTimeout($timeout)
                    ->timeout($timeout)
                    ->acceptJson()
                    ->get($base . $relative);

                if ($response->successful() && is_array($response->json('values'))) {
                    $values = [];

                    foreach (self::KEYS as $key) {
                        $raw = $response->json('values')[$key] ?? false;
                        $values[$key] = $raw === false || $raw === null ? false : (string) $raw;
                    }

                    return [
                        'source' => 'web',
                        'sapi' => (string) $response->json('sapi', 'web'),
                        'version' => (string) $response->json('version', ''),
                        'values' => $values,
                    ];
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    /**
     * The APP_URL name pinned to 127.0.0.1 (so name-based vhosts match), then bare 127.0.0.1 over http and https.
     *
     * @return list<array{0: string, 1: string|null}>
     */
    private function loopbackTargets(): array
    {
        $parts = parse_url((string) config('app.url')) ?: [];
        $targets = [];

        if (! empty($parts['host']) && filter_var($parts['host'], FILTER_VALIDATE_IP) === false) {
            $scheme = $parts['scheme'] ?? 'http';
            $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
            $targets[] = ["{$scheme}://{$parts['host']}:{$port}", "{$parts['host']}:{$port}:127.0.0.1"];
        }

        $targets[] = ['http://127.0.0.1', null];
        $targets[] = ['https://127.0.0.1', null];

        return $targets;
    }
}
