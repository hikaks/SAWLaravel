<?php

namespace Database\Factories;

use App\Models\Criteria;
use Illuminate\Database\Eloquent\Factories\Factory;

class CriteriaFactory extends Factory
{
    protected $model = Criteria::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Kinerja Kerja',
                'Kehadiran', 
                'Kemampuan Teknis',
                'Kerja Sama Tim',
                'Disiplin',
                'Komunikasi',
                'Inisiatif',
                'Kreativitas'
            ]),
            'weight' => fake()->numberBetween(10, 30),
            'type' => fake()->randomElement(['benefit', 'cost']),
        ];
    }

    public function benefit(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'benefit',
        ]);
    }

    public function cost(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cost',
        ]);
    }
}