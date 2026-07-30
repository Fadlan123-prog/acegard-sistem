<?php

namespace App\Services;

class InvoiceCalculationService
{
    public function calculate(int|float $price, int|float $discount = 0, int|float $downpayment = 0): array
    {
        $totalPrice       = max(0, $price - $discount);
        $remainingPayment = max(0, $totalPrice - $downpayment);

        return [
            'price'             => $price,
            'discount'          => $discount,
            'downpayment'       => $downpayment,
            'total_price'       => $totalPrice,
            'remaining_payment' => $remainingPayment,
        ];
    }

    public function calculateFromProducts(array $products, float $discount = 0, float $downpayment = 0): array
    {
        $price = collect($products)->sum(fn($p) => (float) $p['price']);

        return $this->calculate($price, $discount, $downpayment);
    }
}
