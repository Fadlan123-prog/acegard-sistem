<?php

namespace App\Services;

use App\Domain\Sales\SponsorLink;
use App\Models\SalesNetworkProfile;
use App\Models\User;

/**
 * Jalan ke atas rantai sponsor (sales_network_profiles.sponsor_id) dari satu
 * employee, menghasilkan SponsorLink[] yang dipakai SalesCommissionCalculator
 * (yang sendirinya murni/tanpa query DB). Dipisah dari Calculator supaya
 * logika perhitungan komisi tetap testable tanpa database.
 */
class SponsorChainResolver
{
    /**
     * @return SponsorLink[] Urutan dari sponsor terdekat ($chain[0]) ke yang terjauh.
     */
    public function resolveFrom(int $startEmployeeId, int $maxDepth = 50): array
    {
        $chain = [];
        $currentEmployeeId = $startEmployeeId;
        $visited = [$startEmployeeId => true]; // jaga-jaga kalau ada data rantai muter balik

        for ($i = 0; $i < $maxDepth; $i++) {
            $sponsorId = SalesNetworkProfile::where('employee_id', $currentEmployeeId)->value('sponsor_id');

            if (! $sponsorId || isset($visited[$sponsorId])) {
                break;
            }
            $visited[$sponsorId] = true;

            $sponsorUser = User::where('employee_id', $sponsorId)->first();
            $chain[] = new SponsorLink(
                employeeId: $sponsorId,
                isSales: $sponsorUser?->hasRole('sales') ?? false,
                isMarketing: $sponsorUser?->hasRole('marketing') ?? false,
            );

            $currentEmployeeId = $sponsorId;
        }

        return $chain;
    }
}
