<?php

test('composer json', function () {
    $composerJsonPath = base_path('composer.json');
    $composerJsonContent = file_get_contents($composerJsonPath);
    $composerJson = json_decode($composerJsonContent, true);

    // Check for the presence of the "autoload" key
    expect($composerJson)->toHaveKey('autoload', message: 'The "autoload" key is missing in composer.json.');

    // Check for the presence of the "psr-4" key
    expect($composerJson['autoload'])->toHaveKey('psr-4', message: 'The "psr-4" key is missing in composer.json autoload section.');

    // Check for the presence of the "App\\" namespace
    expect($composerJson['autoload']['psr-4'])->toHaveKey('App\\', message: 'The "App\\" namespace is missing in composer.json autoload psr-4 section.');

    // version equals config('app.version')
    expect($composerJson['version'])->toEqual(config('app.version'), 'The version in composer.json does not match the app version in config.');

    // does not have repository key
    expect($composerJson)->not->toHaveKey('repositories', message: 'The "repositories" key should not be present in composer.json.');

    // does not have rconfighub/vector-server-pkg
    expect($composerJson)->not->toHaveKey('rconfighub/vector-server-pkg', message: 'The "rconfighub/vector-server-pkg" key should not be present in composer.json.');

    // should have a 6000 s timeout
    expect($composerJson['config']['process-timeout'])->toEqual(6000, 'The process-timeout in composer.json is not set to 6000 seconds.');
});

test('version file matches app version', function () {
    $versionFilePath = base_path('VERSION');

    expect($versionFilePath)->toBeFile('The root VERSION file is missing.');

    expect(trim((string) file_get_contents($versionFilePath)))->toBe((string) config('app.version'), 'The root VERSION file does not match the app version in config. Bump VERSION, composer.json and config/app.php together when releasing.');
});
