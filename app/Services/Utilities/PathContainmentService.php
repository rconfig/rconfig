<?php

namespace App\Services\Utilities;

/**
 * Confirm that a caller supplied path resolves to somewhere inside a directory we control.
 *
 * Several endpoints accept a filename or path from the client and read it back off disk.
 * Concatenating that onto a base directory is not enough on its own: '..' sequences and
 * symlinks both walk out of it. realpath() collapses the former and follows the latter, so
 * comparing the resolved target against the resolved base closes both at once.
 *
 * The resolved path is returned rather than a boolean so callers open exactly what was
 * checked, instead of re-deriving it and risking a mismatch.
 */
class PathContainmentService
{
    /**
     * Resolve $path and return it only if it is a real file inside $baseDirectory.
     *
     * @return string|null the resolved absolute path, or null if it is not contained
     */
    public function resolveFileWithin(string $baseDirectory, string $path): ?string
    {
        return $this->resolveWithin($baseDirectory, $path, true);
    }

    /**
     * Resolve $path and return it only if it is a real directory inside $baseDirectory.
     *
     * The base directory itself is accepted, since listing the top level is legitimate.
     *
     * @return string|null the resolved absolute path, or null if it is not contained
     */
    public function resolveDirectoryWithin(string $baseDirectory, string $path): ?string
    {
        return $this->resolveWithin($baseDirectory, $path, false);
    }

    /**
     * @param  bool  $mustBeFile  true to require a file, false to require a directory
     */
    private function resolveWithin(string $baseDirectory, string $path, bool $mustBeFile): ?string
    {
        if ($baseDirectory === '' || $path === '') {
            return null;
        }

        /**
         * A base that does not exist yet resolves to false. That happens on a fresh install
         * where the storage directory has never been written to, and denying is the right
         * answer: there is nothing legitimate to serve out of a directory that is not there.
         */
        $resolvedBase = realpath($baseDirectory);
        $resolvedPath = realpath($path);

        if ($resolvedBase === false || $resolvedPath === false) {
            return null;
        }

        $base = rtrim($resolvedBase, DIRECTORY_SEPARATOR);

        if ($mustBeFile) {
            if (! is_file($resolvedPath)) {
                return null;
            }
        } elseif (! is_dir($resolvedPath)) {
            return null;
        } elseif ($resolvedPath === $base) {
            return $resolvedPath;
        }

        /**
         * The trailing separator matters. Without it a sibling directory whose name merely
         * starts with the base, for example 'exports_evil' against 'exports', would pass.
         */
        return str_starts_with($resolvedPath, $base . DIRECTORY_SEPARATOR) ? $resolvedPath : null;
    }
}
