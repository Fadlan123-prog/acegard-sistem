@extends('dashboard.index')

@section('title', 'Detail Lead')
@section('route-breadcrumb', 'leads.index')
@section('breadcrumb', 'Detail Lead')

@push('page-styles')
<style>
.timeline-item { display:flex; gap:14px; padding-bottom:16px; }
.timeline-item:last-child { padding-bottom:0; }
.tl-icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:16px; }
.tl-body { flex:1; min-width:0; }
.tl-header { display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
.tl-meta { font-size:11px; color:var(--neutral-400); white-space:nowrap; }
.tl-text { font-size:13px; margin-top:3px; color:var(--neutral-700); }
[data-theme="dark"] .tl-text { color:var(--neutral-300); }
</style>
@endpush

@section('content')

@php
    $variantMap = ['new'=>'primary','contacted'=>'info','follow_up'=>'warning','quoted'=>'neutral','won'=>'success','lost'=>'danger'];
    $iconMap    = ['new'=>'mdi:plus-circle-outline','contacted'=>'mdi:phone-outline','follow_up'=>'mdi:refresh','quoted'=>'mdi:file-document-outline','won'=>'mdi:checkbox-marked-circle-outline','lost'=>'mdi:close-circle-outline'];
@endphp

<div class="row g-3">

    {{-- LEFT: Main Info --}}
    <div class="col-lg-8">

        {{-- Hero card --}}
        <x-ui.card class="mb-3">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h4 class="mb-2">{{ $lead->name }}</h4>
                    <div class="d-flex gap-2 flex-wrap">
                        <x-ui.badge variant="{{ $variantMap[$lead->status] ?? 'neutral' }}" :dot="true">
                            {{ $lead->status_label }}
                        </x-ui.badge>
                        <x-ui.badge variant="info">{{ $lead->category_label }}</x-ui.badge>
                        <x-ui.badge variant="neutral">{{ $lead->source_label }}</x-ui.badge>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <x-ui.button :href="route('leads.edit', $lead)" variant="outline" size="sm">
                        <iconify-icon icon="mdi:pencil-outline" class="me-1"></iconify-icon> Edit
                    </x-ui.button>
                    <form action="{{ route('leads.destroy', $lead) }}" method="POST"
                          onsubmit="return confirm('Hapus lead ini?')">
                        @csrf @method('DELETE')
                        <x-ui.button type="submit" variant="danger-outline" size="sm">Hapus</x-ui.button>
                    </form>
                </div>
            </div>
        </x-ui.card>

        {{-- Detail info --}}
        <x-ui.card class="mb-3">
            @slot('header')
                <div class="ui-card-title">Informasi Prospek</div>
            @endslot
            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Telepon</div>
                    @if($lead->phone)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $lead->phone) }}"
                           target="_blank" class="text-success fw-semibold text-decoration-none">
                            <iconify-icon icon="mdi:whatsapp" class="me-1"></iconify-icon>{{ $lead->phone }}
                        </a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Email</div>
                    <div>{{ $lead->email ?? '—' }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Alamat</div>
                    <div>{{ $lead->address ?? '—' }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Kota</div>
                    <div>{{ $lead->city ?? '—' }}</div>
                </div>

                @if($lead->vehicle_brand)
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Kendaraan</div>
                    <div class="fw-semibold">
                        {{ $lead->vehicle_brand }} {{ $lead->vehicle_model }}
                        @if($lead->vehicle_year)
                            <span class="text-muted fw-normal">({{ $lead->vehicle_year }})</span>
                        @endif
                    </div>
                </div>
                @endif

                @if($lead->estimated_value)
                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Estimasi Nilai</div>
                    <div class="fw-semibold text-success">
                        Rp {{ number_format($lead->estimated_value, 0, ',', '.') }}
                    </div>
                </div>
                @endif

                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Marketing / PIC</div>
                    <div>{{ $lead->assignee?->name ?? '—' }}</div>
                </div>

                <div class="col-sm-6">
                    <div class="text-muted small mb-1">Follow Up</div>
                    @if($lead->follow_up_date)
                        @php
                            $isToday = $lead->follow_up_date->isToday();
                            $isPast  = $lead->follow_up_date->isPast() && !$isToday;
                        @endphp
                        <span class="{{ $isToday ? 'text-warning fw-bold' : ($isPast && !in_array($lead->status, ['won','lost']) ? 'text-danger' : '') }}">
                            {{ $lead->follow_up_date->format('d M Y') }}
                            @if($isToday)
                                <x-ui.badge variant="warning" size="sm" class="ms-1">Hari ini</x-ui.badge>
                            @endif
                        </span>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>

                @if($lead->notes)
                <div class="col-12">
                    <div class="text-muted small mb-1">Catatan</div>
                    <div class="bg-light rounded p-2 small" style="white-space:pre-line">{{ $lead->notes }}</div>
                </div>
                @endif

                @if($lead->status === 'lost' && $lead->lost_reason)
                <div class="col-12">
                    <div class="text-muted small mb-1">Alasan Gagal</div>
                    <div class="rounded p-2 small text-danger"
                         style="background:rgba(239,68,68,.08)">{{ $lead->lost_reason }}</div>
                </div>
                @endif
            </div>
        </x-ui.card>

        {{-- Activity Log --}}
        <x-ui.card>
            @slot('header')
                <div class="d-flex justify-content-between align-items-center">
                    <div class="ui-card-title">Riwayat Aktivitas</div>
                </div>
            @endslot

            {{-- Add activity form --}}
            @if(!$lead->isConverted())
            <form action="{{ route('leads.activity', $lead) }}" method="POST" class="mb-4">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tipe</label>
                        <select name="type" class="form-select form-select-sm @error('type') is-invalid @enderror">
                            @foreach(\App\Models\LeadActivity::TYPES as $key => $label)
                                @if($key !== 'status_change')
                                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label small fw-semibold">Catatan <span class="text-danger">*</span></label>
                        <input type="text" name="notes"
                               class="form-control form-control-sm @error('notes') is-invalid @enderror"
                               placeholder="Tulis catatan aktivitas..." value="{{ old('notes') }}" required>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <x-ui.button type="submit" variant="primary" size="sm" class="w-100">Catat</x-ui.button>
                    </div>
                </div>
            </form>
            @endif

            {{-- Timeline --}}
            @if($lead->activities->isEmpty())
                <x-ui.empty-state
                    title="Belum ada aktivitas"
                    description="Gunakan form di atas untuk mencatat interaksi dengan prospek."
                />
            @else
            <div class="ps-1">
                @foreach($lead->activities as $activity)
                <div class="timeline-item">
                    <div class="tl-icon bg-{{ $activity->type_color }}-subtle text-{{ $activity->type_color }}-main">
                        <iconify-icon icon="{{ $activity->type_icon }}"></iconify-icon>
                    </div>
                    <div class="tl-body">
                        <div class="tl-header">
                            <div>
                                <span class="fw-semibold small">{{ $activity->type_label }}</span>
                                <span class="text-muted small ms-1">· {{ $activity->creator?->name ?? '—' }}</span>
                            </div>
                            <span class="tl-meta">{{ $activity->created_at->format('d M Y H:i') }}</span>
                        </div>
                        <div class="tl-text">{{ $activity->notes }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

        </x-ui.card>

    </div>

    {{-- RIGHT: Actions & Meta --}}
    <div class="col-lg-4">

        {{-- Quick status change --}}
        @if(!$lead->isConverted())
        <x-ui.card class="mb-3">
            @slot('header')
                <div class="ui-card-title">Ubah Status</div>
            @endslot
            <form action="{{ route('leads.update', $lead) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="name"            value="{{ $lead->name }}">
                <input type="hidden" name="phone"           value="{{ $lead->phone }}">
                <input type="hidden" name="email"           value="{{ $lead->email }}">
                <input type="hidden" name="address"         value="{{ $lead->address }}">
                <input type="hidden" name="city"            value="{{ $lead->city }}">
                <input type="hidden" name="source"          value="{{ $lead->source }}">
                <input type="hidden" name="category"        value="{{ $lead->category }}">
                <input type="hidden" name="vehicle_brand"   value="{{ $lead->vehicle_brand }}">
                <input type="hidden" name="vehicle_model"   value="{{ $lead->vehicle_model }}">
                <input type="hidden" name="vehicle_year"    value="{{ $lead->vehicle_year }}">
                <input type="hidden" name="notes"           value="{{ $lead->notes }}">
                <input type="hidden" name="assigned_to"     value="{{ $lead->assigned_to }}">
                <input type="hidden" name="estimated_value" value="{{ $lead->estimated_value }}">
                <input type="hidden" name="follow_up_date"  value="{{ $lead->follow_up_date?->format('Y-m-d') }}">

                <select name="status" id="quickStatus" class="form-select mb-2">
                    @foreach(\App\Models\Lead::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ $lead->status == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <div id="quickLostReason" style="{{ $lead->status === 'lost' ? '' : 'display:none' }}">
                    <textarea name="lost_reason" class="form-control form-control-sm mb-2" rows="2"
                              placeholder="Alasan gagal...">{{ $lead->lost_reason }}</textarea>
                </div>

                <x-ui.button type="submit" variant="primary" size="sm" class="w-100">Simpan Status</x-ui.button>
            </form>
        </x-ui.card>
        @endif

        {{-- Convert to Customer --}}
        @if(!in_array($lead->status, ['lost']) && !$lead->isConverted())
        <x-ui.card class="mb-3" style="border-color:var(--success-main)">
            @slot('header')
                <div class="ui-card-title text-success">Konversi ke Customer</div>
            @endslot
            <p class="small text-muted mb-3">Data lead akan terisi otomatis di form customer — quotation tidak wajib.</p>
            @if($lead->category === 'car_film')
                <x-ui.button :href="route('customer.create') . '?from_lead=' . $lead->id" variant="success" size="sm" class="w-100">
                    <iconify-icon icon="mdi:account-plus-outline" class="me-1"></iconify-icon> Buat Customer Kaca Film
                </x-ui.button>
            @elseif($lead->category === 'building_film')
                <x-ui.button :href="route('customer.building.create') . '?from_lead=' . $lead->id" variant="success" size="sm" class="w-100">
                    <iconify-icon icon="mdi:office-building-outline" class="me-1"></iconify-icon> Buat Customer Gedung
                </x-ui.button>
            @elseif($lead->category === 'ppf')
                <x-ui.button :href="route('ppf-customers.create') . '?from_lead=' . $lead->id" variant="success" size="sm" class="w-100">
                    <iconify-icon icon="mdi:car-outline" class="me-1"></iconify-icon> Buat Customer PPF
                </x-ui.button>
            @endif
        </x-ui.card>
        @endif

        @if($lead->isConverted())
        <x-ui.card class="mb-3">
            <div class="text-center py-4">
                <div class="fs-2 text-success mb-1"><iconify-icon icon="mdi:checkbox-marked-circle-outline"></iconify-icon></div>
                <div class="fw-semibold text-success">Lead Dikonversi</div>
                <div class="text-muted small mt-1">{{ $lead->converted_at->format('d M Y') }}</div>
            </div>
        </x-ui.card>
        @endif

        {{-- Buat Quotation --}}
        @if(!$lead->isConverted() && !in_array($lead->status, ['lost']))
        <x-ui.card class="mb-3">
            <x-ui.button :href="route('quotations.create', ['from_lead' => $lead->id])" variant="outline" size="sm" class="w-100">
                <iconify-icon icon="mdi:file-document-outline" class="me-1"></iconify-icon> Buat Quotation dari Lead ini
            </x-ui.button>
        </x-ui.card>
        @endif

        {{-- Meta --}}
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title">Informasi Sistem</div>
            @endslot
            <dl class="row mb-0 small">
                <dt class="col-6 text-muted">Cabang</dt>
                <dd class="col-6">{{ $lead->branch?->name ?? '—' }}</dd>
                <dt class="col-6 text-muted">Dibuat oleh</dt>
                <dd class="col-6">{{ $lead->creator?->name ?? '—' }}</dd>
                <dt class="col-6 text-muted">Tgl Dibuat</dt>
                <dd class="col-6">{{ $lead->created_at->format('d M Y') }}</dd>
                <dt class="col-6 text-muted">Diperbarui</dt>
                <dd class="col-6">{{ $lead->updated_at->format('d M Y H:i') }}</dd>
            </dl>
        </x-ui.card>

    </div>
</div>

@push('page-scripts')
<script>
document.getElementById('quickStatus')?.addEventListener('change', function(){
    document.getElementById('quickLostReason').style.display = this.value === 'lost' ? '' : 'none';
});
</script>
@endpush

@endsection
