<?php

namespace App\Services\Security;

use App\Services\Security\Contracts\SecurityCheck;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs security checks and builds the report. A check that throws becomes SKIP with only the exception class shown.
 */
class SecurityCheckRunner
{
    public function __construct(private readonly SecurityCheckRegistry $registry) {}

    /**
     * @param  list<string>  $only
     */
    public function run(SecurityContext $context, array $only = []): SecurityReport
    {
        $started = hrtime(true);
        $ranAt = CarbonImmutable::now();
        $items = [];

        foreach ($this->registry->select($only) as $check) {
            if ($this->applies($check, $context)) {
                $items[] = $this->runOne($check, $context);
            }
        }

        return new SecurityReport(
            ranAt: $ranAt,
            trigger: $context->trigger,
            version: $context->appVersion,
            items: $items,
            durationMs: (int) ((hrtime(true) - $started) / 1_000_000),
            host: $this->hostSummary($context),
        );
    }

    public function runOne(SecurityCheck $check, SecurityContext $context): SecurityReportItem
    {
        $started = hrtime(true);

        try {
            $result = $check->run($context);
        } catch (Throwable $e) {
            Log::warning("Security check {$check->id()} could not run: " . $e->getMessage(), ['exception' => $e]);

            $result = SecurityCheckResult::skip('Could not run: ' . class_basename($e) . '. Details are in the application log.');
        }

        return SecurityReportItem::fromCheck($check, $result, (int) ((hrtime(true) - $started) / 1_000_000));
    }

    private function applies(SecurityCheck $check, SecurityContext $context): bool
    {
        try {
            return $check->appliesTo($context);
        } catch (Throwable) {
            return true;
        }
    }

    /**
     * Non-secret facts about the box, for the report header and support tickets.
     *
     * @return array<string, string|bool>
     */
    private function hostSummary(SecurityContext $context): array
    {
        return [
            'os' => $this->safely(fn (): string => $this->osName(), 'unknown'),
            'php' => PHP_VERSION,
            'sapi' => $context->sapi,
            'database' => $this->safely(fn (): string => DB::connection()->getDriverName(), 'unknown'),
            'docker' => $context->isDocker,
            'checked_as_root' => $context->runsAsRoot,
        ];
    }

    private function osName(): string
    {
        $release = @file_get_contents('/etc/os-release');

        if (! is_string($release) || ! preg_match('/^PRETTY_NAME="?([^"\n]+)"?$/m', $release, $matches)) {
            return PHP_OS_FAMILY;
        }

        return $matches[1];
    }

    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @param  T  $fallback
     * @return T
     */
    private function safely(callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable) {
            return $fallback;
        }
    }
}
