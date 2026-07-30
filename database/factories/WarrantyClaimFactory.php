<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarrantyClaimFactory extends Factory
{
    public function definition(): array
    {
        $customer = Customer::factory()->create();

        return [
            'claim_number'      => 'CLM-' . fake()->unique()->numerify('########'),
            'claim_date'        => now()->toDateString(),
            'warranty_type'     => 'car_film',
            'warrantable_type'  => Customer::class,
            'warrantable_id'    => $customer->id,
            'branch_id'         => $customer->branch_id,
            'card_number'       => $customer->card_number,
            'cause'             => 'unknown',
            'status'            => 'submitted',
            'is_chargeable'     => false,
            'quote_total'       => 0,
        ];
    }
}
