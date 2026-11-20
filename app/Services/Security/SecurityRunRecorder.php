<?php

namespace App\Services\Security;

use App\Enums\SecurityCheckStatus;
use App\Models\SecurityCheckRun;
use App\Models\User;
use App\Services\Notifications\HealthCheckNotifier;
use App\Services\Notifications\Shared\HealthCheckAlert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Stores a full security run as the only kept run and alerts on failures and recoveries against the one it replaces.
 */
class SecurityRunRecorder
{
    public function __construct(
        private readonly HealthCheckNotifier $notifier,
        private readonly SecurityReviewSettings $settings,
    ) {}

    public function record(SecurityReport $report, ?User $user = null): SecurityCheckRun
    {
        $previous = SecurityCheckRun::latestRun()?->report();
        $summary = $report->summary();

        $run = DB::transaction(function () use ($report, $user, $summary): SecurityCheckRun {
            $run = SecurityCheckRun::create([
                'ran_at' => $report->ranAt,
                'trigger' => $report->trigger,
                'user_id' => $user?->id,
                'app_version' => $report->version,
                'duration_ms' => $report->durationMs,
                'pass_count' => $summary['pass'],
                'warn_count' => $summary['warn'],
                'fail_count' => $summary['fail'],
                'skip_count' => $summary['skip'],
                'results' => $report->toArray(),
            ]);

            SecurityCheckRun::query()->whereKeyNot($run->id)->delete();

            return $run;
        });

        if ($this->settings->notifyEnabled()) {
            $this->notifyTransitions($report, $previous);
        }

        return $run;
    }

    /**
     * Checks that fail now but did not before, and checks that failed before and pass now. Warnings never alert.
     *
     * @return array{failing: list<SecurityReportItem>, recovered: list<SecurityReportItem>}
     */
    public function transitions(SecurityReport $current, ?SecurityReport $previous): array
    {
        $before = [];

        foreach ($previous?->items ?? [] as $item) {
            $before[$item->id] = $item->status;
        }

        $failing = [];
        $recovered = [];

        foreach ($current->items as $item) {
            $wasFailing = ($before[$item->id] ?? null) === SecurityCheckStatus::Fail;

            if ($item->status === SecurityCheckStatus::Fail && ! $wasFailing) {
                $failing[] = $item;
            } elseif ($wasFailing && $item->status === SecurityCheckStatus::Pass) {
                $recovered[] = $item;
            }
        }

        return ['failing' => $failing, 'recovered' => $recovered];
    }

    private function notifyTransitions(SecurityReport $current, ?SecurityReport $previous): void
    {
        try {
            $transitions = $this->transitions($current, $previous);

            if ($transitions['failing'] !== []) {
                $this->notifier->notify(HealthCheckAlert::security(false, $this->alertRows($transitions['failing'])));
            }

            if ($transitions['recovered'] !== []) {
                $this->notifier->notify(HealthCheckAlert::security(true, $this->alertRows($transitions['recovered'])));
            }
        } catch (Throwable $e) {
            Log::error('Failed to raise security check notifications: ' . $e->getMessage());
        }
    }

    /**
     * @param  list<SecurityReportItem>  $items
     * @return array<int, array{name: string, label: string, status: string, summary: string, message: string}>
     */
    private function alertRows(array $items): array
    {
        return array_map(fn (SecurityReportItem $item): array => [
            'name' => $item->id,
            'label' => $item->title,
            'status' => $item->status->value,
            'summary' => 'passing',
            'message' => $item->message,
        ], $items);
    }
}
