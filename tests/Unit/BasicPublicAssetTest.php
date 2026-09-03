<?php

test('index php', function () {
    expect(public_path() . '/index.php')->toBeFile();
});

test('assets dir', function () {
    expect(public_path() . '/build/assets')->toBeDirectory();
});

test('assets dir count', function () {
    $files = glob(public_path() . '/build/assets/*');
    expect($files)->toBeGreaterThan(250);
});

test('manifest json', function () {
    expect(public_path() . '/build/manifest.json')->toBeFile();
});

test('main css and js exist', function () {
    $cssFiles = glob(public_path() . '/build/assets/*.css');
    $jsFiles = glob(public_path() . '/build/assets/*.js');
    expect($cssFiles)->not->toBeEmpty('No CSS files found in build/assets');
    expect($jsFiles)->not->toBeEmpty('No JS files found in build/assets');
});

test('manifest json has expected keys', function () {
    $manifestPath = public_path() . '/build/manifest.json';
    expect($manifestPath)->toBeFile();
    $manifest = json_decode(file_get_contents($manifestPath), true);
    expect($manifest)->toBeArray();

    // Check for at least one entry with js and css
    $found = false;
    foreach ($manifest as $entry) {
        if (isset($entry['file']) && (str_ends_with($entry['file'], '.js') || str_ends_with($entry['file'], '.css'))) {
            $found = true;
            break;
        }
    }
    expect($found)->toBeTrue('Manifest does not contain any js or css file entries');
});

test('all asset files are not empty', function () {
    $files = glob(public_path() . '/build/assets/*');
    foreach ($files as $file) {
        expect(filesize($file))->toBeGreaterThan(0, basename($file) . ' is empty');
    }
});

test('fonts css exists', function () {
    expect(resource_path('css/fonts.css'))->toBeFile();
});

test('fonts css is not empty', function () {
    $fontsPath = resource_path('css/fonts.css');
    expect($fontsPath)->toBeFile();
    expect(filesize($fontsPath))->toBeGreaterThan(0, 'fonts.css is empty');
});

test('sora font directory exists', function () {
    expect(public_path('fonts/sora'))->toBeDirectory();
});

test('sora font files exist', function () {
    $soraDir = public_path('fonts/sora');
    expect($soraDir)->toBeDirectory();
    $files = glob($soraDir . '/*.woff2');
    expect($files)->not->toBeEmpty('No Sora font files found');
    expect(count($files))->toBeGreaterThanOrEqual(2, 'Expected at least 2 Sora font files');
});

test('plus jakarta sans font directory exists', function () {
    expect(public_path('fonts/plus-jakarta-sans'))->toBeDirectory();
});

test('plus jakarta sans font files exist', function () {
    $plusJakartaDir = public_path('fonts/plus-jakarta-sans');
    expect($plusJakartaDir)->toBeDirectory();
    $files = glob($plusJakartaDir . '/*.woff2');
    expect($files)->not->toBeEmpty('No Plus Jakarta Sans font files found');
    expect(count($files))->toBeGreaterThanOrEqual(4, 'Expected at least 4 Plus Jakarta Sans font files');
});

test('inter font directory exists', function () {
    expect(public_path('fonts/inter'))->toBeDirectory();
});

test('inter font files exist', function () {
    $interDir = public_path('fonts/inter');
    expect($interDir)->toBeDirectory();
    $files = glob($interDir . '/*.woff2');
    expect($files)->not->toBeEmpty('No Inter font files found');
    expect(count($files))->toBeGreaterThanOrEqual(7, 'Expected at least 7 Inter font files');
});

test('raleway font directory exists', function () {
    expect(public_path('fonts/raleway'))->toBeDirectory();
});

test('raleway font files exist', function () {
    $ralewayDir = public_path('fonts/raleway');
    expect($ralewayDir)->toBeDirectory();
    $files = glob($ralewayDir . '/*.woff2');
    expect($files)->not->toBeEmpty('No Raleway font files found');
    expect(count($files))->toBeGreaterThanOrEqual(5, 'Expected at least 5 Raleway font files (weights 300, 400, 600)');
});

test('figtree font directory exists', function () {
    expect(public_path('fonts/figtree'))->toBeDirectory();
});

test('figtree font files exist', function () {
    $figtreeDir = public_path('fonts/figtree');
    expect($figtreeDir)->toBeDirectory();
    $files = glob($figtreeDir . '/*.woff2');
    expect($files)->not->toBeEmpty('No Figtree font files found');
    expect(count($files))->toBeGreaterThanOrEqual(4, 'Expected at least 4 Figtree font files (weights 300, 400, 500, 600)');
});

