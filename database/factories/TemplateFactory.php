<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->numberBetween(5000, 9000),
            'fileName' => $this->faker->firstNameMale,
            'templateName' => $this->faker->word,
            'description' => $this->faker->sentence,
            'created_at' => $this->faker->dateTimeThisDecade($max = 'now', $timezone = null),
            'updated_at' => $this->faker->dateTimeThisDecade($max = 'now', $timezone = null),
        ];
    }
}
