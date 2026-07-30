@extends('dashboard.index')

@section('title', 'Roles & Permissions')
@section('route-breadcrumb', 'roles.index')
@section('breadcrumb', 'Roles & Permissions')

@section('content')

@if(session('success'))
    <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
@endif
@if(session('error'))
    <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Roles &amp; Permissions</div>
                <div class="ui-card-subtitle">Kelola role dan hak akses tiap role</div>
            </div>
            <x-ui.button href="{{ route('roles.create') }}" variant="primary" size="sm">
                <iconify-icon icon="mdi:plus-circle-outline" class="me-1"></iconify-icon>Tambah Role
            </x-ui.button>
        </div>
    @endslot

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Role</th>
                    <th>Jumlah Permission</th>
                    <th>Jumlah User</th>
                    <th class="text-end pe-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                    <tr>
                        <td class="ps-3">
                            <span class="fw-semibold">{{ $role->name }}</span>
                            @if ($role->name === 'superadmin')
                                <x-ui.badge variant="danger" size="sm" class="ms-1">Dilindungi</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            <x-ui.badge variant="neutral">{{ $role->permissions_count }} permission</x-ui.badge>
                        </td>
                        <td>{{ $role->users_count }} user</td>
                        <td class="pe-3 text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <x-ui.button href="{{ route('roles.edit', $role->id) }}" variant="outline" size="sm">
                                    <iconify-icon icon="mdi:pencil-outline"></iconify-icon>
                                </x-ui.button>
                                @if ($role->name !== 'superadmin')
                                    <form method="POST" action="{{ route('roles.destroy', $role->id) }}"
                                          onsubmit="return confirm('Hapus role &quot;{{ $role->name }}&quot;?')">
                                        @csrf @method('DELETE')
                                        <x-ui.button type="submit" variant="danger-outline" size="sm" title="Hapus">
                                            <iconify-icon icon="mdi:trash-can-outline"></iconify-icon>
                                        </x-ui.button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-5"><x-ui.empty-state title="Belum ada role." /></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-ui.card>

@endsection
