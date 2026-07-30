@extends('dashboard.index')

@section('title', 'Detail Quotation')
@section('route-breadcrumb', 'quotations.index')
@section('breadcrumb', 'Detail Quotation')

@section('content')

@if(session('success'))<x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>@endif

@php
    $rs = $quotation->resolvedStatus();
    $variantMap = [
        'draft'    => 'neutral',
        'sent'     => 'info',
        'accepted' => 'success',
        'rejected' => 'danger',
        'expired'  => 'warning',
    ];
@endphp

<div class="row g-3">

    {{-- LEFT: main content --}}
    <div class="col-lg-8">

        {{-- Hero card --}}
        <x-ui.card class="mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="text-muted small mb-1">No. Quotation</div>
                    <h4 class="font-monospace mb-2">{{ $quotation->quotation_number }}</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <x-ui.badge variant="{{ $variantMap[$rs] ?? 'neutral' }}" :dot="true">
                            {{ \App\Models\Quotation::STATUSES[$rs] ?? $rs }}
                        </x-ui.badge>
                        <x-ui.badge variant="info">{{ $quotation->category_label }}</x-ui.badge>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @if($quotation->isEditable())
                    <x-ui.button href="{{ route('quotations.edit', $quotation) }}" variant="outline" size="sm">
                        <iconify-icon icon="mdi:pencil-outline" class="me-1"></iconify-icon> Edit
                    </x-ui.button>
                    @endif
                    <x-ui.button href="{{ route('quotations.print', $quotation) }}" variant="outline" size="sm">
                        <iconify-icon icon="mdi:printer-outline" class="me-1"></iconify-icon> Print
                    </x-ui.button>
                    <form action="{{ route('quotations.destroy', $quotation) }}" method="POST"
                          onsubmit="return confirm('Hapus quotation ini?')">
                        @csrf @method('DELETE')
                        <x-ui.button type="submit" variant="danger-outline" size="sm">Hapus</x-ui.button>
                    </form>
                </div>
            </div>

            <div class="row mt-3 g-2">
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Tanggal</div>
                    <div class="fw-semibold">{{ $quotation->date->format('d M Y') }}</div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Berlaku s/d</div>
                    @php $expired = $quotation->valid_until->isPast() && !in_array($quotation->status, ['accepted','rejected']); @endphp
                    <div class="fw-semibold {{ $expired ? 'text-danger' : '' }}">
                        {{ $quotation->valid_until->format('d M Y') }}
                        @if($expired)
                            <x-ui.badge variant="danger" size="sm" class="ms-1">Kedaluwarsa</x-ui.badge>
                        @endif
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Marketing / PIC</div>
                    <div>{{ $quotation->assignee?->name ?? '—' }}</div>
                </div>
                <div class="col-sm-3">
                    <div class="text-muted small mb-1">Dibuat oleh</div>
                    <div>{{ $quotation->creator?->name ?? '—' }}</div>
                </div>
            </div>
        </x-ui.card>

        {{-- Prospect Info --}}
        <x-ui.card title="Data Prospek" class="mb-3">
            <div class="row g-3">
                <div class="col-sm-4">
                    <div class="text-muted small mb-1">Nama</div>
                    <div class="fw-semibold">{{ $quotation->prospect_name }}</div>
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small mb-1">Telepon</div>
                    @if($quotation->prospect_phone)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $quotation->prospect_phone) }}"
                           target="_blank" class="text-success fw-semibold text-decoration-none">
                            <iconify-icon icon="mdi:whatsapp" class="me-1"></iconify-icon>{{ $quotation->prospect_phone }}
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
                <div class="col-sm-4">
                    <div class="text-muted small mb-1">Email</div>
                    <div>{{ $quotation->prospect_email ?? '—' }}</div>
                </div>
                @if($quotation->prospect_address)
                <div class="col-12">
                    <div class="text-muted small mb-1">Alamat</div>
                    <div>{{ $quotation->prospect_address }}</div>
                </div>
                @endif
                @if($quotation->vehicle_brand)
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Kendaraan</div>
                    <div class="fw-semibold">
                        {{ $quotation->vehicle_brand }} {{ $quotation->vehicle_model }}
                        @if($quotation->vehicle_year)
                            <span class="text-muted fw-normal">({{ $quotation->vehicle_year }})</span>
                        @endif
                        @if($quotation->vehicle_plate)
                            <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $quotation->vehicle_plate }}</x-ui.badge>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Items Table --}}
        <x-ui.card title="Rincian Penawaran" class="mb-3">
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
                        @forelse($quotation->items as $item)
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
                                <x-ui.empty-state
                                    title="Belum ada item"
                                    description="Edit quotation untuk menambahkan rincian produk/layanan."
                                />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($quotation->items->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-end text-muted fw-semibold border-0">Subtotal</td>
                            <td class="text-end fw-semibold border-0">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($quotation->discount_amount > 0)
                        <tr>
                            <td colspan="6" class="text-end text-muted border-0">Diskon</td>
                            <td class="text-end text-danger border-0">- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr style="background:var(--primary-light)">
                            <td colspan="6" class="text-end fw-bold">Total</td>
                            <td class="text-end fw-bold text-success fs-6">
                                Rp {{ number_format($quotation->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </x-ui.card>

        @if($quotation->notes || $quotation->terms)
        <x-ui.card>
            @if($quotation->notes)
            <div class="{{ $quotation->terms ? 'mb-3' : '' }}">
                <div class="text-muted small fw-semibold mb-1">Catatan</div>
                <div class="small">{{ $quotation->notes }}</div>
            </div>
            @endif
            @if($quotation->terms)
            <div>
                <div class="text-muted small fw-semibold mb-1">Syarat & Ketentuan</div>
                <div class="small" style="white-space:pre-line">{{ $quotation->terms }}</div>
            </div>
            @endif
        </x-ui.card>
        @endif

    </div>

    {{-- RIGHT: Actions & Meta --}}
    <div class="col-lg-4">

        {{-- Action Buttons --}}
        <x-ui.card title="Tindakan" class="mb-3">
            <div class="d-grid gap-2">

                @if($quotation->isDraft())
                    @if($quotation->items()->count() > 0)
                    <form action="{{ route('quotations.send', $quotation) }}" method="POST">
                        @csrf
                        <x-ui.button type="submit" variant="primary" class="w-100">
                            <iconify-icon icon="mdi:send-outline" class="me-1"></iconify-icon> Kirim ke Prospek
                        </x-ui.button>
                    </form>
                    @else
                    <x-ui.alert type="warning" class="py-2 mb-0 small">
                        <iconify-icon icon="mdi:alert-outline" class="me-1"></iconify-icon> Tambahkan item sebelum mengirim.
                    </x-ui.alert>
                    @endif
                @endif

                @if($quotation->isSent())
                    <form action="{{ route('quotations.accept', $quotation) }}" method="POST">
                        @csrf
                        <x-ui.button type="submit" variant="success" class="w-100"
                                onclick="return confirm('Tandai quotation ini sebagai Diterima?')">
                            <iconify-icon icon="mdi:checkbox-marked-circle-outline" class="me-1"></iconify-icon> Diterima (Accepted)
                        </x-ui.button>
                    </form>

                    <x-ui.button type="button" variant="danger-outline" class="w-100"
                            data-bs-toggle="collapse" data-bs-target="#rejectForm">
                        <iconify-icon icon="mdi:close-circle-outline" class="me-1"></iconify-icon> Ditolak (Rejected)
                    </x-ui.button>

                    <div class="collapse" id="rejectForm">
                        <form action="{{ route('quotations.reject', $quotation) }}" method="POST" class="mt-2">
                            @csrf
                            <textarea name="rejection_reason" class="form-control form-control-sm mb-2" rows="2"
                                      placeholder="Alasan penolakan (opsional)..."></textarea>
                            <x-ui.button type="submit" variant="danger" size="sm" class="w-100">
                                Konfirmasi Ditolak
                            </x-ui.button>
                        </form>
                    </div>
                @endif

                @if($quotation->isAccepted() && !$quotation->isConverted())
                    <x-ui.alert type="success" class="py-2 mb-0 small">
                        <iconify-icon icon="mdi:checkbox-marked-circle-outline" class="me-1"></iconify-icon> Quotation diterima! Buat customer:
                    </x-ui.alert>
                    @if($quotation->category === 'car_film')
                        <x-ui.button href="{{ route('customer.create') }}?from_quotation={{ $quotation->id }}" variant="success" size="sm">
                            <iconify-icon icon="mdi:account-plus-outline" class="me-1"></iconify-icon> Buat Customer Kaca Film
                        </x-ui.button>
                    @elseif($quotation->category === 'building_film')
                        <x-ui.button href="{{ route('customer.building.create') }}?from_quotation={{ $quotation->id }}" variant="success" size="sm">
                            <iconify-icon icon="mdi:office-building-outline" class="me-1"></iconify-icon> Buat Customer Gedung
                        </x-ui.button>
                    @elseif($quotation->category === 'ppf')
                        <x-ui.button href="{{ route('ppf-customers.create') }}?from_quotation={{ $quotation->id }}" variant="success" size="sm">
                            <iconify-icon icon="mdi:car-outline" class="me-1"></iconify-icon> Buat Customer PPF
                        </x-ui.button>
                    @endif
                @endif

                @if($quotation->isRejected())
                    <x-ui.alert type="danger" class="py-2 mb-0 small">
                        <iconify-icon icon="mdi:close-circle-outline" class="me-1"></iconify-icon>
                        Ditolak {{ $quotation->rejected_at->format('d M Y') }}
                        @if($quotation->rejection_reason)
                            <div class="mt-1 text-muted">{{ $quotation->rejection_reason }}</div>
                        @endif
                    </x-ui.alert>
                @endif

                @if($quotation->isConverted())
                    <x-ui.alert type="success" class="py-2 mb-0 text-center small">
                        <iconify-icon icon="mdi:checkbox-marked-circle-outline" class="fs-5 d-block mb-1"></iconify-icon>
                        <strong>Quotation Dikonversi</strong>
                        <div class="text-muted">{{ $quotation->converted_at->format('d M Y') }}</div>
                    </x-ui.alert>
                @endif

            </div>
        </x-ui.card>

        {{-- Lead linked --}}
        @if($quotation->lead)
        <x-ui.card title="Lead Terkait" class="mb-3">
            <a href="{{ route('leads.show', $quotation->lead) }}" class="text-decoration-none">
                <div class="d-flex align-items-center gap-3">
                    <div class="w-38-px h-38-px bg-primary-light text-primary-600 rounded-circle d-flex align-items-center justify-content-center shrink-0">
                        <iconify-icon icon="mdi:target"></iconify-icon>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $quotation->lead->name }}</div>
                        <div class="small">
                            <x-ui.badge variant="{{ ['new'=>'primary','contacted'=>'info','follow_up'=>'warning','quoted'=>'neutral','won'=>'success','lost'=>'danger'][$quotation->lead->status] ?? 'neutral' }}" size="sm">
                                {{ $quotation->lead->status_label }}
                            </x-ui.badge>
                        </div>
                    </div>
                </div>
            </a>
        </x-ui.card>
        @endif

        {{-- Meta --}}
        <x-ui.card title="Informasi Sistem">
            <dl class="row mb-0 small">
                <dt class="col-6 text-muted">Cabang</dt>
                <dd class="col-6">{{ $quotation->branch?->name ?? '—' }}</dd>
                <dt class="col-6 text-muted">Dibuat</dt>
                <dd class="col-6">{{ $quotation->created_at->format('d M Y H:i') }}</dd>
                @if($quotation->sent_at)
                <dt class="col-6 text-muted">Dikirim</dt>
                <dd class="col-6">{{ $quotation->sent_at->format('d M Y') }}</dd>
                @endif
                @if($quotation->accepted_at)
                <dt class="col-6 text-muted">Diterima</dt>
                <dd class="col-6">{{ $quotation->accepted_at->format('d M Y') }}</dd>
                @endif
                @if($quotation->rejected_at)
                <dt class="col-6 text-muted">Ditolak</dt>
                <dd class="col-6">{{ $quotation->rejected_at->format('d M Y') }}</dd>
                @endif
            </dl>
        </x-ui.card>

    </div>
</div>

@endsection
