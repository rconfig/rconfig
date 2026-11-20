<?php

use App\Enums\SecurityCheckGroup;
use App\Enums\SecurityCheckStatus;
use App\Enums\SecuritySeverity;
use App\Services\Security\Contracts\SecurityCheck;
use App\Services\Security\SecurityCheckRegistry;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityCheckRunner;
use App\Services\Security\SecurityContext;
use App\Services\Security\SecurityReport;
use App\Services\Security\SecurityReportItem;
use Carbon\CarbonImmutable;
use Tests\Support\Security\StubDuplicateAlphaCheck;
use Tests\Support\Security\StubSecurityCheck;

beforeEach(function () {
    StubSecurityCheck::reset();
    $this->registry = new SecurityCheckRegistry(app(), StubSecurityCheck::classes());
});

function securityContext(): SecurityContext
{
    return new SecurityContext(SecurityContext::TRIGGER_CLI, false, false, true, '8.3.0', 'cli');
}

function securityItem(string $id, SecurityCheckStatus $status, SecuritySeverity $severity = SecuritySeverity::High): SecurityReportItem
{
    return new SecurityReportItem($id, SecurityCheckGroup::App, $id, $severity, $status, 'message', null, [], '', [], '', 0);
}

function securityReport(array $items): SecurityReport
{
    return new SecurityReport(CarbonImmutable::now(), SecurityContext::TRIGGER_CLI, '8.3.0', $items, 5);
}

test('evidence accepts scalars and lists of scalars only', function () {
    expect(SecurityCheckResult::pass('ok', ['a' => 1, 'b' => ['x', 'y'], 'c' => null])->evidence)->toHaveKey('b');

    SecurityCheckResult::pass('ok', ['nested' => ['key' => 'value']]);
})->throws(InvalidArgumentException::class);

test('the registry selects by ID, group and prefix and reports unknown patterns', function () {
    $ids = fn (array $checks): array => array_map(fn (SecurityCheck $check): string => $check->id(), $checks);

    expect($ids($this->registry->select()))->toBe(['app.alpha', 'app.beta', 'redis.gamma'])
        ->and($ids($this->registry->select(['redis.gamma'])))->toBe(['redis.gamma'])
        ->and($ids($this->registry->select(['redis'])))->toBe(['redis.gamma'])
        ->and($ids($this->registry->select(['app.*'])))->toBe(['app.alpha', 'app.beta'])
        ->and($ids($this->registry->select(['app.beta,redis'])))->toBe(['app.beta', 'redis.gamma'])
        ->and($this->registry->unknownPatterns(['app.alpha', 'nope', 'web.*']))->toBe(['nope', 'web.*']);
});

test('the registry refuses duplicate check IDs', function () {
    (new SecurityCheckRegistry(app(), [...StubSecurityCheck::classes(), StubDuplicateAlphaCheck::class]))->all();
})->throws(InvalidArgumentException::class, 'Duplicate security check ID app.alpha');

test('a check that throws becomes SKIP naming only the exception class', function () {
    StubSecurityCheck::$results['app.beta'] = new RuntimeException('connection to secret-host:6379 refused');

    $report = (new SecurityCheckRunner($this->registry))->run(securityContext());
    $beta = $report->find('app.beta');

    expect($beta->status)->toBe(SecurityCheckStatus::Skip)
        ->and($beta->message)->toContain('Could not run: RuntimeException')
        ->and($beta->message)->not->toContain('secret-host')
        ->and($report->find('app.alpha')->status)->toBe(SecurityCheckStatus::Pass);
});

test('the exit code is 0 when everything passes, 1 for warnings and 2 for any failure', function () {
    expect(securityReport([securityItem('a.a', SecurityCheckStatus::Pass), securityItem('a.b', SecurityCheckStatus::Skip)])->exitCode())->toBe(0)
        ->and(securityReport([securityItem('a.a', SecurityCheckStatus::Warn), securityItem('a.b', SecurityCheckStatus::Pass)])->exitCode())->toBe(1)
        ->and(securityReport([securityItem('a.a', SecurityCheckStatus::Warn), securityItem('a.b', SecurityCheckStatus::Fail)])->exitCode())->toBe(2);
});

test('reports sort failures first and survive a round trip through their stored array', function () {
    $report = securityReport([
        securityItem('a.pass', SecurityCheckStatus::Pass, SecuritySeverity::Critical),
        securityItem('a.warn', SecurityCheckStatus::Warn, SecuritySeverity::Low),
        securityItem('a.fail_low', SecurityCheckStatus::Fail, SecuritySeverity::Low),
        securityItem('a.fail_high', SecurityCheckStatus::Fail, SecuritySeverity::Critical),
    ]);

    $stored = json_decode(json_encode($report->toArray()), true);
    $restored = SecurityReport::fromArray($stored);

    expect(array_column($stored['checks'], 'id'))->toBe(['a.fail_high', 'a.fail_low', 'a.warn', 'a.pass'])
        ->and($stored['summary'])->toBe(['pass' => 1, 'warn' => 1, 'fail' => 2, 'skip' => 0, 'total' => 4])
        ->and($stored['product'])->toBe('core')
        ->and($restored->summary())->toBe($report->summary())
        ->and($restored->exitCode())->toBe(2);
});

test('every registered check has a stable ID, metadata and a docs URL on its group page and anchor', function () {
    $checks = app(SecurityCheckRegistry::class)->all();

    expect($checks)->toHaveCount(26);

    foreach ($checks as $check) {
        $group = $check->group()->value;

        expect($check->id())->toStartWith($group . '.')
            ->and($check->title())->not->toBeEmpty()
            ->and($check->rationale())->not->toBeEmpty()
            ->and($check->references())->not->toBeEmpty()
            ->and($check->fixGuide())->not->toBeEmpty()
            ->and($check->docsUrl())->toBe('https://v8coredocs.rconfig.com/security/checks/' . $group . '/#' . str_replace(['.', '_'], '-', $check->id()));
    }
});
