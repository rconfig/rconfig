<?php

namespace App\Services\Security;

use App\Services\Security\Contracts\SecurityCheck;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Every security check class, from config/security-checks.php.
 */
class SecurityCheckRegistry
{
    /** @var list<SecurityCheck>|null */
    private ?array $resolved = null;

    /**
     * @param  list<class-string<SecurityCheck>>|null  $classes  overridable for tests
     */
    public function __construct(
        private readonly Container $container,
        private readonly ?array $classes = null,
    ) {}

    /**
     * @return list<SecurityCheck>
     */
    public function all(): array
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        $checks = [];
        $seen = [];

        foreach ($this->classes ?? (array) config('security-checks.checks', []) as $class) {
            $check = $this->container->make($class);

            if (! $check instanceof SecurityCheck) {
                throw new InvalidArgumentException("{$class} does not implement " . SecurityCheck::class . '.');
            }

            if (isset($seen[$check->id()])) {
                throw new InvalidArgumentException("Duplicate security check ID {$check->id()} ({$class} and {$seen[$check->id()]}).");
            }

            $seen[$check->id()] = $class;
            $checks[] = $check;
        }

        return $this->resolved = $checks;
    }

    public function find(string $id): ?SecurityCheck
    {
        foreach ($this->all() as $check) {
            if ($check->id() === $id) {
                return $check;
            }
        }

        return null;
    }

    /**
     * Checks matching any pattern: an ID ("redis.auth"), a group ("redis") or a prefix ("web.*").
     * No patterns selects every check.
     *
     * @param  list<string>  $only
     * @return list<SecurityCheck>
     */
    public function select(array $only = []): array
    {
        $only = $this->normalise($only);

        if ($only === []) {
            return $this->all();
        }

        return array_values(array_filter(
            $this->all(),
            fn (SecurityCheck $check): bool => $this->matchesAny($check, $only),
        ));
    }

    /**
     * Patterns that match no check, so the command can say so instead of silently running nothing.
     *
     * @param  list<string>  $patterns
     * @return list<string>
     */
    public function unknownPatterns(array $patterns): array
    {
        return array_values(array_filter(
            $this->normalise($patterns),
            fn (string $pattern): bool => array_filter($this->all(), fn (SecurityCheck $check): bool => $this->matches($check, $pattern)) === [],
        ));
    }

    /**
     * @param  list<string>  $patterns
     */
    private function matchesAny(SecurityCheck $check, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($this->matches($check, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function matches(SecurityCheck $check, string $pattern): bool
    {
        if ($pattern === $check->id() || $pattern === $check->group()->value) {
            return true;
        }

        return str_ends_with($pattern, '*') && str_starts_with($check->id(), rtrim($pattern, '*'));
    }

    /**
     * Accepts "a,b" and ["a", "b"] alike.
     *
     * @param  list<string>  $patterns
     * @return list<string>
     */
    private function normalise(array $patterns): array
    {
        return array_values(array_unique(array_filter(array_map(
            'trim',
            explode(',', implode(',', $patterns)),
        ), fn (string $pattern): bool => $pattern !== '')));
    }
}
