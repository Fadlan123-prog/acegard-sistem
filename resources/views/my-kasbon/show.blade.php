@extends('dashboard.index')

@section('title', 'Detail Kasbon')
@section('route-breadcrumb', 'my-kasbon.show')
@section('breadcrumb', 'Detail Kasbon')

@section('content')

@php
    $statusVariant = match($kasbon->status) {
        'pending_approval' => 'warning',
        'active'            => 'info',
        'completed'         => 'success',
        'rejected', 'cancelled' => 'danger',
        default             => 'secondary',
    };
    $viewerEmployeeId = auth()->user()->employee?->id;
    $isOwnKasbon = $viewerEmployeeId && $kasbon->employee_id === $viewerEmployeeId;
@endphp

<div class="row g-3">
    <div class="col-lg-7">
        <x-ui.card title="Rincian Pengajuan">
            <dl class="row mb-0">
                @unless($isOwnKasbon)
                <dt class="col-5 text-muted">Karyawan</dt>
                <dd class="col-7">{{ $kasbon->employee?->name ?? '-' }}</dd>

                <dt class="col-5 text-muted">Cabang</dt>
                <dd class="col-7">{{ $kasbon->branch?->name ?? '-' }}</dd>
                @endunless

                <dt class="col-5 text-muted">Status</dt>
                <dd class="col-7"><x-ui.badge :variant="$statusVariant">{{ \App\Models\Kasbon::$statusLabels[$kasbon->status] ?? $kasbon->status }}</x-ui.badge></dd>

                <dt class="col-5 text-muted">Nominal Diajukan</dt>
                <dd class="col-7">Rp {{ number_format($kasbon->amount, 0, ',', '.') }}</dd>

                <dt class="col-5 text-muted">Cicilan</dt>
                <dd class="col-7">{{ $kasbon->installments_paid }} dari {{ $kasbon->installments }}x &middot; Rp {{ number_format($kasbon->installment_amount, 0, ',', '.') }}/cicilan</dd>

                <dt class="col-5 text-muted">Sumber Potongan</dt>
                <dd class="col-7">{{ \App\Models\Kasbon::$sourceLabels[$kasbon->deduction_source] }}</dd>

                <dt class="col-5 text-muted">Sisa Kasbon</dt>
                <dd class="col-7 fw-semibold">Rp {{ number_format($kasbon->remaining_amount, 0, ',', '.') }}</dd>

                <dt class="col-5 text-muted">Alasan</dt>
                <dd class="col-7">{{ $kasbon->reason }}</dd>

                <dt class="col-5 text-muted">Diajukan</dt>
                <dd class="col-7">{{ $kasbon->created_at->translatedFormat('d M Y H:i') }}</dd>
            </dl>
        </x-ui.card>
    </div>

    <div class="col-lg-5">
        <x-ui.card title="Riwayat Potongan">
            @if($kasbon->deductions->isEmpty())
                <x-ui.empty-state title="Belum ada potongan" description="Potongan akan muncul di sini setelah payroll atau pencairan komisi berikutnya diproses." />
            @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Sumber</th>
                                <th>Periode</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kasbon->deductions as $d)
                                <tr>
                                    <td>{{ $d->source === 'payroll' ? 'Gajian' : 'Komisi' }}</td>
                                    <td class="small text-muted">{{ $d->period ?? ('Batch #' . $d->commission_payment_batch_id) }}</td>
                                    <td class="text-end">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>
</div>

<div class="mt-3">
    <x-ui.button :href="$isOwnKasbon ? route('my-kasbon.index') : route('kasbons.index')" variant="secondary">Kembali</x-ui.button>
</div>

@endsection
