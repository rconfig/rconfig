<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSecurityReviewNotifyRequest;
use App\Jobs\RunSecurityChecksJob;
use App\Models\SecurityCheckRun;
use App\Services\Security\SecurityReviewSettings;
use App\Services\Security\SecurityTaskStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Settings > Security Center. Reads the stored run; runs are queued to Horizon and polled by task token.
 */
class SecurityReviewController extends Controller
{
    public function __construct(
        private readonly SecurityTaskStatus $tasks,
        private readonly SecurityReviewSettings $settings,
    ) {}

    /**
     * The latest report, schedule, alert switch and any running task.
     */
    public function index(): JsonResponse
    {
        $latest = SecurityCheckRun::latestRun();

        return response()->json([
            'report' => $latest?->report()->toArray(),
            'run' => $latest?->toSummaryArray(),
            'schedule' => ['enabled' => (bool) config('security-checks.schedule_enabled', true), 'time' => (string) config('security-checks.schedule_time')],
            'notify' => $this->settings->notifyEnabled(),
            'advisories_url' => (string) config('security-checks.advisories_url'),
            'running_task' => Cache::get(SecurityTaskStatus::RUNNING_TOKEN),
        ]);
    }

    /**
     * Queues a full run. One at a time: a second click while one runs returns the running task.
     */
    public function run(Request $request): JsonResponse
    {
        if (! Cache::lock(SecurityTaskStatus::RUN_LOCK, SecurityTaskStatus::RUN_LOCK_SECONDS)->get()) {
            return response()->json(['token' => Cache::get(SecurityTaskStatus::RUNNING_TOKEN), 'status' => 'running', 'message' => 'A security check is already running.'], 202);
        }

        $token = $this->tasks->create($request->user()?->id);
        Cache::put(SecurityTaskStatus::RUNNING_TOKEN, $token, SecurityTaskStatus::RUN_LOCK_SECONDS);
        RunSecurityChecksJob::dispatch($token, $request->user()?->id);

        return response()->json(['token' => $token, 'status' => 'queued'], 202);
    }

    public function task(string $token): JsonResponse
    {
        $task = $this->tasks->get($token);
        abort_if($task === null, 404, 'Task not found or expired.');

        return response()->json(['token' => $token] + $task);
    }

    public function updateNotify(UpdateSecurityReviewNotifyRequest $request): JsonResponse
    {
        $this->settings->setNotify($request->boolean('enabled'));

        return response()->json(['notify' => $this->settings->notifyEnabled()]);
    }
}
