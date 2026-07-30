<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class CustomerCommissionSyncService
{
    /**
     * Delete old installer commissions for a customer and re-insert based on current
     * split settings. Safe to call on both store (no existing rows) and update.
     *
     * @throws \RuntimeException if $finalType is not in commission config
     */
    public function syncInstaller(
        Customer $customer,
        string   $finalType,
        string   $shareMode,
        int      $pTukang,
        int      $pKenek,
        bool     $normalize
    ): void {
        DB::table('commissions')
            ->where('customer_id', $customer->id)
            ->whereNull('invoice_id')
            ->where('role', 'installer')
            ->delete();

        $rows = $this->buildRows(
            customerId: (int) $customer->id,
            tukangId:   (int) $customer->tukang_id ?: null,
            kenekId:    (int) $customer->kenek_id  ?: null,
            finalType:  $finalType,
            shareMode:  $shareMode,
            pTukang:    $pTukang,
            pKenek:     $pKenek,
            normalize:  $normalize,
        );

        if (!empty($rows)) {
            DB::table('commissions')->insert($rows);
        }
    }

    /**
     * Preview split percentages and amounts without touching the database.
     * Used by CustomerController::previewCommission().
     */
    public function previewSplit(
        string $finalType,
        ?int   $tukangId,
        ?int   $kenekId,
        string $shareMode,
        int    $pTukang,
        int    $pKenek,
        bool   $normalize
    ): array {
        $pool = $this->resolvePool($finalType);
        [$amtT, $amtK] = $this->configAmounts($finalType);

        $hasTukang = !empty($tukangId);
        $hasKenek  = !empty($kenekId);
        $autoSplit = (int) config('commission.auto_split.tukang', 70);
        $autoK     = 100 - $autoSplit;

        $tPct     = 0;
        $kPct     = 0;
        $modeUsed = $shareMode;

        if ($shareMode === 'custom' && ($hasTukang || $hasKenek)) {
            $tPct = $hasTukang ? $pTukang : 0;
            $kPct = $hasKenek  ? $pKenek  : 0;
            $sum  = $tPct + $kPct;

            if ($sum <= 0) {
                $modeUsed = 'auto';
            } elseif ($normalize) {
                $tPct = (int) round($tPct * 100 / $sum);
                $kPct = 100 - $tPct;
            }
        }

        if ($modeUsed === 'auto') {
            if ($hasTukang && $hasKenek) {
                $tPct = $autoSplit; $kPct = $autoK;
            } elseif ($hasTukang) {
                $tPct = 100; $kPct = 0;
            } elseif ($hasKenek) {
                $tPct = 0;   $kPct = 100;
            }
        }

        $tNom  = (int) floor($pool * $tPct / 100);
        $kNom  = (int) floor($pool * $kPct / 100);
        $delta = $pool - ($tNom + $kNom);
        if ($delta !== 0 && $hasTukang) $tNom += $delta;
        elseif ($delta !== 0 && $hasKenek) $kNom += $delta;

        return [
            'pool'      => $pool,
            'amtT'      => $amtT,
            'amtK'      => $amtK,
            'tPct'      => $tPct,
            'kPct'      => $kPct,
            'tNom'      => $tNom,
            'kNom'      => $kNom,
            'modeUsed'  => $modeUsed,
            'autoSplit' => $autoSplit,
            'autoK'     => $autoK,
        ];
    }

    /**
     * Build commission rows for installer(s). Does not write to DB.
     */
    private function buildRows(
        int    $customerId,
        ?int   $tukangId,
        ?int   $kenekId,
        string $finalType,
        string $shareMode,
        int    $pTukang,
        int    $pKenek,
        bool   $normalize
    ): array {
        $pool = $this->resolvePool($finalType);
        if ($pool <= 0) return [];

        $now       = now();
        $hasTukang = !empty($tukangId);
        $hasKenek  = !empty($kenekId);
        $mode      = $shareMode;
        $rows      = [];

        if ($mode === 'custom' && ($hasTukang || $hasKenek)) {
            $pairs = [];
            if ($hasTukang) $pairs[] = ['id' => $tukangId, 'p' => $pTukang];
            if ($hasKenek)  $pairs[] = ['id' => $kenekId,  'p' => $pKenek];

            $sumP = array_sum(array_column($pairs, 'p'));

            if ($sumP <= 0) {
                $mode = 'auto';
            } else {
                if ($normalize) {
                    foreach ($pairs as $i => $pr) {
                        $pairs[$i]['p'] = (int) round($pr['p'] * 100 / $sumP);
                    }
                    $deltaPct = 100 - array_sum(array_column($pairs, 'p'));
                    if ($deltaPct !== 0) $pairs[0]['p'] += $deltaPct;
                }

                $sumAmt = 0;
                foreach ($pairs as $pr) {
                    $pct = max(0, (int) $pr['p']);
                    if ($pct <= 0) continue;
                    $amount = (int) floor($pool * $pct / 100);
                    if ($amount > 0) {
                        $rows[] = $this->row($pr['id'], $customerId, $amount, $now);
                        $sumAmt += $amount;
                    }
                }

                if ($normalize) {
                    $deltaAmt = $pool - $sumAmt;
                    if ($deltaAmt !== 0) {
                        foreach ($rows as &$r) {
                            if ($hasTukang && $r['employees_id'] === $tukangId) { $r['amount'] += $deltaAmt; break; }
                            if (!$hasTukang && $hasKenek && $r['employees_id'] === $kenekId) { $r['amount'] += $deltaAmt; break; }
                        }
                        unset($r);
                    }
                }
            }
        }

        if ($mode === 'auto') {
            if ($hasTukang && $hasKenek) {
                $tukangEmp = Employee::find($tukangId);
                $kenekEmp  = Employee::find($kenekId);
                $tPos      = strtolower(trim($tukangEmp?->job_position ?? ''));
                $kPos      = strtolower(trim($kenekEmp?->job_position ?? ''));
                $samePair  = $tPos !== '' && $tPos === $kPos;

                $tAmt = $samePair
                    ? (int) floor($pool / 2)
                    : (int) floor($pool * (int) config('commission.auto_split.tukang', 70) / 100);
                $kAmt = $pool - $tAmt;

                $rows[] = $this->row($tukangId, $customerId, $tAmt, $now);
                $rows[] = $this->row($kenekId,  $customerId, $kAmt, $now);
            } elseif ($hasTukang) {
                $rows[] = $this->row($tukangId, $customerId, $pool, $now);
            } elseif ($hasKenek) {
                $rows[] = $this->row($kenekId, $customerId, $pool, $now);
            }
        }

        return $rows;
    }

    private function row(int $employeeId, int $customerId, int $amount, $now): array
    {
        return [
            'employees_id' => $employeeId,
            'customer_id'  => $customerId,
            'invoice_id'   => null,
            'amount'       => $amount,
            'role'         => 'installer',
            'created_at'   => $now,
            'updated_at'   => $now,
        ];
    }

    private function resolvePool(string $finalType): int
    {
        [$amtT, $amtK] = $this->configAmounts($finalType);
        return $amtT + $amtK;
    }

    private function configAmounts(string $finalType): array
    {
        $row = config("commission.fixed.$finalType");
        if (!$row) {
            throw new \RuntimeException("Tipe komisi '$finalType' tidak ditemukan di config/commission.php");
        }
        $amtT = (int) ($row['teknisi'] ?? $row['Teknisi'] ?? $row['tukang'] ?? 0);
        $amtK = (int) ($row['kenek']   ?? $row['Kenek']   ?? 0);
        return [$amtT, $amtK];
    }
}
