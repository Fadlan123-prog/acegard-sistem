<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\KpiScore;
use App\Models\KpiTarget;
use Illuminate\Http\Request;

/**
 * Generic KPI target/score engine shared by every division defined in
 * "Kartu KPI per Divisi — Acegard" (CS, Konten, Teknisi, Admin, Warehouse,
 * Purchasing, Keuangan, IT, Development, Strategi, Advertising).
 *
 * Targets/weights are company-wide policy (set once by superadmin/owner/
 * manager), not per-branch data — so unlike other controllers in this app,
 * this one does not filter by BranchContext. Scores are always tied to one
 * employee, so branch is implied by the employee's own branch.
 */
class KpiScorecardController extends Controller
{
    public function targets(Request $request)
    {
        $month = min(12, max(1, (int) $request->query('month', now()->month)));
        $year  = min(2100, max(2026, (int) $request->query('year', now()->year)));

        $targets = KpiTarget::where('period_month', $month)
            ->where('period_year', $year)
            ->orderBy('division')
            ->orderBy('metric_key')
            ->get()
            ->groupBy('division');

        return view('kpi.targets.index', compact('targets', 'month', 'year'));
    }

    public function createTarget(Request $request)
    {
        $month = min(12, max(1, (int) $request->query('month', now()->month)));
        $year  = min(2100, max(2026, (int) $request->query('year', now()->year)));

        return view('kpi.targets.create', compact('month', 'year'));
    }

    public function storeTarget(Request $request)
    {
        $validated = $request->validate([
            'division'      => 'required|in:' . implode(',', array_keys(KpiTarget::DIVISIONS)),
            'metric_key'    => 'required|string|max:100|alpha_dash',
            'source'        => ['required', function ($attr, $value, $fail) {
                if ($value !== 'manual' && ! \App\Services\KpiMetrics::has($value)) {
                    $fail('Sumber data tidak dikenal.');
                }
            }],
            'metric_label'  => 'required|string|max:255',
            'target_value'  => 'required|numeric|min:0',
            'weight'        => 'required|numeric|min:0|max:100',
            'direction'     => 'required|in:higher_better,lower_better',
            'period_month'  => 'required|integer|min:1|max:12',
            'period_year'   => 'required|integer|min:2026|max:2100',
        ]);

        KpiTarget::updateOrCreate(
            [
                'division'     => $validated['division'],
                'metric_key'   => $validated['metric_key'],
                'period_month' => $validated['period_month'],
                'period_year'  => $validated['period_year'],
            ],
            [
                ...$validated,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('kpi.targets.index', ['month' => $validated['period_month'], 'year' => $validated['period_year']])
            ->with('success', 'Target KPI disimpan.');
    }

    public function destroyTarget(KpiTarget $target)
    {
        $target->delete();

        return back()->with('success', 'Target KPI dihapus.');
    }

    public function scores(Request $request)
    {
        $month      = (int) $request->query('month', now()->month);
        $year       = (int) $request->query('year', now()->year);
        $division   = $request->query('division');

        $targets = KpiTarget::where('period_month', $month)
            ->where('period_year', $year)
            ->when($division, fn ($q) => $q->where('division', $division))
            ->orderBy('metric_key')
            ->get();

        $employees = Employee::where('is_active', true)->orderBy('name')->get();

        $scores = KpiScore::with(['target', 'employee'])
            ->whereIn('kpi_target_id', $targets->pluck('id'))
            ->get()
            ->groupBy('employee_id');

        return view('kpi.scores.index', compact('targets', 'employees', 'scores', 'month', 'year', 'division'));
    }

    public function storeScore(Request $request)
    {
        $validated = $request->validate([
            'employee_id'   => 'required|exists:employees,id',
            'kpi_target_id' => 'required|exists:kpi_targets,id',
            'actual_value'  => 'required|numeric|min:0',
        ]);

        abort_if(
            KpiTarget::findOrFail($validated['kpi_target_id'])->isAuto(),
            422,
            'Skor KPI otomatis tidak bisa diisi manual.'
        );

        KpiScore::updateOrCreate(
            [
                'employee_id'   => $validated['employee_id'],
                'kpi_target_id' => $validated['kpi_target_id'],
            ],
            [
                'actual_value' => $validated['actual_value'],
                'is_auto'      => false,
                'created_by'   => auth()->id(),
            ]
        );

        return back()->with('success', 'Skor KPI disimpan.');
    }

    public function myScorecard(Request $request)
    {
        $user = auth()->user();

        if (! $user->employee_id) {
            abort(404, 'Akun kamu tidak terhubung ke data karyawan.');
        }

        $month = (int) $request->query('month', now()->month);
        $year  = (int) $request->query('year', now()->year);

        $scores = KpiScore::with('target')
            ->where('employee_id', $user->employee_id)
            ->whereHas('target', fn ($q) => $q->where('period_month', $month)->where('period_year', $year))
            ->get();

        $totalScore = $scores->sum(fn (KpiScore $s) => $s->weightedScore());

        return view('kpi.my-scorecard', compact('scores', 'totalScore', 'month', 'year'));
    }
}
