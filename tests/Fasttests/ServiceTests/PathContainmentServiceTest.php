<?php

namespace Tests\Fasttests\ServiceTests;

use App\Services\Utilities\PathContainmentService;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Cover for the shared path containment check.
 *
 * Endpoints that read a caller supplied path rely on this to decide whether the target is
 * somewhere we control. The cases below are the escapes that matter: traversal sequences,
 * the doubled dot form that defeats a naive single pass strip, absolute paths, symlinks,
 * and a sibling directory whose name shares a prefix with the base.
 */
class PathContainmentServiceTest extends TestCase
{
    private PathContainmentService $service;
    private string $sandbox;
    private string $base;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = new PathContainmentService;

        $this->sandbox = sys_get_temp_dir() . '/rconfig_containment_' . getmypid();
        $this->base = $this->sandbox . '/base';

        File::ensureDirectoryExists($this->base . '/nested/deeper');
        File::ensureDirectoryExists($this->sandbox . '/base_evil');

        File::put($this->base . '/inside.yml', 'inside');
        File::put($this->base . '/nested/deeper/deep.yml', 'deep');
        File::put($this->sandbox . '/outside.yml', 'outside');
        File::put($this->sandbox . '/base_evil/sibling.yml', 'sibling');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->sandbox);

        parent::tearDown();
    }

    public function test_it_accepts_a_file_directly_inside_the_base(): void
    {
        $this->assertSame(
            realpath($this->base . '/inside.yml'),
            $this->service->resolveFileWithin($this->base, $this->base . '/inside.yml')
        );
    }

    /**
     * Arbitrary depth below the base has to be allowed: the template repository nests
     * files as rConfig-templates/<Vendor>/<file>.yml.
     */
    public function test_it_accepts_a_file_nested_below_the_base(): void
    {
        $this->assertSame(
            realpath($this->base . '/nested/deeper/deep.yml'),
            $this->service->resolveFileWithin($this->base, $this->base . '/nested/deeper/deep.yml')
        );
    }

    public function test_it_rejects_a_parent_traversal(): void
    {
        $this->assertNull($this->service->resolveFileWithin($this->base, $this->base . '/../outside.yml'));
    }

    /**
     * The doubled dot form survives a sanitizer that strips '../' once, because removing
     * the inner sequence from '....//' leaves a working '../' behind.
     */
    public function test_it_rejects_a_doubled_dot_traversal(): void
    {
        $this->assertNull($this->service->resolveFileWithin($this->base, $this->base . '/....//outside.yml'));
    }

    public function test_it_rejects_an_absolute_path_elsewhere(): void
    {
        $this->assertNull($this->service->resolveFileWithin($this->base, '/etc/passwd'));
    }

    /**
     * The containment comparison must be separator terminated, or a sibling directory whose
     * name merely starts with the base name passes.
     */
    public function test_it_rejects_a_sibling_directory_sharing_a_name_prefix(): void
    {
        $this->assertNull(
            $this->service->resolveFileWithin($this->base, $this->sandbox . '/base_evil/sibling.yml')
        );
        $this->assertNull(
            $this->service->resolveDirectoryWithin($this->base, $this->sandbox . '/base_evil')
        );
    }

    public function test_it_rejects_a_symlink_pointing_out_of_the_base(): void
    {
        $link = $this->base . '/escape.yml';
        @symlink($this->sandbox . '/outside.yml', $link);

        if (! is_link($link)) {
            $this->markTestSkipped('Unable to create a symlink in the sandbox.');
        }

        $this->assertNull($this->service->resolveFileWithin($this->base, $link));
    }

    public function test_it_rejects_a_directory_when_a_file_is_required(): void
    {
        $this->assertNull($this->service->resolveFileWithin($this->base, $this->base . '/nested'));
    }

    public function test_it_rejects_a_file_when_a_directory_is_required(): void
    {
        $this->assertNull($this->service->resolveDirectoryWithin($this->base, $this->base . '/inside.yml'));
    }

    public function test_it_accepts_the_base_directory_itself_as_a_directory(): void
    {
        $this->assertSame(
            realpath($this->base),
            $this->service->resolveDirectoryWithin($this->base, $this->base)
        );
    }

    public function test_it_accepts_a_directory_nested_below_the_base(): void
    {
        $this->assertSame(
            realpath($this->base . '/nested/deeper'),
            $this->service->resolveDirectoryWithin($this->base, $this->base . '/nested/deeper')
        );
    }

    /**
     * On a fresh install the storage directory may never have been created, so realpath()
     * on the base returns false. Denying is correct rather than falling back to a loose check.
     */
    public function test_it_rejects_everything_when_the_base_directory_does_not_exist(): void
    {
        $missing = $this->sandbox . '/never_created';

        $this->assertNull($this->service->resolveFileWithin($missing, $this->base . '/inside.yml'));
        $this->assertNull($this->service->resolveDirectoryWithin($missing, $this->base));
    }

    public function test_it_rejects_a_target_that_does_not_exist(): void
    {
        $this->assertNull($this->service->resolveFileWithin($this->base, $this->base . '/no_such_file.yml'));
    }

    public function test_it_rejects_empty_input(): void
    {
        $this->assertNull($this->service->resolveFileWithin('', $this->base . '/inside.yml'));
        $this->assertNull($this->service->resolveFileWithin($this->base, ''));
        $this->assertNull($this->service->resolveDirectoryWithin('', ''));
    }

    public function test_it_tolerates_a_base_given_with_a_trailing_separator(): void
    {
        $this->assertSame(
            realpath($this->base . '/inside.yml'),
            $this->service->resolveFileWithin($this->base . '/', $this->base . '/inside.yml')
        );
    }
}
