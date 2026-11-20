<?php

namespace App\Services\Security\Checks\Web;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\Probes\ProbeResult;
use App\Services\Security\Probes\WebProber;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use Illuminate\Http\Client\Response;

/**
 * Self-probe for paths that must never be served. Exposed only on a 2xx whose body matches the file's signature.
 */
abstract class AbstractExposureCheck extends AbstractSecurityCheck
{
    protected SecurityCheckGroup $group = SecurityCheckGroup::Web;
    protected SecuritySeverity $severity = SecuritySeverity::Critical;

    public function __construct(protected readonly WebProber $prober) {}

    /**
     * Paths to probe, each with the signature that proves the real file was served.
     *
     * @return array<string, (callable(string, Response): bool)|null>
     */
    abstract protected function paths(): array;

    /**
     * What is exposed, e.g. "the .env file".
     */
    abstract protected function subject(): string;

    public function appliesTo(SecurityContext $context): bool
    {
        return $context->probesEnabled;
    }

    public function run(SecurityContext $context): SecurityCheckResult
    {
        if ($this->prober->liveTargets() === []) {
            return SecurityCheckResult::skip('No web endpoint on this server answered the probe, so nothing could be checked.');
        }

        $exposed = $this->prober->exposed($this->paths());
        $targets = count($this->prober->liveTargets());

        if ($exposed === []) {
            return SecurityCheckResult::pass(
                $this->sentenceSubject() . ' is not served on any of the ' . $this->plural($targets, 'address', 'addresses') . ' probed.',
                ['targets_probed' => $targets, 'paths_probed' => count($this->paths())],
            );
        }

        $urls = array_values(array_unique(array_map(fn (ProbeResult $result): string => $result->url, $exposed)));

        return $this->exposedResult($urls, $targets);
    }

    /**
     * @param  list<string>  $urls
     */
    protected function exposedResult(array $urls, int $targets): SecurityCheckResult
    {
        return SecurityCheckResult::fail(
            $this->sentenceSubject() . ' can be opened without signing in at ' . $this->plural(count($urls), 'URL') . ': ' . implode(', ', array_slice($urls, 0, 5)) . (count($urls) > 5 ? ' and more' : '') . '.',
            $this->fixHint(),
            ['exposed_urls' => array_slice($urls, 0, 10), 'targets_probed' => $targets],
        );
    }

    public function fixGuide(): string
    {
        return $this->fixHint();
    }

    /**
     * The subject at the start of a sentence; "rConfig" keeps its lower-case r.
     */
    protected function sentenceSubject(): string
    {
        $subject = $this->subject();

        return str_starts_with($subject, 'rConfig') ? $subject : ucfirst($subject);
    }

    /**
     * How to close the exposure. Defaults to the web server document root.
     */
    protected function fixHint(): string
    {
        return 'Point the Apache DocumentRoot at ' . rconfig_appdir_path() . '/public only (see rconfig-vhost.conf.dist), remove any site that serves the app folder itself, then reload Apache.';
    }

    /**
     * The app folder as a URL prefix, e.g. "/rconfig", for installs served from the web root.
     */
    protected function appFolderPrefix(): string
    {
        return '/' . basename(rtrim((string) rconfig_appdir_path(), '/'));
    }

    protected static function looksLikeEnv(string $body): bool
    {
        return (bool) preg_match('/^\s*(APP_KEY|APP_NAME|APP_ENV|DB_PASSWORD|DB_CONNECTION)\s*=/m', $body);
    }

    protected static function looksLikeLaravelLog(string $body): bool
    {
        return (bool) preg_match('/^\[\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}[^\]]*\]\s+\w+\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY)/m', $body);
    }

    protected static function looksLikeDirectoryListing(string $body): bool
    {
        return (bool) preg_match('/<title>\s*Index of\s+\//i', $body);
    }
}
