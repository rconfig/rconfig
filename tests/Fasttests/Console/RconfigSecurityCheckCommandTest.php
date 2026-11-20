<?php

use App\Enums\WorkerCheckStatus;
use App\Models\Device;
use App\Models\RestApiToken;
use App\Models\SecurityCheckRun;
use App\Models\User;
use App\Services\Security\Probes\TlsInspector;
use App\Services\Security\Probes\WebProber;
use App\Services\Security\SecurityCheckRegistry;
use App\Services\Security\SecurityCheckResult;
use App\Services\System\WorkerUserCheckService;
use App\Services\Utilities\RedisSecurityService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Process;
use Tests\Support\Security\StubSecurityCheck;

beforeEach(function () {
    $this->beginTransaction();
    Notification::fake();
    StubSecurityCheck::reset();
    SecurityCheckRun::query()->delete();
});

afterEach(function () {
    $this->rollBackTransaction();
});

function useStubSecurityChecks(): void
{
    app()->instance(SecurityCheckRegistry::class, new SecurityCheckRegistry(app(), StubSecurityCheck::classes()));
}

test('--list prints every registered check without running any', function () {
    $this->artisan('rconfig:security-check --list')
        ->expectsOutputToContain('app.debug')
        ->expectsOutputToContain('os.firewall')
        ->expectsOutputToContain('26 checks.')
        ->assertExitCode(0);

    expect(SecurityCheckRun::query()->count())->toBe(0);
});

test('exit codes are 0 when all pass, 1 for warnings and 2 for failures', function () {
    useStubSecurityChecks();

    $this->artisan('rconfig:security-check --no-store')->assertExitCode(0);

    StubSecurityCheck::$results['app.beta'] = SecurityCheckResult::warn('Beta warns.');
    $this->artisan('rconfig:security-check --no-store')->assertExitCode(1);

    StubSecurityCheck::$results['redis.gamma'] = SecurityCheckResult::fail('Gamma fails.', 'Run: fix gamma');
    $this->artisan('rconfig:security-check --no-store')->expectsOutputToContain('Run: fix gamma')->assertExitCode(2);
});

test('--json prints the schema v1 report', function () {
    useStubSecurityChecks();
    StubSecurityCheck::$results['app.beta'] = SecurityCheckResult::warn('Beta warns.', null, ['count' => 2]);

    Artisan::call('rconfig:security-check', ['--json' => true, '--no-store' => true]);
    $report = json_decode(Artisan::output(), true);

    expect($report)->toHaveKeys(['schema', 'product', 'version', 'ran_at', 'trigger', 'duration_ms', 'host', 'summary', 'checks'])
        ->and($report['schema'])->toBe(1)
        ->and($report['product'])->toBe('core')
        ->and($report['summary'])->toBe(['pass' => 2, 'warn' => 1, 'fail' => 0, 'skip' => 0, 'total' => 3])
        ->and($report['checks'][0])->toMatchArray(['id' => 'app.beta', 'status' => 'warn', 'evidence' => ['count' => 2]])
        ->and($report['checks'][0])->toHaveKeys(['group', 'group_label', 'title', 'severity', 'message', 'fix', 'docs_url', 'references', 'rationale']);
});

test('--only runs the selected checks, is not stored and rejects unknown patterns', function () {
    useStubSecurityChecks();

    Artisan::call('rconfig:security-check', ['--json' => true, '--only' => ['redis']]);
    expect(array_column(json_decode(Artisan::output(), true)['checks'], 'id'))->toBe(['redis.gamma'])
        ->and(SecurityCheckRun::query()->count())->toBe(0);

    $this->artisan('rconfig:security-check --only=nope')->expectsOutputToContain('No check matches: nope')->assertExitCode(2);
});

