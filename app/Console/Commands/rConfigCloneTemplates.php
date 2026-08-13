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
        $dstDir = templates_path() . 'rConfig-templates';

        if (is_dir($dstDir)) {
            File::deleteDirectory($dstDir);
        }

        if (! is_dir($dstDir)) {
            $this->ensureCloneDirectoryExists($dstDir);

            $gitCmd = 'git -C ' . templates_path() . ' clone https://github.com/rconfig/rConfig-templates.git';
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

    /**
     * Create the clone target, and the templates directory above it.
     *
     * Both are created recursively. On an install whose storage tree was never
     * fully seeded, a bind mounted Docker volume being the usual case, the
     * templates directory itself is absent, and a non recursive mkdir here
     * turns that into a 500 on template import rather than a missing directory
     * being quietly created.
     *
     * Public so it can be tested without the command's git clone running.
     */
    public function ensureCloneDirectoryExists(string $dstDir): void
    {
        File::ensureDirectoryExists(templates_path());
        File::makeDirectory($dstDir, 0775, true, true);
    }
}
