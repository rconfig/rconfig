<?php

namespace App\Services\System;

use App\Console\Commands\EnvironmentSetCommand;
use App\Services\Utilities\HostFilesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * Serves rConfig over HTTPS with a self-signed certificate. setup_apache.sh
 * links an HTTP-only vhost from the app folder into Apache; rconfig:ssl-self-signed
 * calls this to add rconfig-ssl.conf next to it, holding the :443 vhost and a
 * :80 vhost that redirects to it, and to point APP_URL at the new URL. It runs
 * as root from the CLI only: the web user never gets a path to rewrite Apache
 * config.
 *
 * The app's own rconfig-vhost.conf is never edited, because it lives in the
 * git checkout that update.sh pulls. rconfig-ssl.conf sorts before it, so
 * Apache matches its :80 redirect first for the same ServerName.
 *
 * Every host change goes through HostFilesystem or the Process facade so tests
 * can fake both. Files are tracked as they are written, and a failed Apache
 * config test puts every one of them back the way it was.
 */
class SelfSignedSslService
{
    public const COMMAND = 'sudo php artisan rconfig:ssl-self-signed';

    public const DOCS_URL = 'https://v8coredocs.rconfig.com/auth-security/ssl-setup/';

    public const VHOST_NAME = 'rconfig-ssl';

    public const CERT_NAME = 'rconfig-selfsigned';

    /**
     * ServerName values that are template placeholders, not real names.
     *
     * @var list<string>
     */
    private const PLACEHOLDER_NAMES = ['localhost', 'yourservername'];

    /**
     * Apache layout per OS family.
     *
     * @var array<string, array{vhost_dir: string, enabled_dir: string, service: string, cert_dir: string, key_dir: string}>
     */
    private const LAYOUTS = [
        'rhel' => [
            'vhost_dir' => '/etc/httpd/conf.d',
            'enabled_dir' => '/etc/httpd/conf.d',
            'service' => 'httpd',
            'cert_dir' => '/etc/pki/tls/certs',
            'key_dir' => '/etc/pki/tls/private',
        ],
        'debian' => [
            'vhost_dir' => '/etc/apache2/sites-available',
            'enabled_dir' => '/etc/apache2/sites-enabled',
            'service' => 'apache2',
            'cert_dir' => '/etc/ssl/certs',
            'key_dir' => '/etc/ssl/private',
        ],
    ];

    /**
     * Paths written during apply(), each with what it held before (null when
     * it did not exist), so a rollback can restore or remove it.
     *
     * @var array<string, string|null>
     */
    private array $written = [];

    public function __construct(private readonly HostFilesystem $host) {}

    /**
     * Whether APP_URL already points at an https:// address.
     */
    public static function appUsesHttps(): bool
    {
        return str_starts_with(strtolower((string) config('app.url')), 'https://');
    }

    /**
     * Inspect the host and work out what apply() would do, without changing
     * anything. Throws when the command cannot run here at all.
     *
     * @return array{
     *     family: string,
     *     layout: array{vhost_dir: string, enabled_dir: string, service: string, cert_dir: string, key_dir: string},
     *     hostname: string,
     *     document_root: string,
     *     redirect: bool,
     *     state: 'ready'|'already_configured'|'foreign_https',
     *     foreign_https_vhost: string|null,
     *     ssl_vhost: string,
     *     cert_path: string,
     *     key_path: string,
     *     days: int,
     *     force: bool
     * }
     */
    public function plan(?string $hostname, int $days, bool $redirect, bool $force): array
    {
        if ($this->host->isDocker()) {
            throw new RuntimeException('This command does not apply in Docker. Terminate TLS on your reverse proxy and set APP_URL to https://. See ' . self::DOCS_URL);
        }

        if (! $this->host->isRoot()) {
            throw new RuntimeException('This command must run as root. Run: ' . self::COMMAND);
        }

        if ($days < 1 || $days > 3650) {
            throw new RuntimeException('--days must be between 1 and 3650.');
        }

        $family = $this->detectFamily();
        $layout = self::LAYOUTS[$family];
        $sslVhost = $layout['vhost_dir'] . '/' . self::VHOST_NAME . '.conf';

        $vhosts = $this->rconfigVhosts($layout['enabled_dir'], $sslVhost);
        $hostname = $this->resolveHostname($hostname, $vhosts['http']['server_name'] ?? null);

        $state = 'ready';

        if ($vhosts['https'] !== null) {
            $state = 'foreign_https';
        } elseif ($this->host->fileExists($sslVhost) && ! $force) {
            $state = 'already_configured';
        }

        return [
            'family' => $family,
            'layout' => $layout,
            'hostname' => $hostname,
            'document_root' => $vhosts['http']['document_root'] ?? base_path('public'),
            'redirect' => $redirect,
            'state' => $state,
            'foreign_https_vhost' => $vhosts['https']['path'] ?? null,
            'ssl_vhost' => $sslVhost,
            'cert_path' => $layout['cert_dir'] . '/' . self::CERT_NAME . '.crt',
            'key_path' => $layout['key_dir'] . '/' . self::CERT_NAME . '.key',
            'days' => $days,
            'force' => $force,
        ];
    }

