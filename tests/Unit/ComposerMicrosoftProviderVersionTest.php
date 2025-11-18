<?php

namespace Tests\Unit;

use Tests\TestCase;

class ComposerMicrosoftProviderVersionTest extends TestCase
{
    public function test_only_specific_microsoft_provider_version_present()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $require = $composer['require'] ?? [];
        $this->assertArrayHasKey('socialiteproviders/microsoft', $require, 'Missing socialiteproviders/microsoft in composer.json');
        $this->assertEquals('4.6.0', $require['socialiteproviders/microsoft'], 'socialiteproviders/microsoft must be version 4.6.0');
    }
}
