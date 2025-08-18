<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_code' => $this->faker->unique()->regexify('[A-Z]{2}[0-9]{4}'),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'department' => $this->faker->randomElement([
                'IT',
                'HR',
                'Finance',
                'Marketing',
                'Operations',
                'Sales'
            ]),
            'position' => $this->faker->randomElement([
                'Manager',
                'Senior Staff',
                'Staff',
                'Junior Staff',
                'Supervisor',
                'Team Lead'
            ]),
        ];
    }


}