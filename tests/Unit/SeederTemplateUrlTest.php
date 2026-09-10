<?php

const SEEDER_DIR = __DIR__ . '/../../database/seeders';

const URL_PATTERN = '#https://raw\.githubusercontent\.com/rconfig/rConfig-templates/(?<ref>[^/]+)/(?<path>[^\'"\s]+)#';

/**
 * @return array<int, array{file: string, url: string, ref: string, path: string}>
 */
function templateUrls(): array
{
    $found = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(SEEDER_DIR, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());

        if (preg_match_all(URL_PATTERN, $contents, $matches, PREG_SET_ORDER) === 0) {
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

test('seeders reference the templates repo', function () {
    expect(templateUrls())->not->toBeEmpty('No rConfig-templates URLs were found in the seeders. If the seeders legitimately no longer '
    . 'download templates, delete this test; otherwise the URL pattern has drifted and this guard '
    . 'is silently passing.');
});

test('every template url uses the default branch or a release tag', function () {
    foreach (templateUrls() as $url) {
        expect($url['ref'])->toMatch('/^(main|v\d+\.\d+\.\d+)$/', sprintf(
            '%s points a templates URL at "%s". Seeder URLs must use the repo default branch "main" '
            . '(the current policy) or a release tag such as v2.0.0 if a build is ever pinned. '
            . '"master" is the pre-restructure branch and a "refs/heads/..." prefix is not a bare ref, '
            . 'so both silently 404. URL: %s',
            $url['file'],
            $url['ref'],
            $url['url']
        ));
    }
});

test('no template url uses a pre restructure path', function () {
    foreach (templateUrls() as $url) {
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

        expect($url['path'])->toBe(strtolower($url['path']), sprintf(
            '%s uses an uppercase templates path. The restructured repo is lowercase and hyphenated, '
            . 'so pre-restructure paths such as Brocade/ or Mikrotik/ will 404. URL: %s',
            $url['file'],
            $url['url']
        ));
    }
});
