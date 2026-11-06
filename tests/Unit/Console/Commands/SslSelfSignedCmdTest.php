<?php

use App\Services\Utilities\HostFilesystem;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Process;
use Tests\Traits\FakesHostFilesystem;

uses(FakesHostFilesystem::class);

/**
 * Apache config, certificates and .env all live in the faked HostFilesystem
 * and every process is faked, so nothing on the real host is read or changed.
 */
const RHEL_VHOST = '/etc/httpd/conf.d/rconfig-vhost.conf';
const RHEL_SSL_VHOST = '/etc/httpd/conf.d/rconfig-ssl.conf';
const RHEL_CERT = '/etc/pki/tls/certs/rconfig-selfsigned.crt';
const RHEL_KEY = '/etc/pki/tls/private/rconfig-selfsigned.key';
const DEBIAN_SSL_VHOST = '/etc/apache2/sites-available/rconfig-ssl.conf';

beforeEach(function () {
    expect(App::configurationIsCached())->toBeFalse('Clear the config cache before running these tests: they must never rebuild it.');

    config(['app.url' => 'http://rconfig.example.com']);

    $this->appVhost = base_path('rconfig-vhost.conf');
    $this->envPath = App::environmentFilePath();
    $this->host = $this->fakeHostFilesystem(isRoot: true);
    $this->host->existingPaths = [];
    $this->host->directories = [];

    putHostFile($this->host, $this->envPath, "APP_NAME=rConfig\nAPP_URL=http://rconfig.example.com\n");
});

function putHostFile(HostFilesystem $host, string $path, string $contents = ''): void
{
    $host->putFile($path, $contents);
    $host->existingPaths[] = $path;
}

function coreHttpVhost(string $serverName): string
{
    $public = base_path('public');

    return <<<CONF
        <VirtualHost *:80>
                ServerName {$serverName}
                ServerAlias {$serverName}
                DocumentRoot {$public}
                <Directory "{$public}">
                        AllowOverride All
                        Require all granted
                </Directory>
        </VirtualHost>
        CONF;
}

/**
 * A Rocky box after setup_apache.sh: the vhost lives in the app folder and is
 * linked into conf.d.
 */
function withCoreRhel(object $test, string $serverName = 'YourServerName', bool $modSslInstalled = true): void
{
    $test->host->directories = ['/etc/httpd/conf.d'];
    putHostFile($test->host, '/etc/httpd/conf.d/ssl.conf');
    putHostFile($test->host, $test->appVhost, coreHttpVhost($serverName));
    $test->host->existingPaths[] = RHEL_VHOST;
    $test->host->resolvedPaths[RHEL_VHOST] = $test->appVhost;

    if ($modSslInstalled) {
        putHostFile($test->host, '/etc/httpd/modules/mod_ssl.so');
    }
}

function fakeSslProcesses(HostFilesystem $host, bool $configValid = true, bool $firewalldActive = false): void
{
    Process::fake([
        '*openssl*req*' => function (PendingProcess $process) use ($host) {
            $command = (array) $process->command;
            putHostFile($host, $command[array_search('-keyout', $command, true) + 1], 'key');
            putHostFile($host, $command[array_search('-out', $command, true) + 1], 'cert');

            return Process::result();
        },
        '*openssl*x509*' => Process::result(output: "sha256 Fingerprint=AB:CD:EF\n"),
        '*apachectl*' => $configValid
            ? Process::result(errorOutput: 'Syntax OK')
            : Process::result(errorOutput: 'AH00526: Syntax error on line 3', exitCode: 1),
        '*is-active*firewalld*' => Process::result(exitCode: $firewalldActive ? 0 : 3),
        '*firewall-cmd*' => Process::result(output: 'success'),
        '*systemctl*restart*' => Process::result(),
        '*dnf*' => Process::result(),
        '*a2enmod*' => Process::result(),
        '*a2ensite*' => Process::result(),
        '*a2dissite*' => Process::result(),
    ]);

    // Fail loudly rather than run a real binary on the host.
    Process::preventStrayProcesses();
}

test('it refuses to run without root', function () {
    $this->host = $this->fakeHostFilesystem(isRoot: false);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')
        ->expectsOutputToContain('must run as root')
        ->assertExitCode(1);

    expect($this->host->writes)->toBe([]);
    Process::assertNothingRan();
});

