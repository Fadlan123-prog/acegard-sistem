<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerBuildingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id'    => Branch::factory(),
            'wsn'          => fake()->unique()->numerify('WSNB########'),
            'card_number'  => fake()->unique()->numerify('BD2026#####'),
            'name'         => fake()->name(),
            'email'        => fake()->safeEmail(),
            'phone_number' => fake()->phoneNumber(),
            'address'      => fake()->address(),
            'dealer_name'  => fake()->company(),
            'applicator'   => fake()->name(),
            'city'         => fake()->city(),
            'country'      => 'Indonesia',
        ];
    }
}
