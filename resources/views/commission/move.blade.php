@extends('dashboard.index')

@section('title', 'Pindahkan Komisi')
@section('route-breadcrumb', 'commission.index')
@section('breadcrumb', 'Pindahkan Komisi')

@section('content')

@if($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

@php
    $roleConfig = [
        'installer'    => ['label' => 'Installer',    'color' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1d4ed8'],
        'teknisi'      => ['label' => 'Teknisi',      'color' => '#f0fdf4', 'border' => '#86efac', 'text' => '#15803d'],
        'kenek'        => ['label' => 'Kenek',        'color' => '#fefce8', 'border' => '#fde047', 'text' => '#a16207'],
        'marketing'    => ['label' => 'Marketing',    'color' => '#fdf4ff', 'border' => '#e9d5ff', 'text' => '#7c3aed'],
        'adit_override'=> ['label' => 'Override Adit','color' => '#fff7ed', 'border' => '#fdba74', 'text' => '#c2410c'],
    ];
    $rc = $roleConfig[$commission->role] ?? ['label' => $commission->role, 'color' => '#f8fafc', 'border' => '#e2e8f0', 'text' => '#64748b'];
@endphp

<div class="row justify-content-center">
    <div class="col-xl-10">

        {{-- ALERT INFO --}}
        <x-ui.alert type="warning" class="mb-4" :dismissible="false" title="Perhatian — Tindakan Koreksi">
            Fitur ini untuk memperbaiki kesalahan input teknisi, kenek, atau marketing.
            Nominal komisi tidak akan berubah, hanya kepemilikannya yang dipindahkan.
        </x-ui.alert>

        <div class="row g-3">

            {{-- DETAIL KOMISI (kiri) --}}
            <div class="col-lg-5">
                <x-ui.card class="h-100">
                    @slot('header')
                        <h6 class="mb-0 fw-semibold">
                            <iconify-icon icon="mdi:information-outline" class="me-2 text-primary"></iconify-icon>
                            Detail Komisi Saat Ini
                        </h6>
                    @endslot

                        {{-- Nominal --}}
                        <div class="text-center p-3 mb-4 rounded-3" style="background:#0f172a;">
                            <div class="text-muted small" style="color:#94a3b8!important;">Nominal Komisi</div>
                            <div class="fw-bold mt-1" style="font-size:1.75rem;color:#fff;">
                                Rp{{ number_format($commission->amount, 0, ',', '.') }}
                            </div>
                        </div>

                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted ps-0" style="width:40%;font-size:.82rem;">Peran</td>
                                <td class="pe-0">
                                    <span class="badge fw-semibold" style="background:{{ $rc['color'] }};color:{{ $rc['text'] }};border:1px solid {{ $rc['border'] }};font-size:.75rem;">
                                        {{ $rc['label'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0" style="font-size:.82rem;">Pemilik Saat Ini</td>
                                <td class="fw-semibold pe-0">{{ $commission->employee->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0" style="font-size:.82rem;">Jabatan</td>
                                <td class="pe-0 text-muted small">{{ $commission->employee->job_position ?? '-' }}</td>
                            </tr>
                            <tr><td colspan="2"><hr class="my-1"></td></tr>
                            <tr>
                                <td class="text-muted ps-0" style="font-size:.82rem;">Customer</td>
                                <td class="fw-semibold pe-0">{{ $commission->customer->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0" style="font-size:.82rem;">No. Invoice</td>
                                <td class="pe-0 text-muted small font-monospace">{{ $commission->invoice->invoice_number ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted ps-0" style="font-size:.82rem;">Dibuat</td>
                                <td class="pe-0 text-muted small">{{ \Carbon\Carbon::parse($commission->created_at)->isoFormat('D MMM Y') }}</td>
                            </tr>
                        </table>
                </x-ui.card>
            </div>

            {{-- FORM PINDAH (kanan) --}}
            <div class="col-lg-7">
                <x-ui.card>
                    @slot('header')
                        <h6 class="mb-0 fw-semibold">
                            <iconify-icon icon="mdi:account-switch-outline" class="me-2 text-warning"></iconify-icon>
                            Pindahkan Ke
                        </h6>
                    @endslot
                        <form action="{{ route('commission.move.update', $commission->id) }}" method="POST"
                              onsubmit="return confirm('Yakin ingin memindahkan komisi ini?\n\nDari: {{ $commission->employee->name ?? "-" }}\nNominal: Rp{{ number_format($commission->amount, 0, ",", ".") }}')">
                            @csrf
                            @method('PUT')

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Karyawan Tujuan <span class="text-danger">*</span></label>
                                <select name="employees_id" class="form-select" required>
                                    <option value="">-- Pilih Karyawan --</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}"
                                            @selected(old('employees_id') == $emp->id)
                                            @disabled($emp->id == $commission->employees_id)>
                                            {{ $emp->name }}
                                            @if($emp->id == $commission->employees_id) (Pemilik Saat Ini) @endif
                                            — {{ $emp->job_position ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih karyawan yang seharusnya menerima komisi ini.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold">Catatan Koreksi</label>
                                <textarea name="correction_note" class="form-control" rows="3"
                                    placeholder="Contoh: Salah input teknisi saat pembuatan invoice, seharusnya Budi bukan Andi...">{{ old('correction_note') }}</textarea>
                                <div class="form-text">Opsional — untuk keperluan audit dan histori perubahan.</div>
                            </div>

                            <div class="p-3 rounded-3 mb-4" style="background:#f0fdf4;border:1px solid #bbf7d0;">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="update_source" value="1"
                                           id="updateSource" {{ old('update_source', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="updateSource" style="color:#15803d;">
                                        <iconify-icon icon="mdi:database-sync-outline" class="me-1"></iconify-icon>
                                        Update data sumber customer (tukang/kenek)
                                    </label>
                                    <div class="text-muted small mt-1">
                                        Jika diaktifkan, sistem akan ikut memperbaiki field tukang/kenek pada data customer mobil sesuai peran komisi ini.
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <x-ui.button variant="warning" type="submit">
                                    <iconify-icon icon="mdi:account-switch-outline" class="me-1"></iconify-icon>
                                    Pindahkan Komisi
                                </x-ui.button>
                                <x-ui.button href="{{ route('commission.index') }}" variant="outline">
                                    Batal
                                </x-ui.button>
                            </div>
                        </form>
                    </x-ui.card>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
