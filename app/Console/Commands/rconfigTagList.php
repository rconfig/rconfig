<?php

namespace App\Console\Commands;

use App\Models\Tag;
use Illuminate\Console\Command;

class rconfigTagList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:list-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all tag IDs and names in rConfig';

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
        $headers = ['ID', 'TAGNAME'];
        $items = Tag::select('id', 'tagname')->orderBy('id', 'asc')->get();
        $data = $items->toArray();
        $this->info('Results for Tags List:');
        $this->table($headers, $data);
    }
}
