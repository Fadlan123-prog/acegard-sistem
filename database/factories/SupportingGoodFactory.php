<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupportingGoodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id'     => Branch::factory(),
            'name'          => fake()->words(2, true),
            'category'      => 'chemical',
            'unit'          => 'pcs',
            'current_stock' => 10,
            'minimum_stock' => 2,
        ];
    }
}
