<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class rConfigActivityLogArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:archive-logs {--days=*} {--rows=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Will archive x amount of activity log rows or days';

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
        $days = $this->option('days');
        $rows = $this->option('rows');
        $logmsg = null;

        if (empty($days) && empty($rows)) {
            $this->error('Must add options: --days=x or --rows=x');

            return 0;
        }

        if (! empty($rows)) {
            $count = $rows;
            ActivityLog::all()->take($count[0])
                ->each(function ($oldRecord) {
                    $newRecord = $oldRecord->replicate();
                    $newRecord['original_id'] = $oldRecord->id;
                    $newRecord['original_created_at'] = \Carbon\Carbon::createFromFormat('M d, Y G:ia', $oldRecord->created_at); // convert to datetime object form mutated created_at
                    $newRecord['original_updated_at'] = \Carbon\Carbon::createFromFormat('M d, Y G:ia', $oldRecord->updated_at);
                    $newRecord->setTable('activity_log_archives');
                    $newRecord->save();
                    $oldRecord->delete();
                });
            $logmsg = $count[0].' logs entries sent to activity log archive table!';
        }

        if (! empty($days)) {
            $count = $days;

            ActivityLog::query()
                ->where('created_at', '<=', now()->subDays($days[0])->toDateTimeString())->get()
                ->each(function ($oldRecord) {
                    $newRecord = $oldRecord->replicate();
                    $newRecord['original_id'] = $oldRecord->id;
                    $newRecord['original_created_at'] = \Carbon\Carbon::createFromFormat('M d, Y G:ia', $oldRecord->created_at); // convert to datetime object form mutated created_at
                    $newRecord['original_updated_at'] = \Carbon\Carbon::createFromFormat('M d, Y G:ia', $oldRecord->updated_at);
                    $newRecord->setTable('activity_log_archives');
                    $newRecord->save();
                    $oldRecord->delete();
                });
            $logmsg = 'logs older than '.$count[0].' days sent to activity log archive table';
        }

        $this->info($logmsg);
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'logs', '', '', '', []);
    }
}
