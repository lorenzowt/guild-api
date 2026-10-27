<?php

namespace Database\Factories;

use App\Models\Hero;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Hero>
 */
class HeroFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'hero_class' => fake()->randomElement([
                'cleric',
                'mage',
                'rogue',
                'warrior',
            ]),
        ];
    }
}
