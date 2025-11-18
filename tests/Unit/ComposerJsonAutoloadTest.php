<?php

namespace Tests\Unit;

use Tests\TestCase;

class ComposerJsonAutoloadTest extends TestCase
{
    /**
     * Ensure composer.json contains PSR-4 autoload for App\
     */
    public function test_composer_json_has_correct_namespace_configuration(): void
    {
        $composerJsonPath = base_path('composer.json');
        $this->assertFileExists($composerJsonPath, 'composer.json file does not exist');

        $composerJson = json_decode(file_get_contents($composerJsonPath), true);

        $this->assertIsArray($composerJson, 'composer.json content could not be parsed as JSON');
        $this->assertArrayHasKey('autoload', $composerJson, 'composer.json is missing autoload section');
        $this->assertArrayHasKey('psr-4', $composerJson['autoload'], 'composer.json autoload is missing psr-4 section');

        $this->assertArrayHasKey('App\\', $composerJson['autoload']['psr-4'], 'The App\\ namespace is missing in composer.json autoload psr-4 section');
        $this->assertEquals(
            'app/',
            $composerJson['autoload']['psr-4']['App\\'],
            'The App\\ namespace should map to app/ directory'
        );
    }
}
