<?php

namespace App\Domain\Sales;

/**
 * Pure domain service: menentukan siapa dapat komisi apa untuk satu transaksi
 * sales, dari rantai sponsor yang sudah di-resolve oleh pemanggil (lihat
 * SponsorChainResolver — yang melakukan query DB-nya).
 *
 * Tidak pernah query/tulis DB, tidak pernah lazy-load relasi — konsisten
 * dengan kontrak App\Domain\Commission\CommissionCalculator yang sudah ada.
 *
 * Aturan:
 *   - Sales yang closing selalu dapat closing_rate × total_price.
 *   - Stage 1/2/3 = simpul ke-1/2/3 di $chain (0-indexed: $chain[0] = stage 1).
 *     HANYA dibayar kalau simpul itu ADA dan SponsorLink->isSales === true.
 *     Kalau simpul tidak ada atau isSales === false (mis. marketing) →
 *     dilewati, tidak dibayar, tidak dialihkan ke siapa pun.
 *   - Marketing referral = simpul PERTAMA di $chain (urutan mana pun, tanpa
 *     batas kedalaman) dengan isMarketing === true. Marketing TIDAK PERNAH
 *     menerima komisi stage — kalau satu simpul isMarketing true, dia otomatis
 *     tidak akan lolos cek isSales di stage manapun (lihat SponsorChainResolver).
 */
final class SalesCommissionCalculator
{
    public function __construct(
        private readonly array $config, // config('sales_commission')
    ) {}

    /**
     * @param  SponsorLink[]  $chain  Urutan dari sponsor terdekat ke yang terjauh.
     * @return array<int, array{employee_id:int, role:string, amount:int}>
     */
    public function resolve(int $closingEmployeeId, int $totalPrice, array $chain): array
    {
        $lines = [];

        $closingAmount = $this->closingAmount($totalPrice);
        if ($closingAmount > 0) {
            $lines[] = [
                'employee_id' => $closingEmployeeId,
                'role'        => 'sales_closing',
                'amount'      => $closingAmount,
            ];
        }

        foreach ([1 => 'referral_stage_1', 2 => 'referral_stage_2', 3 => 'referral_stage_3'] as $stage => $role) {
            $node = $chain[$stage - 1] ?? null;
            if (! $node || ! $node->isSales) {
                continue; // tidak ada ancestor di posisi ini, atau dia marketing — stage dilewati
            }

            $amount = (int) ($this->config['stage'][$stage] ?? 0);
            if ($amount <= 0) continue;

            $lines[] = ['employee_id' => $node->employeeId, 'role' => $role, 'amount' => $amount];
        }

        $marketingNode = $this->firstMarketingNode($chain);
        if ($marketingNode) {
            $amount = (int) ($this->config['marketing_referral'] ?? 0);
            if ($amount > 0) {
                $lines[] = ['employee_id' => $marketingNode->employeeId, 'role' => 'marketing_referral', 'amount' => $amount];
            }
        }

        return $lines;
    }

    public function closingAmount(int $totalPrice): int
    {
        $rate = (float) ($this->config['closing_rate'] ?? 0);

        return (int) round($totalPrice * $rate);
    }

    /** @param  SponsorLink[]  $chain */
    private function firstMarketingNode(array $chain): ?SponsorLink
    {
        foreach ($chain as $node) {
            if ($node->isMarketing) {
                return $node;
            }
        }

        return null;
    }
}
