<?php

namespace App\Providers;

use App\Listeners\BackupZipWasCreatedListener;
use App\Listeners\CleanupWasSuccessfulListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Google\GoogleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\MicrosoftExtendSocialite;
use SocialiteProviders\Okta\OktaExtendSocialite;
use SocialiteProviders\Saml2\Saml2ExtendSocialite;
use Illuminate\Support\Facades\Event;
use Spatie\Backup\Events\BackupZipWasCreated;
use Spatie\Backup\Events\CleanupWasSuccessful;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SocialiteWasCalled::class => [
            // ... other providers
            MicrosoftExtendSocialite::class . '@handle',
            OktaExtendSocialite::class . '@handle',
            GoogleExtendSocialite::class . '@handle',
            Saml2ExtendSocialite::class . '@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
