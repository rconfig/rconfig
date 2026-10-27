<?php

namespace App\Console\Commands;

use App\Enums\WorkerCheckStatus;
use App\Services\System\WorkerUserCheckService;
use Illuminate\Console\Command;

class RconfigCheckWorkerUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:check-worker-user {--json : Print the report as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check that Horizon, the scheduler and the Supervisor config run nothing as root';

    /**
     * Prints one line per check and the state of the box. update.sh and
     * v8core:install fail on a non-zero exit, so only a FAIL exits non-zero:
     * a warning, a skipped check or Docker exits 0.
     */
    public function handle(WorkerUserCheckService $service): int
    {
        $report = $service->report();
        $exitCode = $report['status'] === WorkerCheckStatus::Fail ? self::FAILURE : self::SUCCESS;

        if ($this->option('json')) {
            $this->line((string) json_encode($this->toJson($report), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $exitCode;
        }

        if ($report['checks'] === []) {
            $this->line($report['summary'] . '.');

            return $exitCode;
        }

        $this->line("Web user: {$report['web_user']}" . ($report['checked_as_root'] ? ' (checked as root)' : ''));

        foreach ($report['checks'] as $check) {
            $colour = match ($check['status']) {
                WorkerCheckStatus::Ok => 'green',
                WorkerCheckStatus::Warn => 'yellow',
                WorkerCheckStatus::Fail => 'red',
                WorkerCheckStatus::Skipped => 'gray',
            };

            $this->line(sprintf('<fg=%s>%-4s</> %s: %s', $colour, $check['status']->label(), $check['label'], $check['message']));
        }

        $this->line("Overall: {$report['summary']}");

        if ($report['fix'] !== null) {
            $this->line("Fix: {$report['fix']}");
        }

        return $exitCode;
    }

    /**
     * @param  array<string, mixed>  $report
     * @return array<string, mixed>
     */
    private function toJson(array $report): array
    {
        $report['status'] = $report['status']->value;
        $report['checks'] = array_map(function (array $check): array {
            $check['status'] = $check['status']->value;

            return $check;
        }, $report['checks']);

        return $report;
    }
}
