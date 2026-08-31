<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'department_id' => \App\Models\Department::factory(),
            'employee_code' => fake()->unique()->bothify('EMP-####'),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'position' => fake()->jobTitle(),
            'hire_date' => fake()->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
            'employment_status' => 'active',
            'avatar_path' => null,
        ];
    }
}
