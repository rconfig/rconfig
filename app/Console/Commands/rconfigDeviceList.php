<?php

namespace App\Console\Commands;

use App\Models\Device;
use Illuminate\Console\Command;

class rconfigDeviceList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:list-devices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all Device IDs and names in rConfig';

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
        $headers = ['ID', 'DEVICE NAME', 'DEVICE IP'];
        $items = Device::select('id', 'device_name', 'device_ip')->orderBy('id', 'asc')->get();
        $data = $items->toArray();
        $this->info('Results for Devices List:');
        $this->table($headers, $data);
    }
}
