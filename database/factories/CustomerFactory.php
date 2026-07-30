<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id'         => Branch::factory(),
            'wsn'               => fake()->unique()->numerify('WSN########'),
            'card_number'       => fake()->unique()->numerify('2026####'),
            'name'              => fake()->name(),
            'email'             => fake()->safeEmail(),
            'phone_number'      => fake()->phoneNumber(),
            'address'           => fake()->address(),
            'vehicle_brand'     => 'Toyota',
            'vehicle_model'     => 'Avanza',
            'plat_number'       => strtoupper(fake()->bothify('B #### ??')),
            'vehicle_year'      => (string) fake()->numberBetween(2015, 2026),
            'dealer_name'       => fake()->company(),
            'city'              => fake()->city(),
            'country'           => 'Indonesia',
            'warantee_start'    => now()->toDateString(),
            'warantee_end'      => now()->addYears(5)->toDateString(),
            'warantee_duration' => 5,
            'install_type'      => 'fullset',
            'has_panoramic'     => false,
            'install_date'      => now()->toDateString(),
        ];
    }
}
