<?php

namespace App\Services\Security;

use App\Services\Utilities\HostFilesystem;

/**
 * What a security check needs to know about the run and the box it runs on.
 */
final class SecurityContext
{
    public const TRIGGER_CLI = 'cli';

    public const TRIGGER_SCHEDULE = 'schedule';

    public const TRIGGER_UI = 'ui';

    public function __construct(
        public readonly string $trigger,
        public readonly bool $runsAsRoot,
        public readonly bool $isDocker,
        public readonly bool $probesEnabled,
        public readonly string $appVersion,
        public readonly string $sapi,
    ) {}

    public static function current(string $trigger = self::TRIGGER_CLI): self
    {
        $host = app(HostFilesystem::class);

        return new self(
            trigger: $trigger,
            runsAsRoot: $host->isRoot(),
            isDocker: $host->isDocker(),
            probesEnabled: (bool) config('security-checks.probes.enabled', true),
            appVersion: (string) config('app.version', ''),
            sapi: PHP_SAPI,
        );
    }
}
