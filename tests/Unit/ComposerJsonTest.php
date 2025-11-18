<?php

namespace Tests\Unit;

use Tests\TestCase;

class ComposerJsonTest extends TestCase
{
    public function test_composer_json()
    {
        $composerJsonPath = base_path('composer.json');
        $composerJsonContent = file_get_contents($composerJsonPath);
        $composerJson = json_decode($composerJsonContent, true);

        // Check for the presence of the "autoload" key
        $this->assertArrayHasKey('autoload', $composerJson, 'The "autoload" key is missing in composer.json.');

        // Check for the presence of the "psr-4" key
        $this->assertArrayHasKey('psr-4', $composerJson['autoload'], 'The "psr-4" key is missing in composer.json autoload section.');

        // Check for the presence of the "App\\" namespace
        $this->assertArrayHasKey('App\\', $composerJson['autoload']['psr-4'], 'The "App\\" namespace is missing in composer.json autoload psr-4 section.');

        // version equals config('app.version')
        $this->assertEquals(config('app.version'), $composerJson['version'], 'The version in composer.json does not match the app version in config.');

        // does not have repository key
        $this->assertArrayNotHasKey('repositories', $composerJson, 'The "repositories" key should not be present in composer.json.');

        // does not have rconfighub/vector-server-pkg
        $this->assertArrayNotHasKey('rconfighub/vector-server-pkg', $composerJson, 'The "rconfighub/vector-server-pkg" key should not be present in composer.json.');

        // should have a 6000 s timeout
        $this->assertEquals(6000, $composerJson['config']['process-timeout'], 'The process-timeout in composer.json is not set to 6000 seconds.');
    }
}
