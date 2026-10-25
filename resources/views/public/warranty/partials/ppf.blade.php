@php
  $mask = fn($s) => $s ? substr($s,0,3).'***'.substr($s,-2) : '-';
  $panelLabels = [
    'hood' => 'Kap Mesin',
    'front_bumper' => 'Bumper Depan',
    'rear_bumper' => 'Bumper Belakang',
    'fender_left' => 'Fender Kiri',
    'fender_right' => 'Fender Kanan',
    'door_fl' => 'Pintu Depan Kiri',
    'door_fr' => 'Pintu Depan Kanan',
    'door_rl' => 'Pintu Belakang Kiri',
    'door_rr' => 'Pintu Belakang Kanan',
    'mirror_left' => 'Spion Kiri',
    'mirror_right' => 'Spion Kanan',
    'roof' => 'Atap',
    'trunk' => 'Bagasi',
    'side_skirt_left' => 'Side Skirt Kiri',
    'side_skirt_right' => 'Side Skirt Kanan',
  ];
@endphp

<div class="row g-3">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-2">Customer (PPF)</h6>
        <div><b>Nama:</b> {{ $ppf->name }}</div>
        <div><b>Phone:</b> {{ $mask($ppf->phone) }}</div>
        <div><b>Email:</b> {{ $ppf->email ? '***@'.explode('@',$ppf->email)[1] : '-' }}</div>
        <div><b>Mobil:</b> {{ $ppf->vehicle_brand }} / {{ $ppf->vehicle_model }} ({{ $ppf->vehicle_year }})</div>
        <div><b>Plat:</b> {{ $ppf->vehicle_plate }}</div>
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-3">Riwayat Pemasangan PPF</h6>

        @forelse($ppf->installations as $ins)
          @php
            $installedPanels = is_array($ins->panels) ? $ins->panels : (json_decode($ins->panels ?? '[]', true) ?: []);
            $techNames = ($ins->technicians ?? collect())->pluck('name')->implode(', ');
          @endphp

          <div class="border rounded p-3 mb-3">
            <div class="d-flex justify-content-between flex-wrap gap-2">
              <div>
                <div class="fw-bold">{{ $ins->ppfProduct->name ?? 'PPF Product' }}</div>
                <div class="text-muted small">
                  Tanggal: {{ $ins->install_date }} | Warranty Code: <b>{{ $ins->warranty_code }}</b>
                </div>
                <div class="text-muted small">Team: {{ $techNames ?: '-' }}</div>
              </div>
              <div class="text-end">
                <span class="badge bg-light text-dark">End: {{ $ins->warranty_end ?? '-' }}</span>
                <span class="badge {{ ($ins->warranty_status==='active') ? 'bg-success' : 'bg-danger' }}">
                  {{ strtoupper($ins->warranty_status ?? '-') }}
                </span>
              </div>
            </div>

            <hr class="my-2">

            <form method="POST" action="{{ route('warranty.claim.store') }}" class="row g-3">
              @csrf
              <input type="hidden" name="type" value="ppf">
              <input type="hidden" name="id" value="{{ $ppf->id }}">
              <input type="hidden" name="card_number" value="{{ $card }}">
              <input type="hidden" name="installation_id" value="{{ $ins->id }}">

              <div class="col-12">
                <label class="form-label">Keluhan (untuk instalasi ini)</label>
                <textarea name="complaint" class="form-control" rows="2" required></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Pilih Panel yang Diklaim</label>
                <div class="row g-2">
                  @foreach($installedPanels as $i => $k)
                    @php $id = "ppf_panel_{$i}_{$k}"; @endphp

                    <div class="col-md-4">
                        <div class="form-check border rounded p-2">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="ppf_panels[]"
                            value="{{ $k }}"
                            id="{{ $id }}"
                        >
                        <label class="form-check-label ms-2" for="{{ $id }}" style="cursor:pointer;">
                            {{ $panelLabels[$k] ?? $k }}
                        </label>
                        </div>
                    </div>
                    @endforeach
                </div>
              </div>

              <div class="col-12">
                <button class="btn btn-primary btn-sm">Kirim Claim Instalasi Ini</button>
              </div>
            </form>
          </div>
        @empty
          <div class="text-muted">Belum ada instalasi.</div>
        @endforelse

      </div>
    </div>
  </div>
</div>
