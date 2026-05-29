<?php

namespace App\Providers;

use App\Models\Device;
use App\Models\TrackedJob;
use App\Observers\DeviceObserver;
use App\Services\Email\MailConfigService;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Contracts\Mail\Factory as MailFactory;
use Illuminate\Mail\MailManager;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    protected $trackedJob;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Mail configuration service
        $this->app->singleton(MailConfigService::class);

        $this->configureMailLazyLoading();
    }

    /**
     * Configure lazy loading of mail configuration.
     *
     * Mail settings are loaded from the database only when the mail system
     * is actually used, rather than on every request. This avoids unnecessary
     * database queries on requests that never send mail.
     *
     * The MailConfigService is registered as a singleton and triggered via
     * afterResolving hooks when MailFactory, MailManager, or the mailer alias
     * is resolved by the container.
     */
    protected function configureMailLazyLoading(): void
    {
        $configure = function () {
            $this->app->make(MailConfigService::class)->configure();
        };

        $this->app->afterResolving(MailFactory::class, $configure);
        $this->app->afterResolving(MailManager::class, $configure);
        $this->app->afterResolving('mailer', $configure);
    }

    /**
     * Bootstrap any application services.
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
