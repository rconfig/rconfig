<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class rConfigClearHorizon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:clear-horizon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Horizon Queue History';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('queue:flush');
        $this->call('queue:restart');
        Redis::connection()
            ->del([config('horizon.prefix').'failed:*']);
        $this->info('each individual failed job flushed');
        Redis::connection()
            ->del([config('horizon.prefix').'failed_jobs']);
        $this->info('failed_jobs flushed');
        Redis::command('flushdb');
    }
}
