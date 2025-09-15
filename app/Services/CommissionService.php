<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Commission;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Mencatat komisi nominal tetap untuk tukang/kenek berdasar config('commission.fixed')
     */
    public function createFixedForInvoice(Invoice $invoice, string $installType, int $tukangId, ?int $kenekId = null): void
    {
         Log::info('[CommissionService] ENTER', compact('installType','tukangId','kenekId'));

        $row = config("commission.fixed.$installType");
        if (!$row) {
            Log::warning('[CommissionService] Unknown install type', compact('installType'));
            return;
        }

        $amtT = $row['tukang'] ?? $row['Teknisi'] ?? 0;
        $amtK = $row['kenek']  ?? $row['Kenek']  ?? 0;

        // DIAG: jangan pakai DB::transaction di sini dulu
        if ($tukangId && $amtT > 0) {
            $id = DB::table('commissions')->insertGetId([
                'employees_id' => $tukangId,
                'customer_id'  => $invoice->customer_id,
                'invoice_id'   => $invoice->id,
                'amount'       => (int)$amtT,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            Log::info('[CommissionService] inserted tukang', ['id'=>$id,'amount'=>$amtT]);
        }

        if ($kenekId && $amtK > 0) {
            $id = DB::table('commissions')->insertGetId([
                'employees_id' => $kenekId,
                'customer_id'  => $invoice->customer_id,
                'invoice_id'   => $invoice->id,
                'amount'       => (int)$amtK,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
            Log::info('[CommissionService] inserted kenek', ['id'=>$id,'amount'=>$amtK]);
        }

        Log::info('[CommissionService] DONE', ['invoice_id'=>$invoice->id]);
    }

    private function baseFromFinal(string $finalType): string
    {
        $t = strtolower($finalType);
        if (str_starts_with($t, 'fullset') || str_contains($t, 'panoramic')) {
            return 'fullset';
        }
        if ($t === 'dsp')  return 'dsp';
        if ($t === 'skkb') return 'skkb';
        return 'fullset';
    }

    /**
     * Simpan komisi marketing
     */
    public function createMarketingForCustomer(
        int $marketingEmployeeId,
        int $customerId,
        ?int $invoiceId,
        int $categoryProductId,
        string $finalType
    ): void {
        if (!$marketingEmployeeId) return;

        $base = $this->baseFromFinal($finalType);
        $map  = config('commission.marketing', []);
        $cat  = $map[$categoryProductId] ?? ($map['default'] ?? []);
        $amount = (int)($cat[$base] ?? 0);

        if ($amount <= 0) return;

        DB::table('commissions')->insert([
            'employees_id'        => $marketingEmployeeId,
            'customer_id'         => $customerId,
            'invoice_id'          => $invoiceId,
            'amount'              => $amount,
            'role'                => 'marketing',
            'category_product_id' => $categoryProductId,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);
    }

    public function resolveMarketingCategoryId(int $customerId): ?int
    {
        // Ambil semua category_product_id unik milik customer ini
        $catIds = DB::table('customer_product')
            ->where('customer_id', $customerId)
            ->pluck('category_product_id')
            ->unique()
            ->values();

        if ($catIds->isEmpty()) {
            return null;
        }

        // 1) Coba aturan prioritas dari config
        $priority = config('commission.marketing_category_priority', []);
        foreach ($priority as $prefer) {
            if ($catIds->contains((int)$prefer)) {
                return (int)$prefer;
            }
        }

        // 2) Fallback: pilih kategori dengan durasi terkecil (mis. NOTCH 5 < 4K 7)
        $candidates = DB::table('category_products')
            ->whereIn('id', $catIds)
            ->select('id', 'duration')
            ->orderBy('duration', 'asc') // durasi kecil = prioritas
            ->orderBy('id', 'asc')
            ->get();

        if ($candidates->isNotEmpty()) {
            return (int)$candidates->first()->id;
        }

        // 3) Fallback terakhir: ambil yang pertama
        return (int)$catIds->first();
    }

    /**
     * Buat komisi marketing untuk customer (memakai kategori hasil resolver di atas)
     */
    public function createMarketingForCustomerAutoCategory(
        int $marketingEmployeeId,
        int $customerId,
        ?int $invoiceId,
        string $finalType
    ): void {
        $categoryProductId = $this->resolveMarketingCategoryId($customerId);
        if (!$categoryProductId) return;

        $this->createMarketingForCustomer(
            $marketingEmployeeId,
            $customerId,
            $invoiceId,
            $categoryProductId,
            $finalType
        );
    }
}
