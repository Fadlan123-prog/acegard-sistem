@extends('dashboard.index')

@section('title', 'Users')
@section('route-breadcrumb', 'users.index')
@section('breadcrumb', 'Users')

@section('content')
<div class="card basic-data-table">

    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0">Daftar User</h5>

        <div class="d-flex gap-2">
            {{-- kalau mau export user, isi route ini --}}
            {{-- <a href="{{ route('users.export') }}" class="btn btn-outline-success btn-sm">
                Export Excel
            </a> --}}
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">+ Add User</a>
        </div>
    </div>

    <div class="card-body">

        {{-- Search & Bulk Delete --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">

            {{-- Search + Filter Cabang --}}
            <form method="GET" action="{{ route('users.index') }}" id="filterForm"
                  class="d-flex align-items-center gap-2 flex-wrap">

                {{-- Input pencarian --}}
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control" placeholder="Cari nama atau email user..." style="max-width: 250px;">

                {{-- Tombol cari --}}
                <button type="submit" class="btn btn-outline-primary">Search</button>

                {{-- Dropdown filter cabang --}}
                <div class="col-md-3">
                    <select name="branch_id" class="form-select"
                            onchange="document.getElementById('filterForm').submit()">
                        <option value="">-- Semua Cabang --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                    {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol reset jika filter aktif --}}
                @if(request('branch_id') || request('search'))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>

            {{-- Bulk Delete (opsional, buat route users.bulkDelete kalau mau dipakai) --}}
            <form id="bulkDeleteForm" action="{{ route('users.bulkDelete') }}" method="POST" class="d-none"
                  onsubmit="return confirm('Yakin hapus user terpilih?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="selected_ids" id="selected_ids">
                <button type="submit" class="btn btn-danger btn-sm">Hapus Terpilih</button>
            </form>

        </div>


        <table class="table bordered-table mb-0">
            <thead>
                <tr>
                    <th><input type="checkbox" id="checkAll" class="form-check-input"></th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Cabang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <input type="checkbox" class="check-item form-check-input" value="{{ $user->id }}">
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            {{ $user->roles->pluck('name')->implode(', ') ?: '-' }}
                        </td>
                        <td>
                            @if ($user->branches->count())
                                {{ $user->branches->pluck('name')->implode(', ') }}
                            @else
                                <span class="badge bg-secondary">Tidak ada cabang</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                {{-- Edit --}}
                                <a href="{{ route('users.edit', $user->id) }}"
                                   class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                    <iconify-icon icon="lucide:edit"></iconify-icon>
                                </a>

                                {{-- Delete --}}
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus user ini?')" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-32-px h-32-px bg-danger-focus text-danger-main rounded-circle d-inline-flex align-items-center justify-content-center border-0">
                                        <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">User tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3">{{ $users->withQueryString()->links() }}</div>
    </div>
</div>

{{-- Script bulk checkbox --}}
<script>
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.check-item');
    const bulkForm = document.getElementById('bulkDeleteForm');
    const selectedIdsInput = document.getElementById('selected_ids');

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkButton();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', toggleBulkButton);
        });
    }

    function toggleBulkButton() {
        const selected = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selected.length > 0) {
            bulkForm.classList.remove('d-none');
            selectedIdsInput.value = selected.join(',');
        } else {
            bulkForm.classList.add('d-none');
            selectedIdsInput.value = '';
        }
    }
</script>
@endsection
