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
        $badgecolors = [
            'bg-stone-700 text-stone-200 border-stone-500',
            'bg-lime-700 text-lime-200 border-lime-500',
            'bg-yellow-700 text-yellow-200 border-yellow-500',
            'bg-teal-700 text-teal-100 border-teal-500',
            'bg-sky-700 text-sky-100 border-sky-500',
            'bg-violet-700 text-violet-200 border-violet-500',
            'bg-fuchsia-700 text-fuchsia-200 border-fuchsia-500',
        ];

        return [
            'categoryName' => $this->faker->firstName . mt_rand(1000000, 9999999),
            'categoryDescription' => $this->faker->sentence,
            'badgeColor' =>
            $this->faker->randomElement($badgecolors),
        ];
    }
}
