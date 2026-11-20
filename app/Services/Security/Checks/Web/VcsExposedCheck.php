<?php

namespace App\Services\Security\Checks\Web;

use App\Enums\SecuritySeverity;

class VcsExposedCheck extends AbstractExposureCheck
{
    protected string $id = 'web.vcs_exposed';
    protected string $title = 'Git metadata is not served over the web';
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'A served .git folder lets anyone rebuild the deployed source and its history, and find the exact version running.';
    protected array $references = ['NIST CM-7', 'OWASP ASVS V14.3'];

    protected function subject(): string
    {
        return 'Git metadata';
    }

    protected function paths(): array
    {
        $head = fn (string $body): bool => (bool) preg_match('/^ref:\s*refs\/|^[0-9a-f]{40}\s*$/m', $body);
        $config = fn (string $body): bool => str_contains($body, '[core]');
        $prefix = $this->appFolderPrefix();

        return [
            '/.git/HEAD' => $head,
            '/.git/config' => $config,
            $prefix . '/.git/HEAD' => $head,
        ];
    }
}
