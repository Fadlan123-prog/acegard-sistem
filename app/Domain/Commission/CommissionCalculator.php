<?php

namespace App\Domain\Commission;

use App\Models\Customer;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Pure domain service for installer and marketing commission resolution.
 *
 * Rules encoded here, previously scattered across three copies in:
 *   - CustomerController::store()  (~120 lines)
 *   - CustomerController::update() (~120 lines, copy-paste)
 *   - CustomerController::previewCommission() (condensed duplicate)
 *
 * Contract:
 *   - Never writes to the database.
 *   - Never reads from session.
 *   - Never dispatches events or notifications.
 *   - All relations must be eager-loaded BEFORE calling. Lazy-loading inside
 *     this class is prohibited — it couples domain logic to infrastructure.
 *
 * Required eager loads before calling resolveInstallerSplit():
 *   $customer->load(['tukang', 'kenek', 'marketing', 'customerProducts.part']);
 *
 * Usage:
 *   $calculator = app(CommissionCalculator::class);
 *   $split = $calculator->resolveInstallerSplit($customer, $invoice, lebaran: false);
 */
final class CommissionCalculator
{
    public function __construct(
        private readonly array $config, // config('commission')
    ) {}

    // ── Public API ────────────────────────────────────────────────

    /**
     * Resolves the full installer commission split for an invoice.
     * Returns an immutable CommissionSplit value object.
     */
    public function resolveInstallerSplit(
        Customer $customer,
        Invoice  $invoice,
        bool     $lebaran = false,
    ): CommissionSplit {
        $baseType = $this->resolveBaseType($customer);
        $carCount = $this->carCountForInstaller($invoice, $customer->tukang_id);

        // The "2nd/3rd car of the day" upgrade tier is a fullset-only bonus (it
        // upgrades into fullset_ekstra / fullset_ekstra_plus). DSP and SKKB have
        // their own flat rates and must never be bumped into a fullset tier just
        // because the same installer also did a fullset job earlier that day.
        $upgradeEligible = ! str_starts_with($baseType, 'dsp') && ! str_starts_with($baseType, 'skkb');

        // Select upgrade tier based on how many same-day invoices the installer already has
        $isSecond  = $upgradeEligible && $carCount === 1;
        $isThird   = $upgradeEligible && $carCount >= 2;
        $finalType = match (true) {
            $upgradeEligible && $carCount >= 2 => $this->config['third_car_upgrade']  ?? 'fullset_ekstra_plus',
            $upgradeEligible && $carCount >= 1 => $this->config['second_car_upgrade'] ?? 'fullset_ekstra',
            default                            => $baseType,
        };

        // When upgraded to ekstra/plus tier, carry over panoramic suffix if applicable.
        // fullset_ekstra + panoramic → ekstra_panoramic (config uses short name, not fullset_ekstra_panoramic)
        // fullset_ekstra_plus + panoramic → fullset_ekstra_plus_panoramic (suffix works normally)
        if ($upgradeEligible && $carCount >= 1 && str_contains($baseType, 'panoramic')) {
            $finalType = match ($finalType) {
                'fullset_ekstra' => 'ekstra_panoramic',
                default          => $finalType . '_panoramic',
            };
        }

        $splitClass = $this->detectSplitClass($customer);
        $configKey  = $this->resolveConfigKey($finalType, $splitClass, $lebaran);
        $rates      = $this->ratesForKey($configKey);

        return match ($splitClass) {
            'solo'       => CommissionSplit::solo($rates['teknisi'], $configKey, $isSecond, $isThird, $lebaran),
            'pair_50_50' => CommissionSplit::fiftyFifty($rates['teknisi'], $configKey, $isSecond, $isThird, $lebaran),
            default      => CommissionSplit::standard($rates['teknisi'], $rates['kenek'], $configKey, $isSecond, $isThird, $lebaran),
        };
    }

    /**
     * Returns the marketing install-type key for a customer (e.g. 'dsp_panoramic').
     * Always based on the customer's own install_type and panoramic flag — never
     * affected by the installer's same-day car-count upgrade.
     *
     * Requires: $customer->customerProducts relation loaded with 'part'.
     */
    public function resolveMarketingBase(Customer $customer): string
    {
        return $this->resolveBaseType($customer);
    }

    /**
     * Calculates the marketing commission amount for a customer.
     * Returns 0 if the customer has no marketing or no applicable rate.
     */
    public function resolveMarketingAmount(Customer $customer): int
    {
        if (! $customer->marketing_id) {
            return 0;
        }

        $categoryId  = $this->resolveMarketingCategoryId($customer);
        $installType = $this->resolveBaseType($customer); // handles panoramic product detection

        $amount = $this->config['marketing'][$categoryId][$installType]
            ?? $this->config['marketing']['default'][$installType]
            ?? 0;

        return (int) $amount;
    }

    // ── Private resolution ────────────────────────────────────────

