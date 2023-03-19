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
            'setup-supervisor',
            fn (Run $run): Run => $run
                ->exec('if [ -f /etc/redhat-release ]; then  SUPDIR=/etc/supervisord.d; fi;')
                ->exec('if [ -f /etc/lsb-release ]; then  SUPDIR=/etc/supervisor; fi;')
                ->exec('if [ -f $SUPDIR/horizon_supervisor.ini ]; then unlink $SUPDIR/horizon_supervisor.ini; fi')
                ->exec('sed -i -e s+PWD+$PWD+g $PWD/horizon_supervisor.ini')
                ->exec('sudo ln -s $PWD/horizon_supervisor.ini $SUPDIR/horizon_supervisor.ini')
                ->exec('if [ -f /etc/redhat-release ]; then systemctl restart supervisord; fi;')
                ->exec('if [ -f /etc/lsb-release ]; then service supervisor restart; fi;')
        );
        Run::newScript(
            'setup-apache',
            fn (Run $run): Run => $run
                ->exec('if [ -f /etc/redhat-release ]; then  HTTPDDIR=/etc/httpd/conf.d/; fi;')
                ->exec('if [ -f /etc/lsb-release ]; then  HTTPDDIR=/etc/apache2/sites-enabled; fi;')
                ->exec('sed -i -e s+PWD+$PWD+g $PWD/rconfig-vhost.conf')
                ->exec('if [ -f $HTTPDDIR/rconfig-vhost.conf ]; then unlink $HTTPDDIR/rconfig-vhost.conf; fi')
                ->exec('sudo ln -s $PWD/rconfig-vhost.conf $HTTPDDIR/rconfig-vhost.conf')
                ->exec('if [ -f $HTTPDDIR/000-default.conf ]; then unlink $HTTPDDIR/000-default.conf; fi;')
                ->exec('if [ -f /etc/redhat-release ]; then chown -R apache:apache $PWD; fi;')
                ->exec('if [ -f /etc/redhat-release ]; then systemctl restart httpd; fi;')
                ->exec('if [ -f /etc/lsb-release ]; then sudo chown -R www-data:www-data /var/www/html/rconfig; fi;')
                ->exec('if [ -f /etc/lsb-release ]; then sudo chown -R $USER:www-data /var/www/html/rconfig; fi;')
                ->exec('if [ -f /etc/lsb-release ]; then service apache2 restart; fi;')
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
