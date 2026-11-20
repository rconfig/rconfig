<?php

namespace Tests\Support\Security;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use RuntimeException;

/**
 * Configurable checks for Security Center engine tests. Results are set per test through StubSecurityCheck::$results[id].
 */
abstract class StubSecurityCheck extends AbstractSecurityCheck
{
    /** @var array<string, SecurityCheckResult|RuntimeException> */
    public static array $results = [];

    protected SecurityCheckGroup $group = SecurityCheckGroup::App;
    protected SecuritySeverity $severity = SecuritySeverity::High;
    protected string $rationale = 'Stub.';

    /**
     * The stub classes in this file. Calling this loads the file, so the classes below autoload.
     *
     * @return list<class-string<StubSecurityCheck>>
     */
    public static function classes(): array
    {
        return [StubAlphaCheck::class, StubBetaCheck::class, StubRedisCheck::class];
    }

    public static function reset(): void
    {
        self::$results = [];
    }

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $result = self::$results[$this->id] ?? SecurityCheckResult::pass("{$this->id} passes.");

        if ($result instanceof RuntimeException) {
            throw $result;
        }

        return $result;
    }
}

class StubAlphaCheck extends StubSecurityCheck
{
    protected string $id = 'app.alpha';
    protected string $title = 'Alpha';
}

class StubBetaCheck extends StubSecurityCheck
{
    protected string $id = 'app.beta';
    protected string $title = 'Beta';
}

class StubRedisCheck extends StubSecurityCheck
{
    protected string $id = 'redis.gamma';
    protected string $title = 'Gamma';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Redis;
}

class StubDuplicateAlphaCheck extends StubSecurityCheck
{
    protected string $id = 'app.alpha';
}
