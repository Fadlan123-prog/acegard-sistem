<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employees_id' => Employee::factory(),
            'customer_id'  => Customer::factory(),
            'amount'       => 100000,
            'role'         => 'installer',
            'status'       => 'eligible',
            'is_active'    => true,
        ];
    }

    // branch_id is intentionally not in Commission::$fillable (see CommissionReleaseService —
    // the real creation paths never populate it either, a separate gap this test suite flags
    // but does not fix). Set it directly post-create, bypassing mass assignment, since tests
    // need a populated branch_id to exercise CommissionController's branch-scoping guard.
    public function withBranch(int $branchId): static
    {
        return $this->afterCreating(function (\App\Models\Commission $commission) use ($branchId) {
            $commission->branch_id = $branchId;
            $commission->save();
        });
    }
}
