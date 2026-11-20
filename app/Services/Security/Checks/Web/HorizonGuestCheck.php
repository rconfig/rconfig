<?php

namespace App\Services\Security\Checks\Web;

use App\Enums\SecuritySeverity;

class HorizonGuestCheck extends AbstractExposureCheck
{
    protected string $id = 'web.horizon_guest';
    protected string $title = 'The Horizon dashboard is closed to guests';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'Horizon shows every queued job with its payload and lets the viewer retry or delete jobs. Only signed-in users may open it.';
    protected array $references = ['NIST AC-3', 'NIS2 21(2)(i)'];

    protected function fixHint(): string
    {
        return 'Set APP_ENV=production in .env (Horizon skips its sign-in gate in the local environment), then ' . lcfirst($this->artisan('config:cache')) . ' and restart Horizon.';
    }

    protected function subject(): string
    {
        return 'the Horizon dashboard';
    }

    protected function paths(): array
    {
        $path = '/' . trim((string) config('horizon.path', 'horizon'), '/');

        return [
            $path => fn (string $body): bool => str_contains($body, 'window.Horizon'),
            $path . '/api/stats' => fn (string $body): bool => str_contains($body, 'jobsPerMinute'),
        ];
    }
}
