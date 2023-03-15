<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class rconfigCatList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rconfig:list-categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all category IDs and names in rConfig';

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
        $headers = ['ID', 'CATEGORY NAME'];
        $items = Category::select('id', 'categoryName')->orderBy('id', 'asc')->get();
        $data = $items->toArray();
        $this->info('Results for Categories List:');
        $this->table($headers, $data);
    }
}
