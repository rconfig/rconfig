<?php

namespace Database\Factories;

use App\Models\RestApiToken;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RestApiToken>
 */
class RestApiTokenFactory extends Factory
{
    protected $model = RestApiToken::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'api_token' => (string) Str::uuid(),
            'api_token_name' => $this->faker->unique()->words(2, true),
            'api_token_status' => 1,
            'api_token_expire_date' => null,
        ];
    }
}
