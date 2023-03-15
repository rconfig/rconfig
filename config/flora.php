<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Force Assets Publish
    |--------------------------------------------------------------------------
    |
    | Force publish assets on every installation or update. By default, assets
    | will always be force published, which would completely automate the
    | setup. Switch it to false if you want to manually publish assets.
    | For example if you prefer to commit them.
    */
    'force_publish' => false,

    /*
    |--------------------------------------------------------------------------
    | Publishable Assets
    |--------------------------------------------------------------------------
    |
    | List of assets that will be published during installation or update.
    | Most of required assets detects on the way. If you need specific
    | tag or provider, feel free to add it to the array.
    */
    'assets' => [
        'laravel-assets',
    ],
];
