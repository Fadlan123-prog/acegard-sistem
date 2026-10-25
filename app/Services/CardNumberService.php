<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerBuilding;
use Illuminate\Support\Facades\DB;

class CardNumberService
{
    /**
     * Generate card number for car customers: YYYY#### (e.g. 20240001)
     */
    public function generateCustomerCardNumber(): string
    {
        $year = (int) now()->format('Y');

        DB::table('card_number_sequences')->insertOrIgnore([
            'year'          => $year,
            'last_sequence' => 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $counter = DB::table('card_number_sequences')
            ->where('year', $year)
            ->lockForUpdate()
            ->first();

        $maxExisting = Customer::query()
            ->whereRaw('CAST(card_number AS CHAR) LIKE ?', [$year . '%'])
            ->selectRaw('MAX(CAST(SUBSTRING(CAST(card_number AS CHAR), 5) AS UNSIGNED)) as max_sequence')
            ->value('max_sequence');

        $base         = max((int) ($counter->last_sequence ?? 0), (int) ($maxExisting ?? 0));
        $nextSequence = $base + 1;

        DB::table('card_number_sequences')
            ->where('year', $year)
            ->update(['last_sequence' => $nextSequence, 'updated_at' => now()]);

        return sprintf('%d%04d', $year, $nextSequence);
    }

    /**
     * Generate card number for building customers: BD{YYYY}{#####} (e.g. BD202500001)
     */
    public function generateBuildingCardNumber(): string
    {
        $prefix   = 'BD' . now()->year;
        $lastCard = CustomerBuilding::query()
            ->where('card_number', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('card_number')
            ->value('card_number');

        $seq = 1;
        if ($lastCard && preg_match('/^' . preg_quote($prefix, '/') . '(\d{5})$/', $lastCard, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
