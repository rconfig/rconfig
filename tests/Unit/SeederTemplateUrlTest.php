<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Guards the templates repo URLs baked into the seeders.
 *
 * The templates repo was restructured in 2026 (see its docs/MIGRATION.md): directories and
 * file names moved to a single lowercase hyphenated convention and no redirect stubs were
 * left behind. Seeder URLs track the repo's default branch, so this guard keeps them on that
 * one ref and off the pre-restructure paths that no longer resolve.
 *
 * This test is deliberately offline. It checks the shape of every URL, not its reachability,
 * so it stays deterministic in CI. It cannot catch a future restructure that moves a file
 * again: tracking a moving branch trades that risk for always getting the latest template.
 */
class SeederTemplateUrlTest extends TestCase
{
    private const SEEDER_DIR = __DIR__ . '/../../database/seeders';

    private const URL_PATTERN = '#https://raw\.githubusercontent\.com/rconfig/rConfig-templates/(?<ref>[^/]+)/(?<path>[^\'"\s]+)#';

    /**
     * @return array<int, array{file: string, url: string, ref: string, path: string}>
     */
    private function templateUrls(): array
    {
        $found = [];

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::SEEDER_DIR, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $contents = (string) file_get_contents($file->getPathname());

            if (preg_match_all(self::URL_PATTERN, $contents, $matches, PREG_SET_ORDER) === 0) {
                continue;
            }

            foreach ($matches as $match) {
                $found[] = [
                    'file' => $file->getFilename(),
                    'url' => $match[0],
                    'ref' => $match['ref'],
                    'path' => $match['path'],
                ];
            }
        }

        return $found;
    }

    public function test_seeders_reference_the_templates_repo(): void
    {
        $this->assertNotEmpty(
            $this->templateUrls(),
            'No rConfig-templates URLs were found in the seeders. If the seeders legitimately no longer '
            . 'download templates, delete this test; otherwise the URL pattern has drifted and this guard '
            . 'is silently passing.'
        );
    }

    public function test_every_template_url_uses_the_default_branch_or_a_release_tag(): void
    {
        foreach ($this->templateUrls() as $url) {
            $this->assertMatchesRegularExpression(
                '/^(main|v\d+\.\d+\.\d+)$/',
                $url['ref'],
                sprintf(
                    '%s points a templates URL at "%s". Seeder URLs must use the repo default branch "main" '
                    . '(the current policy) or a release tag such as v2.0.0 if a build is ever pinned. '
                    . '"master" is the pre-restructure branch and a "refs/heads/..." prefix is not a bare ref, '
                    . 'so both silently 404. URL: %s',
                    $url['file'],
                    $url['ref'],
                    $url['url']
                )
            );
        }
    }

    public function test_no_template_url_uses_a_pre_restructure_path(): void
    {
        foreach ($this->templateUrls() as $url) {
            $this->assertStringNotContainsString(
                '%20',
                $url['path'],
                sprintf(
                    '%s uses a percent encoded space in a templates path. The old "Palo Alto Networks" '
                    . 'directory never resolved under that encoding and no longer exists. URL: %s',
                    $url['file'],
                    $url['url']
                )
            );

            $this->assertSame(
                strtolower($url['path']),
                $url['path'],
                sprintf(
                    '%s uses an uppercase templates path. The restructured repo is lowercase and hyphenated, '
                    . 'so pre-restructure paths such as Brocade/ or Mikrotik/ will 404. URL: %s',
                    $url['file'],
                    $url['url']
                )
            );
        }
    }
}
