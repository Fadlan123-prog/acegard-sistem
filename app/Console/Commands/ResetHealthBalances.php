<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\EmployeeHealthBalance;
use Illuminate\Console\Command;

class ResetHealthBalances extends Command
{
    protected $signature = 'claims:reset-health-balances {--year=}';
    protected $description = 'Create the new year\'s employee_health_balances row for every active employee (does not touch previous years\' history)';

    public function handle(): int
    {
        $year = (int) ($this->option('year') ?: now()->year);
        $created = 0;

        Employee::where('is_active', true)->each(function (Employee $employee) use ($year, &$created) {
            $previous = EmployeeHealthBalance::where('employee_id', $employee->id)
                ->orderByDesc('year')
                ->first();

            $allocation = $previous?->allocated_amount ?? config('hr.default_health_allocation');

            $balance = EmployeeHealthBalance::firstOrCreate(
                ['employee_id' => $employee->id, 'year' => $year],
                ['allocated_amount' => $allocation, 'used_amount' => 0]
            );

            if ($balance->wasRecentlyCreated) {
                $created++;
            }
        });

        $this->info("Saldo kesehatan tahun {$year} dibuat untuk {$created} karyawan aktif.");

        return self::SUCCESS;
    }
}
