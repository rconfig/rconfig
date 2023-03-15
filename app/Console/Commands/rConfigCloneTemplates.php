<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class rConfigCloneTemplates extends Command
{
    protected $signature = 'rconfig:clone-templates';

    protected $hidden = true;

    protected $description = 'Clone templates from git repo https://github.com/rconfig/rconfig-templates';

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
        $dstDir = templates_path().'rConfig-templates';

        if (is_dir($dstDir)) {
            File::deleteDirectory($dstDir);
        }

        if (! is_dir($dstDir)) {
            mkdir($dstDir);
            $gitCmd = 'git -C '.templates_path().' clone https://github.com/rconfig/rConfig-templates.git';
            // dd($gitCmd);
            exec($gitCmd);

            if (count(File::allFiles($dstDir))) {
                $msg = 'Clone from https://github.com/rconfig/rconfig-templates successful!';
                $this->info($msg);
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $msg, 'clone');
            } else {
                $msg = 'Clone from https://github.com/rconfig/rconfig-templates unsuccessful! Check the application logs!';
                $this->error($msg);
                activityLogIt(__CLASS__, __FUNCTION__, 'info', $msg, 'clone');
            }
        }
    }
}
