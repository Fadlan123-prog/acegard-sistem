<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id'      => Branch::factory(),
            'name'           => fake()->name(),
            'phone_number'   => fake()->phoneNumber(),
            'job_position'   => 'Teknisi',
            'monthly_salary' => 3000000,
            'is_active'      => true,
            'hire_date'      => fake()->date(),
        ];
    }
}
