<?php

namespace App\Services\Utilities;

use App\Jobs\CheckForUpdateJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VersionCheckService
{
    /**
     * GitHub repository tags endpoint used to discover released versions.
     */
    private const GITHUB_TAGS_URL = 'https://api.github.com/repos/rconfig/rconfig/tags';

    /**
     * Prefix used on Core release tags, for example "core-8.0.2".
     */
    private const TAG_PREFIX = 'core-';

    /**
     * Cache key holding the persisted version-check status.
     */
    private const CACHE_KEY = 'version_check.status';

    /**
     * Persist the status for a week; the scheduled job refreshes it well
     * before then, this only bounds staleness if the scheduler stops.
     */
    private const CACHE_TTL = 604800;

    /**
     * Number of consecutive failed checks before escalating the log level.
     */
    private const FAILURE_ESCALATION_THRESHOLD = 3;

    /**
     * Return the current version status for the UI. This never performs a
     * network call: it reads the status last written by the scheduled job
     * (or an explicit refresh). When no status exists yet it dispatches a
     * background check and returns a neutral "not yet checked" payload so the
     * UI stays fast.
     *
     * @return array{current_version: string, latest_version: ?string, update_available: bool, latest_url: ?string, reachable: bool, checked: bool, last_checked_at: ?string, consecutive_failures: int, last_error: ?string}
     */
    public function getStatus(): array
    {
        $status = Cache::get(self::CACHE_KEY);

        if ($status === null) {
            CheckForUpdateJob::dispatch();

            return $this->decorate($this->emptyStatus());
        }

        return $this->decorate($status);
    }

    /**
     * Perform a live check against GitHub, persist the result (including
     * failure metadata) and return the decorated status. Used by the
     * scheduled job and the explicit "Recheck" action.
     *
     * @return array{current_version: string, latest_version: ?string, update_available: bool, latest_url: ?string, reachable: bool, checked: bool, last_checked_at: ?string, consecutive_failures: int, last_error: ?string}
     */
    public function refresh(): array
    {
        $previous = Cache::get(self::CACHE_KEY, $this->emptyStatus());
        $now = Carbon::now()->toIso8601String();

        try {
            $latestVersion = $this->fetchLatestVersion();

            $status = [
                'latest_version' => $latestVersion,
                'latest_url' => $latestVersion !== null ? 'https://github.com/rconfig/rconfig/releases/tag/' . self::TAG_PREFIX . $latestVersion : null,
                'reachable' => true,
                'checked' => true,
                'last_checked_at' => $now,
                'last_success_at' => $now,
                'consecutive_failures' => 0,
                'last_error' => null,
            ];
        } catch (\Throwable $e) {
            $failures = ($previous['consecutive_failures'] ?? 0) + 1;

            $this->logFailure($failures, $e->getMessage());

            // Keep the last known version so the UI can still show it while offline.
            $status = [
                'latest_version' => $previous['latest_version'] ?? null,
                'latest_url' => $previous['latest_url'] ?? null,
                'reachable' => false,
                'checked' => true,
                'last_checked_at' => $now,
                'last_success_at' => $previous['last_success_at'] ?? null,
                'consecutive_failures' => $failures,
                'last_error' => $e->getMessage(),
            ];
        }

        Cache::put(self::CACHE_KEY, $status, self::CACHE_TTL);

        return $this->decorate($status);
    }

    /**
     * Fetch the highest Core release version from GitHub tags. Retries a few
     * times to ride out transient network blips, and throws on a definitive
     * failure so the caller can record it.
     */
    private function fetchLatestVersion(): ?string
    {
        $response = Http::timeout(5)
            ->retry(3, 250, throw: false)
            ->get(self::GITHUB_TAGS_URL);

        if (! $response->successful()) {
            throw new \RuntimeException('GitHub returned HTTP ' . $response->status());
        }

        return $this->resolveLatestVersionFromTags($response->json());
    }

    /**
     * Filter GitHub tag entries to Core release tags and return the highest
     * version number (without the "core-" prefix).
     *
     * @param  array<int, array{name?: string}>|null  $tags
     */
    private function resolveLatestVersionFromTags(?array $tags): ?string
    {
        if (empty($tags)) {
            return null;
        }

        $latest = null;

        foreach ($tags as $tag) {
            $name = $tag['name'] ?? '';

            if (! str_starts_with($name, self::TAG_PREFIX)) {
                continue;
            }

            $version = substr($name, strlen(self::TAG_PREFIX));

            if ($latest === null || version_compare($version, $latest, '>')) {
                $latest = $version;
            }
        }

        return $latest;
    }

    /**
     * Add the live-computed fields (current version, update availability) to a
     * persisted status payload.
     *
     * @param  array<string, mixed>  $status
     * @return array{current_version: string, latest_version: ?string, update_available: bool, latest_url: ?string, reachable: bool, checked: bool, last_checked_at: ?string, consecutive_failures: int, last_error: ?string}
     */
    private function decorate(array $status): array
    {
        $currentVersion = (string) config('app.version');
        $latestVersion = $status['latest_version'] ?? null;
        $updateAvailable = $latestVersion !== null && version_compare($latestVersion, $currentVersion, '>');

        return [
            'current_version' => $currentVersion,
            'latest_version' => $latestVersion,
            'update_available' => $updateAvailable,
            'latest_url' => $status['latest_url'] ?? null,
            'reachable' => (bool) ($status['reachable'] ?? false),
            'checked' => (bool) ($status['checked'] ?? false),
            'last_checked_at' => $status['last_checked_at'] ?? null,
            'consecutive_failures' => (int) ($status['consecutive_failures'] ?? 0),
            'last_error' => $status['last_error'] ?? null,
        ];
    }

    /**
     * The default status before any check has completed.
     *
     * @return array<string, mixed>
     */
    private function emptyStatus(): array
    {
        return [
            'latest_version' => null,
            'latest_url' => null,
            'reachable' => false,
            'checked' => false,
            'last_checked_at' => null,
            'last_success_at' => null,
            'consecutive_failures' => 0,
            'last_error' => null,
        ];
    }

    /**
     * Log a failed update check, escalating to error once failures persist.
     */
    private function logFailure(int $failures, string $message): void
    {
        $context = ['attempts' => $failures, 'error' => $message];

        if ($failures >= self::FAILURE_ESCALATION_THRESHOLD) {
            Log::error("Update check has failed {$failures} times in a row.", $context);
        } else {
            Log::warning('Update check failed.', $context);
        }
    }
}
