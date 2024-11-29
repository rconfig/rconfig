<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;

class rconfigSearchConfigs extends Command
{
    protected $signature = 'rconfig:search-configs
                            {Category : The Category of devices to search.}
                            {SearchString : The search string. If searching multiple words, wrap in double-quotes.}
                            {--lines=0 : Number of lines to display before/ after matches.}
                            {--l : Include latest downloaded files in the search only}';

    protected $description = 'Search all config files for a given string';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->error('This command is deprecated. Please contact support if your require it.');
    }
}
