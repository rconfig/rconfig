<?php

namespace App\Console\Commands;

use App\Models\Config;
use App\Models\Device;
use Illuminate\Console\Command;

class rConfigReportLastDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:report-lastDownload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Output report of all devices latest downloaded configs';

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
        $devices = Device::select('id', 'device_name', 'device_ip')->orderBy('id', 'asc')->get();

        $collection = collect();
        foreach ($devices as $key => $value) {
            $uniqueCommandsCount = Config::select('command')->where('device_id', $value->id)->distinct('command')->count();
            $collection->push(Config::all()->where('device_id', $value->id)->sortByDesc('created_at')->take($uniqueCommandsCount));
        }

        $flattenedCollection = $collection->flatten();
        $dataOutput = collect();
        foreach ($flattenedCollection as $key => $value) {
            $dataOutput->push($value->only('id', 'device_id', 'device_name', 'device_category', 'command', 'config_filesize', 'created_at'));
        }

        $headers = ['ID', 'DEVICE ID', 'DEVICE_NAME', 'CATEGORY', 'COMMAND', 'FILE SZIE', 'CREATED DATE'];

        $this->info('Results for Latest Configs List:');
        $this->table($headers, $dataOutput);
    }
}
