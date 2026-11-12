<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;

class CustomerExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $user = auth()->user();

        // Non-superadmin/owner: hanya cabang yang boleh diakses user.
        return Customer::query()
            ->when(! $user || ! $user->hasAnyRole(['superadmin', 'owner']),
                fn ($q) => $q->whereIn('branch_id', $user ? $user->accessibleBranchIds() : []))
            ->get();
    }
}
