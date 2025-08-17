<?php

namespace Database\Factories;

use App\Models\Evaluation;
use App\Models\Employee;
use App\Models\Criteria;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvaluationFactory extends Factory
{
    protected $model = Evaluation::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'criteria_id' => Criteria::factory(),
            'score' => fake()->numberBetween(60, 100),
            'evaluation_period' => fake()->randomElement([
                '2024-01', '2024-02', '2024-03', 
                '2023-12', '2023-11', '2023-10'
            ]),
        ];
    }

    public function forPeriod(string $period): static
    {
        return $this->state(fn (array $attributes) => [
            'evaluation_period' => $period,
        ]);
    }

    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(90, 100),
        ]);
    }

    public function good(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(80, 89),
        ]);
    }

    public function average(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(70, 79),
        ]);
    }

    public function poor(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->numberBetween(50, 69),
        ]);
    }
}