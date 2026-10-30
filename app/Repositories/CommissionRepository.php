<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommissionRepository
{
    /** Paginated commission list with employee/customer/invoice joins and all filters applied. */
    public function paginated(array $filters, ?int $branchId): LengthAwarePaginator
    {
        return $this->buildListQuery($filters, $branchId)
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();
    }

    /** Source-type label map for display purposes. */
    public static function sourceTypes(): array
    {
        return [
            'mobil'  => ['label' => 'Kaca Film Mobil', 'icon' => 'mdi:car-outline',               'color' => '#1d4ed8'],
            'ppf'    => ['label' => 'PPF',              'icon' => 'mdi:shield-check-outline',      'color' => '#0f766e'],
            'gedung' => ['label' => 'Gedung',           'icon' => 'mdi:office-building-outline',   'color' => '#7c3aed'],
        ];
    }

    /** Sum of commission amounts matching the current filters. */
    public function totalAmount(array $filters, ?int $branchId): int
    {
        return (int) $this->buildListQuery($filters, $branchId)->sum('c.amount');
    }

    /**
     * Role breakdown totals.
     * Role filter is intentionally excluded so the summary shows all roles
     * even when the list is filtered to a single role.
     */
    public function roleSummary(array $filters, ?int $branchId): Collection
    {
        $q = DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->leftJoin('customers as cu', 'cu.id', '=', 'c.customer_id');

        $this->applyBranchFilter($q, $branchId);
        $this->applySourceTypeFilter($q, $filters['source_type'] ?? null);

        if (!empty($filters['employee_id'])) {
            $q->where('e.id', (int) $filters['employee_id']);
        }
        if (!empty($filters['from'])) {
            $q->whereDate('c.created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $q->whereDate('c.created_at', '<=', $filters['to']);
        }

        return $q->selectRaw('c.role, COUNT(*) as cnt, SUM(c.amount) as total')
            ->groupBy('c.role')
            ->get()
            ->keyBy('role');
    }

    /**
     * Top 3 employees for the given set of roles, ordered by total commission.
     * Used for leaderboard widgets on the commission index page.
     */
    public function topByRoles(
        array   $roles,
        ?int    $branchId,
        ?string $from = null,
        ?string $to   = null
    ): Collection {
        return DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->leftJoin('customers as cu', 'cu.id', '=', 'c.customer_id')
            ->whereIn('c.role', $roles)
            ->where('c.is_active', true)
            ->when($branchId, fn($q) => $q->where(function ($w) use ($branchId) {
                $w->where('cu.branch_id', $branchId)->orWhere('c.branch_id', $branchId);
            }))
            ->when($from, fn($q) => $q->whereDate('c.created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('c.created_at', '<=', $to))
            ->selectRaw('e.name as employee_name, e.job_position, COUNT(*) as cnt, SUM(c.amount) as total')
            ->groupBy('e.id', 'e.name', 'e.job_position')
            ->orderByDesc('total')
            ->limit(3)
            ->get();
    }

    /** Active employee list for the branch filter dropdown. */
    public function employeeList(?int $branchId): Collection
    {
        return DB::table('employees')
            ->when($branchId, fn($q) => $q->where(function ($q2) use ($branchId) {
                $q2->where('branch_id', $branchId)->orWhereNull('branch_id');
            }))
            ->orderBy('name')
            ->get(['id', 'name', 'job_position']);
    }

    /** Commission rows for a single employee over a date range — used by the print slip. */
    public function forEmployee(int $employeeId, Carbon $from, Carbon $to): Collection
    {
        return DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->leftJoin('customers as cu', 'cu.id', '=', 'c.customer_id')
            ->leftJoin('invoices as i', 'i.id', '=', 'c.invoice_id')
            ->where('c.employees_id', $employeeId)
            ->whereBetween('c.created_at', [$from, $to])
            ->select([
                'c.id', 'c.amount', 'c.role', 'c.created_at',
                'e.name as employee_name', 'e.job_position as employee_job',
                'cu.name as customer_name', 'cu.vehicle_brand',
                'cu.vehicle_model', 'cu.install_type',
                'i.invoice_number', 'i.invoice_date',
            ])
            ->orderBy('c.created_at', 'asc')
            ->get();
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function buildListQuery(array $filters, ?int $branchId)
    {
        $q = DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->leftJoin('customers as cu', 'cu.id', '=', 'c.customer_id')
            ->leftJoin('invoices as i', 'i.id', '=', 'c.invoice_id')
            ->leftJoin('ppf_invoices as pi', 'pi.id', '=', 'c.ppf_invoice_id')
            ->select([
                'c.id', 'c.amount', 'c.created_at',
                'c.customer_id', 'c.invoice_id', 'c.ppf_invoice_id', 'c.role', 'c.is_active',
                'e.name as employee_name', 'e.job_position as employee_job',
                'cu.name as customer_name',
                'i.invoice_number', 'i.invoice_date',
                'pi.invoice_number as ppf_invoice_number', 'pi.invoice_date as ppf_invoice_date',
            ]);

        $this->applyBranchFilter($q, $branchId);
        $this->applySourceTypeFilter($q, $filters['source_type'] ?? null);

        if (!empty($filters['employee_id'])) {
            $q->where('e.id', (int) $filters['employee_id']);
        }
        if (!empty($filters['role'])) {
            $q->where('c.role', $filters['role']);
        }
        if (!empty($filters['from'])) {
            $q->whereDate('c.created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $q->whereDate('c.created_at', '<=', $filters['to']);
        }

        switch ($filters['sort'] ?? 'date_desc') {
            case 'date_asc':    $q->orderBy('c.created_at', 'asc'); break;
            case 'amount_desc': $q->orderBy('c.amount', 'desc')->orderBy('c.created_at', 'desc'); break;
            case 'amount_asc':  $q->orderBy('c.amount', 'asc')->orderBy('c.created_at', 'asc'); break;
            default:            $q->orderBy('c.created_at', 'desc');
        }

        return $q;
    }

    private function applyBranchFilter($q, ?int $branchId): void
    {
        if ($branchId) {
            $q->where(function ($w) use ($branchId) {
                $w->where('cu.branch_id', $branchId)
                  ->orWhere('c.branch_id', $branchId);
            });
        }
    }

    private function applySourceTypeFilter($q, ?string $sourceType): void
    {
        match ($sourceType) {
            // customer_id is always set for car commissions (old and new alike).
            // Old releases pre-date invoice linkage so invoice_id may be NULL, but
            // customer_id is reliable — PPF commissions always have customer_id = NULL.
            'mobil'  => $q->whereNotNull('c.customer_id')->whereNull('c.ppf_invoice_id'),
            'ppf'    => $q->whereNotNull('c.ppf_invoice_id'),
            'gedung' => $q->whereNull('c.customer_id')->whereNull('c.ppf_invoice_id'),
            default  => null,
        };
    }
}