test('full runs store only the latest run', function () {
    useStubSecurityChecks();

    $this->artisan('rconfig:security-check');
    StubSecurityCheck::$results['app.alpha'] = SecurityCheckResult::fail('Alpha fails.');
    $this->artisan('rconfig:security-check');

    expect(SecurityCheckRun::query()->count())->toBe(1)
        ->and(SecurityCheckRun::latestRun()->fail_count)->toBe(1)
        ->and(SecurityCheckRun::latestRun()->report()->find('app.alpha')->message)->toBe('Alpha fails.');

    $this->artisan('rconfig:security-check --no-store');
    expect(SecurityCheckRun::query()->count())->toBe(1);
});

test('no secret value appears in the command JSON or the Security Review API', function () {
    $sentinels = [
        'app_key' => str_pad('SNTLAPPKEY', 32, 'K'),
        'db' => 'SNTL-DB-PASSWORD-7c1f',
        'redis' => 'SNTL-REDIS-PASSWORD-9a2e',
        'token' => 'SNTL-REST-TOKEN-4b8d',
        'device' => 'SNTL-DEVICE-CRED-1e6f',
    ];
    $appKey = 'base64:' . base64_encode($sentinels['app_key']);

    config([
        'app.key' => $appKey,
        'database.connections.' . config('database.default') . '.password' => $sentinels['db'],
        'database.redis.default.password' => $sentinels['redis'],
        'app.url' => 'https://rconfig.example.test',
    ]);
    app()->forgetInstance('encrypter');
    Crypt::clearResolvedInstance('encrypter');

    $token = RestApiToken::factory()->create(['api_token' => $sentinels['token'], 'api_token_name' => 'old token']);
    DB::table('rest_api_tokens')->where('id', $token->id)->update(['created_at' => now()->subYears(2)]);
    $device = Device::factory()->create(['device_password' => $sentinels['device']]);
    DB::table('devices')->where('id', $device->id)->update(['device_enable_password' => $sentinels['device']]);

    $leakyEnv = "APP_KEY={$appKey}\nDB_PASSWORD={$sentinels['db']}\nREDIS_PASSWORD={$sentinels['redis']}\n";
    Http::fake(['*/.env' => Http::response($leakyEnv, 200), '*' => Http::response('<html></html>', 200)]);
    Process::fake(['*' => Process::result('inactive')]);
    app()->instance(WebProber::class, new WebProber(['https://192.0.2.10']));

    $tls = Mockery::mock(TlsInspector::class);
    $tls->shouldReceive('certificate')->andReturn(['valid_to' => time() + 90 * 86400, 'days_left' => 90]);
    app()->instance(TlsInspector::class, $tls);

    $redis = Mockery::mock(RedisSecurityService::class);
    $redis->shouldReceive('status')->andReturn(['reachable' => true, 'password_required' => true, 'app_authenticated' => true, 'secure' => true, 'bind' => '127.0.0.1', 'protected_mode' => 'yes', 'detail' => '']);
    app()->instance(RedisSecurityService::class, $redis);

    $workers = Mockery::mock(WorkerUserCheckService::class);
    $workers->shouldReceive('report')->andReturn(['state' => 'docker', 'summary' => 'Docker', 'status' => WorkerCheckStatus::Skipped, 'web_user' => 'apache', 'checked_as_root' => false, 'checks' => [], 'fix' => null]);
    app()->instance(WorkerUserCheckService::class, $workers);

    Artisan::call('rconfig:security-check', ['--json' => true]);
    $json = Artisan::output();
    $report = json_decode($json, true);

    expect($report['summary']['total'])->toBeGreaterThanOrEqual(20)
        ->and(collect($report['checks'])->firstWhere('id', 'web.env_exposed')['status'])->toBe('fail')
        ->and(collect($report['checks'])->firstWhere('id', 'api.tokens')['status'])->toBe('warn');

    $this->actingAs(User::factory()->create());
    $api = $this->getJson('/api/settings/security-review')->assertOk()->getContent();

    foreach ([$json, $api] as $output) {
        foreach ([...array_values($sentinels), $appKey] as $secret) {
            expect($output)->not->toContain($secret);
        }
    }
});
