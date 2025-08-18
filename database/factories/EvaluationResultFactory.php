<?php

namespace Database\Factories;

use App\Models\EvaluationResult;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EvaluationResult>
 */
class EvaluationResultFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvaluationResult::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'total_score' => $this->faker->randomFloat(4, 0.5000, 1.0000),
            'ranking' => $this->faker->numberBetween(1, 10),
            'evaluation_period' => $this->faker->randomElement([
                '2024-01',
                '2024-02',
                '2024-03',
                '2024-04',
                '2024-05',
                '2024-06'
            ]),
        ];
    }

    /**
     * Set a specific evaluation period.
     */
    public function period(string $period): static
    {
        return $this->state(fn (array $attributes) => [
            'evaluation_period' => $period,
        ]);
    }

    /**
     * Set a specific employee.
     */
    public function forEmployee($employee): static
    {
        return $this->state(fn (array $attributes) => [
            'employee_id' => $employee instanceof Employee ? $employee->id : $employee,
        ]);
    }

    /**
     * Set a specific ranking.
     */
    public function ranking(int $ranking): static
    {
        return $this->state(fn (array $attributes) => [
            'ranking' => $ranking,
        ]);
    }
}