<?php

namespace Database\Factories;

use App\Models\SettingsPurge;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SettingsPurge>
 */
class SettingsPurgeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'target' => fake()->unique()->word() . '_' . fake()->randomNumber(5),
            'retention_days' => fake()->numberBetween(1, 365),
            'options' => [
                'batch_size' => 1000,
                'cleanup_frequency' => 'daily',
                'enabled' => false,
            ],
        ];
    }

    public function enabled(): static
    {
        return $this->withOption('enabled', true);
    }

    public function disabled(): static
    {
        return $this->withOption('enabled', false);
    }

    public function forTarget(string $target): static
    {
        return $this->state(fn (array $attributes): array => ['target' => $target]);
    }

    public function withRetentionDays(int $days): static
    {
        return $this->state(fn (array $attributes): array => ['retention_days' => $days]);
    }

    public function withOption(string $key, mixed $value): static
    {
        return $this->state(fn (array $attributes): array => [
            'options' => array_merge($attributes['options'] ?? [], [$key => $value]),
        ]);
    }
}
