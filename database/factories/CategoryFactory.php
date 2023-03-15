<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'categoryName' => $this->faker->firstName.mt_rand(1000000, 9999999),
            'categoryDescription' => $this->faker->sentence,
            'badgeColor' => $this->faker->safeColorName,
        ];
    }
}
