@extends('dashboard.index')

@section('title', 'Buat Invoice Roll Antar Cabang')
@section('route-breadcrumb', 'branch-roll-invoices.index')
@section('breadcrumb', 'Buat Invoice Roll Antar Cabang')

@section('content')

@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <strong>Terjadi kesalahan.</strong>
        <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="ui-card-title">Form Penjualan Roll ke Cabang Lain</div>
        <x-ui.button :href="route('branch-roll-invoices.index')" variant="secondary" size="sm">Kembali</x-ui.button>
    @endslot

    <form action="{{ route('branch-roll-invoices.store') }}" method="POST" id="brForm">
        @csrf
        <div class="row">
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Cabang Penjual" name="from_branch_id">
                    @if($crossBranch)
                        <select name="from_branch_id" id="fromBranch" class="form-select">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" @selected(old('from_branch_id', $fromId) == $b->id)>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" class="form-control" value="{{ $branches->firstWhere('id', $fromId)?->name }}" disabled>
                        <input type="hidden" id="fromBranch" value="{{ $fromId }}">
                    @endif
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Cabang Pembeli" name="to_branch_id" :required="true">
                    <select name="to_branch_id" id="toBranch" class="form-select @error('to_branch_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Cabang --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('to_branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    @error('to_branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Tanggal Invoice" name="invoice_date" :required="true">
                    <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                </x-ui.form-field>
            </div>
            <div class="col-md-3 mb-3">
                <x-ui.form-field label="Jatuh Tempo" name="due_date">
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </x-ui.form-field>
            </div>
        </div>

        <div class="mb-4">
            <x-ui.form-field label="Catatan" name="notes">
                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
            </x-ui.form-field>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Roll yang Dijual</h5>
            <x-ui.button type="button" variant="primary" size="sm" id="addRow">+ Tambah Roll</x-ui.button>
        </div>

        <x-ui.alert type="info" class="mb-3">
            Harga roll mengikuti daftar harga antar cabang dan tidak bisa diubah di sini. Roll tertua di gudang cabang
            penjual diambil lebih dulu. Stok penjual langsung berkurang, dan roll masuk ke stok cabang pembeli setelah
            pembeli menekan "Terima Roll".
        </x-ui.alert>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Film (stok roll utuh)</th>
                        <th style="width:120px;">Tersedia</th>
                        <th style="width:110px;">Qty <span class="text-danger">*</span></th>
                        <th style="width:170px;" class="text-end">Harga / Roll</th>
                        <th style="width:170px;" class="text-end">Total</th>
                        <th style="width:50px;"></th>
                    </tr>
                </thead>
                <tbody id="rows"></tbody>
                <tfoot>
                    <tr><th colspan="4" class="text-end">Grand Total</th><th class="text-end" id="grand">Rp0</th><th></th></tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex gap-2 mt-4">
            <x-ui.button type="submit" variant="primary">Simpan Invoice</x-ui.button>
            <x-ui.button :href="route('branch-roll-invoices.index')" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection

@php
    $poolData = $pools->map(function ($p) {
        return ['id' => $p->id, 'label' => trim($p->film_type . ' ' . $p->darkness_code), 'available' => $p->available_rolls, 'price' => $p->roll_price, 'unit' => $p->roll_unit];
    })->values();
@endphp
@push('page-scripts')
<script>
(function () {
  var poolsUrl = @json(route('branch-roll-invoices.pools'));
  var pools = @json($poolData);
  var rows = document.getElementById('rows'), grand = document.getElementById('grand'), idx = 0;
  var fromEl = document.getElementById('fromBranch');
  var rp = function (n) { return 'Rp' + Math.round(n).toLocaleString('id-ID'); };

  function options() {
    return '<option value="">-- Pilih film --</option>' + pools.map(function (p) {
      var note = p.price === null ? ' (harga belum diatur)' : '';
      return '<option value="' + p.id + '"' + (p.price === null ? ' disabled' : '') + '>' + p.label + note + '</option>';
    }).join('');
  }

  function recalc() {
    var sum = 0;
    rows.querySelectorAll('tr').forEach(function (tr) {
      var p = pools.find(function (x) { return String(x.id) === tr.querySelector('select').value; });
      var qty = parseInt(tr.querySelector('.qty').value, 10) || 0;
      tr.querySelector('.avail').textContent = p ? p.available + ' roll' : '-';
      tr.querySelector('.price').textContent = p && p.price !== null ? rp(p.price) : '-';
      var t = p && p.price !== null ? p.price * qty : 0;
      tr.querySelector('.line').textContent = rp(t);
      if (p) tr.querySelector('.qty').max = p.available;
      sum += t;
    });
    grand.textContent = rp(sum);
  }

  function addRow() {
    var i = idx++, tr = document.createElement('tr');
    tr.innerHTML = '<td><select name="items[' + i + '][film_stock_pool_id]" class="form-select" required>' + options() + '</select></td>' +
      '<td class="avail text-muted">-</td>' +
      '<td><input type="number" name="items[' + i + '][qty]" class="form-control qty" min="1" value="1" required></td>' +
      '<td class="price text-end">-</td><td class="line text-end fw-semibold">Rp0</td>' +
      '<td><button type="button" class="btn btn-sm btn-outline-danger rm" aria-label="Hapus baris">&times;</button></td>';
    rows.appendChild(tr);
    recalc();
  }

  rows.addEventListener('input', recalc);
  rows.addEventListener('change', recalc);
  rows.addEventListener('click', function (e) {
    var b = e.target.closest('.rm');
    if (b && rows.children.length > 1) { b.closest('tr').remove(); recalc(); }
  });
  document.getElementById('addRow').addEventListener('click', addRow);

  if (fromEl && fromEl.tagName === 'SELECT') {
    fromEl.addEventListener('change', function () {
      fetch(poolsUrl + '?branch_id=' + encodeURIComponent(fromEl.value), { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) { pools = data; rows.innerHTML = ''; addRow(); });
    });
  }
  addRow();
})();
</script>
@endpush
