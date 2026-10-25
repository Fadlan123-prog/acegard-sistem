<?php

namespace App\Http\Controllers;

use App\Models\DevelopmentReport;
use Illuminate\Http\Request;

class DevelopmentReportController extends Controller
{
    public function index()
    {
        $reports = DevelopmentReport::with('author')->latest('period_end')->paginate(20);

        return view('development-reports.index', compact('reports'));
    }

    public function create()
    {
        return view('development-reports.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'period_start' => ['required', 'date'],
            'period_end'   => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $report = DevelopmentReport::create($data + ['created_by' => $request->user()->id]);

        return redirect()->route('development-reports.show', $report)->with('success', 'Laporan berkala berhasil disimpan.');
    }

    public function show(DevelopmentReport $developmentReport)
    {
        return view('development-reports.show', ['report' => $developmentReport]);
    }
}