    /**
     * Carry out a plan from plan(). $report is told about each step as it
     * finishes. Returns the HTTPS URL and the certificate fingerprint.
     *
     * @param  array<string, mixed>  $plan
     * @param  callable(string): void  $report
     * @return array{url: string, fingerprint: string|null, firewall: string|null}
     */
    public function apply(array $plan, callable $report): array
    {
        $this->written = [];

        try {
            $this->enableModules($plan);
            $report('Apache SSL module is installed and enabled.');

            $this->generateCertificate($plan);
            $report("Generated a self-signed certificate for {$plan['hostname']}, valid for {$plan['days']} days.");

            $this->writeSslVhost($plan);
            $report($plan['redirect']
                ? "Wrote the HTTPS vhost and the HTTP to HTTPS redirect to {$plan['ssl_vhost']}."
                : "Wrote the HTTPS vhost to {$plan['ssl_vhost']}.");

            $this->assertApacheConfigIsValid();
            $report('Apache accepted the new configuration.');
        } catch (RuntimeException $e) {
            $this->rollback($plan);

            throw $e;
        }

        $firewall = $this->openFirewall();

        $this->mustRun(['systemctl', 'restart', $plan['layout']['service']], 'Apache did not restart', 120);
        $report("Restarted {$plan['layout']['service']}.");

        $url = 'https://' . $plan['hostname'];
        $this->setAppUrl($url);
        $report("Set APP_URL to {$url}.");

        return ['url' => $url, 'fingerprint' => $this->fingerprint($plan['cert_path']), 'firewall' => $firewall];
    }

    /**
     * The ServerName to use: the --hostname option, the HTTP vhost's
     * ServerName, the APP_URL host, then this machine's hostname.
     */
    private function resolveHostname(?string $option, ?string $serverName): string
    {
        $candidates = [
            $option,
            $serverName,
            parse_url((string) config('app.url'), PHP_URL_HOST) ?: null,
            gethostname() ?: null,
        ];

        foreach ($candidates as $index => $candidate) {
            if ($candidate === null || $candidate === '') {
                continue;
            }

            // Only an explicit --hostname may be localhost or a template placeholder.
            if ($index > 0 && (in_array(strtolower($candidate), self::PLACEHOLDER_NAMES, true) || str_starts_with($candidate, 'ENVOY_'))) {
                continue;
            }

            if (! $this->isValidHostname($candidate)) {
                throw new RuntimeException("'{$candidate}' is not a valid hostname or IP address. Pass one with --hostname.");
            }

            return strtolower($candidate);
        }

        throw new RuntimeException('Could not work out this server\'s hostname. Pass one with --hostname.');
    }

    private function isValidHostname(string $hostname): bool
    {
        return filter_var($hostname, FILTER_VALIDATE_IP) !== false
            || preg_match('/^(?=.{1,253}$)[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?(?:\.[A-Za-z0-9](?:[A-Za-z0-9-]{0,61}[A-Za-z0-9])?)*$/', $hostname) === 1;
    }

    private function detectFamily(): string
    {
        foreach (self::LAYOUTS as $family => $layout) {
            if ($this->host->isDirectory($layout['vhost_dir'])) {
                return $family;
            }
        }

        throw new RuntimeException('Could not find Apache (looked for /etc/httpd/conf.d and /etc/apache2/sites-available). Set up HTTPS by hand: ' . self::DOCS_URL);
    }

