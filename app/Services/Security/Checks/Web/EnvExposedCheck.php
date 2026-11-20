<?php

namespace App\Services\Security\Checks\Web;

use App\Services\Security\SecurityCheckResult;

class EnvExposedCheck extends AbstractExposureCheck
{
    protected string $id = 'web.env_exposed';
    protected string $title = 'The .env file is not served over the web';
    protected string $rationale = 'The .env file holds the database password, APP_KEY and the Redis password. A web server that serves the app folder instead of its public folder hands them to anyone who asks.';
    protected array $references = ['NIST SC-28', 'NIST CM-7', 'CIS Apache 2.4'];

    protected function subject(): string
    {
        return 'the .env file';
    }

    protected function paths(): array
    {
        $env = fn (string $body): bool => self::looksLikeEnv($body);
        $prefix = $this->appFolderPrefix();

        return [
            '/.env' => $env,
            '/.env.backup' => $env,
            '/.env.example' => $env,
            $prefix . '/.env' => $env,
        ];
    }

    /**
     * .env.example holds no secrets. Served alone, it shows the app folder is reachable, so it warns.
     */
    protected function exposedResult(array $urls, int $targets): SecurityCheckResult
    {
        $real = array_values(array_filter($urls, fn (string $url): bool => ! str_ends_with($url, '.env.example')));

        if ($real === []) {
            return SecurityCheckResult::warn(
                '.env.example is served, so the app folder itself is reachable over the web, although no .env was found at the probed paths: ' . implode(', ', array_slice($urls, 0, 3)) . '.',
                $this->fixHint(),
                ['exposed_urls' => array_slice($urls, 0, 10), 'targets_probed' => $targets],
            );
        }

        return parent::exposedResult($real, $targets);
    }
}
