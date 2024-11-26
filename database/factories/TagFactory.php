<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $siteCode = 'Site-' . $this->faker->countryISOAlpha3 . '-' . $this->faker->randomNumber(5);

        return [
            'tagname' => $siteCode,
            'tagDescription' => 'A demo tag for ' . $siteCode,
        ];
    }
}
