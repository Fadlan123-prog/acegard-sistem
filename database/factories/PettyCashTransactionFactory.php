<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class PettyCashTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id'   => Branch::factory(),
            'type'        => 'in',
            'date'        => now()->toDateString(),
            'amount'      => 100000,
            'description' => fake()->sentence(),
        ];
    }
}
