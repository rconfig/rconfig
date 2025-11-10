<?php

namespace Tests\Unit;

use Tests\TestCase;

class BasicPublicAssetTest extends TestCase
{
    public function test_index_php()
    {
        $this->assertFileExists(public_path() . '/index.php');
    }

    public function test_assets_dir()
    {
        $this->assertDirectoryExists(public_path() . '/build/assets');
    }

    public function test_assets_dir_count()
    {
        $files = glob(public_path() . '/build/assets/*');
        $this->assertGreaterThan(250, $files);
    }

    public function test_manifest_json()
    {
        $this->assertFileExists(public_path() . '/build/manifest.json');
    }

    public function test_main_css_and_js_exist()
    {
        $cssFiles = glob(public_path() . '/build/assets/*.css');
        $jsFiles = glob(public_path() . '/build/assets/*.js');
        $this->assertNotEmpty($cssFiles, 'No CSS files found in build/assets');
        $this->assertNotEmpty($jsFiles, 'No JS files found in build/assets');
    }

    public function test_manifest_json_has_expected_keys()
    {
        $manifestPath = public_path() . '/build/manifest.json';
        $this->assertFileExists($manifestPath);
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);
        // Check for at least one entry with js and css
        $found = false;
        foreach ($manifest as $entry) {
            if (isset($entry['file']) && (str_ends_with($entry['file'], '.js') || str_ends_with($entry['file'], '.css'))) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Manifest does not contain any js or css file entries');
    }

    public function test_all_asset_files_are_not_empty()
    {
        $files = glob(public_path() . '/build/assets/*');
        foreach ($files as $file) {
            $this->assertGreaterThan(0, filesize($file), basename($file) . ' is empty');
        }
    }

    public function test_fonts_css_exists()
    {
        $this->assertFileExists(resource_path('css/fonts.css'));
    }

    public function test_fonts_css_is_not_empty()
    {
        $fontsPath = resource_path('css/fonts.css');
        $this->assertFileExists($fontsPath);
        $this->assertGreaterThan(0, filesize($fontsPath), 'fonts.css is empty');
    }

    public function test_sora_font_directory_exists()
    {
        $this->assertDirectoryExists(public_path('fonts/sora'));
    }

    public function test_sora_font_files_exist()
    {
        $soraDir = public_path('fonts/sora');
        $this->assertDirectoryExists($soraDir);
        $files = glob($soraDir . '/*.woff2');
        $this->assertNotEmpty($files, 'No Sora font files found');
        $this->assertGreaterThanOrEqual(2, count($files), 'Expected at least 2 Sora font files');
    }

    public function test_plus_jakarta_sans_font_directory_exists()
    {
        $this->assertDirectoryExists(public_path('fonts/plus-jakarta-sans'));
    }

    public function test_plus_jakarta_sans_font_files_exist()
    {
        $plusJakartaDir = public_path('fonts/plus-jakarta-sans');
        $this->assertDirectoryExists($plusJakartaDir);
        $files = glob($plusJakartaDir . '/*.woff2');
        $this->assertNotEmpty($files, 'No Plus Jakarta Sans font files found');
        $this->assertGreaterThanOrEqual(4, count($files), 'Expected at least 4 Plus Jakarta Sans font files');
    }

    public function test_inter_font_directory_exists()
    {
        $this->assertDirectoryExists(public_path('fonts/inter'));
    }

    public function test_inter_font_files_exist()
    {
        $interDir = public_path('fonts/inter');
        $this->assertDirectoryExists($interDir);
        $files = glob($interDir . '/*.woff2');
        $this->assertNotEmpty($files, 'No Inter font files found');
        $this->assertGreaterThanOrEqual(7, count($files), 'Expected at least 7 Inter font files');
    }

    public function test_raleway_font_directory_exists()
    {
        $this->assertDirectoryExists(public_path('fonts/raleway'));
    }

    public function test_raleway_font_files_exist()
    {
        $ralewayDir = public_path('fonts/raleway');
        $this->assertDirectoryExists($ralewayDir);
        $files = glob($ralewayDir . '/*.woff2');
        $this->assertNotEmpty($files, 'No Raleway font files found');
        $this->assertGreaterThanOrEqual(5, count($files), 'Expected at least 5 Raleway font files (weights 300, 400, 600)');
    }

    public function test_figtree_font_directory_exists()
    {
        $this->assertDirectoryExists(public_path('fonts/figtree'));
    }

    public function test_figtree_font_files_exist()
    {
        $figtreeDir = public_path('fonts/figtree');
        $this->assertDirectoryExists($figtreeDir);
        $files = glob($figtreeDir . '/*.woff2');
        $this->assertNotEmpty($files, 'No Figtree font files found');
        $this->assertGreaterThanOrEqual(4, count($files), 'Expected at least 4 Figtree font files (weights 300, 400, 500, 600)');
    }

    public function test_public_fonts_css_exists()
    {
        $this->assertFileExists(public_path('fonts/fonts.css'));
    }

    public function test_public_fonts_css_is_not_empty()
    {
        $fontsPath = public_path('fonts/fonts.css');
        $this->assertFileExists($fontsPath);
        $this->assertGreaterThan(0, filesize($fontsPath), 'public/fonts/fonts.css is empty');
    }

