<?php

namespace App\Services\Security;

use App\Enums\SecurityCheckStatus;
use InvalidArgumentException;

/**
 * One check's outcome. Evidence holds scalars only and never a secret value.
 */
final class SecurityCheckResult
{
    /**
     * @param  array<string, scalar|null|list<scalar>>  $evidence
     */
    public function __construct(
        public readonly SecurityCheckStatus $status,
        public readonly string $message,
        public readonly ?string $fix = null,
        public readonly array $evidence = [],
    ) {
        foreach ($evidence as $key => $value) {
            if (! is_string($key) || ! self::isAllowedValue($value)) {
                throw new InvalidArgumentException("Security check evidence '{$key}' must be a scalar or a list of scalars.");
            }
        }
    }

    /**
     * @param  array<string, scalar|null|list<scalar>>  $evidence
     */
    public static function pass(string $message, array $evidence = []): self
    {
        return new self(SecurityCheckStatus::Pass, $message, null, $evidence);
    }

    /**
     * @param  array<string, scalar|null|list<scalar>>  $evidence
     */
    public static function warn(string $message, ?string $fix = null, array $evidence = []): self
    {
        return new self(SecurityCheckStatus::Warn, $message, $fix, $evidence);
    }

    /**
     * @param  array<string, scalar|null|list<scalar>>  $evidence
     */
    public static function fail(string $message, ?string $fix = null, array $evidence = []): self
    {
        return new self(SecurityCheckStatus::Fail, $message, $fix, $evidence);
    }

    /**
     * @param  array<string, scalar|null|list<scalar>>  $evidence
     */
    public static function skip(string $message, array $evidence = []): self
    {
        return new self(SecurityCheckStatus::Skip, $message, null, $evidence);
    }

    private static function isAllowedValue(mixed $value): bool
    {
        if ($value === null || is_scalar($value)) {
            return true;
        }

        return is_array($value) && array_is_list($value) && array_filter($value, fn (mixed $item): bool => ! is_scalar($item)) === [];
    }
}
