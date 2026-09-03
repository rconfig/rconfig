<?php

test('composer json has correct namespace configuration', function () {
    $composerJsonPath = base_path('composer.json');
    expect($composerJsonPath)->toBeFile('composer.json file does not exist');

    $composerJson = json_decode(file_get_contents($composerJsonPath), true);

    expect($composerJson)->toBeArray('composer.json content could not be parsed as JSON');
    expect($composerJson)->toHaveKey('autoload', message: 'composer.json is missing autoload section');
    expect($composerJson['autoload'])->toHaveKey('psr-4', message: 'composer.json autoload is missing psr-4 section');

    expect($composerJson['autoload']['psr-4'])->toHaveKey('App\\', message: 'The App\\ namespace is missing in composer.json autoload psr-4 section');
    expect($composerJson['autoload']['psr-4']['App\\'])->toEqual('app/', 'The App\\ namespace should map to app/ directory');
});
