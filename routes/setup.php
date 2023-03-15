<?php

use Illuminate\Support\Facades\App;
use Qruto\Flora\Run;

App::install(
    'production',
    fn (Run $run) => $run
        ->command('key:generate', ['--force' => true])
        ->command('migrate', ['--force' => true])
        ->command('passport:install')
        ->exec('if [ -f /etc/supervisord.d/horizon_supervisor.ini ]; then unlink /etc/supervisord.d/horizon_supervisor.ini; fi')
        ->exec('sed -i -e s+PWD+$PWD+g $PWD/horizon_supervisor.ini')
        ->exec('sudo ln -s $PWD/horizon_supervisor.ini /etc/supervisord.d/horizon_supervisor.ini')
        ->exec('systemctl restart supervisord')
        ->exec('sed -i -e s+PWD+$PWD+g $PWD/rconfig-vhost.conf')
        ->exec('if [ -f /etc/httpd/conf.d/rconfig-vhost.conf ]; then unlink /etc/httpd/conf.d/rconfig-vhost.conf; fi')
        ->exec('sudo ln -s $PWD/rconfig-vhost.conf /etc/httpd/conf.d/rconfig-vhost.conf')
        ->exec('systemctl restart httpd')
        ->exec('chown -R apache:apache $PWD')
        ->command('rconfig:clear-all')
        ->command('rconfig:sync-tasks')
        ->script('cache')
        ->script('build')
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
