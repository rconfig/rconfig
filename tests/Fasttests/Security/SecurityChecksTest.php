<?php

use App\Enums\SecurityCheckStatus;
use App\Enums\WorkerCheckStatus;
use App\Models\Device;
use App\Models\RestApiToken;
use App\Models\User;
use App\Services\Security\Checks\Accounts\DefaultPasswordCheck;
use App\Services\Security\Checks\Accounts\LoginThrottleCheck;
use App\Services\Security\Checks\Api\RestApiTokensCheck;
use App\Services\Security\Checks\App\AppDebugCheck;
use App\Services\Security\Checks\App\AppEnvCheck;
use App\Services\Security\Checks\App\AppKeyCheck;
use App\Services\Security\Checks\App\AppUrlHttpsCheck;
use App\Services\Security\Checks\App\EnvFilePermissionsCheck;
use App\Services\Security\Checks\Data\ConfigFileModesCheck;
use App\Services\Security\Checks\Data\CredentialsEncryptedCheck;
use App\Services\Security\Checks\Devices\TelnetDevicesCheck;
use App\Services\Security\Checks\Os\FirewallCheck;
use App\Services\Security\Checks\Php\DisplayErrorsCheck;
use App\Services\Security\Checks\Php\PhpVersionMinimumCheck;
use App\Services\Security\Checks\Redis\RedisAuthCheck;
use App\Services\Security\Checks\Runtime\QueueWorkersCheck;
use App\Services\Security\Checks\Runtime\SchedulerHeartbeatCheck;
use App\Services\Security\Checks\Runtime\WorkerUserCheck;
use App\Services\Security\Checks\Tls\CertificateExpiryCheck;
use App\Services\Security\Checks\Versions\RconfigLatestCheck;
use App\Services\Security\Checks\Web\EnvExposedCheck;
use App\Services\Security\Checks\Web\HorizonGuestCheck;
use App\Services\Security\Checks\Web\LogViewerGuestCheck;
use App\Services\Security\Checks\Web\RegistrationClosedCheck;
use App\Services\Security\Checks\Web\StorageExposedCheck;
use App\Services\Security\Checks\Web\VcsExposedCheck;
use App\Services\Security\Probes\PhpRuntimeInspector;
use App\Services\Security\Probes\TlsInspector;
use App\Services\Security\Probes\WebProber;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use App\Services\System\WorkerUserCheckService;
use App\Services\Utilities\HostFilesystem;
use App\Services\Utilities\RedisSecurityService;
use App\Services\Utilities\VersionCheckService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Spatie\Health\Checks\Checks\ScheduleCheck;

const SECURITY_SPA_PAGE = '<!DOCTYPE html><html><head><title>rConfig</title></head><body><div id="app"></div></body></html>';

beforeEach(function () {
    $this->beginTransaction();
    $this->context = new SecurityContext(SecurityContext::TRIGGER_CLI, false, false, true, '8.3.0', 'cli');
    $this->app->instance(WebProber::class, new WebProber(['http://192.0.2.10', 'https://192.0.2.10']));
});

afterEach(function () {
    $this->rollBackTransaction();
});

function runCheck(string $class, SecurityContext $context): SecurityCheckResult
{
    return app($class)->run($context);
}

