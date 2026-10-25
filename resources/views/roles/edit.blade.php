@extends('dashboard.index')

@section('title', 'Edit Role')
@section('route-breadcrumb', 'roles.index')
@section('breadcrumb', 'Edit Role')

@section('content')

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

@if ($isProtected)
    <x-ui.alert type="warning" class="mb-3">
        <iconify-icon icon="mdi:shield-alert-outline" class="me-1"></iconify-icon>
        Role <strong>superadmin</strong> dilindungi — namanya tidak dapat diubah atau dihapus, tapi permission-nya tetap bisa disesuaikan.
    </x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="ui-card-title">Edit Role</div>
            <x-ui.button :href="route('roles.index')" variant="secondary" size="sm">Kembali</x-ui.button>
        </div>
    @endslot

    <form action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf
        @method('PUT')

        <x-ui.form-field label="Nama Role" name="name" :required="true" help="Huruf kecil dan underscore saja, contoh: manager_gudang">
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $role->name) }}" placeholder="contoh_role" required
                   {{ $isProtected ? 'readonly' : '' }}>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </x-ui.form-field>

        <label class="form-label fw-semibold mt-2">Permissions</label>
        <div class="row g-2 mb-3">
            @foreach ($permissions as $permission)
                @php
                    $checked = in_array($permission->id, old('permissions', $rolePermIds));
                @endphp
                <div class="col-md-4 col-sm-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="permissions[]"
                               value="{{ $permission->id }}" id="perm{{ $permission->id }}"
                               {{ $checked ? 'checked' : '' }}>
                        <label class="form-check-label small" for="perm{{ $permission->id }}">
                            {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary">Simpan Perubahan</x-ui.button>
            <x-ui.button :href="route('roles.index')" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
