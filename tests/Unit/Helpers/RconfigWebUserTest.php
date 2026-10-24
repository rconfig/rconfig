<?php

use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

test('RHEL family distributions resolve to apache', function (string $osRelease) {
    $this->fakeHostFilesystem(osRelease: $osRelease);

    expect(rconfig_web_user())->toBe(['apache', 'apache']);
})->with([
    'Rocky Linux' => "NAME=\"Rocky Linux\"\nVERSION=\"9.6 (Blue Onyx)\"\nID=\"rocky\"\n",
    'RHEL' => "NAME=\"Red Hat Enterprise Linux\"\nID=\"rhel\"\n",
    'AlmaLinux' => "NAME=\"AlmaLinux\"\nID=\"almalinux\"\n",
    'CentOS Stream' => "NAME=\"CentOS Stream\"\nID=\"centos\"\n",
    'Oracle Linux' => "NAME=\"Oracle Linux Server\"\nID=\"ol\"\n",
    'Amazon Linux 2023' => "NAME=\"Amazon Linux\"\nVERSION=\"2023\"\nID=\"amzn\"\n",
]);

test('Debian family distributions resolve to www-data', function (string $osRelease) {
    $this->fakeHostFilesystem(osRelease: $osRelease);

    expect(rconfig_web_user())->toBe(['www-data', 'www-data']);
})->with([
    'Ubuntu' => "NAME=\"Ubuntu\"\nVERSION=\"24.04 LTS (Noble Numbat)\"\nID=ubuntu\nID_LIKE=debian\n",
    'Debian bookworm (Docker image)' => "PRETTY_NAME=\"Debian GNU/Linux 12 (bookworm)\"\nNAME=\"Debian GNU/Linux\"\nID=debian\n",
]);

test('an unrecognised distribution falls back to www-data', function () {
    $this->fakeHostFilesystem(osRelease: "NAME=\"Arch Linux\"\nID=arch\n");

    expect(rconfig_web_user())->toBe(['www-data', 'www-data']);
});

test('a missing or unreadable os-release falls back to www-data without throwing', function () {
    $this->fakeHostFilesystem(osRelease: null);

    expect(rconfig_web_user())->toBe(['www-data', 'www-data']);
});

test('the group always matches the user', function () {
    $this->fakeHostFilesystem();

    [$user, $group] = rconfig_web_user();

    expect($group)->toBe($user);
});