test('app.debug passes when off and fails when on', function () {
    config(['app.debug' => false]);
    expect(runCheck(AppDebugCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);

    config(['app.debug' => true]);
    expect(runCheck(AppDebugCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);
});

test('app.env passes in production, warns in staging and fails in local', function (string $env, SecurityCheckStatus $status) {
    config(['app.env' => $env]);

    expect(runCheck(AppEnvCheck::class, $this->context)->status)->toBe($status);
})->with([
    ['production', SecurityCheckStatus::Pass],
    ['staging', SecurityCheckStatus::Warn],
    ['local', SecurityCheckStatus::Fail],
]);

test('app.key fails when missing or malformed and passes for a valid key, never echoing it', function () {
    $key = 'base64:' . base64_encode(random_bytes(32));
    config(['app.cipher' => 'AES-256-CBC', 'app.key' => $key]);
    $result = runCheck(AppKeyCheck::class, $this->context);
    expect($result->status)->toBe(SecurityCheckStatus::Pass)
        ->and(json_encode([$result->message, $result->fix, $result->evidence]))->not->toContain(substr($key, 7, 20));

    config(['app.key' => '']);
    expect(runCheck(AppKeyCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);

    config(['app.key' => 'base64:' . base64_encode('short')]);
    expect(runCheck(AppKeyCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);
});

test('app.url_https passes on https and fails on http', function () {
    config(['app.url' => 'https://rconfig.example.com']);
    expect(runCheck(AppUrlHttpsCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);

    config(['app.url' => 'http://rconfig.example.com']);
    expect(runCheck(AppUrlHttpsCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);
});

test('app.env_file_perms fails when other users can read .env and passes at 0640', function () {
    $dir = storage_path('framework/testing/security-env-' . uniqid());
    File::ensureDirectoryExists($dir);
    file_put_contents($dir . '/.env', "APP_KEY=x\n");
    config(['rConfig.app_dir_path' => $dir]);

    chmod($dir . '/.env', 0644);
    expect(runCheck(EnvFilePermissionsCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);

    chmod($dir . '/.env', 0640);
    $result = runCheck(EnvFilePermissionsCheck::class, $this->context);
    expect($result->status)->toBe(SecurityCheckStatus::Pass)->and($result->evidence['mode'])->toBe('0640');

    File::deleteDirectory($dir);
});

test('app.env_file_perms skips when .env is missing and does not apply in Docker', function () {
    config(['rConfig.app_dir_path' => '/nonexistent-' . uniqid()]);

    expect(runCheck(EnvFilePermissionsCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Skip)
        ->and(app(EnvFilePermissionsCheck::class)->appliesTo(new SecurityContext('cli', false, true, true, '8.3.0', 'cli')))->toBeFalse();
});

test('the SPA catch-all page is never mistaken for an exposed file', function (string $class) {
    Http::fake(['*' => Http::response(SECURITY_SPA_PAGE, 200)]);

    expect(runCheck($class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);
})->with([EnvExposedCheck::class, StorageExposedCheck::class, VcsExposedCheck::class, HorizonGuestCheck::class, LogViewerGuestCheck::class]);

test('web.env_exposed fails with the URL and never the file contents', function () {
    Http::fake([
        'https://192.0.2.10/.env' => Http::response("APP_NAME=rConfig\nAPP_KEY=base64:SECRETKEYVALUE\nDB_PASSWORD=hunter2\n", 200),
        '*' => Http::response('Not Found', 404),
    ]);

    $result = runCheck(EnvExposedCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Fail)
        ->and($result->evidence['exposed_urls'])->toBe(['https://192.0.2.10/.env'])
        ->and(json_encode([$result->message, $result->evidence, $result->fix]))->not->toContain('hunter2')->not->toContain('SECRETKEYVALUE');
});

test('web.env_exposed only warns when just .env.example is served', function () {
    Http::fake([
        'http://192.0.2.10/.env.example' => Http::response("APP_NAME=rConfig\nAPP_ENV=production\n", 200),
        '*' => Http::response('', 404),
    ]);

    expect(runCheck(EnvExposedCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Warn);
});

test('web checks skip when no address answers', function () {
    Http::fake(fn () => throw new ConnectionException('refused'));

    expect(runCheck(EnvExposedCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Skip)
        ->and(runCheck(RegistrationClosedCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Skip);
});

test('web.storage_exposed and web.vcs_exposed recognise logs, listings and git metadata', function () {
    Http::fake([
        'http://192.0.2.10/storage/logs/laravel.log' => Http::response("[2026-11-19 10:00:00] production.ERROR: boom\n", 200),
        'http://192.0.2.10/.git/HEAD' => Http::response("ref: refs/heads/main\n", 200),
        '*' => Http::response(SECURITY_SPA_PAGE, 200),
    ]);

    expect(runCheck(StorageExposedCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail)
        ->and(runCheck(VcsExposedCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);
});

test('web.horizon_guest and web.log_viewer_guest fail when the dashboards answer guests', function () {
    config(['horizon.path' => 'horizon', 'log-viewer.route_path' => 'log-viewer']);
    Http::fake([
        'https://192.0.2.10/horizon' => Http::response('<script>window.Horizon = {};</script>', 200),
        'https://192.0.2.10/log-viewer/api/files' => Http::response([['identifier' => 'laravel.log']], 200),
        '*' => Http::response('', 302),
    ]);

    expect(runCheck(HorizonGuestCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail)
        ->and(runCheck(LogViewerGuestCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);
});

test('web.registration_closed passes when POST /register is redirected', function () {
    Http::fake(['*' => Http::response('', 302)]);

    expect(runCheck(RegistrationClosedCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);
});

test('web.registration_closed fails when a registration handler answers', function () {
    Http::fake(['http://192.0.2.10/register' => Http::response('', 419), '*' => Http::response('', 200)]);

    expect(runCheck(RegistrationClosedCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);
});

test('tls.cert_expiry fails, warns or passes by days left and skips without a listener', function (?int $days, SecurityCheckStatus $status) {
    $tls = Mockery::mock(TlsInspector::class);
    $tls->shouldReceive('certificate')->andReturn($days === null ? null : ['valid_to' => time() + $days * 86400 + 3600, 'days_left' => $days]);
    $tls->shouldReceive('target')->andReturn(['host' => '127.0.0.1', 'port' => 443, 'server_name' => 'localhost']);
    $this->app->instance(TlsInspector::class, $tls);

    expect(runCheck(CertificateExpiryCheck::class, $this->context)->status)->toBe($status);
})->with([
    [-1, SecurityCheckStatus::Fail],
    [5, SecurityCheckStatus::Fail],
    [20, SecurityCheckStatus::Warn],
    [200, SecurityCheckStatus::Pass],
    [null, SecurityCheckStatus::Skip],
]);

test('php.version_min warns below the configured minimum', function () {
    config(['rConfig.min_php_version' => '8.4.28']);

    $this->app->instance(PhpRuntimeInspector::class, new PhpRuntimeInspector(['source' => 'web', 'sapi' => 'fpm-fcgi', 'version' => '8.4.27', 'values' => []]));
    expect(runCheck(PhpVersionMinimumCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Warn);

    $this->app->instance(PhpRuntimeInspector::class, new PhpRuntimeInspector(['source' => 'web', 'sapi' => 'fpm-fcgi', 'version' => '8.4.30', 'values' => []]));
    expect(runCheck(PhpVersionMinimumCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);
});

test('php.display_errors warns when errors are shown and records which PHP was read', function () {
    $this->app->instance(PhpRuntimeInspector::class, new PhpRuntimeInspector(['source' => 'cli', 'sapi' => 'cli', 'version' => PHP_VERSION, 'values' => ['display_errors' => '1', 'display_startup_errors' => '0', 'log_errors' => '1']]));
    $result = runCheck(DisplayErrorsCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Warn)
        ->and($result->evidence['sapi'])->toBe('cli')
        ->and($result->message)->toContain('command-line PHP');

    $this->app->instance(PhpRuntimeInspector::class, new PhpRuntimeInspector(['source' => 'web', 'sapi' => 'fpm-fcgi', 'version' => PHP_VERSION, 'values' => ['display_errors' => 'Off', 'display_startup_errors' => '', 'log_errors' => 'On']]));
    expect(runCheck(DisplayErrorsCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);
});

test('the php runtime route answers loopback with a valid signature only', function () {
    $url = URL::temporarySignedRoute('security.php-runtime', now()->addMinute(), [], false);

    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])->getJson($url)->assertOk()->assertJsonStructure(['sapi', 'version', 'values' => ['display_errors']]);
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])->getJson($url)->assertNotFound();
    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])->getJson('/security/php-runtime')->assertForbidden();
});

test('redis.auth maps the Redis security status', function (array $status, SecurityCheckStatus $expected) {
    $redis = Mockery::mock(RedisSecurityService::class);
    $redis->shouldReceive('status')->andReturn($status + ['secure' => false, 'bind' => '127.0.0.1', 'protected_mode' => 'yes', 'detail' => '']);
    $this->app->instance(RedisSecurityService::class, $redis);

    expect(runCheck(RedisAuthCheck::class, $this->context)->status)->toBe($expected);
})->with([
    [['reachable' => true, 'password_required' => true, 'app_authenticated' => true], SecurityCheckStatus::Pass],
    [['reachable' => true, 'password_required' => false, 'app_authenticated' => true], SecurityCheckStatus::Fail],
    [['reachable' => true, 'password_required' => true, 'app_authenticated' => false], SecurityCheckStatus::Fail],
    [['reachable' => false, 'password_required' => null, 'app_authenticated' => false], SecurityCheckStatus::Skip],
]);

test('accounts.default_admin flags local accounts on a default password and skips SSO users', function () {
    User::query()->update(['password' => Hash::make('a-long-unique-password')]);
    expect(runCheck(DefaultPasswordCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);

    $seeded = User::factory()->create(['email' => 'seeded@example.com']);
    DB::table('users')->where('id', $seeded->id)->update(['password' => DefaultPasswordCheck::SEEDED_HASH]);
    User::factory()->create(['email' => 'obvious@example.com', 'password' => Hash::make('admin')]);
    User::factory()->create(['email' => 'sso@example.com', 'password' => Hash::make('admin'), 'is_socialite' => 1]);

    $result = runCheck(DefaultPasswordCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Fail)
        ->and($result->message)->toStartWith('2 accounts still use a default password')
        ->and($result->evidence['accounts'])->toContain('seeded@example.com', 'obvious@example.com')
        ->and($result->evidence['accounts'])->not->toContain('sso@example.com');
});

test('accounts.login_throttle passes with the default lockout', function () {
    $result = runCheck(LoginThrottleCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Pass)->and($result->evidence['max_attempts'])->toBe(5);
});

test('api.tokens passes for recent tokens and warns with names, never values, for old ones', function () {
    RestApiToken::query()->delete();
    expect(runCheck(RestApiTokensCheck::class, $this->context)->message)->toBe('No REST API tokens exist.');

    RestApiToken::factory()->create(['api_token' => 'fresh-token-value', 'api_token_name' => 'fresh']);
    expect(runCheck(RestApiTokensCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);

    $old = RestApiToken::factory()->create(['api_token' => 'old-token-value', 'api_token_name' => 'backup script']);
    DB::table('rest_api_tokens')->where('id', $old->id)->update(['created_at' => now()->subDays(400)]);

    $result = runCheck(RestApiTokensCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Warn)
        ->and($result->message)->toBe('1 REST API token is older than 365 days: backup script.')
        ->and($result->evidence['token_names'])->toBe(['backup script'])
        ->and(json_encode([$result->message, $result->evidence, $result->fix]))->not->toContain('old-token-value');

    $older = RestApiToken::factory()->create(['api_token_name' => 'nightly']);
    DB::table('rest_api_tokens')->where('id', $older->id)->update(['created_at' => now()->subDays(800)]);

    expect(runCheck(RestApiTokensCheck::class, $this->context)->message)->toStartWith('2 REST API tokens are older than 365 days');
});

test('data.credentials_encrypted passes for encrypted values and fails for plain text', function () {
    DB::table('devices')->delete();
    DB::table('device_credentials')->delete();
    DB::table('rest_api_tokens')->delete();
    DB::table('settings')->update(['defaultDevicePassword' => null, 'defaultEnablePassword' => null, 'mail_password' => null]);

    Device::factory()->create(['device_password' => 'encrypted-secret', 'device_enable_password' => 'encrypted-enable']);
    expect(runCheck(CredentialsEncryptedCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);

    $device = Device::factory()->create();
    DB::table('devices')->where('id', $device->id)->update(['device_password' => 'plaintext-secret']);

    $result = runCheck(CredentialsEncryptedCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Fail)
        ->and($result->evidence['plaintext_columns'])->toBe(['devices.device_password'])
        ->and(json_encode([$result->message, $result->evidence]))->not->toContain('plaintext-secret');
});

test('data.config_file_modes fails when other users can read configs', function () {
    $dir = storage_path('framework/testing/security-configs-' . uniqid());
    File::ensureDirectoryExists($dir . '/router1');
    file_put_contents($dir . '/router1/a.txt', 'config');
    file_put_contents($dir . '/router1/b.txt', 'config');
    chmod($dir . '/router1/a.txt', 0440);
    chmod($dir . '/router1/b.txt', 0440);

    expect((new ConfigFileModesCheck($dir))->run($this->context)->status)->toBe(SecurityCheckStatus::Pass);

    chmod($dir . '/router1/b.txt', 0644);
    $result = (new ConfigFileModesCheck($dir))->run($this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Fail)
        ->and($result->message)->toBe('1 of 2 sampled configs can be read by any local user.')
        ->and($result->fix)->toContain('rconfig:set-config-permissions');

    File::deleteDirectory($dir);
});

test('runtime.worker_user maps the worker user report', function () {
    $workers = Mockery::mock(WorkerUserCheckService::class);
    $workers->shouldReceive('report')->andReturn(
        ['state' => 'all_good', 'summary' => 'All good', 'status' => WorkerCheckStatus::Ok, 'web_user' => 'apache', 'checked_as_root' => false, 'checks' => [['key' => 'horizon', 'label' => 'Horizon', 'status' => WorkerCheckStatus::Ok, 'message' => 'ok', 'details' => []]], 'fix' => null],
        ['state' => 'still_root', 'summary' => 'Horizon runs as root', 'status' => WorkerCheckStatus::Fail, 'web_user' => 'apache', 'checked_as_root' => true, 'checks' => [['key' => 'horizon', 'label' => 'Horizon', 'status' => WorkerCheckStatus::Fail, 'message' => 'Horizon runs as root.', 'details' => []]], 'fix' => 'Run sudo ./update.sh from /var/www/html/rconfig'],
        ['state' => 'docker', 'summary' => 'Docker', 'status' => WorkerCheckStatus::Skipped, 'web_user' => 'apache', 'checked_as_root' => false, 'checks' => [], 'fix' => null],
    );
    $this->app->instance(WorkerUserCheckService::class, $workers);
    $check = app(WorkerUserCheck::class);

    expect($check->run($this->context)->status)->toBe(SecurityCheckStatus::Pass);

    $failed = $check->run($this->context);
    expect($failed->status)->toBe(SecurityCheckStatus::Fail)->and($failed->fix)->toContain('update.sh');

    expect($check->run($this->context)->status)->toBe(SecurityCheckStatus::Skip);
});

test('runtime.scheduler reads the heartbeat', function () {
    $check = ScheduleCheck::new();
    $store = Cache::store($check->getCacheStoreName());

    $store->put($check->getCacheKey(), now()->timestamp);
    expect(runCheck(SchedulerHeartbeatCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);

    $store->put($check->getCacheKey(), now()->subHour()->timestamp);
    expect(runCheck(SchedulerHeartbeatCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);
});

test('runtime.queue returns a result either way and names the Core supervisor program', function () {
    $result = runCheck(QueueWorkersCheck::class, $this->context);

    expect($result->status)->toBeIn([SecurityCheckStatus::Pass, SecurityCheckStatus::Fail]);

    if ($result->status === SecurityCheckStatus::Fail) {
        expect($result->fix)->toContain('rconfig-core-horizon');
    }
});

test('versions.rconfig_latest skips without data, warns behind and passes when current', function () {
    $versions = Mockery::mock(VersionCheckService::class);
    $status = fn (?string $latest, bool $available): array => ['current_version' => '8.3.0', 'latest_version' => $latest, 'update_available' => $available, 'latest_url' => null, 'reachable' => true, 'checked' => true, 'last_checked_at' => '2026-11-14T03:00:00+00:00', 'consecutive_failures' => 0, 'last_error' => null];
    $versions->shouldReceive('storedStatus')->andReturn(null, $status(null, false), $status('8.3.1', true), $status('8.3.0', false));
    $this->app->instance(VersionCheckService::class, $versions);
    $check = app(RconfigLatestCheck::class);

    expect($check->run($this->context)->status)->toBe(SecurityCheckStatus::Skip)
        ->and($check->run($this->context)->status)->toBe(SecurityCheckStatus::Skip)
        ->and($check->run($this->context)->status)->toBe(SecurityCheckStatus::Warn)
        ->and($check->run($this->context)->status)->toBe(SecurityCheckStatus::Pass);
});

test('versions.rconfig_latest never dispatches an update check', function () {
    Queue::fake();
    Cache::forget('version_check.status');

    runCheck(RconfigLatestCheck::class, $this->context);

    Queue::assertNothingPushed();
});

test('devices.telnet counts devices on Telnet templates', function () {
    DB::table('devices')->delete();
    Device::factory()->count(2)->create(['device_template' => 1]);
    Device::factory()->create(['device_template' => 3]);

    $result = runCheck(TelnetDevicesCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Warn)
        ->and($result->evidence)->toMatchArray(['devices' => 3, 'telnet' => 2])
        ->and($result->message)->toStartWith('2 devices (67%) are backed up over Telnet');

    DB::table('devices')->where('device_template', 1)->delete();
    expect(runCheck(TelnetDevicesCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Pass);
});

test('os.firewall passes when a firewall unit is active', function () {
    Process::fake(['*ufw*' => Process::result('active'), '*' => Process::result('inactive')]);

    $result = runCheck(FirewallCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Pass)->and($result->evidence['service'])->toBe('ufw');
});

test('os.firewall fails when no firewall unit is active', function () {
    Process::fake(['*' => Process::result('inactive')]);

    expect(runCheck(FirewallCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Fail);
});

test('os.firewall skips when systemd does not answer and does not apply in Docker', function () {
    Process::fake(['*' => Process::result('', 'System has not been booted with systemd', 1)]);

    expect(runCheck(FirewallCheck::class, $this->context)->status)->toBe(SecurityCheckStatus::Skip)
        ->and(app(FirewallCheck::class)->appliesTo(new SecurityContext('cli', false, true, true, '8.3.0', 'cli')))->toBeFalse();
});

test('the host filesystem stays untouched by the env permission check', function () {
    $host = Mockery::mock(HostFilesystem::class);
    $host->shouldReceive('resolvePath')->andReturn('/srv/rconfig/.env');
    $host->shouldReceive('linkStat')->andReturn(['uid' => 48, 'gid' => 48, 'mode' => 0600, 'type' => 'file']);
    $host->shouldReceive('userName')->with(48)->andReturn('apache');
    $host->shouldNotReceive('chmod');
    $this->app->instance(HostFilesystem::class, $host);

    $result = runCheck(EnvFilePermissionsCheck::class, $this->context);

    expect($result->status)->toBe(SecurityCheckStatus::Pass)->and($result->evidence['owner'])->toBe('apache');
});
