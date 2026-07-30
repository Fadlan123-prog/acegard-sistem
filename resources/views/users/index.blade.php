@extends('dashboard.index')

@section('title', 'Users')
@section('route-breadcrumb', 'users.index')
@section('breadcrumb', 'Users')

@section('content')
<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center">
            <div class="ui-card-title">Daftar User</div>
            <x-ui.button href="{{ route('users.create') }}" variant="primary" size="sm">
                + Add User
            </x-ui.button>
        </div>
    @endslot

    {{-- Alert --}}
    @if(session('success'))
        <x-ui.alert type="success" class="mb-3">{{ session('success') }}</x-ui.alert>
    @endif

    @if(session('error'))
        <x-ui.alert type="danger" class="mb-3">{{ session('error') }}</x-ui.alert>
    @endif

    {{-- Search, Filter, Bulk Delete --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">

        {{-- Search + Filter Cabang --}}
        <form method="GET"
              action="{{ route('users.index') }}"
              id="filterForm"
              class="d-flex align-items-center gap-2 flex-wrap">

            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control"
                   placeholder="Cari nama atau email user..."
                   style="max-width: 260px;">

            <select name="branch_id"
                    class="form-select"
                    style="max-width: 220px;"
                    onchange="document.getElementById('filterForm').submit()">
                <option value="">-- Semua Cabang --</option>

                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>

            <x-ui.button type="submit" variant="outline">Search</x-ui.button>

            @if(request('branch_id') || request('search'))
                <x-ui.button href="{{ route('users.index') }}" variant="secondary">Reset</x-ui.button>
            @endif
        </form>

        {{-- Bulk Delete --}}
        <form id="bulkDeleteForm"
              action="{{ route('users.bulkDelete') }}"
              method="POST"
              class="d-none"
              onsubmit="return confirm('Yakin ingin menghapus user yang dipilih?')">
            @csrf
            @method('DELETE')

            <input type="hidden" name="selected_ids" id="selected_ids">

            <x-ui.button type="submit" variant="danger" size="sm">Hapus Terpilih</x-ui.button>
        </form>

    </div>

    {{-- Table --}}
    <div class="table-responsive" style="max-height: 520px; overflow-y: auto; overflow-x: auto;">
        <table class="table bordered-table mb-0 align-middle" style="min-width: 950px; white-space: nowrap;">
            <thead>
                <tr>
                    <th style="width: 40px;">
                        <input type="checkbox" id="checkAll" class="form-check-input">
                    </th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Cabang</th>
                    <th style="width: 120px;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            @if(auth()->id() !== $user->id)
                                <input type="checkbox"
                                       class="check-item form-check-input"
                                       value="{{ $user->id }}">
                            @else
                                <input type="checkbox"
                                       class="form-check-input"
                                       disabled
                                       title="Tidak bisa memilih akun sendiri">
                            @endif
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $user->name }}
                            </div>

                            @if(auth()->id() === $user->id)
                                <x-ui.badge variant="neutral" size="sm" class="mt-1">Akun Anda</x-ui.badge>
                            @endif
                        </td>

                        <td>{{ $user->email }}</td>

                        <td>
                            @forelse ($user->roles as $role)
                                <x-ui.badge variant="primary" size="sm" class="me-1">{{ $role->name }}</x-ui.badge>
                            @empty
                                <x-ui.badge variant="neutral" size="sm">Tidak ada role</x-ui.badge>
                            @endforelse
                        </td>

                        <td>
                            @forelse ($user->branches as $branch)
                                <x-ui.badge variant="success" size="sm" class="me-1 mb-1">{{ $branch->name }}</x-ui.badge>
                            @empty
                                <x-ui.badge variant="neutral" size="sm">Tidak ada cabang</x-ui.badge>
                            @endforelse
                        </td>

                        <td>
                            <div class="d-flex gap-1">

                                {{-- View --}}
                                <a href="javascript:void(0)"
                                   class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                                   data-bs-toggle="modal"
                                   data-bs-target="#viewUserModal{{ $user->id }}">
                                    <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                </a>

                                {{-- Edit --}}
                                <a href="{{ route('users.edit', $user->id) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                {{-- Delete --}}
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Hapus user ini?')"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                                            <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                        </button>
                                    </form>
                                @else
                                    <button type="button"
                                            class="w-32-px h-32-px bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center border-0"
                                            disabled
                                            title="Tidak bisa menghapus akun sendiri">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-5">
                            <x-ui.empty-state title="User tidak ditemukan" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @slot('footer'){{ $users->withQueryString()->links() }}@endslot
</x-ui.card>


{{-- Modal Detail User --}}
@foreach ($users as $user)
    <div class="modal fade"
         id="viewUserModal{{ $user->id }}"
         tabindex="-1"
         aria-labelledby="viewUserModalLabel{{ $user->id }}"
         aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel{{ $user->id }}">
                        Detail User - {{ $user->name }}
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <dl class="row">

                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8">{{ $user->name }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>

                        <dt class="col-sm-4">Role</dt>
                        <dd class="col-sm-8">
                            @forelse ($user->roles as $role)
                                <x-ui.badge variant="primary" size="sm" class="me-1">{{ $role->name }}</x-ui.badge>
                            @empty
                                <x-ui.badge variant="neutral" size="sm">Tidak ada role</x-ui.badge>
                            @endforelse
                        </dd>

                        <dt class="col-sm-4">Cabang</dt>
                        <dd class="col-sm-8">
                            @forelse ($user->branches as $branch)
                                <x-ui.badge variant="success" size="sm" class="me-1 mb-1">{{ $branch->name }}</x-ui.badge>
                            @empty
                                <x-ui.badge variant="neutral" size="sm">Tidak ada cabang</x-ui.badge>
                            @endforelse
                        </dd>

                        <dt class="col-sm-4">Total Cabang</dt>
                        <dd class="col-sm-8">
                            {{ $user->branches->count() }}
                        </dd>

                        <dt class="col-sm-4">Tanggal Dibuat</dt>
                        <dd class="col-sm-8">
                            {{ $user->created_at ? $user->created_at->format('d M Y H:i') : '-' }}
                        </dd>

                        <dt class="col-sm-4">Terakhir Diupdate</dt>
                        <dd class="col-sm-8">
                            {{ $user->updated_at ? $user->updated_at->format('d M Y H:i') : '-' }}
                        </dd>

                    </dl>
                </div>

            </div>
        </div>
    </div>
@endforeach


{{-- Script Bulk Checkbox --}}
@include('partials.bulk-delete-script')
@endsection
