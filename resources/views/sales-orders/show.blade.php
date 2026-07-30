@extends('dashboard.index')

@section('title', 'Detail Sales Order')
@section('route-breadcrumb', 'sales-orders.index')
@section('breadcrumb', 'Detail Sales Order')

@section('content')

@if(session('success'))
<x-ui.alert type="success" class="mb-3">
    {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</x-ui.alert>
@endif

@php
    $variantMap = [
        'pending'     => 'neutral',
        'confirmed'   => 'info',
        'in_progress' => 'warning',
        'completed'   => 'success',
        'cancelled'   => 'danger',
    ];

    // Status step indicators
    $steps  = ['pending', 'confirmed', 'in_progress', 'completed'];
    $stepIdx = array_search($salesOrder->status, $steps);
@endphp

<div class="row g-3">

    {{-- LEFT: main content --}}
    <div class="col-lg-8">

        {{-- Hero card --}}
        <x-ui.card class="mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="text-muted small mb-1">No. Sales Order</div>
                    <h4 class="font-monospace mb-2">{{ $salesOrder->so_number }}</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <x-ui.badge variant="{{ $variantMap[$salesOrder->status] ?? 'neutral' }}" :dot="true">
                            {{ $salesOrder->status_label }}
                        </x-ui.badge>
                        <x-ui.badge variant="info">{{ $salesOrder->category_label }}</x-ui.badge>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($salesOrder->isEditable())
                    <x-ui.button variant="outline" size="sm" href="{{ route('sales-orders.edit', $salesOrder) }}">
                        <iconify-icon icon="mdi:pencil-outline" class="me-1"></iconify-icon> Edit
                    </x-ui.button>
                    @endif
                    @if($salesOrder->hasSpk())
                    <x-ui.button variant="ghost" size="sm" href="{{ route('sales-orders.spk', $salesOrder) }}" target="_blank">
                        <iconify-icon icon="mdi:file-document-outline" class="me-1"></iconify-icon> Print SPK
                    </x-ui.button>
                    @endif
                    @if(!$salesOrder->isCompleted() && !$salesOrder->isCancelled())
                    <form action="{{ route('sales-orders.destroy', $salesOrder) }}" method="POST"
                          onsubmit="return confirm('Hapus Sales Order ini?')">
                        @csrf @method('DELETE')
                        <x-ui.button variant="danger-outline" size="sm" type="submit">Hapus</x-ui.button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Progress steps (hide if cancelled) --}}
            @if(!$salesOrder->isCancelled())
            @push('page-styles')
            <style>
            .so-steps { display:flex; align-items:center; gap:0; margin-top:20px; }
            .so-step  { display:flex; flex-direction:column; align-items:center; flex:1; position:relative; }
            .so-step:not(:last-child)::after { content:''; position:absolute; top:14px; left:50%; width:100%; height:2px; background:var(--border-color); z-index:0; }
            .so-step.done::after { background:var(--success-main); }
            .so-step-dot { width:28px; height:28px; border-radius:50%; border:2px solid var(--border-color); background:var(--white); display:flex; align-items:center; justify-content:center; z-index:1; font-size:12px; transition:all .2s; }
            .so-step.done .so-step-dot { background:var(--success-main); border-color:var(--success-main); color:#fff; }
            .so-step.active .so-step-dot { background:var(--primary-600); border-color:var(--primary-600); color:#fff; }
            .so-step-label { font-size:11px; margin-top:4px; color:var(--neutral-400); }
            .so-step.done .so-step-label,.so-step.active .so-step-label { color:var(--neutral-700); font-weight:600; }
            [data-theme="dark"] .so-step-dot { background:var(--dark-2); }
            [data-theme="dark"] .so-step.done .so-step-label,[data-theme="dark"] .so-step.active .so-step-label { color:var(--neutral-200); }
            </style>
            @endpush
            <div class="so-steps mt-3">
                @foreach($steps as $i => $stepKey)
                @php
                    $isDone   = $stepIdx !== false && $i < $stepIdx;
                    $isActive = $stepIdx !== false && $i === $stepIdx;
                @endphp
                <div class="so-step {{ $isDone ? 'done' : ($isActive ? 'active' : '') }}">
                    <div class="so-step-dot">
                        @if($isDone) <iconify-icon icon="mdi:check"></iconify-icon>
                        @elseif($isActive) <iconify-icon icon="mdi:record-circle-outline"></iconify-icon>
                        @else <iconify-icon icon="mdi:circle-outline"></iconify-icon>
                        @endif
                    </div>
                    <div class="so-step-label">{{ \App\Models\SalesOrder::STATUSES[$stepKey] }}</div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="row mt-3 g-2">
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Tanggal SO</div>
                    <div class="fw-semibold">{{ $salesOrder->date->format('d M Y') }}</div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Jadwal Pasang</div>
                    @if($salesOrder->scheduled_date)
                        @php $overdue = $salesOrder->scheduled_date->isPast() && !in_array($salesOrder->status, ['completed','cancelled']); @endphp
                        <div class="fw-semibold {{ $overdue ? 'text-danger' : '' }}">
                            {{ $salesOrder->scheduled_date->format('d M Y') }}
                            @if($overdue)
                                <x-ui.badge variant="danger" size="sm" class="ms-1">Terlambat</x-ui.badge>
                            @endif
                        </div>
                    @else
                        <span class="text-muted">Belum dijadwalkan</span>
                    @endif
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Marketing</div>
                    <div>{{ $salesOrder->marketing?->name ?? '—' }}</div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Dibuat oleh</div>
                    <div>{{ $salesOrder->creator?->name ?? '—' }}</div>
                </div>
            </div>
        </x-ui.card>

        {{-- Prospect Info --}}
        <x-ui.card title="Data Prospek / Customer" class="mb-3">
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="text-muted small mb-1">Nama</div>
                    <div class="fw-semibold">{{ $salesOrder->prospect_name }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small mb-1">Telepon</div>
                    @if($salesOrder->prospect_phone)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $salesOrder->prospect_phone) }}"
                           target="_blank" class="text-success fw-semibold text-decoration-none">
                            <iconify-icon icon="mdi:whatsapp" class="me-1"></iconify-icon>{{ $salesOrder->prospect_phone }}
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small mb-1">Email</div>
                    <div>{{ $salesOrder->prospect_email ?? '—' }}</div>
                </div>
                @if($salesOrder->prospect_address)
                <div class="col-12">
                    <div class="text-muted small mb-1">Alamat</div>
                    <div>{{ $salesOrder->prospect_address }}</div>
                </div>
                @endif
                @if($salesOrder->vehicle_brand)
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Kendaraan</div>
                    <div class="fw-semibold">
                        {{ $salesOrder->vehicle_brand }} {{ $salesOrder->vehicle_model }}
                        @if($salesOrder->vehicle_year)
                            <span class="text-muted fw-normal">({{ $salesOrder->vehicle_year }})</span>
                        @endif
                        @if($salesOrder->vehicle_plate)
                            <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $salesOrder->vehicle_plate }}</x-ui.badge>
                        @endif
                    </div>
                </div>
                @endif
                @if($salesOrder->tukang || $salesOrder->kenek)
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Tim Teknisi</div>
                    <div>
                        @if($salesOrder->tukang)
                            <span class="fw-semibold">{{ $salesOrder->tukang->name }}</span>
                            <span class="text-muted small">(Teknisi)</span>
                        @endif
                        @if($salesOrder->kenek)
                            &nbsp;/ <span>{{ $salesOrder->kenek->name }}</span>
                            <span class="text-muted small">(Kenek)</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Items Table --}}
        <x-ui.card title="Rincian Pekerjaan & Harga" class="basic-data-table mb-3">
            <div class="table-responsive">
                <table class="table bordered-table mb-0">
                    <thead>
                        <tr>
                            <th>Deskripsi</th>
                            <th>Spesifikasi</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-center">Disc%</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($salesOrder->items as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->description }}</td>
                            <td class="small text-muted">{{ $item->specifications ?? '—' }}</td>
                            <td class="text-center small">{{ $item->unit }}</td>
                            <td class="text-center">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-center small">{{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '—' }}</td>
                            <td class="text-end fw-semibold text-primary-600">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-4">
                                <x-ui.empty-state title="Belum ada item" description="Edit SO untuk menambahkan rincian pekerjaan." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($salesOrder->items->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end text-muted fw-semibold border-0">Subtotal</td>
                            <td class="text-end fw-semibold border-0">Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($salesOrder->discount_amount > 0)
                        <tr>
                            <td colspan="6" class="text-end text-muted border-0">Diskon</td>
                            <td class="text-end text-danger border-0">- Rp {{ number_format($salesOrder->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr style="background:var(--primary-light)">
                            <td colspan="6" class="text-end fw-bold">Total</td>
                            <td class="text-end fw-bold text-success fs-6">
                                Rp {{ number_format($salesOrder->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </x-ui.card>

        @if($salesOrder->notes)
        <x-ui.card>
            <div class="text-muted small fw-semibold mb-1">Catatan</div>
            <div class="small" style="white-space:pre-line">{{ $salesOrder->notes }}</div>
        </x-ui.card>
        @endif

    </div>

    {{-- RIGHT: Actions & Meta --}}
    <div class="col-lg-4">

        {{-- SPK Card --}}
        <x-ui.card class="mb-3" style="{{ $salesOrder->hasSpk() ? 'border-color:var(--primary-600)' : '' }}">
            @slot('header')
            <div class="d-flex justify-content-between align-items-center" style="{{ $salesOrder->hasSpk() ? 'background:var(--primary-light);margin:-1px -1px 0;padding:12px 16px;border-radius:inherit inherit 0 0' : '' }}">
                <div class="ui-card-title">
                    <iconify-icon icon="mdi:file-document-outline" class="me-1"></iconify-icon> SPK Digital
                </div>
                @if($salesOrder->hasSpk())
                    <x-ui.badge variant="success" size="sm">Terbit</x-ui.badge>
                @else
                    <x-ui.badge variant="neutral" size="sm">Belum Terbit</x-ui.badge>
                @endif
            </div>
            @endslot
            @if($salesOrder->hasSpk())
                <div class="small mb-3">
                    <div class="text-muted mb-1">No. SPK</div>
                    <div class="fw-bold font-monospace">{{ $salesOrder->spk_number }}</div>
                    <div class="text-muted mt-1" style="font-size:11px">
                        Diterbitkan {{ $salesOrder->spk_issued_at->format('d M Y, H:i') }}
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <x-ui.button variant="primary" size="sm" href="{{ route('sales-orders.spk', $salesOrder) }}" target="_blank">
                        <iconify-icon icon="mdi:printer-outline" class="me-1"></iconify-icon> Print / Download SPK
                    </x-ui.button>
                    <x-ui.button variant="ghost" size="sm" href="{{ $salesOrder->spk_public_url }}" target="_blank">
                        <iconify-icon icon="mdi:qrcode" class="me-1"></iconify-icon> Halaman Publik (QR)
                    </x-ui.button>
                </div>
            @else
                <p class="small text-muted mb-3">
                    Terbitkan SPK untuk mengirim instruksi kerja ke teknisi beserta QR code verifikasi.
                </p>
                @if(!$salesOrder->isCancelled())
                <form action="{{ route('sales-orders.issue-spk', $salesOrder) }}" method="POST">
                    @csrf
                    <x-ui.button variant="outline" size="sm" type="submit" class="w-100">
                        <iconify-icon icon="mdi:file-document-outline" class="me-1"></iconify-icon> Terbitkan SPK
                    </x-ui.button>
                </form>
                @endif
            @endif
        </x-ui.card>

        {{-- Action Buttons --}}
        <x-ui.card title="Tindakan" class="mb-3">
            <div class="d-grid gap-2">

                {{-- Confirm --}}
                @if($salesOrder->isPending())
                <form action="{{ route('sales-orders.confirm', $salesOrder) }}" method="POST">
                    @csrf
                    <x-ui.button variant="secondary" type="submit" class="w-100">
                        <iconify-icon icon="mdi:checkbox-marked-circle-outline" class="me-1"></iconify-icon> Konfirmasi SO
                    </x-ui.button>
                </form>
                @endif

                {{-- Start Work --}}
                @if($salesOrder->isConfirmed())
                <form action="{{ route('sales-orders.start-work', $salesOrder) }}" method="POST">
                    @csrf
                    <x-ui.button variant="warning" type="submit" class="w-100">
                        <iconify-icon icon="mdi:tools" class="me-1"></iconify-icon> Mulai Pengerjaan
                    </x-ui.button>
                </form>
                @endif

                {{-- Complete --}}
                @if($salesOrder->isInProgress())
                <form action="{{ route('sales-orders.complete', $salesOrder) }}" method="POST">
                    @csrf
                    <x-ui.button variant="success" type="submit" class="w-100"
                            onclick="return confirm('Tandai pekerjaan ini sebagai Selesai?')">
                        <iconify-icon icon="mdi:check-all" class="me-1"></iconify-icon> Selesai
                    </x-ui.button>
                </form>
                @endif

                {{-- Create Customer/Invoice after completed --}}
                @if($salesOrder->isCompleted() && !$salesOrder->isConverted())
                <x-ui.alert type="success" class="mb-0">
                    <iconify-icon icon="mdi:check-all" class="me-1"></iconify-icon>
                    Pekerjaan selesai! Buat Customer & Invoice:
                </x-ui.alert>
                @if($salesOrder->category === 'car_film')
                    <x-ui.button variant="success" size="sm" href="{{ route('customer.create') }}?from_so={{ $salesOrder->id }}">
                        <iconify-icon icon="mdi:account-plus-outline" class="me-1"></iconify-icon> Buat Customer Kaca Film
                    </x-ui.button>
                @elseif($salesOrder->category === 'building_film')
                    <x-ui.button variant="success" size="sm" href="{{ route('customer.building.create') }}?from_so={{ $salesOrder->id }}">
                        <iconify-icon icon="mdi:office-building-outline" class="me-1"></iconify-icon> Buat Customer Gedung
                    </x-ui.button>
                @elseif($salesOrder->category === 'ppf')
                    <x-ui.button variant="success" size="sm" href="{{ route('ppf-customers.create') }}?from_so={{ $salesOrder->id }}">
                        <iconify-icon icon="mdi:car-outline" class="me-1"></iconify-icon> Buat Customer PPF
                    </x-ui.button>
                @endif
                @endif

                @if($salesOrder->isConverted())
                <x-ui.alert type="success" class="mb-0 text-center">
                    <iconify-icon icon="mdi:checkbox-marked-circle-outline" class="fs-5 d-block mb-1"></iconify-icon>
                    <strong>SO Dikonversi ke Customer</strong>
                    <div class="text-muted">{{ $salesOrder->converted_at->format('d M Y') }}</div>
                </x-ui.alert>
                @endif

                {{-- Cancel --}}
                @if(!$salesOrder->isCompleted() && !$salesOrder->isCancelled())
                <x-ui.button variant="danger-outline" type="button" class="w-100 mt-1"
                        data-bs-toggle="collapse" data-bs-target="#cancelForm">
                    <iconify-icon icon="mdi:close-circle-outline" class="me-1"></iconify-icon> Batalkan SO
                </x-ui.button>
                <div class="collapse" id="cancelForm">
                    <form action="{{ route('sales-orders.cancel', $salesOrder) }}" method="POST" class="mt-2">
                        @csrf
                        <textarea name="cancel_reason" class="form-control form-control-sm mb-2" rows="2"
                                  placeholder="Alasan pembatalan (opsional)..."></textarea>
                        <x-ui.button variant="danger" size="sm" type="submit" class="w-100"
                                onclick="return confirm('Batalkan SO ini?')">
                            Konfirmasi Batalkan
                        </x-ui.button>
                    </form>
                </div>
                @endif

                @if($salesOrder->isCancelled())
                <x-ui.alert type="danger" class="mb-0">
                    <iconify-icon icon="mdi:close-circle-outline" class="me-1"></iconify-icon>
                    Dibatalkan {{ $salesOrder->cancelled_at?->format('d M Y') }}
                    @if($salesOrder->cancel_reason)
                        <div class="mt-1 text-muted">{{ $salesOrder->cancel_reason }}</div>
                    @endif
                </x-ui.alert>
                @endif

            </div>
        </x-ui.card>

        {{-- Quotation & Lead linked --}}
        @if($salesOrder->quotation || $salesOrder->lead)
        <x-ui.card title="Referensi" class="mb-3">
            <div class="d-grid gap-2">
                @if($salesOrder->quotation)
                <a href="{{ route('quotations.show', $salesOrder->quotation) }}" class="text-decoration-none">
                    <div class="d-flex align-items-center gap-3">
                        <div class="w-38-px h-38-px bg-success-focus text-success-main rounded-circle d-flex align-items-center justify-content-center shrink-0">
                            <iconify-icon icon="mdi:file-document-outline"></iconify-icon>
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $salesOrder->quotation->quotation_number }}</div>
                            <div class="text-muted" style="font-size:11px">Quotation</div>
                        </div>
                    </div>
                </a>
                @endif
                @if($salesOrder->lead)
                <a href="{{ route('leads.show', $salesOrder->lead) }}" class="text-decoration-none">
                    <div class="d-flex align-items-center gap-3">
                        <div class="w-38-px h-38-px bg-primary-light text-primary-600 rounded-circle d-flex align-items-center justify-content-center shrink-0">
                            <iconify-icon icon="mdi:target"></iconify-icon>
                        </div>
                        <div>
                            <div class="fw-semibold small">{{ $salesOrder->lead->name }}</div>
                            <div class="text-muted" style="font-size:11px">Lead</div>
                        </div>
                    </div>
                </a>
                @endif
            </div>
        </x-ui.card>
        @endif

        {{-- Meta --}}
        <x-ui.card title="Informasi Sistem">
            <dl class="row mb-0 small">
                <dt class="col-6 text-muted">Cabang</dt>
                <dd class="col-6">{{ $salesOrder->branch?->name ?? '—' }}</dd>
                <dt class="col-6 text-muted">Dibuat</dt>
                <dd class="col-6">{{ $salesOrder->created_at->format('d M Y H:i') }}</dd>
                @if($salesOrder->confirmed_at)
                <dt class="col-6 text-muted">Dikonfirmasi</dt>
                <dd class="col-6">{{ $salesOrder->confirmed_at->format('d M Y') }}</dd>
                @endif
                @if($salesOrder->in_progress_at)
                <dt class="col-6 text-muted">Mulai kerja</dt>
                <dd class="col-6">{{ $salesOrder->in_progress_at->format('d M Y') }}</dd>
                @endif
                @if($salesOrder->completed_at)
                <dt class="col-6 text-muted">Selesai</dt>
                <dd class="col-6">{{ $salesOrder->completed_at->format('d M Y') }}</dd>
                @endif
            </dl>
        </x-ui.card>

    </div>
</div>

@endsection
