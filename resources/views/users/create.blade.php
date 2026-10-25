@extends('dashboard.index')

@section('title', 'Tambah User')
@section('route-breadcrumb', 'users.index')
@section('breadcrumb', 'Tambah User')

@section('content')

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Tambah User</div>
            <x-ui.button :href="route('users.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    @include('partials.form-errors')

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        {{-- Nama --}}
        <x-ui.form-field label="Nama" name="name" :required="true" :error="$errors->first('name')">
            <input type="text"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   placeholder="Masukkan nama lengkap"
                   value="{{ old('name') }}"
                   required>
        </x-ui.form-field>

        {{-- Email --}}
        <x-ui.form-field label="Email" name="email" :required="true" :error="$errors->first('email')">
            <input type="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="email@domain.com"
                   value="{{ old('email') }}"
                   required>
        </x-ui.form-field>

        {{-- Password --}}
        <div class="row">
            <div class="col-md-6">
                <x-ui.form-field label="Password" name="password" :required="true" :error="$errors->first('password')">
                    <input type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Minimal 6 karakter"
                           required>
                </x-ui.form-field>
            </div>

            <div class="col-md-6">
                <x-ui.form-field label="Konfirmasi Password" name="password_confirmation" :required="true">
                    <input type="password"
                           name="password_confirmation"
                           class="form-control"
                           placeholder="Ulangi password"
                           required>
                </x-ui.form-field>
            </div>
        </div>

        {{-- Role --}}
        <x-ui.form-field label="Role" name="role" :required="true" :error="$errors->first('role')">
            <select name="role"
                    id="roleSelect"
                    class="form-select @error('role') is-invalid @enderror"
                    required>
                <option value="">-- Pilih Role --</option>

                @foreach ($roles as $role)
                    <option value="{{ $role->name }}"
                        {{ old('role') == $role->name ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                    </option>
                @endforeach
            </select>
        </x-ui.form-field>

        {{-- Karyawan (untuk Teknisi / Kenek / Marketing) --}}
        <div class="mb-3" id="employeeSection" style="display:none">
            <label class="form-label fw-medium">
                Karyawan <span class="text-danger" id="empRequired">*</span>
                <span class="text-muted small" id="empOptional" style="display:none">(opsional)</span>
            </label>

            <select name="employee_id"
                    id="employee_id"
                    class="form-select @error('employee_id') is-invalid @enderror">
                <option value="">-- Pilih Karyawan --</option>
                @foreach ($employees as $emp)
                    <option value="{{ $emp->id }}"
                        {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->name }}
                        @if ($emp->job_position)
                            ({{ $emp->job_position }})
                        @endif
                    </option>
                @endforeach
            </select>

            @error('employee_id')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
            <small class="text-muted">Hubungkan akun ini ke data karyawan yang sudah terdaftar.</small>
        </div>

        {{-- Cabang --}}
        <div class="mb-4" id="branchSection">
            <label class="form-label fw-medium">
                Cabang <span class="text-danger" id="branchRequired">*</span>
                <span class="text-muted small" id="branchOptional" style="display:none">(tidak diperlukan untuk role ini)</span>
            </label>

            @php
                $selectedBranches = collect(
                    old('branch_ids', isset($defaultBranchId) && $defaultBranchId ? [$defaultBranchId] : [])
                )->map(fn($id) => (int) $id)->toArray();
            @endphp

            <div class="border rounded p-3 bg-light @error('branch_ids') border-danger @enderror">

                {{-- Select All --}}
                <div class="form-check mb-3 pb-2 border-bottom">
                    <input class="form-check-input"
                           type="checkbox"
                           id="selectAllBranches">

                    <label class="form-check-label fw-semibold" for="selectAllBranches">
                        Pilih Semua Cabang
                    </label>
                </div>

                {{-- List Cabang --}}
                <div class="row">
                    @forelse ($branches as $branch)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-2">
                            <div class="form-check bg-white border rounded p-3 h-100">
                                <input class="form-check-input branch-checkbox"
                                       type="checkbox"
                                       name="branch_ids[]"
                                       value="{{ $branch->id }}"
                                       id="branch{{ $branch->id }}"
                                       {{ in_array($branch->id, $selectedBranches) ? 'checked' : '' }}>

                                <label class="form-check-label ms-1" for="branch{{ $branch->id }}">
                                    <span class="fw-semibold d-block">
                                        {{ $branch->name }}
                                    </span>

                                    <small class="text-muted">
                                        {{ $branch->city ?? '-' }}
                                    </small>
                                </label>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <x-ui.alert type="warning" class="mb-0">
                                Belum ada data cabang. Silakan tambahkan cabang terlebih dahulu.
                            </x-ui.alert>
                        </div>
                    @endforelse
                </div>
            </div>

            @error('branch_ids')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

            @error('branch_ids.*')
                <div class="text-danger small mt-1">
                    {{ $message }}
                </div>
            @enderror

            <small class="text-muted d-block mt-1">
                Centang satu atau beberapa cabang yang dapat diakses oleh user ini.
            </small>
        </div>

        {{-- Button --}}
        <div class="d-flex gap-2">
            <x-ui.button type="submit" variant="primary" class="px-4">Simpan User</x-ui.button>
            <x-ui.button :href="route('users.index')" variant="secondary" class="px-4">Batal</x-ui.button>
        </div>

    </form>
</x-ui.card>


{{-- Script Role Toggle --}}
<script>
    const staffRoles    = @json($staffRoles);    // teknisi, kenek — bypass branch
    const employeeRoles = @json($employeeRoles); // teknisi, kenek, marketing — show employee picker
    const noBranchRoles = @json($noBranchRoles); // superadmin, teknisi, kenek — no branch needed

    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect     = document.getElementById('roleSelect');
        const employeeSection = document.getElementById('employeeSection');
        const empRequired    = document.getElementById('empRequired');
        const empOptional    = document.getElementById('empOptional');
        const branchSection  = document.getElementById('branchSection');
        const branchRequired = document.getElementById('branchRequired');
        const branchOptional = document.getElementById('branchOptional');
        const employeeInput  = document.getElementById('employee_id');

        function toggleSections() {
            const role        = roleSelect.value;
            const showEmp     = employeeRoles.includes(role);
            const empRequired_ = staffRoles.includes(role); // teknisi/kenek: required
            const hideBranch  = noBranchRoles.includes(role);

            // Employee section
            employeeSection.style.display = showEmp ? '' : 'none';
            employeeInput.required        = empRequired_;
            empRequired.style.display     = empRequired_ ? '' : 'none';
            empOptional.style.display     = (showEmp && !empRequired_) ? '' : 'none';

            // Branch section
            branchRequired.style.display  = hideBranch ? 'none' : '';
            branchOptional.style.display  = hideBranch ? '' : 'none';
        }

        roleSelect.addEventListener('change', toggleSections);
        toggleSections();
    });
</script>

{{-- Script Select All Branch --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllBranches');
        const branchCheckboxes = document.querySelectorAll('.branch-checkbox');

        function updateSelectAllState() {
            const totalCheckboxes = branchCheckboxes.length;
            const checkedCheckboxes = document.querySelectorAll('.branch-checkbox:checked').length;

            if (!selectAll || totalCheckboxes === 0) {
                return;
            }

            selectAll.checked = checkedCheckboxes === totalCheckboxes;
            selectAll.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                branchCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });

                updateSelectAllState();
            });
        }

        branchCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                updateSelectAllState();
            });
        });

        updateSelectAllState();
    });
</script>

@endsection
