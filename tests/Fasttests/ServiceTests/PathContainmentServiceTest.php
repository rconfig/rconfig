<?php

use App\Services\Utilities\PathContainmentService;
use Illuminate\Support\Facades\File;

/*
 * Cover for the shared path containment check.
 *
 * Endpoints that read a caller supplied path rely on this to decide whether the target is
 * somewhere we control. The cases below are the escapes that matter: traversal sequences,
 * the doubled dot form that defeats a naive single pass strip, absolute paths, symlinks,
 * and a sibling directory whose name shares a prefix with the base.
 */
beforeEach(function () {
    $this->service = new PathContainmentService;

    $this->sandbox = sys_get_temp_dir() . '/rconfig_containment_' . getmypid();
    $this->base = $this->sandbox . '/base';

    File::ensureDirectoryExists($this->base . '/nested/deeper');
    File::ensureDirectoryExists($this->sandbox . '/base_evil');

    File::put($this->base . '/inside.yml', 'inside');
    File::put($this->base . '/nested/deeper/deep.yml', 'deep');
    File::put($this->sandbox . '/outside.yml', 'outside');
    File::put($this->sandbox . '/base_evil/sibling.yml', 'sibling');
});

afterEach(function () {
    File::deleteDirectory($this->sandbox);
});

test('it accepts a file directly inside the base', function () {
    expect($this->service->resolveFileWithin($this->base, $this->base . '/inside.yml'))
        ->toBe(realpath($this->base . '/inside.yml'));
});

test('it accepts a file nested below the base', function () {
    // Arbitrary depth below the base has to be allowed: the template repository nests
    // files as rConfig-templates/<Vendor>/<file>.yml.
    expect($this->service->resolveFileWithin($this->base, $this->base . '/nested/deeper/deep.yml'))
        ->toBe(realpath($this->base . '/nested/deeper/deep.yml'));
});

test('it rejects a parent traversal', function () {
    expect($this->service->resolveFileWithin($this->base, $this->base . '/../outside.yml'))->toBeNull();
});

test('it rejects a doubled dot traversal', function () {
    // The doubled dot form survives a sanitizer that strips '../' once, because removing
    // the inner sequence from '....//' leaves a working '../' behind.
    expect($this->service->resolveFileWithin($this->base, $this->base . '/....//outside.yml'))->toBeNull();
});

test('it rejects an absolute path elsewhere', function () {
    expect($this->service->resolveFileWithin($this->base, '/etc/passwd'))->toBeNull();
});

test('it rejects a sibling directory sharing a name prefix', function () {
    // The containment comparison must be separator terminated, or a sibling directory whose
    // name merely starts with the base name passes.
    expect($this->service->resolveFileWithin($this->base, $this->sandbox . '/base_evil/sibling.yml'))->toBeNull();
    expect($this->service->resolveDirectoryWithin($this->base, $this->sandbox . '/base_evil'))->toBeNull();
});

test('it rejects a symlink pointing out of the base', function () {
    $link = $this->base . '/escape.yml';
    @symlink($this->sandbox . '/outside.yml', $link);

    if (! is_link($link)) {
        $this->markTestSkipped('Unable to create a symlink in the sandbox.');
    }

    expect($this->service->resolveFileWithin($this->base, $link))->toBeNull();
});

test('it rejects a directory when a file is required', function () {
    expect($this->service->resolveFileWithin($this->base, $this->base . '/nested'))->toBeNull();
});

test('it rejects a file when a directory is required', function () {
    expect($this->service->resolveDirectoryWithin($this->base, $this->base . '/inside.yml'))->toBeNull();
});

test('it accepts the base directory itself as a directory', function () {
    expect($this->service->resolveDirectoryWithin($this->base, $this->base))->toBe(realpath($this->base));
});

test('it accepts a directory nested below the base', function () {
    expect($this->service->resolveDirectoryWithin($this->base, $this->base . '/nested/deeper'))
        ->toBe(realpath($this->base . '/nested/deeper'));
});

test('it rejects everything when the base directory does not exist', function () {
    // On a fresh install the storage directory may never have been created, so realpath()
    // on the base returns false. Denying is correct rather than falling back to a loose check.
    $missing = $this->sandbox . '/never_created';

    expect($this->service->resolveFileWithin($missing, $this->base . '/inside.yml'))->toBeNull();
    expect($this->service->resolveDirectoryWithin($missing, $this->base))->toBeNull();
});

test('it rejects a target that does not exist', function () {
    expect($this->service->resolveFileWithin($this->base, $this->base . '/no_such_file.yml'))->toBeNull();
});

test('it rejects empty input', function () {
    expect($this->service->resolveFileWithin('', $this->base . '/inside.yml'))->toBeNull();
    expect($this->service->resolveFileWithin($this->base, ''))->toBeNull();
    expect($this->service->resolveDirectoryWithin('', ''))->toBeNull();
});

test('it tolerates a base given with a trailing separator', function () {
    expect($this->service->resolveFileWithin($this->base . '/', $this->base . '/inside.yml'))
        ->toBe(realpath($this->base . '/inside.yml'));
});
