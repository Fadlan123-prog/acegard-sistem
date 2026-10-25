@extends('dashboard.index')

@section('title', 'Kasbon Saya')
@section('route-breadcrumb', 'my-kasbon.index')
@section('breadcrumb', 'Kasbon Saya')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Pengajuan Kasbon</h5>
    <x-ui.button :href="route('my-kasbon.create')" variant="primary">
        <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Ajukan Kasbon
    </x-ui.button>
</div>

<x-ui.card>
    @if($kasbons->isEmpty())
        <x-ui.empty-state
            icon='<iconify-icon icon="mdi:cash-fast" style="font-size:26px;opacity:.5"></iconify-icon>'
            title="Belum ada pengajuan kasbon"
            description="Kasbon yang Anda ajukan, beserta progres cicilannya, akan muncul di sini." />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Nominal</th>
                        <th>Cicilan</th>
                        <th>Sumber Potongan</th>
                        <th>Sisa</th>
                        <th>Status</th>
                        <th>Diajukan</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kasbons as $k)
                        <tr>
                            <td class="tab-nums">Rp {{ number_format($k->amount, 0, ',', '.') }}</td>
                            <td class="small">{{ $k->installments_paid }}/{{ $k->installments }}x &middot; Rp {{ number_format($k->installment_amount, 0, ',', '.') }}/cicilan</td>
                            <td><x-ui.badge variant="neutral" size="sm">{{ \App\Models\Kasbon::$sourceLabels[$k->deduction_source] }}</x-ui.badge></td>
                            <td class="tab-nums">Rp {{ number_format($k->remaining_amount, 0, ',', '.') }}</td>
                            <td>
                                @php
                                    $statusVariant = match($k->status) {
                                        'pending_approval' => 'warning',
                                        'active'            => 'info',
                                        'completed'         => 'success',
                                        'rejected', 'cancelled' => 'danger',
                                        default             => 'secondary',
                                    };
                                @endphp
                                <x-ui.badge :variant="$statusVariant">{{ \App\Models\Kasbon::$statusLabels[$k->status] ?? $k->status }}</x-ui.badge>
                            </td>
                            <td class="small text-muted">{{ $k->created_at->translatedFormat('d M Y') }}</td>
                            <td><x-ui.button :href="route('my-kasbon.show', $k)" variant="outline" size="sm">Detail</x-ui.button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $kasbons->links() }}</div>
    @endif
</x-ui.card>

@endsection
