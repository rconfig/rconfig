<?php

namespace Database\Factories;

use App\Models\SecurityCheckRun;
use App\Services\Security\SecurityContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityCheckRun>
 */
class SecurityCheckRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ranAt = now()->subHour();

        return [
            'ran_at' => $ranAt,
            'trigger' => SecurityContext::TRIGGER_SCHEDULE,
            'user_id' => null,
            'app_version' => (string) config('app.version'),
            'duration_ms' => 120,
            'pass_count' => 1,
            'warn_count' => 0,
            'fail_count' => 0,
            'skip_count' => 0,
            'results' => [
                'schema' => 1,
                'product' => 'core',
                'version' => (string) config('app.version'),
                'ran_at' => $ranAt->toIso8601String(),
                'trigger' => SecurityContext::TRIGGER_SCHEDULE,
                'duration_ms' => 120,
                'host' => [],
                'summary' => ['pass' => 1, 'warn' => 0, 'fail' => 0, 'skip' => 0, 'total' => 1],
                'checks' => [[
                    'id' => 'app.debug',
                    'group' => 'app',
                    'group_label' => 'Application',
                    'title' => 'Debug mode is off',
                    'severity' => 'critical',
                    'status' => 'pass',
                    'message' => 'APP_DEBUG is off.',
                    'evidence' => [],
                    'fix' => null,
                    'docs_url' => '',
                    'references' => [],
                    'rationale' => '',
                    'duration_ms' => 1,
                ]],
            ],
        ];
    }
}
