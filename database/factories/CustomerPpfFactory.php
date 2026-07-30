<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerPpfFactory extends Factory
{
    protected $model = \App\Models\CustomerPpf::class;

    public function definition(): array
    {
        return [
            'branch_id'      => Branch::factory(),
            'wsn'            => fake()->unique()->numerify('WSNP########'),
            'card_number'    => fake()->unique()->bothify('PPF-####-????'),
            'name'           => fake()->name(),
            'phone'          => fake()->phoneNumber(),
            'email'          => fake()->safeEmail(),
            'address'        => fake()->address(),
            'city'           => fake()->city(),
            'country'        => 'Indonesia',
            'vehicle_brand'  => 'Honda',
            'vehicle_model'  => 'Civic',
            'vehicle_year'   => (string) fake()->numberBetween(2015, 2026),
            'vehicle_color'  => 'Hitam',
            'vehicle_plate'  => strtoupper(fake()->bothify('B #### ??')),
        ];
    }
}
