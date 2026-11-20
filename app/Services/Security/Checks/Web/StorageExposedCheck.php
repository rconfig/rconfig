<?php

namespace App\Services\Security\Checks\Web;

class StorageExposedCheck extends AbstractExposureCheck
{
    protected string $id = 'web.storage_exposed';
    protected string $title = 'Storage, logs and saved configs are not served over the web';
    protected string $rationale = 'The storage folder holds device configs, logs and exports. Served over the web, anyone who can reach the server can list and download them.';
    protected array $references = ['NIST SC-28', 'NIST AC-3', 'CIS Apache 2.4'];

    protected function subject(): string
    {
        return 'rConfig storage';
    }

    protected function paths(): array
    {
        $log = fn (string $body): bool => self::looksLikeLaravelLog($body);
        $listing = fn (string $body): bool => self::looksLikeDirectoryListing($body);
        $prefix = $this->appFolderPrefix();

        return [
            '/storage/logs/laravel.log' => $log,
            $prefix . '/storage/logs/laravel.log' => $log,
            '/storage/' => $listing,
            $prefix . '/storage/' => $listing,
        ];
    }
}
