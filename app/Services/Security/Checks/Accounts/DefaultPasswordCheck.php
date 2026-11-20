<?php

namespace App\Services\Security\Checks\Accounts;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Models\User;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Finds accounts still on the installer's default password. Every user has full access in Core, so every local account counts.
 */
class DefaultPasswordCheck extends AbstractSecurityCheck
{
    /**
     * Hash the users migration writes for admin@domain.com.
     */
    public const SEEDED_HASH = '$2y$10$ZF3AXzM/N/xduWF0CQiswOaHYh6EwiNcWJ8AUp.7xv0qDcfLvVsGi';

    /**
     * Passwords published in the install docs, or commonly tried first.
     *
     * @var list<string>
     */
    public const DEFAULT_PASSWORDS = ['admin', 'password', 'rconfig', 'admin123'];

    protected string $id = 'accounts.default_admin';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Accounts;
    protected string $title = 'No account uses a default password';
    protected SecuritySeverity $severity = SecuritySeverity::Critical;
    protected string $rationale = 'The install docs publish admin@domain.com / admin. Every rConfig Core user has full access, so any account still on it, or on another obvious password, hands the whole install to anyone who can reach the sign-in page.';
    protected array $references = ['NIST IA-5', 'NIS2 21(2)(i)', 'CIS Controls 4.7', 'OWASP ASVS V2.1'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $flagged = User::query()
            ->get(['id', 'email', 'password', 'is_socialite'])
            ->filter(fn (User $user): bool => $this->usesDefaultPassword($user))
            ->map(fn (User $user): string => (string) $user->email)
            ->values()
            ->all();

        if ($flagged === []) {
            return SecurityCheckResult::pass('No account signs in with the installer\'s default or an obvious password.');
        }

        $count = count($flagged);

        return SecurityCheckResult::fail(
            $this->plural($count, 'account') . ' still ' . $this->verb($count, 'uses', 'use') . ' a default password: ' . implode(', ', $flagged) . '.',
            $this->fixGuide(),
            ['accounts' => $flagged],
        );
    }

    public function fixGuide(): string
    {
        return 'Sign in as each account and change its password under your profile, or set a new one under Settings > Users.';
    }

    private function usesDefaultPassword(User $user): bool
    {
        $hash = (string) $user->password;

        if ($hash === '' || (bool) $user->is_socialite) {
            return false;
        }

        if (hash_equals(self::SEEDED_HASH, $hash)) {
            return true;
        }

        foreach (self::DEFAULT_PASSWORDS as $password) {
            try {
                if (Hash::check($password, $hash)) {
                    return true;
                }
            } catch (RuntimeException) {
                return false;
            }
        }

        return false;
    }
}
