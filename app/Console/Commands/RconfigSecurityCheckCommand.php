<?php

namespace App\Console\Commands;

use App\Enums\SecurityCheckStatus;
use App\Services\Security\SecurityCheckRegistry;
use App\Services\Security\SecurityCheckRunner;
use App\Services\Security\SecurityContext;
use App\Services\Security\SecurityReport;
use App\Services\Security\SecurityReportItem;
use App\Services\Security\SecurityRunRecorder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Runs every Security Review check. Read-only and never prints a secret. Exit 0 all pass, 1 warnings, 2 failures.
 */
class RconfigSecurityCheckCommand extends Command
{
    protected $signature = 'rconfig:security-check
        {--json : Print the full report as JSON (schema v1)}
        {--only=* : Run only these checks: an ID (redis.auth), a group (web) or a prefix (web.*); comma-separated or repeated}
        {--list : List every check and exit}
        {--no-store : Do not save this run for the Security Review page}';
    protected $description = 'Run the rConfig Security Review checks: configuration, web exposure, TLS, PHP, Redis, accounts, REST API, data, runtime, versions, devices and OS';

    public function handle(SecurityCheckRegistry $registry, SecurityCheckRunner $runner, SecurityRunRecorder $recorder): int
    {
        if ($this->option('list')) {
            return $this->listChecks($registry);
        }

        $only = (array) $this->option('only');
        $unknown = $registry->unknownPatterns($only);

        if ($unknown !== []) {
            $this->error('No check matches: ' . implode(', ', $unknown) . '. Run with --list to see every check.');

            return 2;
        }

        $report = $runner->run(SecurityContext::current(SecurityContext::TRIGGER_CLI), $only);

        // Only full runs are stored, so the page and the alerts always compare like with like.
        if ($only === [] && ! $this->option('no-store') && Schema::hasTable('security_check_runs')) {
            $recorder->record($report);
        }

        if ($this->option('json')) {
            $this->line($report->toJson());
        } else {
            $this->renderTable($report);
        }

        return $report->exitCode();
    }

    private function listChecks(SecurityCheckRegistry $registry): int
    {
        $rows = [];

        foreach ($registry->all() as $check) {
            $rows[] = ['id' => $check->id(), 'group' => $check->group()->value, 'severity' => $check->severity()->value, 'title' => $check->title()];
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->table(['ID', 'Group', 'Severity', 'Checks that'], array_map(fn (array $row): array => array_values($row), $rows));
        $this->line(count($rows) . ' checks.');

        return self::SUCCESS;
    }

    private function renderTable(SecurityReport $report): void
    {
        $this->newLine();
        $this->line('<options=bold>rConfig security check</> · Core ' . $report->version . ' · ' . $report->ranAt->toDayDateTimeString());
        $this->newLine();

        $this->table(['Status', 'Check', 'Severity', 'Result'], array_map(fn (SecurityReportItem $item): array => [
            $this->statusLabel($item->status),
            $item->id,
            $item->severity->label(),
            wordwrap($item->message, 90, "\n", true),
        ], $report->sortedItems()));

        $needsAction = array_filter($report->sortedItems(), fn (SecurityReportItem $item): bool => $item->status->needsAction() && $item->fix !== null);

        if ($needsAction !== []) {
            $this->newLine();
            $this->line('<options=bold>How to fix</>');

            foreach ($needsAction as $item) {
                $this->line("  <fg=yellow>{$item->id}</>: {$item->fix}");
                $this->line("    <fg=gray>{$item->docsUrl}</>");
            }
        }

        $summary = $report->summary();
        $this->newLine();
        $this->line(sprintf(
            '<fg=red>%d failed</>, <fg=yellow>%d %s</>, <fg=green>%d passed</>, %d skipped · %d ms',
            $summary['fail'], $summary['warn'], $summary['warn'] === 1 ? 'warning' : 'warnings', $summary['pass'], $summary['skip'], $report->durationMs,
        ));
        $this->line('<fg=gray>Full report: php artisan rconfig:security-check --json · Advisories: ' . config('security-checks.advisories_url') . '</>');
    }

    private function statusLabel(SecurityCheckStatus $status): string
    {
        return match ($status) {
            SecurityCheckStatus::Pass => '<fg=green>PASS</>',
            SecurityCheckStatus::Warn => '<fg=yellow>WARN</>',
            SecurityCheckStatus::Fail => '<fg=red;options=bold>FAIL</>',
            SecurityCheckStatus::Skip => '<fg=gray>SKIP</>',
        };
    }
}
