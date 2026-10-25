<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $price = 1500000;

        return [
            'customer_id'       => Customer::factory(),
            'payment_method'    => 'cash',
            'price'             => $price,
            'discount'          => 0,
            'downpayment'       => 0,
            'total_price'       => $price,
            'remaining_payment' => $price,
            'status'            => 'unpaid',
            'work_status'       => 'pending',
            'invoice_number'    => 'INV-' . fake()->unique()->numerify('########'),
            'invoice_date'      => now(),
        ];
    }

    public function workCompleted(): static
    {
        return $this->state(fn () => [
            'work_status'  => 'completed',
            'completed_at' => now(),
            'qc_status'    => 'pending',
        ]);
    }
}
