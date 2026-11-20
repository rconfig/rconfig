<?php

namespace App\Services\Security\Checks\App;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;

/**
 * APP_KEY encrypts stored credentials and signs sessions. Compared by hash only.
 */
class AppKeyCheck extends AbstractSecurityCheck
{
    /**
     * SHA-256 of APP_KEY values published in examples, images or docs.
     *
     * @var list<string>
     */
    public const KNOWN_KEY_HASHES = [];

    private const REPLACE_KEY = 'Do not regenerate the key on a live install: stored credentials would no longer decrypt. Generate a new key, then re-enter the device credentials.';

    protected string $id = 'app.key';
    protected SecurityCheckGroup $group = SecurityCheckGroup::App;
    protected string $title = 'Application key is set and unique';
    protected SecuritySeverity $severity = SecuritySeverity::Critical;
    protected string $rationale = 'APP_KEY encrypts the stored device and enable passwords and signs sessions. A missing, malformed or published key lets anyone who has it decrypt those secrets or forge a session.';
    protected array $references = ['NIST SC-12', 'NIST SC-28', 'NIS2 21(2)(h)'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $key = (string) config('app.key');
        $replace = self::REPLACE_KEY;

        if ($key === '') {
            return SecurityCheckResult::fail('APP_KEY is not set.', $this->artisan('key:generate'));
        }

        $raw = str_starts_with($key, 'base64:') ? base64_decode(substr($key, 7), true) : $key;
        $expected = str_contains(strtolower((string) config('app.cipher')), '128') ? 16 : 32;

        if ($raw === false || strlen($raw) !== $expected) {
            return SecurityCheckResult::fail("APP_KEY is not a valid {$expected}-byte key for " . config('app.cipher') . '.', $replace);
        }

        if (in_array(hash('sha256', $key), $this->knownHashes(), true)) {
            return SecurityCheckResult::fail('APP_KEY is a published example key, so anyone can decrypt the stored credentials.', $replace);
        }

        return SecurityCheckResult::pass('APP_KEY is set, the right length, and not a known example key.');
    }

    public function fixGuide(): string
    {
        return 'If APP_KEY is empty, ' . lcfirst($this->artisan('key:generate')) . '. If it is malformed or a published example key, generate a new key, then re-enter the device credentials, because values saved with the old key no longer decrypt.';
    }

    /**
     * @return list<string>
     */
    private function knownHashes(): array
    {
        $hashes = self::KNOWN_KEY_HASHES;
        $example = @file_get_contents(base_path('.env.example'));

        if (is_string($example) && preg_match('/^APP_KEY=(\S+)$/m', $example, $matches)) {
            $hashes[] = hash('sha256', trim($matches[1], '"\''));
        }

        return $hashes;
    }
}
