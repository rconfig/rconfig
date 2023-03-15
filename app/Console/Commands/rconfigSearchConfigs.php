<?php

namespace App\Console\Commands;

use App\CustomClasses\ConfigSearch;
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
        $arguments = $this->arguments();
        $options = $this->options();

        $validCategories = \Cache::remember('search.categories', 604800, function () {
            return $this->getCategories();
        });

        if (! in_array($arguments['Category'], $validCategories->toArray())) {
            $this->error('"'.$arguments['Category'].'" is an invalid category!');
            $this->error('You can check valid categories by running "php artisan rconfig:list-categories"');

            return;
        }

        if (! is_numeric($options['lines'])) {
            $this->error('Invalid lines input');

            return;
        }

        $results = (new ConfigSearch($arguments['Category'], $arguments['SearchString'], $options['lines'], $options['l']))->search();

        if (empty($results['search_results'])) {
            $this->error('No results returned. Refine your search parameters!');

            return;
        }

        foreach ($results['search_results'] as $key => $result) {
            $this->info($result);
        }
        $this->error('Duration: '.$results['duration']);
        $this->error('Line Count: '.$results['lineCount']);
        $this->error('Match Count: '.$results['matchCount']);
        $this->error('File Count: '.$results['fileCount']);

        return $results;
    }

    private function getCategories()
    {
        return Category::all()->pluck('categoryName');
    }
}

// $command = "find storage/app/rconfig/data/Routers/router1/ -type f -exec grep -n " . escapeshellarg($arguments['SearchString']) ." /dev/null {} +";
// $command1 = 'find storage/app/rconfig/data/Routers/router1/ -name "showrun*" | xargs grep -il '.$grepNumLineStr.' ' . escapeshellarg($arguments['SearchString']) . '| while read file ; do echo File:"$file"; grep ' . $grepNumLineStr . ' ' . escapeshellarg($arguments['SearchString']) . ' "$file" ; done';
// $command2 =  "find storage/app/rconfig/data/Routers/router1/ -type f | xargs grep -n -h -C2 ".escapeshellarg($arguments['SearchString'])."";
// $command3 =  "find storage/app/rconfig/data/Routers/router1/ -type f | xargs grep -n -C2 ".escapeshellarg($arguments['SearchString'])."";
// $command4 =  'find storage/app/rconfig/data/Routers/router1/ -type f | xargs grep -n -C4 '.escapeshellarg($arguments['SearchString']).' | while read file ; do echo File:"$file"; grep ' . $grepNumLineStr . ' ' . escapeshellarg($arguments['SearchString']) . ' "$file" ; done';
// $countMatches =  "find storage/app/rconfig/data/Routers/router1/ -type f -printf 'echo \"$(grep -o \"ha\" %p | wc -l) %p\";' | sh";

// //SilverSearcher
// $ag1 = "ag -c ".escapeshellarg($arguments['SearchString'])." storage/app/rconfig/data/Routers/router1";
// $ag2 = "ag -c ".escapeshellarg($arguments['SearchString'])." storage/app/rconfig/data/Routers/router1  --stats";
// $ag3 = "ag -c -t ".escapeshellarg($arguments['SearchString'])." storage/app/rconfig/data/Routers/router1  --column";
// $ag4 = 'ag -il '.escapeshellarg($arguments['SearchString']).' storage/app/rconfig/data/Routers/router1 --stats | while read file ; do echo File:"$file"; grep ' . $grepNumLineStr . ' ' . escapeshellarg($arguments['SearchString']) . ' "$file" ; done';
// // $countMatches = 'grep -o '.escapeshellarg($arguments['SearchString']).' storage/app/rconfig/data/Routers/router1/ | wc -l';
// $ag5 = "ag ".escapeshellarg($arguments['SearchString'])." storage/app/rconfig/data/Routers/router1 | xargs cat file | while read line ; do grep -lr ". escapeshellarg($arguments['SearchString']) ." * ; done";

// $awkcommand1 =  "find storage/app/rconfig/data/Routers/router1/ -type f | xargs grep -n ".escapeshellarg($arguments['SearchString']). " | awk -f search.awk";
//next find all instances of the search term under the specific cat/dir
// $command = 'find /home/rconfig/data' . $subDir . escapeshellarg($nodeId) . ' -name ' . escapeshellarg($catCommand) . ' | xargs grep -il ' . $grepNumLineStr . ' ' . $searchTerm . ' | while read file ; do echo File:"$file"; grep ' . $grepNumLineStr . ' ' . $searchTerm . ' "$file" ; done';
// // echo $command;die();
// exec(escapeshellarg($command), $searchArr);

// dd(escapeshellcmd($arguments['SearchString']));
// $headers = ['ID', 'DEVICE NAME', 'DEVICE IP'];
// $items = Device::select('id', 'device_name', 'device_ip')->orderBy('id', 'asc')->get();
// $data = $items->toArray();
// $this->info('Results for Devices List:');
// $this->table($headers, $data);