    /**
     * Determines the base install type from the customer's products.
     * Upgrades to 'full_panoramic' if any part contains "pano" and the base type
     * is a fullset variant.
     *
     * Requires: $customer->customerProducts relation loaded with 'part'.
     */
    private function resolveBaseType(Customer $customer): string
    {
        // Sole signal: the explicit has_panoramic flag on the customer record.
        // Do NOT fall back to part-name detection — a car with a regular sunroof
        // also has a SUNROOF/PANORAMIC part row but must NOT trigger panoramic commission.
        $hasPanorama = (bool) $customer->has_panoramic;

        $installType = (string) $customer->install_type;

        if ($hasPanorama) {
            if (str_starts_with($installType, 'fullset_ekstra')) {
                return 'ekstra_panoramic';
            }
            if (str_starts_with($installType, 'fullset')) {
                return 'full_panoramic';
            }
            // Kaca depan + panoramic = 2× D/S/P
            if ($installType === 'dsp') {
                return 'dsp_panoramic';
            }
        }

        return $installType;
    }

    /**
     * Detects which split class applies for the given pair:
     *   - 'solo'       → no kenek assigned
     *   - 'pair_50_50' → tukang and kenek share the same job_position (e.g. teknisi+teknisi)
     *   - 'standard'   → different positions (normal teknisi+kenek pair)
     *
     * Requires: $customer->tukang and $customer->kenek relations loaded.
     */
    private function detectSplitClass(Customer $customer): string
    {
        if (! $customer->kenek_id) {
            return 'solo';
        }

        $tukangPos = strtolower((string) $customer->tukang?->job_position);
        $kenekPos  = strtolower((string) $customer->kenek?->job_position);

        if ($tukangPos && $kenekPos && $tukangPos === $kenekPos) {
            return 'pair_50_50';
        }

        return 'standard';
    }

    /**
     * Resolves the config key with the appropriate suffix:
     *   base → base_50 (same pair) or base_solo → optionally + _lebaran
     */
    private function resolveConfigKey(string $baseType, string $splitClass, bool $lebaran): string
    {
        $key = match ($splitClass) {
            'solo'       => "{$baseType}_solo",
            'pair_50_50' => "{$baseType}_50",
            default      => $baseType,
        };

        if ($lebaran) {
            $lebaranKey = "{$key}_lebaran";
            if (isset($this->config['fixed'][$lebaranKey])) {
                return $lebaranKey;
            }
        }

        return $key;
    }

    /**
     * Retrieves commission rates from config, throwing if the key is missing.
     * A missing key is a programmer error (misconfigured commission.php), not a runtime condition.
     */
    private function ratesForKey(string $configKey): array
    {
        $rates = $this->config['fixed'][$configKey] ?? null;

        if ($rates === null) {
            throw new RuntimeException(
                "CommissionCalculator: missing config key [{$configKey}] in config/commission.php. "
                . "Available keys: " . implode(', ', array_keys($this->config['fixed'] ?? []))
            );
        }

        return $rates;
    }

    /**
     * Counts how many other fullset customers this tukang has on the same
     * calendar day as the current customer (based on customers.created_at).
     *
     * Using the customers table (not commissions) is intentional: commission
     * release order is arbitrary and unreliable for nth-car detection. The
     * installation date is always customers.created_at.
     *
     * Returns 0 for car 1, 1 for car 2, 2+ for car 3+.
     */
    private function carCountForInstaller(Invoice $invoice, ?int $tukangId): int
    {
        if (! $tukangId) {
            return 0;
        }

        $customer = $invoice->customer;
        if (! $customer) {
            return 0;
        }

        $refDate = ($customer->install_date
            ? Carbon::parse($customer->install_date)
            : Carbon::parse($customer->created_at)
        )->toDateString();

        // Count only customers with a LOWER id (entered before this one).
        // Using != caused both cars to see each other → both upgraded to "2nd car".
        // Using < establishes a stable entry-order: car 1 (lower id) sees 0 prior
        // cars and stays at base rate; car 2 (higher id) sees 1 and gets upgraded.
        return DB::table('customers')
            ->where('tukang_id', $tukangId)
            ->where('install_type', 'fullset')
            ->whereRaw('COALESCE(install_date, DATE(created_at)) = ?', [$refDate])
            ->where('id', '<', $customer->id)
            ->count();
    }

    /**
     * Resolves which marketing commission category to use.
     * Checks priority list first (config['marketing_category_priority']),
     * falls back to 'default'.
     *
     * Requires: $customer->customerProducts relation loaded.
     */
    private function resolveMarketingCategoryId(Customer $customer): int|string
    {
        $priorities = $this->config['marketing_category_priority'] ?? [3, 2, 1];

        foreach ($priorities as $catId) {
            $hasCategory = $customer->customerProducts->contains(
                fn ($cp) => $cp->category_product_id === $catId
            );

            if ($hasCategory) {
                return $catId;
            }
        }

        return 'default';
    }
}
