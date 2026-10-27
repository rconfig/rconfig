<?php

use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

test('prints apache on RHEL family hosts', function () {
    $this->fakeHostFilesystem(osRelease: "NAME=\"Rocky Linux\"\nID=\"rocky\"\n");

    $this->artisan('rconfig:web-user')->expectsOutput('apache')->assertExitCode(0);
});

test('prints www-data on Ubuntu hosts', function () {
    $this->fakeHostFilesystem(osRelease: "NAME=\"Ubuntu\"\nID=ubuntu\nID_LIKE=debian\n");

    $this->artisan('rconfig:web-user')->expectsOutput('www-data')->assertExitCode(0);
});

test('prints only the user name so setup_supervisor.sh can read it', function () {
    $this->fakeHostFilesystem(osRelease: "NAME=\"Rocky Linux\"\nID=\"rocky\"\n");

    $this->artisan('rconfig:web-user')->expectsOutputToContain('apache')->doesntExpectOutputToContain(' ')->assertExitCode(0);
});
