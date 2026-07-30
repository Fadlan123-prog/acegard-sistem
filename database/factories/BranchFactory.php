<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'    => 'Acegard ' . fake()->city(),
            'code'    => strtoupper(fake()->unique()->lexify('???')),
            'type'    => 'branch',
            'status'  => 'active',
            'city'    => fake()->city(),
            'address' => fake()->address(),
        ];
    }
}
