<?php

namespace App\Providers;

use App\Models\TrackedJob;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Qruto\Flora\Run;

class AppServiceProvider extends ServiceProvider
{
    protected $trackedJob;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // if ($this->app->environment('local')) {
        //     $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        // }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Run::newScript(
            'build-assets',
            fn (Run $run): Run => $run
                ->exec('export NODE_OPTIONS="--dns-result-order=ipv4first"')
                ->exec('export NODE_OPTIONS=--max_old_space_size=4096')
                ->exec('npm install')
                ->exec('npm run build')
        );


        //https: //github.com/laravel/horizon/issues/256
        // /**
        // * Log jobs
        // *
        // * Job dispatched & processing
        // */
        Queue::before(function (JobProcessing $event) {
            Log::info('Job ready: ' . $event->job->resolveName());

            if (
                $event->job->resolveName() === 'App\Jobs\DownloadConfigNow' ||
                $event->job->resolveName() === 'App\Jobs\DeviceDownloadJob' ||
                $event->job->resolveName() === 'App\Jobs\PurgeConfigsJob'
            ) {
                $this->trackedJob = TrackedJob::create([
                    'trackable_id' => $event->job->getJobId(),
                    'trackable_type' => $event->job->resolveName(),
                    'queue' => $event->job->getQueue(),
                ]);

                $this->trackedJob->markAsStarted();
                $this->trackedJob->setPayload($event->job->payload());
            }

            Log::info('Job started: ' . $event->job->resolveName());
        });

        // /**
        // * Log jobs
        // *
        // * Job processed
        // */
        Queue::after(function (JobProcessed $event) {
            if (
                $event->job->resolveName() === 'App\Jobs\DownloadConfigNow' ||
                $event->job->resolveName() === 'App\Jobs\DeviceDownloadJob' ||
                $event->job->resolveName() === 'App\Jobs\PurgeConfigsJob'
            ) {
                $this->trackedJob = TrackedJob::where('trackable_id', $event->job->getJobId())->first();
                $this->trackedJob->setOutput('Job done: ' . $event->job->resolveName());
                $this->trackedJob->markAsFinished('Job done: ' . $event->job->resolveName());
            }
            Log::notice('Job done: ' . $event->job->resolveName());
        });

        // /**
        // * Log jobs
        // *
        // * Job failed
        // */
        Queue::failing(function (JobFailed $event) {
            if (
                $event->job->resolveName() === 'App\Jobs\DownloadConfigNow' ||
                $event->job->resolveName() === 'App\Jobs\DeviceDownloadJob' ||
                $event->job->resolveName() === 'App\Jobs\PurgeConfigsJob'
            ) {
                $this->trackedJob = TrackedJob::where('trackable_id', $event->job->getJobId())->first();
                $this->trackedJob->setOutput('Job failed: ' . $event->job->resolveName() . '(' . $event->exception->getMessage() . ')');
                $this->trackedJob->markAsFailed($event->exception);
            }
            Log::error('Job failed: ' . $event->job->resolveName() . '(' . $event->exception->getMessage() . ')');
        });
    }
}
