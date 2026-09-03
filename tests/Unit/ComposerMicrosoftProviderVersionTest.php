<?php

test('only specific microsoft provider version present', function () {
    $composer = json_decode(file_get_contents(base_path('composer.json')), true);
    $require = $composer['require'] ?? [];
    expect($require)->toHaveKey('socialiteproviders/microsoft', message: 'Missing socialiteproviders/microsoft in composer.json');
    expect($require['socialiteproviders/microsoft'])->toEqual('^4.9', 'socialiteproviders/microsoft must be constrained to ^4.9');
});
