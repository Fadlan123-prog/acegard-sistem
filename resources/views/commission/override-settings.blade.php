@extends('dashboard.index')

@section('title', 'Setting Override Komisi')
@section('route-breadcrumb', 'commission.override.index')
@section('breadcrumb', 'Override Komisi')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

@if(isset($branch) && $branch)
<div class="d-flex align-items-center gap-2 mb-3">
    <iconify-icon icon="proicons-branch" style="font-size:16px;color:#64748b;"></iconify-icon>
    <span class="text-muted small">Menampilkan data cabang:</span>
    <x-ui.badge variant="neutral" class="px-3 py-2 fw-semibold" style="font-size:.8rem;">{{ $branch->name }}</x-ui.badge>
</div>
@endif

{{-- INFO --}}
<x-ui.alert type="info" class="mb-4">
    <x-slot name="title">Cara Kerja Override Komisi</x-slot>
    <span class="small">
        Ketika sebuah invoice dirilis komisinya, sistem akan mengecek tabel ini.
        Jika marketing yang tercatat di customer ada di daftar <strong>Marketing Pemicu</strong>,
        maka karyawan penerima override akan mendapatkan komisi sebesar <strong>rate × harga jual</strong>.
        Aturan yang tidak aktif tidak akan dijalankan.
    </span>
</x-ui.alert>

<div class="row g-3">

    {{-- ── FORM TAMBAH ── --}}
    <div class="col-xl-4">
        <x-ui.card>
            @slot('header')
                <div class="ui-card-title">
                    <iconify-icon icon="mdi:plus-circle" class="me-2 text-success"></iconify-icon>
                    Tambah Aturan Override
                </div>
            @endslot
            <form method="POST" action="{{ route('commission.override.store') }}">
                @csrf

                <x-ui.form-field label="Karyawan Penerima Override" name="override_employee_id" :required="true">
                    <select name="override_employee_id" class="form-select form-select-sm @error('override_employee_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Karyawan --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected(old('override_employee_id') == $emp->id)>
                                {{ $emp->name }} — {{ $emp->job_position ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Karyawan yang akan menerima komisi tambahan (misal: Adit).</div>
                    @error('override_employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-ui.form-field>

                <x-ui.form-field label="Marketing Pemicu" name="trigger_marketing_id" :required="true">
                    <select name="trigger_marketing_id" class="form-select form-select-sm @error('trigger_marketing_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Marketing --</option>
                        @foreach($marketings as $m)
                            <option value="{{ $m->id }}" @selected(old('trigger_marketing_id') == $m->id)>
                                {{ $m->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Jika marketing ini yang tercatat di customer, override akan berjalan.</div>
                    @error('trigger_marketing_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-ui.form-field>

                <x-ui.form-field label="Rate (%)" name="rate" :required="true">
                    <div class="input-group input-group-sm">
                        <input type="number" name="rate" step="0.01" min="0.01" max="100"
                               value="{{ old('rate', 2) }}"
                               class="form-control @error('rate') is-invalid @enderror" required>
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Persentase dari harga jual invoice.</div>
                    @error('rate')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </x-ui.form-field>

                <x-ui.button variant="success" size="sm" type="submit" class="w-100">
                    <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Simpan Aturan
                </x-ui.button>
            </form>
        </x-ui.card>
    </div>

    {{-- ── DAFTAR ATURAN ── --}}
    <div class="col-xl-8">
        @if($triggers->isEmpty())
        <x-ui.card>
            <x-ui.empty-state title="Belum ada aturan override yang dikonfigurasi." />
        </x-ui.card>
        @else

        @foreach($triggers as $overrideEmpId => $group)
        @php $overrideEmp = $group->first()->overrideEmployee; @endphp
        <x-ui.card class="mb-3">
            @slot('header')
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                             style="width:38px;height:38px;background:#0f172a;color:#fff;font-size:.85rem;flex-shrink:0;">
                            {{ strtoupper(substr($overrideEmp?->name ?? '?', 0, 2)) }}
                        </div>
                        <div>
                            <div class="fw-bold">{{ $overrideEmp?->name ?? '-' }}</div>
                            <div class="text-muted small">{{ $overrideEmp?->job_position ?? '-' }}</div>
                        </div>
                    </div>
                    <x-ui.badge variant="neutral">{{ $group->count() }} marketing</x-ui.badge>
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Marketing Pemicu</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Rate</th>
                            <th class="text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group as $trigger)
                        <tr class="{{ !$trigger->is_active ? 'opacity-50' : '' }}">
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $trigger->triggerMarketing?->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $trigger->triggerMarketing?->job_position ?? '-' }}</div>
                            </td>
                            <td class="text-center">
                                @if($trigger->is_active)
                                    <x-ui.badge variant="success">Aktif</x-ui.badge>
                                @else
                                    <x-ui.badge variant="neutral">Nonaktif</x-ui.badge>
                                @endif
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('commission.override.rate', $trigger->id) }}"
                                      class="d-flex align-items-center gap-1 justify-content-center">
                                    @csrf
                                    <div class="input-group input-group-sm" style="width:100px;">
                                        <input type="number" name="rate" step="0.01" min="0.01" max="100"
                                               value="{{ round($trigger->rate * 100, 2) }}"
                                               class="form-control form-control-sm text-center"
                                               style="min-width:60px;">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <x-ui.button variant="outline" size="sm" type="submit" title="Simpan rate">
                                        <iconify-icon icon="mdi:check" style="font-size:14px;"></iconify-icon>
                                    </x-ui.button>
                                </form>
                            </td>
                            <td class="text-center pe-3">
                                <div class="d-flex gap-1 justify-content-center">
                                    {{-- Toggle aktif/nonaktif --}}
                                    <form method="POST" action="{{ route('commission.override.toggle', $trigger->id) }}">
                                        @csrf
                                        <x-ui.button
                                            :variant="$trigger->is_active ? 'warning' : 'success'"
                                            size="sm" type="submit"
                                            :title="$trigger->is_active ? 'Nonaktifkan' : 'Aktifkan'">
                                            <iconify-icon icon="{{ $trigger->is_active ? 'mdi:pause' : 'mdi:play' }}"
                                                          style="font-size:14px;"></iconify-icon>
                                        </x-ui.button>
                                    </form>

                                    {{-- Hapus --}}
                                    <form method="POST" action="{{ route('commission.override.destroy', $trigger->id) }}"
                                          onsubmit="return confirm('Hapus aturan override ini?')">
                                        @csrf @method('DELETE')
                                        <x-ui.button variant="danger-outline" size="sm" type="submit" title="Hapus">
                                            <iconify-icon icon="mdi:trash-can-outline" style="font-size:14px;"></iconify-icon>
                                        </x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
        @endforeach

        @endif
    </div>

</div>

@endsection
