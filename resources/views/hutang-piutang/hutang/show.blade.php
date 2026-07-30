@extends('layout.index')

@section('title', 'Detail Hutang')
@section('route-breadcrumb', route('hutang.index'))
@section('breadcrumb', 'Detail Hutang')

@section('content')

@php
    $fmtRp   = fn($v) => 'Rp '.number_format($v, 0, ',', '.');
    $remaining = $payable->remaining();
    $progress  = $payable->amount > 0 ? round($payable->paid_amount / $payable->amount * 100) : 0;
    $badgeVariant = match($payable->status) {
        'paid'    => 'success',
        'partial' => 'warning',
        default   => 'danger',
    };
    $badgeLabel = match($payable->status) {
        'paid'    => 'Lunas',
        'partial' => 'Sebagian',
        default   => 'Belum Bayar',
    };
@endphp

<div class="row g-4">

    {{-- Detail Card --}}
    <div class="col-12 col-lg-5">
        <x-ui.card class="h-100">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="ui-card-title">{{ $payable->vendor_name }}</div>
                    <x-ui.badge :variant="$badgeVariant" size="sm">{{ $badgeLabel }}</x-ui.badge>
                </div>
            @endslot

            @if ($payable->reference_number)
            <div class="mb-3">
                <div class="text-muted small mb-1">No. Referensi</div>
                <div class="fw-medium">{{ $payable->reference_number }}</div>
            </div>
            @endif
            <div class="mb-3">
                <div class="text-muted small mb-1">Deskripsi</div>
                <div>{{ $payable->description }}</div>
            </div>
            @if ($payable->supplier)
            <div class="mb-3">
                <div class="text-muted small mb-1">Supplier</div>
                <div>{{ $payable->supplier->name }}</div>
            </div>
            @endif
            <div class="mb-3">
                <div class="text-muted small mb-1">Jatuh Tempo</div>
                <div class="{{ $payable->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                    {{ $payable->due_date ? $payable->due_date->format('d M Y') : '—' }}
                    @if ($payable->isOverdue()) <x-ui.badge variant="danger" class="ms-1">Terlambat</x-ui.badge> @endif
                </div>
            </div>
            <div class="mb-3">
                <div class="text-muted small mb-1">Cabang</div>
                <div>{{ $payable->branch?->name }}</div>
            </div>
            @if ($payable->notes)
            <div class="mb-3">
                <div class="text-muted small mb-1">Catatan</div>
                <div class="text-muted">{{ $payable->notes }}</div>
            </div>
            @endif

            {{-- Progress --}}
            <div class="mt-4 pt-3" style="border-top:1px solid #f1f5f9">
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Progress Pembayaran</span>
                    <span class="fw-semibold">{{ $progress }}%</span>
                </div>
                <div class="progress mb-3" style="height:8px;border-radius:4px">
                    <div class="progress-bar" style="width:{{ $progress }}%;background:#10b981;border-radius:4px"></div>
                </div>
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted" style="font-size:11px">Total</div>
                        <div class="fw-bold" style="color:#6366f1">{{ $fmtRp($payable->amount) }}</div>
                    </div>
                    <div class="text-center">
                        <div class="text-muted" style="font-size:11px">Dibayar</div>
                        <div class="fw-bold" style="color:#10b981">{{ $fmtRp($payable->paid_amount) }}</div>
                    </div>
                    <div class="text-end">
                        <div class="text-muted" style="font-size:11px">Sisa</div>
                        <div class="fw-bold" style="color:#ef4444">{{ $fmtRp($remaining) }}</div>
                    </div>
                </div>
            </div>

            @slot('footer')
                <div class="d-flex gap-2">
                    <x-ui.button href="{{ route('hutang.edit', $payable->id) }}" variant="outline" size="sm">
                        <iconify-icon icon="mdi:pencil-outline" class="me-1"></iconify-icon>Edit
                    </x-ui.button>
                    <form method="POST" action="{{ route('hutang.destroy', $payable->id) }}">
                        @csrf @method('DELETE')
                        <x-ui.button variant="danger-outline" size="sm" type="submit"
                                onclick="return confirm('Hapus hutang ini?')">
                            <iconify-icon icon="mdi:trash-can-outline" class="me-1"></iconify-icon>Hapus
                        </x-ui.button>
                    </form>
                    <x-ui.button href="{{ route('hutang.index') }}" variant="outline" size="sm" class="ms-auto">Kembali</x-ui.button>
                </div>
            @endslot
        </x-ui.card>
    </div>

    {{-- Payments Column --}}
    <div class="col-12 col-lg-7">

        {{-- Add Payment Form --}}
        @if ($payable->status !== 'paid')
        <x-ui.card class="mb-4">
            @slot('header')
                <div class="ui-card-title"><iconify-icon icon="mdi:currency-usd-circle-outline" class="me-1 text-success"></iconify-icon>Catat Pembayaran</div>
            @endslot

            <form method="POST" action="{{ route('hutang.payment', $payable->id) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-6">
                        <x-ui.form-field label="Jumlah" name="amount" :required="true">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                                       min="1" max="{{ $remaining }}" required placeholder="{{ number_format($remaining) }}"
                                       value="{{ old('amount', $remaining) }}">
                            </div>
                        </x-ui.form-field>
                    </div>
                    <div class="col-6">
                        <x-ui.form-field label="Tanggal" name="payment_date" :required="true">
                            <input type="date" name="payment_date" class="form-control form-control-sm @error('payment_date') is-invalid @enderror"
                                   value="{{ old('payment_date', today()->format('Y-m-d')) }}" required>
                        </x-ui.form-field>
                    </div>
                    <div class="col-6">
                        <x-ui.form-field label="Metode" name="payment_method">
                            <select name="payment_method" class="form-select form-select-sm">
                                @foreach (\App\Models\DebtPayment::$methods as $k => $v)
                                    <option value="{{ $k }}" {{ old('payment_method') === $k ? 'selected' : '' }}>{{ $v }}</option>
                                @endforeach
                            </select>
                        </x-ui.form-field>
                    </div>
                    <div class="col-6">
                        <x-ui.form-field label="Catatan" name="notes">
                            <input type="text" name="notes" class="form-control form-control-sm"
                                   value="{{ old('notes') }}" placeholder="Opsional" maxlength="255">
                        </x-ui.form-field>
                    </div>
                </div>
                <x-ui.button variant="success" size="sm" type="submit" class="mt-3">
                    <iconify-icon icon="mdi:content-save-outline" class="me-1"></iconify-icon>Simpan Pembayaran
                </x-ui.button>
            </form>
        </x-ui.card>
        @endif

        {{-- Payment History --}}
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title">Riwayat Pembayaran</div>
            @endslot

            @if ($payable->payments->isEmpty())
                <div class="py-4">
                    <x-ui.empty-state title="Belum ada pembayaran" />
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size:13px">
                        <thead style="background:#fafafa">
                            <tr>
                                <th class="ps-4 py-2 text-muted fw-medium border-bottom">Tanggal</th>
                                <th class="py-2 text-muted fw-medium border-bottom">Metode</th>
                                <th class="py-2 text-end text-muted fw-medium border-bottom">Jumlah</th>
                                <th class="py-2 text-muted fw-medium border-bottom">Catatan</th>
                                <th class="py-2 pe-4 text-muted fw-medium border-bottom">Oleh</th>
                                <th class="py-2 pe-4 border-bottom"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payable->payments as $pay)
                            <tr>
                                <td class="ps-4 py-2">{{ $pay->payment_date->format('d/m/Y') }}</td>
                                <td class="py-2">{{ \App\Models\DebtPayment::$methods[$pay->payment_method] ?? $pay->payment_method }}</td>
                                <td class="py-2 text-end fw-semibold" style="color:#10b981">{{ $fmtRp($pay->amount) }}</td>
                                <td class="py-2 text-muted" style="font-size:12px">{{ $pay->notes ?? '—' }}</td>
                                <td class="py-2 text-muted" style="font-size:12px">{{ $pay->recorder?->name ?? '—' }}</td>
                                <td class="py-2 pe-4 text-end">
                                    <form method="POST" action="{{ route('debt-payments.destroy', $pay->id) }}">
                                        @csrf @method('DELETE')
                                        <x-ui.button variant="danger-outline" size="sm" type="submit"
                                                onclick="return confirm('Hapus pembayaran ini?')">
                                            <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                        </x-ui.button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>
    </div>
</div>

@endsection
