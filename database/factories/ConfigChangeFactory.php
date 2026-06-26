<?php

namespace Database\Factories;

use App\Models\Config;
use App\Models\ConfigChange;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConfigChange>
 */
class ConfigChangeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'current_config_id' => Config::factory(),
            'previous_config_id' => Config::factory(),
            'config_version' => $this->faker->numberBetween(2, 20),
            'config_change_type' => $this->faker->randomElement(['added', 'deleted', 'changed']),
            'config_diff' => '<div class="diff-wrapper"><ins>new line</ins><del>old line</del></div>',
            'compare_exclusion_settings' => [
                'config_compare_settings' => ['ignoreWhitespace' => false],
                'config_compare_exclusion_file' => '',
            ],
            'change_trigger' => 'pull',
        ];
    }
}
