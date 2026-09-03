<?php

namespace Tests\Unit;

use Tests\UnitTestCase;

class ComposerMicrosoftProviderVersionTest extends UnitTestCase
{
    public function test_only_specific_microsoft_provider_version_present()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $require = $composer['require'] ?? [];
        $this->assertArrayHasKey('socialiteproviders/microsoft', $require, 'Missing socialiteproviders/microsoft in composer.json');
        $this->assertEquals('^4.9', $require['socialiteproviders/microsoft'], 'socialiteproviders/microsoft must be constrained to ^4.9');
    }
}
