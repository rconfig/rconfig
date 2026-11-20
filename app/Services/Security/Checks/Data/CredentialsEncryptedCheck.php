<?php

namespace App\Services\Security\Checks\Data;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Samples every encrypted credential column straight from the database. Values are inspected in memory only.
 */
class CredentialsEncryptedCheck extends AbstractSecurityCheck
{
    /**
     * table => columns stored with EncryptStringCast.
     *
     * @var array<string, list<string>>
     */
    public const COLUMNS = [
        'devices' => ['device_password', 'device_enable_password'],
        'device_credentials' => ['cred_password', 'cred_enable_password'],
        'settings' => ['defaultDevicePassword', 'defaultEnablePassword', 'mail_password'],
        'rest_api_tokens' => ['api_token'],
    ];

    public const SAMPLE_SIZE = 200;

    public const DECRYPT_SAMPLE_SIZE = 5;

    protected string $id = 'data.credentials_encrypted';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Data;
    protected string $title = 'Stored credentials are encrypted and readable with APP_KEY';
    protected SecuritySeverity $severity = SecuritySeverity::Critical;
    protected string $rationale = 'Device, enable, SMTP and REST API secrets must be stored encrypted with APP_KEY. Plain text leaks them through any database backup or export, and values APP_KEY cannot decrypt mean the key changed.';
    protected array $references = ['NIST SC-28', 'NIS2 21(2)(h)'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $plaintext = [];
        $undecryptable = [];
        $serialized = [];
        $sampled = 0;

        foreach (self::COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $values = DB::table($table)->whereNotNull($column)->where($column, '!=', '')->limit(self::SAMPLE_SIZE)->pluck($column);
                $decryptTries = 0;
                $label = "{$table}.{$column}";

                foreach ($values as $value) {
                    $sampled++;

                    if (! $this->isPayload((string) $value)) {
                        $plaintext[$label] = ($plaintext[$label] ?? 0) + 1;

                        continue;
                    }

                    if ($decryptTries++ >= self::DECRYPT_SAMPLE_SIZE) {
                        continue;
                    }

                    try {
                        $decrypted = Crypt::decryptString((string) $value);
                    } catch (DecryptException) {
                        $undecryptable[$label] = ($undecryptable[$label] ?? 0) + 1;

                        continue;
                    }

                    if (preg_match('/^(O|C|a):\d+:/', $decrypted)) {
                        $serialized[$label] = ($serialized[$label] ?? 0) + 1;
                    }

                    unset($decrypted);
                }
            }
        }

        $evidence = ['values_sampled' => $sampled, 'plaintext_columns' => array_keys($plaintext), 'undecryptable_columns' => array_keys($undecryptable), 'serialized_columns' => array_keys($serialized)];

        if ($plaintext !== [] || $serialized !== []) {
            $parts = [];

            if ($plaintext !== []) {
                $parts[] = 'stored in plain text: ' . $this->describe($plaintext);
            }

            if ($serialized !== []) {
                $parts[] = 'holding serialized objects or arrays: ' . $this->describe($serialized);
            }

            return SecurityCheckResult::fail(
                'Credentials ' . implode('; ', $parts) . '.',
                'Re-save the affected credentials from the UI so they are encrypted again, then change them on the devices or services they belong to.',
                $evidence,
            );
        }

        if ($undecryptable !== []) {
            return SecurityCheckResult::fail(
                'Credentials APP_KEY cannot decrypt: ' . $this->describe($undecryptable) . '. APP_KEY may have changed since they were saved.',
                'Restore the previous APP_KEY from your .env backup, or re-enter the affected credentials.',
                $evidence,
            );
        }

        if ($sampled === 0) {
            return SecurityCheckResult::pass('No stored credentials were found yet.', $evidence);
        }

        return SecurityCheckResult::pass(
            $this->verb($sampled, 'The ', 'All ') . $this->plural($sampled, 'sampled credential value') . ' ' . $this->verb($sampled, 'is', 'are') . ' encrypted and ' . $this->verb($sampled, 'decrypts', 'decrypt') . ' with APP_KEY.',
            $evidence,
        );
    }

    public function fixGuide(): string
    {
        return 'Plain text or serialized values: re-save the affected credentials from the UI so they are encrypted again, then change them on the devices or services they belong to. Values APP_KEY cannot decrypt: restore the previous APP_KEY from your .env backup, or re-enter the affected credentials.';
    }

    private function isPayload(string $value): bool
    {
        $decoded = json_decode((string) base64_decode($value, true), true);

        return is_array($decoded) && isset($decoded['iv'], $decoded['value'], $decoded['mac']);
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function describe(array $counts): string
    {
        return implode(', ', array_map(fn (string $label, int $count): string => "{$label} ({$count})", array_keys($counts), $counts));
    }
}