test('public fonts css exists', function () {
    expect(public_path('fonts/fonts.css'))->toBeFile();
});

test('public fonts css is not empty', function () {
    $fontsPath = public_path('fonts/fonts.css');
    expect($fontsPath)->toBeFile();
    expect(filesize($fontsPath))->toBeGreaterThan(0, 'public/fonts/fonts.css is empty');
});

test('public fonts css contains figtree', function () {
    $fontsPath = public_path('fonts/fonts.css');
    expect($fontsPath)->toBeFile();

    $content = file_get_contents($fontsPath);
    $this->assertStringContainsString("font-family: 'Figtree'", $content, 'Figtree font-family not found in public/fonts/fonts.css');
    $this->assertStringContainsString('figtree/figtree-latin-', $content, 'Figtree font paths not found in public/fonts/fonts.css');
});

test('all font files are not empty', function () {
    $fontDirs = [
        'sora', // for general use and headers
        'plus-jakarta-sans', // for general use
        'inter', // for forms
        'raleway', // for reports
        'figtree', // laravel horizon default font
    ];

    foreach ($fontDirs as $fontDir) {
        $dir = public_path("fonts/{$fontDir}");
        expect($dir)->toBeDirectory("Font directory {$fontDir} does not exist");

        $files = glob($dir . '/*.woff2');
        expect($files)->not->toBeEmpty("No font files found in {$fontDir}");

        foreach ($files as $file) {
            expect(filesize($file))->toBeGreaterThan(0, basename($file) . " in {$fontDir} is empty");
        }
    }
});

test('fonts css contains all font families', function () {
    $fontsPath = resource_path('css/fonts.css');
    expect($fontsPath)->toBeFile();

    $content = file_get_contents($fontsPath);

    // Check for each font family
    $this->assertStringContainsString("font-family: 'Sora'", $content, 'Sora font-family not found in fonts.css');
    $this->assertStringContainsString("font-family: 'Plus Jakarta Sans'", $content, 'Plus Jakarta Sans font-family not found in fonts.css');
    $this->assertStringContainsString("font-family: 'Inter'", $content, 'Inter font-family not found in fonts.css');
    $this->assertStringContainsString("font-family: 'Raleway'", $content, 'Raleway font-family not found in fonts.css');

    // Check for local paths (not external URLs)
    $this->assertStringContainsString('url(../../public/fonts/', $content, 'Local font paths not found in fonts.css');
    $this->assertStringNotContainsString('googleapis.com', $content, 'Google Fonts external URL still present in fonts.css');
});

test('horizon layout exists and uses local fonts', function () {
    $horizonLayoutPath = resource_path('views/vendor/horizon/layout.blade.php');

    // Ensure the override file exists
    expect($horizonLayoutPath)->toBeFile('Horizon layout override does not exist. This is required for offline installations.');

    $content = file_get_contents($horizonLayoutPath);

    // Verify it uses local fonts.css from public/fonts directory
    $this->assertStringContainsString("asset('fonts/fonts.css')", $content, 'Horizon layout should reference public/fonts/fonts.css for local Figtree fonts');
    $this->assertStringContainsString('<link rel="stylesheet"', $content, 'Horizon layout should have a stylesheet link for fonts.css');

    // Ensure no external font CDN references
    $this->assertStringNotContainsString('fonts.bunny.net', $content, 'Horizon layout still references fonts.bunny.net external CDN');
    $this->assertStringNotContainsString('fonts.googleapis.com', $content, 'Horizon layout still references Google Fonts external CDN');
    $this->assertStringNotContainsString('fonts.gstatic.com', $content, 'Horizon layout still references Google Fonts static CDN');
});

test('no external font cdn in views', function () {
    $viewsPath = resource_path('views');
    $externalFontDomains = [
        'fonts.googleapis.com',
        'fonts.gstatic.com',
        'fonts.bunny.net',
    ];

    // Recursively find all blade files
    $bladeFiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsPath)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $bladeFiles[] = $file->getPathname();
        }
    }

    expect($bladeFiles)->not->toBeEmpty('No blade files found in views directory');

    $filesWithExternalFonts = [];

    foreach ($bladeFiles as $file) {
        $content = file_get_contents($file);

        foreach ($externalFontDomains as $domain) {
            if (stripos($content, $domain) !== false) {
                $filesWithExternalFonts[] = str_replace(resource_path('views/'), '', $file) . " (contains: {$domain})";
            }
        }
    }

    expect($filesWithExternalFonts)->toBeEmpty("Found external font CDN references in view files:\n" . implode("\n", $filesWithExternalFonts) .
        "\n\nAll fonts should be self-hosted in public/fonts/ for offline installation support.");
});
