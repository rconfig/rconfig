<?php

namespace App\Providers;

use App\Models\Device;
use App\Models\TrackedJob;
use App\Observers\DeviceObserver;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;
use Qruto\Flora\Run;

class AppServiceProvider extends ServiceProvider
{
    protected $trackedJob;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerIdeHelper();
        $this->configureHttps();
        $this->runObservers();
        $this->logViewerAuthCheck();
        $this->configureQueueLogging();
    }
    protected function registerIdeHelper(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }

    protected function configureHttps(): void
    {
        if (config('app.force_https') && $this->app->environment('production')) {
            \URL::forceScheme('https');
        }
    }

    protected function runObservers(): void
    {
        // Prevent loading tasks and related models during unit tests to avoid premature autoloading and mocking conflicts
        // Task model loads many related models, which can cause issues in unit tests.
        if (app()->runningUnitTests()) {
            return;
        }

        Device::observe(DeviceObserver::class);
    }

    protected function logViewerAuthCheck(): void
    {
        LogViewer::auth(function ($request) {
            return Auth::check();
        });
    }

    protected function configureQueueLogging(): void
    {
        Queue::before(function (JobProcessing $event) {
            $this->handleJobStarted($event);
        });

        Queue::after(function (JobProcessed $event) {
            $this->handleJobCompleted($event);
        });

        Queue::failing(function (JobFailed $event) {
            $this->handleJobFailed($event);
        });
    }

    protected function handleJobStarted(JobProcessing $event): void
    {
        $jobName = $event->job->resolveName();

        if ($this->shouldTrackJob($jobName)) {
            Log::info('Job ready: ' . $jobName);
            Log::info('Job dispatched: ' . json_encode($event->job->payload(), JSON_PRETTY_PRINT));

            $this->trackedJob = TrackedJob::create([
                'trackable_id' => $event->job->getJobId(),
                'trackable_type' => $jobName,
                'queue' => $event->job->getQueue(),
            ]);

            $this->trackedJob->markAsStarted();
            $this->trackedJob->setPayload($event->job->payload());
            Log::info('Job started: ' . $jobName);
        }
    }

    /**
     * Handle job completed event
     */
    protected function handleJobCompleted(JobProcessed $event): void
    {
        $jobName = $event->job->resolveName();

        if ($this->shouldTrackJob($jobName)) {
            $this->trackedJob = TrackedJob::where('trackable_id', $event->job->getJobId())->first();
            $this->trackedJob->setOutput('Job done: ' . $jobName);
            $this->trackedJob->markAsFinished('Job done: ' . $jobName);
            Log::notice('Job done: ' . $jobName);
        }
    }

    /**
     * Handle job failed event
     */
    protected function handleJobFailed(JobFailed $event): void
    {
        $jobName = $event->job->resolveName();

        if ($this->shouldTrackJob($jobName)) {
            $this->trackedJob = TrackedJob::where('trackable_id', $event->job->getJobId())->first();
            $this->trackedJob->setOutput('Job failed: ' . $jobName . '(' . $event->exception->getMessage() . ')');
            $this->trackedJob->markAsFailed($event->exception);
        }

        Log::error('Job failed: ' . $jobName . '(' . $event->exception->getMessage() . ')');
    }

    protected function shouldTrackJob(string $jobName): bool
    {
        $trackedJobs = [
            'App\Jobs\DownloadConfigNowJob',
            'App\Jobs\DeviceDownloadJob',
            'App\Jobs\PurgeConfigsJob',
        ];

        return in_array($jobName, $trackedJobs) ||
            str_contains($jobName, 'rconfig:download-api');
    }
}
