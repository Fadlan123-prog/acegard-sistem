@extends('dashboard.index')

@section('title', 'Pencairan Klaim Karyawan')
@section('route-breadcrumb', 'claims-disbursement.index')
@section('breadcrumb', 'Pencairan Klaim')

@section('content')

<x-ui.card>
    @if($claims->isEmpty())
        <x-ui.empty-state
            icon='<iconify-icon icon="mdi:cash-check" style="font-size:26px;opacity:.5"></iconify-icon>'
            title="Tidak ada klaim siap dicairkan"
            description="Klaim yang sudah disetujui Manager Operasional dan menunggu pencairan akan muncul di sini." />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Cabang</th>
                        <th>Jenis</th>
                        <th>Nominal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($claims as $claim)
                        <tr>
                            <td>{{ $claim->employee->name ?? '-' }}</td>
                            <td>{{ $claim->branch->name ?? '-' }}</td>
                            <td>{{ \App\Models\EmployeeClaim::$typeLabels[$claim->type] }}</td>
                            <td class="tab-nums">Rp {{ number_format($claim->amount, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('claims-disbursement.disburse', $claim) }}"
                                      onsubmit="return confirm('Cairkan klaim ini lewat Petty Cash?');">
                                    @csrf
                                    <x-ui.button type="submit" variant="success" size="sm">
                                        <iconify-icon icon="mdi:cash-fast" class="me-1"></iconify-icon>Cairkan
                                    </x-ui.button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $claims->links() }}</div>
    @endif
</x-ui.card>

@endsection
