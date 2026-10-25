<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentAnnualPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DevelopmentAnnualPlanController extends Controller
{
    public function index()
    {
        $plans = DevelopmentAnnualPlan::with(['author', 'initiatives'])->orderByDesc('year')->paginate(20);

        return view('development-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('development-plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year'    => ['required', 'integer', 'min:2020', 'max:2100', Rule::unique('development_annual_plans', 'year')],
            'summary' => ['required', 'string'],
        ]);

        $plan = DevelopmentAnnualPlan::create($data + ['created_by' => $request->user()->id]);

        return redirect()->route('development-plans.show', $plan)->with('success', 'Rencana tahunan berhasil dibuat.');
    }

    public function show(DevelopmentAnnualPlan $developmentPlan)
    {
        $developmentPlan->load(['author', 'initiatives']);

        return view('development-plans.show', ['plan' => $developmentPlan]);
    }

    public function storeInitiative(Request $request, DevelopmentAnnualPlan $developmentPlan)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
        ]);

        $developmentPlan->initiatives()->create($data + ['status' => 'planned']);

        return back()->with('success', 'Inisiatif berhasil ditambahkan.');
    }

    public function updateInitiativeStatus(Request $request, DevelopmentAnnualPlan $developmentPlan, \App\Models\DevelopmentAnnualPlanInitiative $initiative)
    {
        abort_unless($initiative->plan_id === $developmentPlan->id, 404);

        $data = $request->validate([
            'status' => ['required', 'in:planned,in_progress,done,cancelled'],
        ]);

        $initiative->update($data);

        return back()->with('success', 'Status inisiatif diperbarui.');
    }
}
