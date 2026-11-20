<?php

namespace App\Services\Security;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Status of a queued Security Center run, polled by the page. Kept in the cache for an hour.
 */
class SecurityTaskStatus
{
    public const TTL_SECONDS = 3600;

    public const RUN_LOCK = 'security-review:run-lock';

    public const RUN_LOCK_SECONDS = 600;

    public const RUNNING_TOKEN = 'security-review:running-token';

    public function create(?int $userId): string
    {
        $token = (string) Str::uuid();
        $this->put($token, ['status' => 'queued', 'user_id' => $userId, 'result' => null, 'error' => null]);

        return $token;
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function update(string $token, array $changes): void
    {
        $current = $this->get($token);

        if ($current === null) {
            return;
        }

        $this->put($token, array_merge($current, $changes));
    }

    /**
     * @return array{status: string, user_id: int|null, result: mixed, error: string|null, updated_at: string}|null
     */
    public function get(string $token): ?array
    {
        if (! Str::isUuid($token)) {
            return null;
        }

        $value = Cache::get($this->key($token));

        return is_array($value) ? $value : null;
    }

    /**
     * Frees the run lock and forgets the running task, after a run ends either way.
     */
    public function releaseRun(): void
    {
        Cache::lock(self::RUN_LOCK)->forceRelease();
        Cache::forget(self::RUNNING_TOKEN);
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function put(string $token, array $value): void
    {
        $value['updated_at'] = now()->toIso8601String();
        Cache::put($this->key($token), $value, self::TTL_SECONDS);
    }

    private function key(string $token): string
    {
        return 'security-review:task:' . $token;
    }
}
