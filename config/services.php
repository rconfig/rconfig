<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'microsoft' => [
        'client_id' => env('MICROSOFT_CLIENT_ID', null),
        'client_secret' => env('MICROSOFT_CLIENT_SECRET', null),
        'redirect' => env('MICROSOFT_REDIRECT_URI', null),
        'tenant' => env('MICROSOFT_TENANT_ID'),
    ],

    'okta' => [
        'base_url' => env('OKTA_BASE_URL'),
        'client_id' => env('OKTA_CLIENT_ID'),
        'client_secret' => env('OKTA_CLIENT_SECRET'),
        'redirect' => env('OKTA_REDIRECT_URI'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    'saml2' => [
        'metadata' => env('SAML2_METADATA_URL', null), // must be in the public dir of this app url
        'sp_certificate' => env('SAML2_SP_CERTIFICATE') ? file_get_contents(env('SAML2_SP_CERTIFICATE')) : null,
        'sp_private_key' => env('SAML2_SP_KEY') ? file_get_contents(env('SAML2_SP_KEY')) : null,
        'sp_sign_assertions' => true, // or false to disable assertion signing
        'sp_default_binding_method' => \LightSaml\SamlConstants::BINDING_SAML2_HTTP_POST,
        'sp_name_id_format' => \LightSaml\SamlConstants::NAME_ID_FORMAT_UNSPECIFIED,
        'sp_sls' => 'auth/saml2/logout',
        'sp_acs' => 'auth/callback/saml2',
    ],

];
