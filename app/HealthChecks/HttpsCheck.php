<?php

namespace App\HealthChecks;

use App\Services\System\SelfSignedSslService;
use App\Services\Utilities\HostFilesystem;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;

/**
 * Warns on the dashboard while rConfig is served over plain HTTP, judged by
 * APP_URL. Outside Docker the warning names the one command that moves a box
 * to a self-signed certificate; in Docker TLS belongs on the reverse proxy,
 * so it points at the docs only.
 */
class HttpsCheck extends Check
{
    public function __construct(private readonly HostFilesystem $host)
    {
        parent::__construct();
    }

    public function run(): Result
    {
        $result = Result::make();

        if (SelfSignedSslService::appUsesHttps()) {
            return $result->ok('rConfig is served over HTTPS.')->shortSummary('Ok');
        }

        $fix = $this->host->isDocker()
            ? 'Terminate TLS on your reverse proxy and set APP_URL to https://.'
            : 'Run: ' . SelfSignedSslService::COMMAND . ' for a self-signed certificate, or install your own certificate.';

        return $result
            ->warning('rConfig is served over HTTP, so passwords and device credentials cross the network unencrypted. ' . $fix)
            ->shortSummary('Enable HTTPS');
    }
}
