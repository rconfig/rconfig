<?php

namespace App\Services\Security\Checks\Web;

use App\Enums\SecuritySeverity;

class LogViewerGuestCheck extends AbstractExposureCheck
{
    protected string $id = 'web.log_viewer_guest';
    protected string $title = 'The log viewer is closed to guests';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'Application logs record errors, usernames, device addresses and sometimes device output. Only signed-in users may read them.';
    protected array $references = ['NIST AU-9', 'NIST AC-3'];

    protected function fixHint(): string
    {
        return 'Set APP_ENV=production in .env and keep log-viewer.require_auth_in_production on, then ' . lcfirst($this->artisan('config:cache'));
    }

    protected function subject(): string
    {
        return 'the log viewer';
    }

    protected function paths(): array
    {
        $path = '/' . trim((string) config('log-viewer.route_path', 'log-viewer'), '/');

        return [
            $path . '/api/files' => function (string $body): bool {
                $decoded = json_decode($body, true);

                return is_array($decoded) && $decoded !== [] && isset($decoded[0]['identifier']);
            },
            $path => fn (string $body): bool => str_contains($body, 'LogViewer') && str_contains($body, 'log-viewer'),
        ];
    }
}
