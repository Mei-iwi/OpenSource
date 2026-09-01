<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    public function definition(): array
    {
        return ['employee_id' => Employee::factory(), 'leave_type' => fake()->randomElement(['annual', 'sick', 'unpaid', 'other']), 'start_date' => now()->addDays(5)->toDateString(), 'end_date' => now()->addDays(7)->toDateString(), 'reason' => fake()->sentence(), 'status' => 'pending'];
    }
}
