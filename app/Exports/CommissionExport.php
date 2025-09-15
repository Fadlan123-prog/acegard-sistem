<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CommissionExport implements FromCollection, WithHeadings
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $q = DB::table('commissions as c')
            ->leftJoin('employees as e', 'e.id', '=', 'c.employees_id')
            ->leftJoin('customers as cu', 'cu.id', '=', 'c.customer_id')
            ->leftJoin('invoices as i', 'i.id', '=', 'c.invoice_id')
            ->select([
                'c.created_at',
                'e.name as employee_name',
                'e.job_position as employee_job',
                'cu.name as customer_name',
                'i.invoice_number',
                'i.invoice_date',
                'c.role',
                'c.amount',
            ]);

        if (!empty($this->filters['employee_id'])) {
            $q->where('e.id', (int)$this->filters['employee_id']);
        }
        if (!empty($this->filters['from'])) {
            $q->whereDate('c.created_at', '>=', $this->filters['from']);
        }
        if (!empty($this->filters['to'])) {
            $q->whereDate('c.created_at', '<=', $this->filters['to']);
        }

        // Sort agar konsisten dengan index
        switch ($this->filters['sort'] ?? 'date_desc') {
            case 'date_asc':
                $q->orderBy('c.created_at', 'asc');
                break;
            case 'amount_desc':
                $q->orderBy('c.amount', 'desc')->orderBy('c.created_at', 'desc');
                break;
            case 'amount_asc':
                $q->orderBy('c.amount', 'asc')->orderBy('c.created_at', 'asc');
                break;
            default:
                $q->orderBy('c.created_at', 'desc');
        }

        return $q->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Employee',
            'Posisi',
            'Customer',
            'No Invoice',
            'Tgl Invoice',
            'Role',
            'Amount',
        ];
    }
}