test('it refuses to run in Docker and points at the docs', function () {
    $this->host = $this->fakeHostFilesystem(isDocker: true);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')
        ->expectsOutputToContain('does not apply in Docker')
        ->assertExitCode(1);

    Process::assertNothingRan();
});

test('it fails when Apache cannot be found', function () {
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')
        ->expectsOutputToContain('Could not find Apache')
        ->assertExitCode(1);
});

test('it sets up HTTPS on Rocky without touching the vhost in the app folder', function () {
    withCoreRhel($this);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')
        ->expectsOutputToContain('https://rconfig.example.com')
        ->assertExitCode(0);

    $sslVhost = $this->host->fileContents[RHEL_SSL_VHOST];

    expect($sslVhost)->toContain('<VirtualHost *:443>')
        ->and($sslVhost)->toContain('ServerName rconfig.example.com')
        ->and($sslVhost)->toContain('DocumentRoot "' . base_path('public') . '"')
        ->and($sslVhost)->toContain('SSLCertificateFile ' . RHEL_CERT)
        ->and($sslVhost)->toContain('SSLCertificateKeyFile ' . RHEL_KEY)
        ->and($sslVhost)->toContain('RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]')
        ->and(strpos($sslVhost, '<VirtualHost *:80>'))->toBeLessThan(strpos($sslVhost, '<VirtualHost *:443>'))
        ->and($sslVhost)->not->toContain('Listen 443')
        ->and($this->host->pathStats[RHEL_KEY]['mode'])->toBe(0600)
        ->and($this->host->writes)->not->toContain($this->appVhost)
        ->and($this->host->fileContents[$this->envPath])->toContain("APP_URL=https://rconfig.example.com\n")
        ->and(config('app.url'))->toBe('https://rconfig.example.com');

    Process::assertRan(fn (PendingProcess $process): bool => in_array('subjectAltName=DNS:rconfig.example.com', (array) $process->command, true));
    Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['systemctl', 'restart', 'httpd']);
    Process::assertDidntRun(fn (PendingProcess $process): bool => ($process->command[0] ?? null) === 'dnf');
    Process::assertDidntRun(fn (PendingProcess $process): bool => ($process->command[0] ?? null) === 'firewall-cmd');
});

test('a real ServerName in the vhost names the certificate', function () {
    withCoreRhel($this, serverName: 'netops.example.org');
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')->assertExitCode(0);

    expect($this->host->fileContents[RHEL_SSL_VHOST])->toContain('ServerName netops.example.org');
});

test('it installs mod_ssl when it is missing', function () {
    withCoreRhel($this, modSslInstalled: false);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')->assertExitCode(0);

    Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['dnf', 'install', '-y', 'mod_ssl']);
});

test('it adds Listen when the Rocky ssl.conf was removed', function () {
    withCoreRhel($this);
    $this->host->existingPaths = array_values(array_diff($this->host->existingPaths, ['/etc/httpd/conf.d/ssl.conf']));
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')->assertExitCode(0);

    expect($this->host->fileContents[RHEL_SSL_VHOST])->toContain('Listen 443 https');
});

test('it opens https in an active firewalld', function () {
    withCoreRhel($this);
    putHostFile($this->host, '/usr/bin/firewall-cmd');
    fakeSslProcesses($this->host, firewalldActive: true);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')
        ->expectsOutputToContain('Opened https in firewalld')
        ->assertExitCode(0);

    Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['firewall-cmd', '--permanent', '--add-service=https']);
    Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['firewall-cmd', '--reload']);
});

test('it sets up HTTPS on Ubuntu through sites-available and a2ensite', function () {
    $this->host->directories = ['/etc/apache2/sites-available', '/etc/apache2/sites-enabled'];
    putHostFile($this->host, $this->appVhost, coreHttpVhost('YourServerName'));
    $this->host->existingPaths[] = '/etc/apache2/sites-enabled/rconfig-vhost.conf';
    $this->host->resolvedPaths['/etc/apache2/sites-enabled/rconfig-vhost.conf'] = $this->appVhost;
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')->assertExitCode(0);

    expect($this->host->fileContents[DEBIAN_SSL_VHOST])->toContain('SSLCertificateFile /etc/ssl/certs/rconfig-selfsigned.crt')
        ->and($this->host->writes)->not->toContain($this->appVhost);

    Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['a2enmod', 'ssl', 'rewrite']);
    Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['a2ensite', 'rconfig-ssl']);
    Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['systemctl', 'restart', 'apache2']);
});

