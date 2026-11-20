<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Security\SecurityCheckRunner;
use App\Services\Security\SecurityContext;
use App\Services\Security\SecurityRunRecorder;
use App\Services\Security\SecurityTaskStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * A full security check run in Horizon: "Run checks" on the page (with a task token, lock taken by the
 * controller) and the daily scheduled run (no token, takes the lock itself and skips if a run holds it).
 */
class RunSecurityChecksJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;
    public int $tries = 1;

    public function __construct(
        public readonly ?string $token = null,
        public readonly ?int $userId = null,
        public readonly string $trigger = SecurityContext::TRIGGER_UI,
    ) {
        $this->onQueue((string) config('security-checks.queue', 'rConfigDefault'));
    }

    public static function scheduled(): self
    {
        return new self(null, null, SecurityContext::TRIGGER_SCHEDULE);
    }

    public function handle(SecurityCheckRunner $runner, SecurityRunRecorder $recorder, SecurityTaskStatus $status): void
    {
        if ($this->token === null && ! Cache::lock(SecurityTaskStatus::RUN_LOCK, SecurityTaskStatus::RUN_LOCK_SECONDS)->get()) {
            return;
        }

        if ($this->token !== null) {
            $status->update($this->token, ['status' => 'running']);
        }

        try {
            $report = $runner->run(SecurityContext::current($this->trigger));
            $run = $recorder->record($report, $this->userId ? User::find($this->userId) : null);

            if ($this->token !== null) {
                $status->update($this->token, ['status' => 'done', 'result' => ['run_id' => $run->id, 'summary' => $report->summary()]]);
            }
        } finally {
            $status->releaseRun();
        }
    }

    public function failed(Throwable $exception): void
    {
        $status = app(SecurityTaskStatus::class);

        if ($this->token !== null) {
            $status->update($this->token, ['status' => 'failed', 'error' => 'The security check could not finish (' . class_basename($exception) . ').']);
        }

        $status->releaseRun();
    }
}
