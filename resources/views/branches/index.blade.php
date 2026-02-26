@extends('dashboard.index')

@section('title', 'Branches')
@section('route-breadcrumb', 'branch.index')
@section('breadcrumb', 'Branches')

@section('content')
<div class="card basic-data-table">

    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="card-title mb-0">Daftar Cabang</h5>

        <div class="d-flex gap-2">
            <a href="{{ route('branches.create') }}" class="btn btn-primary btn-sm">+ Add Branch</a>
        </div>
    </div>

    <div class="card-body">

        {{-- Search & Bulk Delete --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">

            {{-- Search --}}
            <form method="GET" action="{{ route('branches.index') }}" id="filterForm"
                  class="d-flex align-items-center gap-2 flex-wrap">

                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-control" placeholder="Cari nama / kota cabang..." style="max-width: 250px;">

                <button type="submit" class="btn btn-outline-primary">Search</button>

                @if(request('search'))
                    <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>

            {{-- Bulk Delete --}}
            <form id="bulkDeleteForm" action="{{ route('branches.bulkDelete') }}" method="POST" class="d-none"
                  onsubmit="return confirm('Yakin hapus cabang terpilih?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="selected_ids" id="selected_ids">
                <button type="submit" class="btn btn-danger btn-sm">Hapus Terpilih</button>
            </form>

        </div>

        @if(session('success'))
            <div class="alert alert-success mb-3">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger mb-3">{{ session('error') }}</div>
        @endif

        {{-- WRAPPER OVERFLOW --}}
        <div class="table-responsive" style="max-height: 520px; overflow-y: auto; overflow-x: auto;">
            <table class="table bordered-table mb-0" style="min-width: 900px; white-space: nowrap;">
                <thead>
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="checkAll" class="form-check-input">
                        </th>
                        <th>Nama Cabang</th>
                        <th>Kota</th>
                        <th>Alamat</th>
                        <th>Customer</th>
                        <th>Customer Gedung</th>
                        <th>Karyawan</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($branches as $branch)
                        <tr>
                            <td>
                                <input type="checkbox"
                                       class="check-item form-check-input"
                                       value="{{ $branch->id }}">
                            </td>
                            <td>{{ $branch->name }}</td>
                            <td>{{ $branch->city }}</td>
                            <td>{{ $branch->address }}</td>
                            <td>{{ $branch->customers_count }}</td>
                            <td>{{ $branch->customer_buildings_count }}</td>
                            <td>{{ $branch->employees_count }}</td>
                            <td>
                                <div class="d-flex gap-1">

                                    {{-- View --}}
                                    <a href="javascript:void(0)"
                                       class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                                       data-bs-toggle="modal"
                                       data-bs-target="#viewBranchModal{{ $branch->id }}">
                                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('branches.edit', $branch->id) }}"
                                       class="w-32-px h-32-px bg-success-focus text-success-main rounded-circle d-inline-flex align-items-center justify-content-center">
                                        <iconify-icon icon="lucide:edit"></iconify-icon>
                                    </a>

                                    {{-- Delete --}}
                                    <form action="{{ route('branches.destroy', $branch->id) }}" method="POST"
                                          onsubmit="return confirm('Hapus cabang ini?')" class="d-inline">
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
                        <tr>
                            <td colspan="8" class="text-center">Data cabang tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $branches->withQueryString()->links() }}</div>
    </div>
</div>

{{-- Modal View Branch --}}
@foreach ($branches as $branch)
    <div class="modal fade" id="viewBranchModal{{ $branch->id }}" tabindex="-1"
         aria-labelledby="viewBranchModalLabel{{ $branch->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Cabang</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <dl class="row">
                        <dt class="col-sm-4">Nama Cabang</dt>
                        <dd class="col-sm-8">{{ $branch->name }}</dd>

                        <dt class="col-sm-4">Kota</dt>
                        <dd class="col-sm-8">{{ $branch->city }}</dd>

                        <dt class="col-sm-4">Alamat</dt>
                        <dd class="col-sm-8">{{ $branch->address }}</dd>

                        <dt class="col-sm-4">Jumlah Customer</dt>
                        <dd class="col-sm-8">{{ $branch->customers_count }}</dd>

                        <dt class="col-sm-4">Jumlah Customer Gedung</dt>
                        <dd class="col-sm-8">{{ $branch->customer_buildings_count }}</dd>

                        <dt class="col-sm-4">Jumlah Karyawan</dt>
                        <dd class="col-sm-8">{{ $branch->employees_count }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endforeach

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
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', toggleBulkButton);
    });

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