    /**
     * The enabled vhosts that serve this install's public folder: the first
     * :80 one, and any :443 one that is not ours.
     *
     * @return array{http: array{path: string, server_name: string|null, document_root: string}|null, https: array{path: string}|null}
     */
    private function rconfigVhosts(string $enabledDir, string $sslVhost): array
    {
        $publicPath = $this->normalisePath(base_path('public'));
        $found = ['http' => null, 'https' => null];

        foreach ($this->host->listDirectory($enabledDir) as $path) {
            if (! str_ends_with($path, '.conf')) {
                continue;
            }

            // setup_apache.sh links the vhost in from the app folder: read the target.
            $target = $this->host->resolvePath($path) ?? $path;
            $contents = $this->host->readFile($target);

            if ($contents === null || ! preg_match_all('#<VirtualHost\s+([^>]+)>(.*?)</VirtualHost>#is', $contents, $blocks, PREG_SET_ORDER)) {
                continue;
            }

            foreach ($blocks as [, $address, $body]) {
                if (! preg_match('/^\s*DocumentRoot\s+"?([^"\s]+)"?/mi', $body, $root) || $this->normalisePath($root[1]) !== $publicPath) {
                    continue;
                }

                if (str_ends_with(trim($address), ':443')) {
                    if ($target !== $sslVhost) {
                        $found['https'] ??= ['path' => $target];
                    }

                    continue;
                }

                if (str_ends_with(trim($address), ':80')) {
                    $found['http'] ??= [
                        'path' => $target,
                        'server_name' => preg_match('/^\s*ServerName\s+(\S+)/mi', $body, $name) ? $name[1] : null,
                        'document_root' => $root[1],
                    ];
                }
            }
        }

        return $found;
    }

