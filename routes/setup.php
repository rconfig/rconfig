<?php

use Illuminate\Support\Facades\App;
use Qruto\Flora\Run;

App::install(
    'production',
    fn (Run $run) => $run
        ->command('key:generate', ['--force' => true])
        ->command('migrate', ['--force' => true])
        ->command('passport:install')
        ->command('rconfig:clear-all')
        ->command('rconfig:sync-tasks')
        ->script('cache')
    // ->script('build-assets')
);

// App::install(
//     'local',
//     fn (Run $run) => $run
//         ->command('key:generate')
//         ->command('migrate')
//         ->command('storage:link')
//         ->script('build')
//         ->command('ide-helper:generate')
//         ->command('ide-helper:meta')
//         ->command('ide-helper:models', ['--nowrite' => true])
// );

// App::install(
//     'production',
//     fn (Run $run) => $run
//         ->command('key:generate', ['--force' => true])
//         ->command('migrate', ['--force' => true])
//         ->command('storage:link')
//         ->script('cache')
//         ->script('build')
// );

// App::update(
//     'local',
//     fn (Run $run) => $run
//         ->command('migrate')
//         ->command('cache:clear')
//         ->script('build')
//         ->command('ide-helper:generate')
// );

// App::update(
//     'production',
//     fn (Run $run) => $run
//         ->script('cache')
//         ->command('migrate', ['--force' => true])
//         ->command('cache:clear')
//         ->command('horizon:terminate')
//         ->script('build')
// );
