<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CommissionExport;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        // Ambil filter dari request
        $filters = [
            'employee_id' => $request->input('employee_id'),
            'from'        => $request->input('from'),
            'to'          => $request->input('to'),
            'sort'        => $request->input('sort', 'date_desc'),
            'per_page'    => (int) $request->input('per_page', 20),
        ];

        // Base query
        $base = DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->leftJoin('customers as cu', 'cu.id', '=', 'c.customer_id')
            ->leftJoin('invoices as i', 'i.id', '=', 'c.invoice_id')
            ->select([
                'c.id',
                'c.amount',
                'c.created_at',
                'c.customer_id',
                'c.invoice_id',
                'c.role',
                'e.name as employee_name',
                'e.job_position as employee_job',
                'cu.name as customer_name',
                'i.invoice_number',
                'i.invoice_date',
            ]);

        // Filter: employee
        if (!empty($filters['employee_id'])) {
            $base->where('e.id', (int) $filters['employee_id']);
        }

        // Filter: date range (pakai c.created_at)
        if (!empty($filters['from'])) {
            $base->whereDate('c.created_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $base->whereDate('c.created_at', '<=', $filters['to']);
        }

        // Sorting
        switch ($filters['sort']) {
            case 'date_asc':
                $base->orderBy('c.created_at', 'asc');
                break;
            case 'amount_desc':
                $base->orderBy('c.amount', 'desc')->orderBy('c.created_at', 'desc');
                break;
            case 'amount_asc':
                $base->orderBy('c.amount', 'asc')->orderBy('c.created_at', 'asc');
                break;
            default: // date_desc
                $base->orderBy('c.created_at', 'desc');
        }

        // Total sesuai filter (sebelum paginate)
        $totalFiltered = (clone $base)->sum('c.amount');

        // Paginate
        $commissions = $base->paginate($filters['per_page'])->withQueryString();

        // Total hanya halaman ini
        $pageTotal = $commissions->sum('amount');

        return view('commission.index', compact('commissions', 'pageTotal', 'totalFiltered', 'filters'));
    }

    public function exportByEmployee(Request $request)
    {
        $filters = [
            'employee_id' => $request->input('employee_id'),
            'from'        => $request->input('from'),
            'to'          => $request->input('to'),
            'sort'        => $request->input('sort', 'date_desc'),
        ];

        // Nama file
        $suffix = [];
        if (!empty($filters['employee_id'])) {
            $empName = DB::table('employees')->where('id', $filters['employee_id'])->value('name');
            $suffix[] = \Str::slug($empName ?? 'employee');
        } else {
            $suffix[] = 'all';
        }
        if (!empty($filters['from'])) $suffix[] = $filters['from'];
        if (!empty($filters['to']))   $suffix[] = $filters['to'];

        $fileName = 'commissions_'.implode('_', $suffix).'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new CommissionExport($filters), $fileName);
    }
}
