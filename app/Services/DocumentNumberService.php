<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Generates unique document numbers atomically using a Redis distributed lock.
 *
 * Fixes the race condition present in 4 separate busy-wait loops:
 *   - InvoiceController::generateInvoiceNumber()
 *   - InvoiceBuildingController::generateInvoiceNumber()
 *   - PurchaseController (inline)
 *   - FilmStockTransferService::generateTransferNumber()
 *
 * Design:
 *   1. Acquire a Redis lock scoped to the (table, column) pair.
 *   2. Generate a candidate number.
 *   3. Check DB uniqueness while holding the lock.
 *   4. Release the lock.
 *   5. The DB unique constraint acts as the final safety net if two processes
 *      somehow generate the same value (callers should catch UniqueConstraintViolation).
 *
 * Usage:
 *   $number = $this->docNumbers->generate('invoices', 'invoice_number', 'INV');
 *   // → "INV-260706-4271"
 */
final class DocumentNumberService
{
    private const LOCK_TTL_SECONDS = 5;
    private const MAX_ATTEMPTS     = 3;
    private const BACKOFF_MICROSEC = 100_000; // 100ms

    /**
     * @param  string $table  DB table to check (e.g. 'invoices')
     * @param  string $column Column holding the number (e.g. 'invoice_number')
     * @param  string $prefix Human-readable prefix (e.g. 'INV', 'BLDG', 'TRF')
     * @return string         Generated unique document number
     *
     * @throws RuntimeException if a unique number cannot be generated after MAX_ATTEMPTS
     */
    public function generate(string $table, string $column, string $prefix): string
    {
        $lockKey  = "docnum.lock.{$table}.{$column}";
        $attempts = 0;

        while ($attempts < self::MAX_ATTEMPTS) {
            $lock = Cache::lock($lockKey, self::LOCK_TTL_SECONDS);

            if (! $lock->get()) {
                $attempts++;
                usleep(self::BACKOFF_MICROSEC);
                continue;
            }

            try {
                $candidate = $this->buildCandidate($prefix);

                $taken = DB::table($table)
                    ->where($column, $candidate)
                    ->exists();

                if (! $taken) {
                    return $candidate;
                }
            } finally {
                $lock->release();
            }

            $attempts++;
        }

        throw new RuntimeException(
            "DocumentNumberService: could not generate a unique [{$column}] for [{$table}] "
            . "after " . self::MAX_ATTEMPTS . " attempts. Lock key: {$lockKey}"
        );
    }

    /**
     * Generates a number in the format: {PREFIX}-{YYMMDD}-{4-digit-random}
     * Example: INV-260706-4271
     */
    private function buildCandidate(string $prefix): string
    {
        $date   = now()->format('ymd');
        $suffix = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$suffix}";
    }
}
