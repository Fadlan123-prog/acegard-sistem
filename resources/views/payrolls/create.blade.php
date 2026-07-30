@extends('dashboard.index')

@section('title', 'Generate Payroll')
@section('route-breadcrumb', 'payrolls.index')
@section('breadcrumb', 'Generate Payroll')

@section('content')

<x-ui.card>

    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Generate Payroll Bulanan</div>
                <small class="text-muted">
                    Sistem akan mengambil data absensi dan karyawan aktif dari cabang yang dipilih.
                </small>
            </div>
            <x-ui.button href="{{ route('payrolls.index') }}" variant="secondary" size="sm">
                Kembali
            </x-ui.button>
        </div>
    @endslot

    @if ($errors->any())
        <x-ui.alert type="danger" class="mb-3">
            <strong>Terjadi kesalahan.</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    <form method="POST" action="{{ route('payrolls.store') }}">
        @csrf

        <div class="row">

            <div class="col-md-4 mb-3">
                <label class="form-label">Bulan Payroll</label>
                <input type="month"
                       name="month"
                       value="{{ old('month', $month) }}"
                       class="form-control"
                       required>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Cabang</label>
                <select name="branch_id" class="form-select" required>
                    <option value="">-- Pilih Cabang --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ old('branch_id', $branchId) == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} - {{ $branch->city ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label class="form-label">Hari Kerja Pembagi</label>
                <input type="number"
                       name="working_days"
                       value="{{ old('working_days', $workingDays) }}"
                       class="form-control"
                       min="1"
                       required>
                <small class="text-muted">
                    Contoh: 26 / 27 / 30 hari.
                </small>
            </div>

        </div>

        <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea name="notes"
                      class="form-control"
                      rows="3"
                      placeholder="Catatan payroll opsional">{{ old('notes') }}</textarea>
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input"
                   type="checkbox"
                   name="regenerate"
                   value="1"
                   id="regenerate"
                   {{ old('regenerate') ? 'checked' : '' }}>
            <label class="form-check-label" for="regenerate">
                Generate ulang jika payroll bulan ini sudah ada
            </label>
            <br>
            <small class="text-muted">
                Hanya bisa generate ulang payroll yang masih berstatus draft.
            </small>
        </div>

        <x-ui.alert type="info" class="mb-4">
            <strong>Aturan Payroll:</strong>
            Teknisi dan Kenek mendapatkan jatah libur 3 hari.
            Marketing, Admin, dan posisi lainnya mendapatkan jatah libur 4 hari.
            Payroll yang digenerate masih berstatus draft dan bisa diedit.
        </x-ui.alert>

        <div class="d-flex gap-2">
            <x-ui.button type="submit" variant="primary"
                onclick="return confirm('Generate payroll untuk bulan dan cabang ini?')">
                Generate Payroll
            </x-ui.button>

            <x-ui.button href="{{ route('payrolls.index') }}" variant="outline">
                Batal
            </x-ui.button>
        </div>

    </form>

</x-ui.card>

@endsection
