<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;

class rconfigTaskList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:list-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all task IDs and names in rConfig';

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
        $headers = ['ID', 'NAME', 'DESCRIPTION'];
        $items = Task::select('id', 'task_name', 'task_desc')->orderBy('id', 'asc')->get();
        $data = $items->map->only('id', 'task_name', 'task_desc')->toArray();
        $this->info('Results for Tasks List:');
        $this->table($headers, $data);
    }
}