test('a rejected Apache config is rolled back', function () {
    withCoreRhel($this);
    fakeSslProcesses($this->host, configValid: false);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')
        ->expectsOutputToContain('rolled back')
        ->assertExitCode(1);

    expect($this->host->fileContents)->not->toHaveKeys([RHEL_SSL_VHOST, RHEL_CERT, RHEL_KEY])
        ->and($this->host->fileContents[$this->envPath])->toContain('APP_URL=http://rconfig.example.com');

    Process::assertDidntRun(fn (PendingProcess $process): bool => ($process->command[0] ?? null) === 'systemctl');
});

test('no-redirect writes only the HTTPS vhost', function () {
    withCoreRhel($this);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-redirect --no-interaction')
        ->expectsOutputToContain('not redirected')
        ->assertExitCode(0);

    expect($this->host->fileContents[RHEL_SSL_VHOST])->not->toContain('<VirtualHost *:80>')
        ->and($this->host->fileContents[RHEL_SSL_VHOST])->toContain('<VirtualHost *:443>');
});

test('dry run changes nothing', function () {
    withCoreRhel($this);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --dry-run --no-interaction')
        ->expectsOutputToContain('[dry run]')
        ->expectsOutputToContain(RHEL_SSL_VHOST)
        ->assertExitCode(0);

    expect($this->host->writes)->toBe([]);
    Process::assertNothingRan();
});

test('a second run reports already set up without force', function () {
    withCoreRhel($this);
    putHostFile($this->host, RHEL_SSL_VHOST, "<VirtualHost *:443>\nDocumentRoot \"" . base_path('public') . "\"\n</VirtualHost>\n");
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')
        ->expectsOutputToContain('already set up')
        ->assertExitCode(0);

    Process::assertNothingRan();
});

test('force replaces our files and restores them if Apache rejects the change', function () {
    withCoreRhel($this);
    $previous = "<VirtualHost *:443>\nDocumentRoot \"" . base_path('public') . "\"\n</VirtualHost>\n";
    putHostFile($this->host, RHEL_SSL_VHOST, $previous);
    putHostFile($this->host, RHEL_CERT, 'old cert');
    fakeSslProcesses($this->host, configValid: false);

    $this->artisan('rconfig:ssl-self-signed --force --no-interaction')->assertExitCode(1);

    expect($this->host->fileContents[RHEL_SSL_VHOST])->toBe($previous)
        ->and($this->host->fileContents[RHEL_CERT])->toBe('old cert');
});

test('an HTTPS vhost set up by hand is left alone', function () {
    withCoreRhel($this);
    putHostFile($this->host, '/etc/httpd/conf.d/manual-ssl.conf', "<VirtualHost *:443>\n    DocumentRoot \"" . base_path('public') . "/\"\n</VirtualHost>\n");
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --no-interaction')
        ->expectsOutputToContain('already has an HTTPS vhost')
        ->expectsOutputToContain('APP_URL still starts with http://')
        ->assertExitCode(0);

    expect($this->host->writes)->toBe([]);
    Process::assertNothingRan();
});

test('the hostname option wins and an IP gets an IP SAN', function () {
    withCoreRhel($this);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --hostname=10.1.2.3 --no-interaction')->assertExitCode(0);

    Process::assertRan(fn (PendingProcess $process): bool => in_array('subjectAltName=IP:10.1.2.3', (array) $process->command, true));
    expect($this->host->fileContents[$this->envPath])->toContain('APP_URL=https://10.1.2.3');
});

test('an unsafe hostname is rejected before anything changes', function () {
    withCoreRhel($this);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed', ['--hostname' => "evil.com\nInclude /etc/shadow", '--no-interaction' => true])
        ->expectsOutputToContain('not a valid hostname')
        ->assertExitCode(1);

    expect($this->host->writes)->toBe([]);
    Process::assertNothingRan();
});

test('days out of range is rejected', function () {
    withCoreRhel($this);
    fakeSslProcesses($this->host);

    $this->artisan('rconfig:ssl-self-signed --days=0 --no-interaction')
        ->expectsOutputToContain('--days must be between')
        ->assertExitCode(1);
});
