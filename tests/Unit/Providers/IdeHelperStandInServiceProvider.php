<?php

namespace Tests\Unit\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Stands in for the ide-helper provider in AppServiceProviderIdeHelperTest, so the
 * test sees what registerIdeHelper() registers without the real dev package.
 */
class IdeHelperStandInServiceProvider extends ServiceProvider {}
