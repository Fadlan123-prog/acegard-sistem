@extends('dashboard.index')

@section('title', 'Klaim Pembelian')
@section('route-breadcrumb', 'petty-cash.index')
@section('breadcrumb', 'Klaim Pembelian')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-7">
        <x-ui.card>
            @slot('header')
                <div class="d-flex justify-content-between align-items-center">
                    <div class="ui-card-title">
                        <iconify-icon icon="mdi:cart-outline" class="me-2 text-warning"></iconify-icon>
                        Klaim Pembelian / Pengeluaran
                    </div>
                </div>
                <small class="text-muted">Untuk klaim pembelian material seperti sealant, lakban, dll yang dibeli oleh teknisi</small>
            @endslot

            <form method="POST" action="{{ route('petty-cash.store-purchase') }}">
                @csrf

                <div class="row g-3">

                    <div class="col-md-6">
                        <x-ui.form-field label="Cabang" name="branch_id">
                            <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Cabang --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" @selected(old('branch_id', $branchId) == $b->id)>{{ $b->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-6">
                        <x-ui.form-field label="Tanggal Pembelian" name="date">
                            <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
                                   class="form-control @error('date') is-invalid @enderror" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Pembeli / Pengklaim" name="employee_id">
                            <select name="employee_id" class="form-select @error('employee_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" @selected(old('employee_id') == $emp->id)>
                                        {{ $emp->name }} — {{ $emp->job_position ?? '-' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Nama Barang / Item" name="item_name">
                            <input type="text" name="item_name" value="{{ old('item_name') }}"
                                   class="form-control @error('item_name') is-invalid @enderror"
                                   placeholder="cth: Sealant hitam 310ml x2, Lakban hitam, Pembersih kaca..." required>
                            @error('item_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-md-6">
                        <x-ui.form-field label="Jumlah Klaim (Rp)" name="amount">
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="amount" value="{{ old('amount') }}"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       min="1" placeholder="0" required>
                            </div>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                    <div class="col-12">
                        <x-ui.form-field label="Keterangan Tambahan" name="description">
                            <textarea name="description" rows="2"
                                      class="form-control @error('description') is-invalid @enderror"
                                      placeholder="Keperluan, nama pelanggan, nomor order, dll...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </x-ui.form-field>
                    </div>

                </div>

                <x-ui.alert type="info" class="mt-3 mb-0">
                    <iconify-icon icon="mdi:information-outline" style="font-size:18px;flex-shrink:0;margin-top:1px;"></iconify-icon>
                    <span>Pastikan menyimpan struk/bukti pembelian sebagai dokumen fisik untuk keperluan audit.</span>
                </x-ui.alert>

                <div class="d-flex gap-2 mt-4">
                    <x-ui.button variant="warning" type="submit">
                        <iconify-icon icon="mdi:check" class="me-1"></iconify-icon>Simpan Klaim
                    </x-ui.button>
                    <x-ui.button variant="secondary" href="{{ route('petty-cash.index') }}">Batal</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</div>

@endsection
