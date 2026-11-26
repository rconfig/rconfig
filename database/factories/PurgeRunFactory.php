<?php

namespace Database\Factories;

use App\Models\PurgeRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurgeRun>
 */
class PurgeRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startedAt = now()->subMinutes(5);

        return [
            'type' => 'data_purge',
            'mode' => 'purge',
            'dry_run' => false,
            'status' => 'success',
            'source' => 'rconfig:data-purge',
            'scope' => ['table' => null],
            'summary' => ['options' => [], 'metrics' => ['total_checked' => 0]],
            'message' => null,
            'started_at' => $startedAt,
            'finished_at' => $startedAt->copy()->addSeconds(3),
        ];
    }

    public function dryRun(): static
    {
        return $this->state(fn (array $attributes): array => ['dry_run' => true]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => 'failed', 'message' => 'Purge failed']);
    }
}
