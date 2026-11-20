<?php

namespace App\Services\Security\Checks\Api;

use App\Enums\SecurityCheckGroup;
use App\Enums\SecuritySeverity;
use App\Services\Security\Checks\AbstractSecurityCheck;
use App\Services\Security\SecurityCheckResult;
use App\Services\Security\SecurityContext;
use Illuminate\Support\Facades\DB;

/**
 * REST API tokens never expire and have full access, so old ones should be rotated. Reads names and dates only.
 */
class RestApiTokensCheck extends AbstractSecurityCheck
{
    protected string $id = 'api.tokens';
    protected SecurityCheckGroup $group = SecurityCheckGroup::Api;
    protected string $title = 'REST API tokens are rotated';
    protected SecuritySeverity $severity = SecuritySeverity::Medium;
    protected string $rationale = 'REST API tokens never expire and give full access to the API. The longer a token lives, the more places it has been copied to, so old tokens should be replaced.';
    protected array $references = ['NIST IA-5(1)', 'NIST AC-2(3)', 'OWASP ASVS V3.5'];

    public function run(SecurityContext $context): SecurityCheckResult
    {
        $maxAgeDays = (int) config('security-checks.thresholds.api_token_max_age_days', 365);
        $total = DB::table('rest_api_tokens')->count();
        $old = DB::table('rest_api_tokens')
            ->where('created_at', '<', now()->subDays($maxAgeDays))
            ->orderBy('created_at')
            ->pluck('api_token_name')
            ->map(fn (mixed $name): string => trim((string) $name) !== '' ? (string) $name : 'unnamed')
            ->all();

        $evidence = ['tokens' => $total, 'older_than_days' => $maxAgeDays, 'old_tokens' => count($old)];

        if ($old === []) {
            return SecurityCheckResult::pass(
                $total === 0 ? 'No REST API tokens exist.' : 'None of the ' . $this->plural($total, 'REST API token') . " is older than {$maxAgeDays} days.",
                $evidence,
            );
        }

        $count = count($old);

        return SecurityCheckResult::warn(
            $this->plural($count, 'REST API token') . ' ' . $this->verb($count, 'is', 'are') . " older than {$maxAgeDays} days: " . implode(', ', array_slice($old, 0, 5)) . ($count > 5 ? ' and more' : '') . '.',
            $this->fixGuide(),
            $evidence + ['token_names' => array_slice($old, 0, 10)],
        );
    }

    public function fixGuide(): string
    {
        return 'Create a new token under Settings > REST API, move your scripts to it, then delete the old one.';
    }
}