    private function normalisePath(string $path): string
    {
        return rtrim($this->host->resolvePath($path) ?? $path, '/');
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function enableModules(array $plan): void
    {
        if ($plan['family'] === 'debian') {
            $this->mustRun(['a2enmod', 'ssl', 'rewrite'], 'Could not enable the Apache ssl and rewrite modules');

            return;
        }

        if (! $this->host->fileExists('/etc/httpd/modules/mod_ssl.so')) {
            $this->mustRun(['dnf', 'install', '-y', 'mod_ssl'], 'Could not install mod_ssl', 600);
        }
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function generateCertificate(array $plan): void
    {
        $this->track($plan['cert_path']);
        $this->track($plan['key_path']);

        $san = (filter_var($plan['hostname'], FILTER_VALIDATE_IP) !== false ? 'IP:' : 'DNS:') . $plan['hostname'];

        $this->mustRun([
            'openssl', 'req', '-x509', '-newkey', 'rsa:2048', '-nodes', '-sha256',
            '-days', (string) $plan['days'],
            '-keyout', $plan['key_path'],
            '-out', $plan['cert_path'],
            '-subj', '/CN=' . $plan['hostname'],
            '-addext', 'subjectAltName=' . $san,
        ], 'openssl could not create the certificate');

        $this->host->chmod($plan['key_path'], 0600);
        $this->host->chmod($plan['cert_path'], 0644);

        if ($this->host->fileExists('/usr/sbin/restorecon')) {
            Process::timeout(30)->run(['/usr/sbin/restorecon', $plan['cert_path'], $plan['key_path']]);
        }
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function writeSslVhost(array $plan): void
    {
        $this->writeTracked($plan['ssl_vhost'], $this->renderSslVhost($plan));

        if ($plan['family'] === 'debian') {
            $this->mustRun(['a2ensite', self::VHOST_NAME], 'Could not enable the HTTPS site');
        }
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function renderSslVhost(array $plan): string
    {
        // mod_ssl's own ssl.conf normally opens the port on RHEL; add it when an admin removed that file.
        $listen = $plan['family'] === 'rhel' && ! $this->host->fileExists('/etc/httpd/conf.d/ssl.conf') ? "Listen 443 https\n\n" : '';
        $docsUrl = self::DOCS_URL;

        $redirect = $plan['redirect'] ? <<<CONF
            <VirtualHost *:80>
                ServerName {$plan['hostname']}
                RewriteEngine On
                RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
            </VirtualHost>


            CONF : '';

        return <<<CONF
            # Written by php artisan rconfig:ssl-self-signed. To use your own
            # certificate, point SSLCertificateFile and SSLCertificateKeyFile at it
            # and restart Apache. See {$docsUrl}
            {$listen}{$redirect}<VirtualHost *:443>
                ServerName {$plan['hostname']}
                DocumentRoot "{$plan['document_root']}"

                <Directory "{$plan['document_root']}">
                    Options FollowSymLinks
                    AllowOverride All
                    Require all granted
                </Directory>

                SSLEngine on
                SSLCertificateFile {$plan['cert_path']}
                SSLCertificateKeyFile {$plan['key_path']}
            </VirtualHost>

            CONF;
    }

    private function assertApacheConfigIsValid(): void
    {
        $result = Process::timeout(60)->run(['apachectl', 'configtest']);

        if (! $result->successful()) {
            throw new RuntimeException('Apache rejected the new configuration, so every change was rolled back: ' . trim($result->errorOutput() . ' ' . $result->output()));
        }
    }

    /**
     * Open 443 in firewalld or ufw when either is active. Returns what was
     * done, or null when no firewall was found.
     */
    private function openFirewall(): ?string
    {
        if ($this->host->fileExists('/usr/bin/firewall-cmd') && Process::timeout(30)->run(['systemctl', 'is-active', '--quiet', 'firewalld'])->successful()) {
            $this->mustRun(['firewall-cmd', '--permanent', '--add-service=https'], 'firewalld did not accept the https service');
            $this->mustRun(['firewall-cmd', '--reload'], 'firewalld did not reload');

            return 'Opened https in firewalld.';
        }

        if ($this->host->fileExists('/usr/sbin/ufw') && str_contains(Process::timeout(30)->run(['ufw', 'status'])->output(), 'Status: active')) {
            $this->mustRun(['ufw', 'allow', '443/tcp'], 'ufw did not open port 443');

            return 'Opened 443/tcp in ufw.';
        }

        return null;
    }

    /**
     * Write APP_URL to .env and, when the config is cached, rebuild it and
     * hand it back to the web user.
     */
    private function setAppUrl(string $url): void
    {
        $envPath = App::environmentFilePath();
        $contents = $this->host->readFile($envPath);

        if ($contents === null) {
            throw new RuntimeException("Could not read {$envPath}. Set APP_URL={$url} by hand.");
        }

        [$updated] = (new EnvironmentSetCommand)->setEnvVariable($contents, 'APP_URL', $url);

        if (! $this->host->writeFile($envPath, $updated)) {
            throw new RuntimeException("Could not write {$envPath}. Set APP_URL={$url} by hand.");
        }

        config(['app.url' => $url]);

        if (App::configurationIsCached()) {
            Artisan::call('config:cache');
            custom_chown(App::getCachedConfigPath());
        }
    }

    private function fingerprint(string $certPath): ?string
    {
        $result = Process::timeout(30)->run(['openssl', 'x509', '-noout', '-fingerprint', '-sha256', '-in', $certPath]);

        return $result->successful() ? trim(substr($result->output(), (int) strpos($result->output(), '=') + 1)) : null;
    }

    /**
     * Put back every file written so far, in reverse order.
     *
     * @param  array<string, mixed>  $plan
     */
    private function rollback(array $plan): void
    {
        if ($plan['family'] === 'debian' && array_key_exists($plan['ssl_vhost'], $this->written) && $this->written[$plan['ssl_vhost']] === null) {
            Process::timeout(30)->run(['a2dissite', self::VHOST_NAME]);
        }

        foreach (array_reverse($this->written, true) as $path => $original) {
            if ($original === null) {
                $this->host->deleteFile($path);
            } else {
                $this->host->writeFile($path, $original);
            }
        }

        $this->written = [];
    }

    private function track(string $path): void
    {
        if (! array_key_exists($path, $this->written)) {
            $this->written[$path] = $this->host->fileExists($path) ? $this->host->readFile($path) : null;
        }
    }

    private function writeTracked(string $path, string $contents): void
    {
        $this->track($path);

        if (! $this->host->writeFile($path, $contents)) {
            throw new RuntimeException("Could not write {$path}.");
        }
    }

    /**
     * @param  list<string>  $command
     */
    private function mustRun(array $command, string $failure, int $timeout = 60): void
    {
        $result = Process::timeout($timeout)->run($command);

        if (! $result->successful()) {
            throw new RuntimeException($failure . ': ' . trim($result->errorOutput() ?: $result->output()));
        }
    }
}
