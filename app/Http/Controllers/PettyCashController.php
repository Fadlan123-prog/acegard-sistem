<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\PettyCashTransaction;
use App\Services\BranchContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PettyCashController extends Controller
{
    public function __construct(private BranchContext $branch) {}

    private function assertBranchAllowed(int $branchId): void
    {
        if (auth()->user()->hasRole('superadmin')) {
            return;
        }

        $allowed = auth()->user()
            ->branches()
            ->pluck('branches.id')
            ->map(fn ($id) => (int) $id)
            ->toArray();

        if (!in_array($branchId, $allowed, true)) {
            abort(403, 'Anda tidak memiliki akses ke cabang ini.');
        }
    }

    public function index(Request $request)
    {
        $user     = auth()->user();
        $branches = Branch::orderBy('name')->get();

        $branchId = $request->input('branch_id') ?? $this->branch->idOrNull();

        if ($branchId) {
            $this->assertBranchAllowed((int) $branchId);
        }

        if (!$branchId && $user->hasRole('superadmin')) {
            $branchId = $branches->first()?->id;
        }

        $month = $request->input('month', now()->format('Y-m'));

        $transactions = collect();
        $balance      = 0;
        $branch       = null;

        if ($branchId) {
            $branch = Branch::find($branchId);

            $transactions = PettyCashTransaction::with(['employee', 'creator'])
                ->forBranch((int) $branchId)
                ->when($month, fn ($q) => $q->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$month]))
                ->orderBy('date')
                ->orderBy('id')
                ->get();

            $balance = PettyCashTransaction::forBranch((int) $branchId)
                ->where('date', '<=', Carbon::parse($month . '-01')->endOfMonth()->toDateString())
                ->selectRaw('SUM(CASE WHEN type = "in" THEN amount ELSE -amount END) as balance')
                ->value('balance') ?? 0;
        }

        $totalIn  = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->whereIn('type', ['out', 'purchase'])->sum('amount');

        return view('petty-cash.index', compact(
            'transactions', 'branches', 'branchId', 'month',
            'balance', 'totalIn', 'totalOut', 'branch'
        ));
    }

    public function createIn()
    {
        $branches = Branch::orderBy('name')->get();
        $branchId = $this->branch->idOrNull();

        return view('petty-cash.create-in', compact('branches', 'branchId'));
    }

    public function storeIn(Request $request)
    {
        $data = $request->validate([
            'branch_id'   => ['required', 'exists:branches,id'],
            'date'        => ['required', 'date'],
            'amount'      => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->assertBranchAllowed((int) $data['branch_id']);

        PettyCashTransaction::create([
            'branch_id'   => $data['branch_id'],
            'type'        => 'in',
            'date'        => $data['date'],
            'amount'      => $data['amount'],
            'description' => $data['description'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        return redirect()
            ->route('petty-cash.index', ['branch_id' => $data['branch_id'], 'month' => Carbon::parse($data['date'])->format('Y-m')])
            ->with('success', 'Saldo petty cash berhasil ditambahkan.');
    }

    public function createOut()
    {
        $branches  = Branch::orderBy('name')->get();
        $branchId  = $this->branch->idOrNull();
        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        return view('petty-cash.create-out', compact('branches', 'branchId', 'employees'));
    }

    public function storeOut(Request $request)
    {
        $data = $request->validate([
            'branch_id'   => ['required', 'exists:branches,id'],
            'date'        => ['required', 'date'],
            'employee_id' => ['required', 'exists:employees,id'],
            'motor_name'  => ['required', 'string', 'max:100'],
            'destination' => ['required', 'string', 'max:255'],
            'distance_km' => ['required', 'numeric', 'min:0.1'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->assertBranchAllowed((int) $data['branch_id']);

        $fuelCost = PettyCashTransaction::calcFuelCost((float) $data['distance_km']);

        PettyCashTransaction::create([
            'branch_id'   => $data['branch_id'],
            'type'        => 'out',
            'date'        => $data['date'],
            'employee_id' => $data['employee_id'],
            'motor_name'  => $data['motor_name'],
            'destination' => $data['destination'],
            'distance_km' => $data['distance_km'],
            'fuel_cost'   => $fuelCost,
            'amount'      => $fuelCost,
            'description' => $data['description'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        return redirect()
            ->route('petty-cash.index', ['branch_id' => $data['branch_id'], 'month' => Carbon::parse($data['date'])->format('Y-m')])
            ->with('success', 'Pemakaian motor berhasil dicatat.');
    }

    public function storeBulkOut(Request $request)
    {
        $data = $request->validate([
            'branch_id'                => ['required', 'exists:branches,id'],
            'rows'                     => ['required', 'array', 'min:1'],
            'rows.*.date'              => ['required', 'date'],
            'rows.*.employee_id'       => ['required', 'exists:employees,id'],
            'rows.*.motor_name'        => ['required', 'string', 'max:100'],
            'rows.*.destination'       => ['required', 'string', 'max:255'],
            'rows.*.distance_km'       => ['required', 'numeric', 'min:0.1'],
            'rows.*.description'       => ['nullable', 'string', 'max:500'],
        ]);

        $branchId    = $data['branch_id'];
        $redirectMonth = null;

        $this->assertBranchAllowed((int) $branchId);

        DB::transaction(function () use ($data, $branchId, &$redirectMonth) {
            foreach ($data['rows'] as $row) {
                $fuelCost = PettyCashTransaction::calcFuelCost((float) $row['distance_km']);

                PettyCashTransaction::create([
                    'branch_id'   => $branchId,
                    'type'        => 'out',
                    'date'        => $row['date'],
                    'employee_id' => $row['employee_id'],
                    'motor_name'  => $row['motor_name'],
                    'destination' => $row['destination'],
                    'distance_km' => $row['distance_km'],
                    'fuel_cost'   => $fuelCost,
                    'amount'      => $fuelCost,
                    'description' => $row['description'] ?? null,
                    'created_by'  => auth()->id(),
                ]);

                $redirectMonth = Carbon::parse($row['date'])->format('Y-m');
            }
        });

        return redirect()
            ->route('petty-cash.index', ['branch_id' => $branchId, 'month' => $redirectMonth ?? now()->format('Y-m')])
            ->with('success', count($data['rows']) . ' pemakaian motor berhasil dicatat.');
    }

    public function createPurchase()
    {
        $branches  = Branch::orderBy('name')->get();
        $branchId  = $this->branch->idOrNull();
        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        return view('petty-cash.create-purchase', compact('branches', 'branchId', 'employees'));
    }

    public function storePurchase(Request $request)
    {
        $data = $request->validate([
            'branch_id'   => ['required', 'exists:branches,id'],
            'date'        => ['required', 'date'],
            'employee_id' => ['required', 'exists:employees,id'],
            'item_name'   => ['required', 'string', 'max:255'],
            'amount'      => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->assertBranchAllowed((int) $data['branch_id']);

        PettyCashTransaction::create([
            'branch_id'   => $data['branch_id'],
            'type'        => 'purchase',
            'date'        => $data['date'],
            'employee_id' => $data['employee_id'],
            'item_name'   => $data['item_name'],
            'amount'      => $data['amount'],
            'description' => $data['description'] ?? null,
            'created_by'  => auth()->id(),
        ]);

        return redirect()
            ->route('petty-cash.index', ['branch_id' => $data['branch_id'], 'month' => Carbon::parse($data['date'])->format('Y-m')])
            ->with('success', 'Klaim pembelian berhasil dicatat.');
    }

    public function destroy(PettyCashTransaction $pettyCash)
    {
        $this->assertBranchAllowed((int) $pettyCash->branch_id);

        $branchId = $pettyCash->branch_id;
        $month    = $pettyCash->date->format('Y-m');

        $pettyCash->delete();

        return redirect()
            ->route('petty-cash.index', ['branch_id' => $branchId, 'month' => $month])
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    public function downloadPdf(Request $request)
    {
        $branchId = $request->input('branch_id');
        $month    = $request->input('month', now()->format('Y-m'));

        abort_if(!$branchId, 400, 'Branch wajib dipilih.');
        $this->assertBranchAllowed((int) $branchId);

        $branch = Branch::findOrFail($branchId);

        $transactions = PettyCashTransaction::with(['employee'])
            ->forBranch((int) $branchId)
            ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$month])
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        // Saldo awal = saldo sebelum bulan ini
        $balanceBefore = PettyCashTransaction::forBranch((int) $branchId)
            ->where('date', '<', Carbon::parse($month . '-01')->startOfMonth()->toDateString())
            ->selectRaw('SUM(CASE WHEN type = "in" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;

        $totalIn  = $transactions->where('type', 'in')->sum('amount');
        $totalOut = $transactions->whereIn('type', ['out', 'purchase'])->sum('amount');
        $balanceEnd = $balanceBefore + $totalIn - $totalOut;

        $period = Carbon::parse($month . '-01')->isoFormat('MMMM Y');

        $pdf = Pdf::loadView('petty-cash.report-pdf', compact(
            'transactions', 'branch', 'month', 'period',
            'totalIn', 'totalOut', 'balanceBefore', 'balanceEnd'
        ))->setPaper('a4', 'portrait');

        $filename = 'petty-cash-' . str($branch->name)->slug() . '-' . $period . '.pdf';

        return $pdf->stream($filename);
    }

    public function calcFuel(Request $request)
    {
        $km   = (float) $request->input('km', 0);
        $cost = PettyCashTransaction::calcFuelCost($km);

        return response()->json(['fuel_cost' => $cost]);
    }
}
