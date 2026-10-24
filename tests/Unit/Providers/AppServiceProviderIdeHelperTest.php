<?php

use App\Providers\AppServiceProvider;
use Illuminate\Support\ServiceProvider;

/**
 * update.sh runs composer install --no-dev, which removes ide-helper. A box
 * left on APP_ENV=local must still boot without it. The real ide-helper is
 * package discovered on a dev box, so a stand-in provider is used to see
 * whether registerIdeHelper() itself registers anything.
 */
class IdeHelperStandInServiceProvider extends ServiceProvider {}

function ideHelperProviderFor(string $providerClass): AppServiceProvider
{
    return new class(app(), $providerClass) extends AppServiceProvider
    {
        public function __construct($app, private string $providerClass)
        {
            parent::__construct($app);
        }

        public function registerIdeHelperNow(): void
        {
            $this->registerIdeHelper();
        }

        protected function ideHelperProvider(): string
        {
            return $this->providerClass;
        }
    };
}

test('it skips ide-helper in local when the dev package is not installed', function () {
    app()->detectEnvironment(fn (): string => 'local');

    ideHelperProviderFor('Barryvdh\\LaravelIdeHelper\\MissingAfterNoDevServiceProvider')->registerIdeHelperNow();

    expect(app()->getProviders('Barryvdh\\LaravelIdeHelper\\MissingAfterNoDevServiceProvider'))->toBe([]);
});

test('it still registers ide-helper in local when the package is installed', function () {
    app()->detectEnvironment(fn (): string => 'local');

    ideHelperProviderFor(IdeHelperStandInServiceProvider::class)->registerIdeHelperNow();

    expect(app()->getProviders(IdeHelperStandInServiceProvider::class))->not->toBe([]);
});

test('it never registers ide-helper outside local', function () {
    app()->detectEnvironment(fn (): string => 'production');

    ideHelperProviderFor(IdeHelperStandInServiceProvider::class)->registerIdeHelperNow();

    expect(app()->getProviders(IdeHelperStandInServiceProvider::class))->toBe([]);
});