    public function test_public_fonts_css_contains_figtree()
    {
        $fontsPath = public_path('fonts/fonts.css');
        $this->assertFileExists($fontsPath);

        $content = file_get_contents($fontsPath);
        $this->assertStringContainsString("font-family: 'Figtree'", $content, 'Figtree font-family not found in public/fonts/fonts.css');
        $this->assertStringContainsString('figtree/figtree-latin-', $content, 'Figtree font paths not found in public/fonts/fonts.css');
    }

    public function test_all_font_files_are_not_empty()
    {
        $fontDirs = [
            'sora', // for general use and headers
            'plus-jakarta-sans', // for general use
            'inter', // for forms
            'raleway', // for reports
            'figtree', // laravel horizon default font
        ];

        foreach ($fontDirs as $fontDir) {
            $dir = public_path("fonts/{$fontDir}");
            $this->assertDirectoryExists($dir, "Font directory {$fontDir} does not exist");

            $files = glob($dir . '/*.woff2');
            $this->assertNotEmpty($files, "No font files found in {$fontDir}");

            foreach ($files as $file) {
                $this->assertGreaterThan(0, filesize($file), basename($file) . " in {$fontDir} is empty");
            }
        }
    }

    public function test_fonts_css_contains_all_font_families()
    {
        $fontsPath = resource_path('css/fonts.css');
        $this->assertFileExists($fontsPath);

        $content = file_get_contents($fontsPath);

        // Check for each font family
        $this->assertStringContainsString("font-family: 'Sora'", $content, 'Sora font-family not found in fonts.css');
        $this->assertStringContainsString("font-family: 'Plus Jakarta Sans'", $content, 'Plus Jakarta Sans font-family not found in fonts.css');
        $this->assertStringContainsString("font-family: 'Inter'", $content, 'Inter font-family not found in fonts.css');
        $this->assertStringContainsString("font-family: 'Raleway'", $content, 'Raleway font-family not found in fonts.css');

        // Check for local paths (not external URLs)
        $this->assertStringContainsString('url(../../public/fonts/', $content, 'Local font paths not found in fonts.css');
        $this->assertStringNotContainsString('googleapis.com', $content, 'Google Fonts external URL still present in fonts.css');
    }

    /**
     * Test that Horizon layout file exists and uses local fonts.
     * 
     * Reason: Laravel Horizon (queue monitoring dashboard) ships with external font references 
     * by default (fonts.bunny.net for Figtree font). For offline installations where external 
     * CDNs are unreachable, this causes 20-30+ second page load delays. We override the vendor 
     * layout to use our local fonts.css (public/fonts/fonts.css) which includes self-hosted 
     * Figtree fonts. This test ensures the override exists and doesn't reference external 
     * font services.
     */
    public function test_horizon_layout_exists_and_uses_local_fonts()
    {
        $horizonLayoutPath = resource_path('views/vendor/horizon/layout.blade.php');

        // Ensure the override file exists
        $this->assertFileExists($horizonLayoutPath, 'Horizon layout override does not exist. This is required for offline installations.');

        $content = file_get_contents($horizonLayoutPath);

        // Verify it uses local fonts.css from public/fonts directory
        $this->assertStringContainsString("asset('fonts/fonts.css')", $content, 'Horizon layout should reference public/fonts/fonts.css for local Figtree fonts');
        $this->assertStringContainsString('<link rel="stylesheet"', $content, 'Horizon layout should have a stylesheet link for fonts.css');

        // Ensure no external font CDN references
        $this->assertStringNotContainsString('fonts.bunny.net', $content, 'Horizon layout still references fonts.bunny.net external CDN');
        $this->assertStringNotContainsString('fonts.googleapis.com', $content, 'Horizon layout still references Google Fonts external CDN');
        $this->assertStringNotContainsString('fonts.gstatic.com', $content, 'Horizon layout still references Google Fonts static CDN');
    }

    /**
     * Test that no external font CDN references exist in any view files.
     * 
     * Reason: External font CDN references (Google Fonts, Bunny Fonts, etc.) cause significant
     * performance issues in offline or air-gapped installations where these services are unreachable.
     * This test scans all Blade view files to ensure no external font CDNs are referenced.
     * All fonts should be self-hosted in public/fonts/ and referenced via fonts.css.
     */
    public function test_no_external_font_cdn_in_views()
    {
        $viewsPath = resource_path('views');
        $externalFontDomains = [
            'fonts.googleapis.com',
            'fonts.gstatic.com',
            'fonts.bunny.net',
        ];

        // Recursively find all blade files
        $bladeFiles = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewsPath)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $bladeFiles[] = $file->getPathname();
            }
        }

        $this->assertNotEmpty($bladeFiles, 'No blade files found in views directory');

        $filesWithExternalFonts = [];

        foreach ($bladeFiles as $file) {
            $content = file_get_contents($file);

            foreach ($externalFontDomains as $domain) {
                if (stripos($content, $domain) !== false) {
                    $filesWithExternalFonts[] = str_replace(resource_path('views/'), '', $file) . " (contains: {$domain})";
                }
            }
        }

        $this->assertEmpty(
            $filesWithExternalFonts,
            "Found external font CDN references in view files:\n" . implode("\n", $filesWithExternalFonts) .
                "\n\nAll fonts should be self-hosted in public/fonts/ for offline installation support."
        );
    }
}
