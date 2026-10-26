<?php

namespace App\Console\Commands;

use App\Services\System\SelfSignedSslService;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * One command from HTTP to HTTPS on a fresh install: a self-signed
 * certificate, a :443 Apache vhost, an HTTP to HTTPS redirect, port 443 in the
 * firewall and APP_URL. Must run as root. Safe to re-run, and a rejected
 * Apache config is rolled back.
 */
class SslSelfSignedCmd extends Command
{
    /**
     * @var string
     */
    protected $signature = 'rconfig:ssl-self-signed
        {--hostname= : Name or IP address the certificate is issued for (default: the vhost ServerName, then the APP_URL host)}
        {--days=825 : How many days the certificate is valid}
        {--no-redirect : Do not redirect HTTP to HTTPS}
        {--force : Replace a certificate and vhost this command created earlier}
        {--dry-run : Report what would change without altering anything}';

    /**
     * @var string
     */
    protected $description = 'Serve rConfig over HTTPS with a self-signed certificate';

    public function handle(SelfSignedSslService $service): int
    {
        try {
            $plan = $service->plan(
                hostname: $this->option('hostname'),
                days: (int) $this->option('days'),
                redirect: ! $this->option('no-redirect'),
                force: (bool) $this->option('force'),
            );
        } catch (RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($plan['state'] === 'foreign_https') {
            $this->components->warn("rConfig already has an HTTPS vhost in {$plan['foreign_https_vhost']}. It was left alone.");

            if (! SelfSignedSslService::appUsesHttps()) {
                $this->components->warn('APP_URL still starts with http://. Set it to your https:// address: php artisan env:set APP_URL https://' . $plan['hostname']);
            }

            return self::SUCCESS;
        }

        if ($plan['state'] === 'already_configured') {
            $this->components->info("HTTPS is already set up by this command ({$plan['ssl_vhost']}). Nothing to do. Use --force to issue a new certificate.");

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->reportDryRun($plan);

            return self::SUCCESS;
        }

        try {
            $result = $service->apply($plan, fn (string $step) => $this->components->info($step));
        } catch (RuntimeException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info($result['firewall'] ?? 'No active firewalld or ufw found. Make sure port 443 is open to your users.');

        if (! $plan['redirect']) {
            $this->components->warn('HTTP is not redirected to HTTPS (--no-redirect).');
        }

        $this->newLine();
        $this->components->twoColumnDetail('rConfig is now at', $result['url']);
        $this->components->twoColumnDetail('Certificate SHA-256', $result['fingerprint'] ?? 'unknown');
        $this->newLine();

        $this->components->bulletList([
            'Browsers will warn about this certificate until you trust it or replace it with one from your own CA or Let\'s Encrypt: ' . SelfSignedSslService::DOCS_URL,
            'Scripts and integrations that call the API at the old http:// address must be pointed at ' . $result['url'] . '.',
        ]);

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function reportDryRun(array $plan): void
    {
        $steps = [
            $plan['family'] === 'debian' ? 'Enable the Apache ssl and rewrite modules' : 'Install mod_ssl if it is missing',
            "Create a self-signed certificate for {$plan['hostname']} valid for {$plan['days']} days: {$plan['cert_path']}",
            $plan['redirect']
                ? "Write the HTTPS vhost and an HTTP to HTTPS redirect to {$plan['ssl_vhost']}"
                : "Write the HTTPS vhost to {$plan['ssl_vhost']}",
            'Check the Apache config, and roll everything back if it is rejected',
            'Open https in firewalld or ufw if either is active',
            "Restart {$plan['layout']['service']}",
            "Set APP_URL to https://{$plan['hostname']}",
        ];

        $this->components->info('[dry run] Nothing was changed. This would:');
        $this->components->bulletList($steps);
    }
}
