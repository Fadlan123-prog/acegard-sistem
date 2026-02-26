@extends('dashboard.index')

@section('title', 'Detail Claim')
@section('breadcrumb', 'Warranty Claims')

@section('content')
@php
  $map = [
    'submitted'=>'secondary','inspected'=>'warning','approved'=>'primary',
    'rejected'=>'danger','in_progress'=>'info','completed'=>'success','closed'=>'dark'
  ];
  $cls = $map[$claim->status] ?? 'secondary';

  $typeLabel = $claim->warranty_type === 'car_film' ? 'KACA FILM MOBIL'
    : ($claim->warranty_type === 'building_film' ? 'KACA FILM GEDUNG' : 'PPF');
@endphp

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="card-title mb-0">Claim {{ $claim->claim_number }}</h5>
      <div class="small text-muted">Tipe: {{ $typeLabel }} | Card: {{ $claim->card_number }}</div>
    </div>
    <a href="{{ route('admin.claims.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>

  <div class="card-body">
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="d-flex flex-wrap gap-2 mb-3">
      <span class="badge bg-{{ $cls }}">STATUS: {{ strtoupper($claim->status) }}</span>
      <span class="badge bg-light text-dark">CAUSE: {{ strtoupper($claim->cause) }}</span>
      @if($claim->is_chargeable)
        <span class="badge bg-danger">CHARGEABLE</span>
        <span class="badge bg-light text-dark">QUOTE: Rp{{ number_format($claim->quote_total,0,',','.') }}</span>
      @else
        <span class="badge bg-success">FREE</span>
      @endif
    </div>

    {{-- Section: Data Warrantable --}}
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="mb-2">Data Warranty</h6>

        @if($claim->warranty_type === 'ppf')
          @php $ins = $claim->warrantable; $c = $ins->customer; @endphp
          <div class="row g-2">
            <div class="col-md-6">
              <div><b>Nama:</b> {{ $c->name ?? '-' }}</div>
              <div><b>Plat:</b> {{ $c->vehicle_plate ?? '-' }}</div>
              <div><b>Mobil:</b> {{ $c->vehicle_brand ?? '-' }} / {{ $c->vehicle_model ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div><b>Warranty Code:</b> {{ $ins->warranty_code ?? '-' }}</div>
              <div><b>Produk:</b> {{ $ins->ppfProduct->name ?? '-' }}</div>
              <div><b>Install Date:</b> {{ $ins->install_date ?? '-' }}</div>
            </div>
          </div>
        @elseif($claim->warranty_type === 'car_film')
          @php $c = $claim->warrantable; @endphp
          <div class="row g-2">
            <div class="col-md-6">
              <div><b>Nama:</b> {{ $c->name ?? '-' }}</div>
              <div><b>Plat:</b> {{ $c->plat_number ?? '-' }}</div>
              <div><b>Mobil:</b> {{ $c->vehicle_brand ?? '-' }} / {{ $c->vehicle_model ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div><b>WSN:</b> {{ $c->wsn ?? '-' }}</div>
              <div><b>Garansi End:</b> {{ $c->warantee_end ?? '-' }}</div>
            </div>
          </div>
        @else
          @php $b = $claim->warrantable; @endphp
          <div class="row g-2">
            <div class="col-md-6">
              <div><b>Nama:</b> {{ $b->name ?? '-' }}</div>
              <div><b>Kota:</b> {{ $b->city ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div><b>Alamat:</b> {{ $b->address ?? '-' }}</div>
              <div><b>Dealer:</b> {{ $b->dealer_name ?? '-' }}</div>
            </div>
          </div>
        @endif

        <hr class="my-2">
        <div><b>Keluhan:</b></div>
        <div class="text-muted">{{ $claim->complaint }}</div>
      </div>
    </div>

    {{-- Section: INSPECTION --}}
    <div class="card mb-3">
      <div class="card-body">
        <h6 class="mb-3">Inspection</h6>

        <form method="POST" action="{{ route('admin.claims.inspect', $claim->id) }}" class="row g-3">
          @csrf
          @method('PUT')

          <div class="col-md-4">
            <label class="form-label">Cause</label>
            <select name="cause" class="form-select" required>
              @foreach(['unknown','workmanship','product','user'] as $cs)
                <option value="{{ $cs }}" {{ $claim->cause===$cs?'selected':'' }}>{{ strtoupper($cs) }}</option>
              @endforeach
            </select>
            <div class="form-text">user = chargeable (bayar).</div>
          </div>

          <div class="col-md-8">
            <label class="form-label">Inspection Notes</label>
            <input type="text" name="inspection_notes" class="form-control" value="{{ old('inspection_notes', $claim->inspection_notes) }}">
          </div>

          <div class="col-12">
            <label class="form-label">Claim Items</label>
            <div class="table-responsive">
              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th>Item</th>
                    <th style="width:140px;">Action</th>
                    <th style="width:170px;">Charge (Rp)</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                @foreach($claim->items as $it)
                  <tr>
                    <td>{{ $it->item_label ?? $it->item_key ?? '-' }}</td>
                    <td>
                      <select name="items[{{ $it->id }}][action]" class="form-select form-select-sm">
                        <option value="replace" {{ $it->action==='replace'?'selected':'' }}>Replace</option>
                        <option value="repair"  {{ $it->action==='repair'?'selected':'' }}>Repair</option>
                      </select>
                    </td>
                    <td>
                      <input type="number" min="0" class="form-control form-control-sm"
                             name="items[{{ $it->id }}][charge_amount]"
                             value="{{ old("items.$it->id.charge_amount", $it->charge_amount) }}"
                             placeholder="0">
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm"
                             name="items[{{ $it->id }}][notes]"
                             value="{{ old("items.$it->id.notes", $it->notes) }}">
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
            <div class="form-text">Jika cause bukan user, sistem akan set charge = 0.</div>
          </div>

          <div class="col-12">
            <button class="btn btn-warning">Simpan Inspection</button>
          </div>
        </form>
      </div>
    </div>

    {{-- Section: Invoice --}}
    <div class="card mb-3">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h6 class="mb-0">Invoice Claim</h6>
          <div class="text-muted small">
            @if($claim->invoice)
              Invoice: {{ $claim->invoice->invoice_number }} ({{ strtoupper($claim->invoice->status) }})
            @else
              Belum ada invoice
            @endif
          </div>
        </div>

        <div class="d-flex gap-2">
          @if($claim->invoice)
            <a href="{{ route('admin.claim-invoices.show', $claim->invoice->id) }}" class="btn btn-outline-success btn-sm">Lihat Invoice</a>
          @else
            @if($claim->is_chargeable)
              <a href="{{ route('admin.claim-invoices.create', $claim->id) }}" class="btn btn-danger btn-sm">+ Buat Invoice</a>
            @else
              <button class="btn btn-secondary btn-sm" disabled>Gratis (No Invoice)</button>
            @endif
          @endif
        </div>
      </div>
    </div>

    {{-- Section: Workflow Status --}}
    <div class="card">
      <div class="card-body">
        <h6 class="mb-3">Workflow</h6>

        <form method="POST" action="{{ route('admin.claims.status', $claim->id) }}" class="row g-3">
          @csrf
          @method('PUT')

          <div class="col-md-4">
            <label class="form-label">Ubah Status</label>
            <select name="status" class="form-select" required>
              @foreach(['approved','rejected','in_progress','completed','closed'] as $st)
                <option value="{{ $st }}">{{ strtoupper($st) }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-8">
            <label class="form-label">Resolution Notes (opsional)</label>
            <input type="text" name="resolution_notes" class="form-control" value="{{ old('resolution_notes', $claim->resolution_notes) }}">
          </div>

          <div class="col-12">
            <button class="btn btn-primary">Update Status</button>
            <small class="text-muted ms-2">
              * Approved chargeable butuh invoice dulu.
            </small>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
@endsection
