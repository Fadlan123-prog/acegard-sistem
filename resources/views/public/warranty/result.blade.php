@extends('frontend.layout.index')

@section('title', 'Warranty Result')

@section('content')
<style>
  .warranty-hero{
    background: radial-gradient(1100px circle at 10% 0%, rgba(13,110,253,.16), transparent 55%),
                radial-gradient(900px circle at 90% 0%, rgba(25,135,84,.12), transparent 50%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 60%, #ffffff 100%);
    border-bottom: 1px solid rgba(0,0,0,.06);
  }
  .glass-card{
    border: 1px solid rgba(0,0,0,.06);
    box-shadow: 0 10px 30px rgba(0,0,0,.06);
    border-radius: 16px;
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(8px);
  }
  .soft-card{
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 16px;
    box-shadow: 0 8px 22px rgba(0,0,0,.05);
    background: #fff;
  }
  .meta-k{ font-size: .78rem; color: #6c757d; margin-bottom: 2px; }
  .meta-v{ font-weight: 600; }
  .badge-soft{
    background: #f1f5f9;
    border: 1px solid rgba(0,0,0,.06);
    color: #111827;
  }
  .btn-pill{ border-radius: 999px; padding: .35rem .65rem; }
  .claim-panels .btn{ border-radius: 999px; }
  .table-sm td, .table-sm th{ padding: .5rem; }
</style>

@php
  $mask  = fn($s) => $s ? substr($s,0,3).'***'.substr($s,-2) : '-';
  $today = now()->toDateString();

  $panelLabels = [
    'hood' => 'Hood',
    'front_bumper' => 'Front Bumper',
    'rear_bumper' => 'Rear Bumper',
    'fender_left' => 'Left Fender',
    'fender_right' => 'Right Fender',
    'door_fl' => 'Front Left Door',
    'door_fr' => 'Front Right Door',
    'door_rl' => 'Rear Left Door',
    'door_rr' => 'Rear Right Door',
    'mirror_left' => 'Left Mirror',
    'mirror_right' => 'Right Mirror',
    'roof' => 'Roof',
    'trunk' => 'Trunk',
    'side_skirt_left' => 'Left Side Skirt',
    'side_skirt_right' => 'Right Side Skirt',
  ];
@endphp

<div class="warranty-hero py-4">
  <div class="container">

    {{-- Alerts --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <div class="fw-bold mb-1">An error occurred:</div>
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <div class="mt-3">
      <div class="glass-card p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h4 class="fw-bold mb-0">Warranty Result</h4>
            <div class="text-muted small fw-semibold">
              Card Number:
              <span class="fw-semibold badge bg-success-subtle text-success">{{ $card }}</span>
            </div>
          </div>

          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary text-uppercase fw-bold">
              {{ $type === 'ppf' ? 'PPF' : ($type === 'car_film' ? 'CAR WINDOW FILM' : 'BUILDING WINDOW FILM') }}
            </span>

            <a href="{{ route('warranty.form') }}" class="btn btn-sm btn-secondary btn-pill">
              Back
            </a>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<div class="container py-4">

  {{-- =========================
      CAR WINDOW FILM
  ========================= --}}
  @if($type === 'car_film')
    @php
      /** @var \App\Models\Customer $car */
      $car = $data;

      $start = !empty($car->warantee_start) ? \Carbon\Carbon::parse($car->warantee_start) : null;
      $end   = !empty($car->warantee_end) ? \Carbon\Carbon::parse($car->warantee_end) : null;

      $isActive = $end && $end->toDateString() >= $today;
      $statusText  = $isActive ? 'ACTIVE' : 'EXPIRED';
      $statusClass = $isActive ? 'bg-success' : 'bg-danger';

      $items = $car->customerProducts ?? collect();

      $defaultCp  = $items->first()?->id ?? 0;
      $selectedCp = (int) request()->query('cp', $defaultCp);

      $signedCardUrl = function (string $routeName, string $side) use ($card, $selectedCp) {
          $params = ['cardNumber' => $card, 'side' => $side];
          if ($selectedCp) { $params['cp'] = $selectedCp; }
          return \Illuminate\Support\Facades\URL::signedRoute($routeName, $params);
      };

      $frontPreview  = $signedCardUrl('public.warranty.card.preview', 'front');
      $backPreview   = $signedCardUrl('public.warranty.card.preview', 'back');
      $frontDownload = $signedCardUrl('public.warranty.card.download', 'front');
      $backDownload  = $signedCardUrl('public.warranty.card.download', 'back');
    @endphp

    <div class="row g-3">

      {{-- Customer --}}
      <div class="col-lg-6">
        <div class="soft-card p-3 p-md-4 h-100">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h6 class="fw-bold mb-1">Customer</h6>
              <div class="text-muted small">Customer summary (Car Window Film)</div>
            </div>
            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
          </div>

          <hr class="my-3">

          <div class="row g-3">
            <div class="col-md-6">
              <div class="meta-k">Name</div>
              <div class="meta-v">{{ $car->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="meta-k">Phone</div>
              <div class="meta-v">{{ $mask($car->phone_number ?? null) }}</div>
            </div>
            <div class="col-md-12">
              <div class="meta-k">Email</div>
              <div class="meta-v">{{ !empty($car->email) ? '***@'.explode('@',$car->email)[1] : '-' }}</div>
            </div>
            <div class="col-md-12">
              <div class="meta-k">Address</div>
              <div class="meta-v">{{ $car->address ?? '-' }}</div>
            </div>
          </div>

          <div class="mt-3 d-flex flex-wrap gap-2">
            <span class="badge badge-soft">Valid From: <b>{{ $start ? $start->format('d M Y') : '-' }}</b></span>
            <span class="badge badge-soft">Valid Until: <b>{{ $end ? $end->format('d M Y') : '-' }}</b></span>
          </div>
        </div>
      </div>

      {{-- Vehicle --}}
      <div class="col-lg-6">
        <div class="soft-card p-3 p-md-4 h-100">
          <h6 class="fw-bold mb-1">Vehicle</h6>
          <div class="text-muted small">Registered vehicle information</div>

          <hr class="my-3">

          <div class="row g-3">
            <div class="col-md-6">
              <div class="meta-k">Plate</div>
              <div class="meta-v">{{ $car->plat_number ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="meta-k">Year</div>
              <div class="meta-v">{{ $car->vehicle_year ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="meta-k">Brand</div>
              <div class="meta-v">{{ $car->vehicle_brand ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="meta-k">Model</div>
              <div class="meta-v">{{ $car->vehicle_model ?? '-' }}</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Detail Pemasangan (customer_product -> parts/category_products/products) --}}
      <div class="col-12">
        <div class="soft-card p-3 p-md-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <h6 class="fw-bold mb-0">Installation Details</h6>
              <div class="text-muted small">List of installed items for this card number.</div>
            </div>
            <span class="badge badge-soft">Total items: <b>{{ $items->count() }}</b></span>
          </div>

          <hr class="my-3">

          @if($items->isEmpty())
            <div class="alert alert-warning mb-0">No installation item found (customer_product) for this card number.</div>
          @else
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead class="table-light">
                  <tr>
                    <th style="width:72px;">#</th>
                    <th>Part</th>
                    <th>Category</th>
                    <th>Product</th>
                    <th class="text-end">Warranty</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($items as $i => $it)
                    <tr>
                      <td>{{ $i+1 }}</td>
                      <td>{{ $it->part->name ?? '-' }}</td>
                      <td>
                        {{ $it->categoryProduct->name ?? '-' }}
                        {{-- @if(!empty($it->categoryProduct?->duration))
                          <span class="text-muted">({{ $it->categoryProduct->duration }} yr)</span>
                        @endif --}}
                      </td>
                      <td>{{ $it->product->name ?? '-' }}</td>
                      <td class="text-end">
                        <span class="badge badge-soft">
                          {{ $start ? $start->format('d M Y') : '-' }} → {{ $end ? $end->format('d M Y') : '-' }}
                        </span>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>

      {{-- Warranty Card --}}
      <div class="col-12">
        <div class="soft-card p-3 p-md-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <h6 class="fw-bold mb-0">Warranty Card</h6>
              <div class="text-muted small">Preview & download warranty card (front & back). Back template handled by controller (Notch UV / 4K).</div>
            </div>

            @if($items->count() > 1)
              <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Card item:</span>
                <select class="form-select form-select-sm" style="min-width:320px" id="cpPicker">
                  @foreach($items as $it)
                    @php
                      $lbl = trim(($it->part->name ?? '').' | '.($it->categoryProduct->name ?? '').' | '.($it->product->name ?? ''));
                    @endphp
                    <option value="{{ (int)$it->id }}" {{ (int)$it->id === $selectedCp ? 'selected' : '' }}>
                      {{ $lbl ?: ('Item #'.$it->id) }}
                    </option>
                  @endforeach
                </select>
              </div>
            @endif
          </div>

          <hr class="my-3">

          <div class="row g-3">
            <div class="col-lg-6">
              <div class="border rounded-3 p-2">
                <div class="small fw-semibold mb-2">Front</div>
                <img class="img-fluid rounded border" src="{{ $frontPreview }}" alt="Film Card Front">
                <div class="mt-2">
                  <a class="btn btn-sm btn-primary" href="{{ $frontDownload }}">Download Front</a>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="border rounded-3 p-2">
                <div class="small fw-semibold mb-2">Back</div>
                <img class="img-fluid rounded border" src="{{ $backPreview }}" alt="Film Card Back">
                <div class="mt-2">
                  <a class="btn btn-sm btn-outline-primary" href="{{ $backDownload }}">Download Back</a>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- Terms & Conditions (Car Film) --}}
      <div class="col-12">
        <div class="soft-card p-3 p-md-4">
          <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary">Info</span>
                <h6 class="mb-0 fw-semibold">Car Window Film Warranty Claim Terms &amp; Conditions</h6>
              </div>
              <div class="text-muted small">Claims are subject to inspection and verification by Acegard.</div>
            </div>

            <a class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" href="#tcCar" role="button" aria-expanded="false" aria-controls="tcCar">
              View Details
            </a>
          </div>

          <div class="collapse mt-3" id="tcCar">
            <div class="row g-3">
              <div class="col-lg-6">
                <div class="p-3 rounded-3 bg-light">
                  <div class="fw-semibold mb-2">A claim may be accepted if:</div>
                  <ul class="mb-0 small text-muted">
                    <li>The warranty is active and within the valid period.</li>
                    <li>Peeling/lifting edges due to confirmed installation workmanship.</li>
                    <li>Bubbles/silvering caused by installation technique (not impact damage).</li>
                    <li>Wrinkles/creases introduced during installation.</li>
                    <li>Contamination under film beyond tolerance, verified during inspection.</li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="p-3 rounded-3 bg-light">
                  <div class="fw-semibold mb-2">A claim may be rejected if:</div>
                  <ul class="mb-0 small text-muted">
                    <li>The warranty is expired/void or record cannot be verified.</li>
                    <li>Damage is caused by accident/impact, scratches, vandalism, or misuse.</li>
                    <li>Damage is caused by chemical exposure / improper cleaning tools.</li>
                    <li>The film has been removed/reinstalled/modified by a non-authorized party.</li>
                    <li><b>Yellowing/discoloration due to aging/sun/heat is not covered</b> unless explicitly stated.</li>
                  </ul>
                </div>
              </div>
              <div class="col-12">
                <div class="p-3 rounded-3 border">
                  <div class="fw-semibold mb-2">Important Notes:</div>
                  <ul class="mb-0 small text-muted">
                    <li>All claims require inspection and documentation (photos/videos).</li>
                    <li>If not covered, claim may be treated as chargeable and quotation will be provided.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- Claim Form (Car Film) --}}
      <div class="col-12">
        <div class="soft-card p-3 p-md-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <h5 class="fw-bold mb-0">Car Window Film Claim</h5>
              <div class="text-muted small">Describe the issue and select claimed items (optional).</div>
            </div>
            <span class="badge badge-soft">Items: {{ $items->count() }}</span>
          </div>

          <hr class="my-3">

          <form method="POST" action="{{ route('warranty.claim.store') }}" class="row g-3">
            @csrf
            <input type="hidden" name="type" value="car_film">
            <input type="hidden" name="id" value="{{ $car->id }}">
            <input type="hidden" name="card_number" value="{{ $card }}">

            <div class="col-12">
              <label class="form-label fw-semibold">Complaint</label>
              <textarea name="complaint" class="form-control" rows="3" required
                placeholder="Example: bubbling on rear window / edges peeling / haze appears ..."></textarea>
            </div>

            <div class="col-12">
              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <label class="form-label fw-semibold mb-0">Select Claimed Items (optional)</label>
                <div class="d-flex gap-2">
                  <button type="button" class="btn btn-outline-secondary btn-sm btn-pill js-select-all" data-scope="car-items">Select All</button>
                  <button type="button" class="btn btn-outline-secondary btn-sm btn-pill js-clear" data-scope="car-items">Clear</button>
                </div>
              </div>

              @if($items->isEmpty())
                <div class="alert alert-warning mt-2 mb-0">No item history found for this card number.</div>
              @else
                <div class="claim-panels d-flex flex-wrap gap-2 mt-2" data-scope="car-items">
                  @foreach($items as $row)
                    @php
                      $pid = "car_item_{$row->id}";
                      $label = trim(($row->part->name ?? '').' | '.($row->categoryProduct->name ?? '').' | '.($row->product->name ?? ''));
                    @endphp

                    <input class="btn-check" type="checkbox" name="car_items[]" value="{{ (int)$row->id }}" id="{{ $pid }}">
                    <label class="btn btn-outline-primary btn-sm" for="{{ $pid }}">
                      {{ $label ?: ('Item #'.$row->id) }}
                    </label>
                  @endforeach
                </div>

                <div class="text-muted small mt-2">
                  * If you don’t select any item, the claim will be submitted as a general claim.
                </div>
              @endif
            </div>

            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-primary btn-pill px-4">Submit Claim</button>
            </div>
          </form>
        </div>
      </div>

    </div>

    <script>
      (function(){
        const picker = document.getElementById('cpPicker');
        if(!picker) return;

        picker.addEventListener('change', () => {
          const url = new URL(window.location.href);
          url.searchParams.set('cp', picker.value);
          window.location.href = url.toString();
        });
      })();
    </script>
  @endif


  {{-- =========================
      BUILDING WINDOW FILM
  ========================= --}}
  @if($type === 'building_film')
    @php
    /** @var \App\Models\CustomerBuilding $b */
    $b = $data;

    $items = $b->customerBuildingProducts ?? collect();

    $getStart = function ($row) {
        return !empty($row->warantee_start)
            ? \Carbon\Carbon::parse($row->warantee_start)
            : null;
    };

    $getEnd = function ($row) use ($getStart) {
        $start = $getStart($row);

        $end = !empty($row->warantee_end)
            ? \Carbon\Carbon::parse($row->warantee_end)
            : null;

        if (!$end && $start && !empty($row->warantee_duration)) {
            $end = $start->copy()->addYears((int) $row->warantee_duration);
        }

        return $end;
    };

    $activeItems = $items->filter(function ($row) use ($getEnd) {
        $end = $getEnd($row);
        return $end && $end->copy()->endOfDay()->gte(now());
    });

    $validStarts = $items->map(fn($row) => $getStart($row))->filter();
    $validEnds   = $items->map(fn($row) => $getEnd($row))->filter();

    $start = $validStarts->isNotEmpty()
        ? $validStarts->sortBy(fn($date) => $date->timestamp)->first()
        : null;

    $end = $validEnds->isNotEmpty()
        ? $validEnds->sortByDesc(fn($date) => $date->timestamp)->first()
        : null;

    $isActive = $activeItems->isNotEmpty();
    $statusText = $isActive ? 'ACTIVE' : 'EXPIRED';
    $statusClass = $isActive ? 'bg-success' : 'bg-danger';
    @endphp

    <div class="row g-3">

      <div class="col-lg-6">
        <div class="soft-card p-3 p-md-4 h-100">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h6 class="fw-bold mb-1">Customer</h6>
              <div class="text-muted small">Customer summary (Building Window Film)</div>
            </div>
            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
          </div>

          <hr class="my-3">

          <div class="row g-3">
            <div class="col-md-12">
              <div class="meta-k">Name</div>
              <div class="meta-v">{{ $b->name ?? '-' }}</div>
            </div>
            <div class="col-md-12">
              <div class="meta-k">City</div>
              <div class="meta-v">{{ $b->city ?? '-' }}</div>
            </div>
            <div class="col-md-12">
              <div class="meta-k">Address</div>
              <div class="meta-v">{{ $b->address ?? '-' }}</div>
            </div>
          </div>

          {{-- <div class="mt-3 d-flex flex-wrap gap-2">
            <span class="badge badge-soft">Valid From: <b>{{ $start ? $start->format('d M Y') : '-' }}</b></span>
            <span class="badge badge-soft">Valid Until: <b>{{ $end ? $end->format('d M Y') : '-' }}</b></span>
          </div> --}}
        </div>
      </div>

      <div class="col-lg-6">
        <div class="soft-card p-3 p-md-4 h-100">
            <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
                <h6 class="fw-bold mb-1">Specification</h6>
                <div class="text-muted small">Used Window Film</div>
            </div>
            <span class="badge badge-soft">Total: <b>{{ $items->count() }}</b></span>
            </div>

            <hr class="my-3">

            @if($items->isEmpty())
            <div class="alert alert-warning mb-0">
                No building film specification found for this card number.
            </div>
            @else
            <div class="d-flex flex-column gap-2">
                @foreach($items as $i => $item)
                @php
                    $itemStart = $getStart($item);
                    $itemEnd   = $getEnd($item);

                    $itemActive = $itemEnd && $itemEnd->copy()->endOfDay()->gte(now());
                    $itemStatusText = $itemActive ? 'ACTIVE' : 'EXPIRED';
                    $itemStatusClass = $itemActive ? 'bg-success' : 'bg-danger';

                    $typeName = $item->categoryProductBuilding->name
                        ?? $item->category->name
                        ?? '-';

                    $productName = $item->product->name ?? '-';

                    $meters = !empty($item->meters)
                        ? number_format((float) $item->meters, 2) . ' m²'
                        : '-';
                @endphp

                <div class="border rounded-3 p-3">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="small text-muted">Type</div>
                        <div class="fw-bold">{{ $typeName }}</div>
                    </div>

                    <span class="badge {{ $itemStatusClass }}">
                        {{ $itemStatusText }}
                    </span>
                    </div>

                    <div class="row g-2 mt-2">
                    <div class="col-md-6">
                        <div class="meta-k">Product</div>
                        <div class="meta-v">{{ $productName }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="meta-k">Size</div>
                        <div class="meta-v">{{ $meters }}</div>
                    </div>

                    <div class="col-md-6">
                        <div class="meta-k">Valid From</div>
                        <div class="meta-v">
                        {{ $itemStart ? $itemStart->format('d M Y') : '-' }}
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="meta-k">Valid Until</div>
                        <div class="meta-v">
                        {{ $itemEnd ? $itemEnd->format('d M Y') : '-' }}
                        </div>
                    </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        </div>

      {{-- <div class="col-lg-6">
        <div class="soft-card p-3 p-md-4 h-100">
          <h6 class="fw-bold mb-1">Warranty Card</h6>
          <div class="text-muted small">Preview & download warranty card (front & back).</div>

          <hr class="my-3">

          <div class="row g-3">
            <div class="col-6">
              <div class="border rounded-3 p-2">
                <div class="small fw-semibold mb-2">Front</div>
                <img class="img-fluid rounded border" src="{{ $frontPreview }}" alt="Warranty Card Front">
                <div class="mt-2">
                  <a class="btn btn-sm btn-primary" href="{{ $frontDownload }}">Download</a>
                </div>
              </div>
            </div>
            <div class="col-6">
              <div class="border rounded-3 p-2">
                <div class="small fw-semibold mb-2">Back</div>
                <img class="img-fluid rounded border" src="{{ $backPreview }}" alt="Warranty Card Back">
                <div class="mt-2">
                  <a class="btn btn-sm btn-outline-primary" href="{{ $backDownload }}">Download</a>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div> --}}

      {{-- Terms & Conditions (Building Film) --}}
      <div class="col-12">
        <div class="soft-card p-3 p-md-4">
          <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary">Info</span>
                <h6 class="mb-0 fw-semibold">Building Window Film Warranty Claim Terms &amp; Conditions</h6>
              </div>
              <div class="text-muted small">Claims are subject to inspection and verification by Acegard.</div>
            </div>

            <a class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" href="#tcBld" role="button" aria-expanded="false" aria-controls="tcBld">
              View Details
            </a>
          </div>

          <div class="collapse mt-3" id="tcBld">
            <div class="row g-3">
              <div class="col-lg-6">
                <div class="p-3 rounded-3 bg-light">
                  <div class="fw-semibold mb-2">A claim may be accepted if:</div>
                  <ul class="mb-0 small text-muted">
                    <li>The warranty is active and within the valid period.</li>
                    <li>Peeling/lifting edges due to confirmed installation workmanship.</li>
                    <li>Bubbles/silvering caused by installation technique (not impact damage).</li>
                    <li>Wrinkles/creases introduced during installation.</li>
                    <li>Contamination under film beyond tolerance, verified during inspection.</li>
                  </ul>
                </div>
              </div>
              <div class="col-lg-6">
                <div class="p-3 rounded-3 bg-light">
                  <div class="fw-semibold mb-2">A claim may be rejected if:</div>
                  <ul class="mb-0 small text-muted">
                    <li>The warranty is expired/void or record cannot be verified.</li>
                    <li>Glass damage/cracks, impact damage, vandalism, or misuse.</li>
                    <li>Damage from harsh chemicals/solvents, scraping tools, or improper cleaning methods.</li>
                    <li>The film has been removed/reinstalled/modified by a non-authorized party.</li>
                    <li>Yellowing/discoloration due to aging/sun/heat is not covered unless explicitly stated.</li>
                  </ul>
                </div>
              </div>
              <div class="col-12">
                <div class="p-3 rounded-3 border">
                  <div class="fw-semibold mb-2">Important Notes:</div>
                  <ul class="mb-0 small text-muted">
                    <li>All claims require inspection and documentation (photos/videos).</li>
                    <li>If not covered, claim may be treated as chargeable and quotation will be provided.</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      {{-- Claim Form (Building Film) --}}
      <div class="col-12">
        <div class="soft-card p-3 p-md-4">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
              <h5 class="fw-bold mb-0">Building Window Film Claim</h5>
              <div class="text-muted small">Describe the issue and submit your claim.</div>
            </div>
          </div>

          <hr class="my-3">

          <form method="POST" action="{{ route('warranty.claim.store') }}" class="row g-3">
            @csrf
            <input type="hidden" name="type" value="building_film">
            <input type="hidden" name="id" value="{{ $b->id }}">
            <input type="hidden" name="card_number" value="{{ $card }}">

            <div class="col-12">
              <label class="form-label fw-semibold">Complaint</label>
              <textarea name="complaint" class="form-control" rows="3" required
                placeholder="Example: bubbles on window A / peeling at edges / haze appears ..."></textarea>
            </div>

            <div class="col-12 d-flex justify-content-end">
              <button class="btn btn-primary btn-pill px-4">Submit Claim</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  @endif


  {{-- =========================
      PPF
  ========================= --}}
  @if($type === 'ppf')
    @php
      /** @var \App\Models\CustomerPpf $ppf */
      $ppf = $data;

      $installs = ($ppf->installations ?? collect())->sortByDesc('install_date')->values();
      $latest   = $installs->first();

      $isActive = $latest
        && ($latest->warranty_status ?? 'active') === 'active'
        && !empty($latest->warranty_end)
        && $latest->warranty_end >= $today;

      $statusText  = $isActive ? 'ACTIVE' : 'EXPIRED';
      $statusClass = $isActive ? 'bg-success' : 'bg-danger';
    @endphp

    <div class="row g-3">

      {{-- Customer --}}
      <div class="col-lg-6">
        <div class="soft-card p-3 p-md-4 h-100">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h6 class="fw-bold mb-1">Customer</h6>
              <div class="text-muted small">Customer summary (PPF)</div>
            </div>
            <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
          </div>

          <hr class="my-3">

          <div class="row g-3">
            <div class="col-md-6">
              <div class="meta-k">Name</div>
              <div class="meta-v">{{ $ppf->name ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="meta-k">Phone</div>
              <div class="meta-v">{{ $mask($ppf->phone ?? null) }}</div>
            </div>
            <div class="col-md-12">
              <div class="meta-k">Email</div>
              <div class="meta-v">{{ !empty($ppf->email) ? '***@'.explode('@',$ppf->email)[1] : '-' }}</div>
            </div>
            <div class="col-md-12">
              <div class="meta-k">Address</div>
              <div class="meta-v">{{ $ppf->address ?? '-' }}, {{ $ppf->city ?? '-' }}, {{ $ppf->country ?? 'Indonesia' }}</div>
            </div>
          </div>

          @if($latest && $latest->warranty_end)
            <div class="mt-3 d-flex flex-wrap gap-2">
              <span class="badge badge-soft">
                Warranty valid until: <b>{{ \Carbon\Carbon::parse($latest->warranty_end)->format('d M Y') }}</b>
              </span>
              <span class="badge badge-soft">
                Total installations: <b>{{ $installs->count() }}</b>
              </span>
            </div>
          @endif
        </div>
      </div>

      {{-- Vehicle --}}
      <div class="col-lg-6">
        <div class="soft-card p-3 p-md-4 h-100">
          <h6 class="fw-bold mb-1">Vehicle</h6>
          <div class="text-muted small">Registered vehicle information</div>

          <hr class="my-3">

          <div class="row g-3">
            <div class="col-md-6">
              <div class="meta-k">Plate</div>
              <div class="meta-v">{{ $ppf->vehicle_plate ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="meta-k">Year</div>
              <div class="meta-v">{{ $ppf->vehicle_year ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="meta-k">Brand</div>
              <div class="meta-v">{{ $ppf->vehicle_brand ?? '-' }}</div>
            </div>
            <div class="col-md-6">
              <div class="meta-k">Model</div>
              <div class="meta-v">{{ $ppf->vehicle_model ?? '-' }}</div>
            </div>
            <div class="col-md-12">
              <div class="meta-k">Color</div>
              <div class="meta-v">{{ $ppf->vehicle_color ?? '-' }}</div>
            </div>
          </div>
        </div>
      </div>

      {{-- Latest Card Preview --}}
      @if($latest)
        <div class="col-12">
          <div class="soft-card p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <div>
                <h6 class="fw-bold mb-0">Warranty Card (Latest Installation)</h6>
                <div class="text-muted small">
                  Preview & download card for the latest installation. (Back template handled by controller: Aegis/Aegis Plus vs Aegis Matte).
                </div>
              </div>
              <span class="badge badge-soft">Installation ID: {{ $latest->id }}</span>
            </div>

            <hr class="my-3">

            <div class="row g-3">
              <div class="col-lg-6">
                <div class="border rounded-3 p-2">
                  <div class="small fw-semibold mb-2">Front</div>
                  <img class="img-fluid rounded border"
                       src="{{ \Illuminate\Support\Facades\URL::signedRoute('public.warranty.ppf.preview', ['installationId' => $latest->id, 'side' => 'front', 'v' => time()]) }}"
                       alt="PPF Card Front">
                  <div class="mt-2">
                    <a class="btn btn-sm btn-primary"
                       href="{{ \Illuminate\Support\Facades\URL::signedRoute('public.warranty.ppf.download', ['installationId' => $latest->id, 'side' => 'front']) }}">
                      Download Front
                    </a>
                  </div>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="border rounded-3 p-2">
                  <div class="small fw-semibold mb-2">Back</div>
                  <img class="img-fluid rounded border"
                       src="{{ \Illuminate\Support\Facades\URL::signedRoute('public.warranty.ppf.preview', ['installationId' => $latest->id, 'side' => 'back', 'v' => time()]) }}"
                       alt="PPF Card Back">
                  <div class="mt-2">
                    <a class="btn btn-sm btn-outline-primary"
                       href="{{ \Illuminate\Support\Facades\URL::signedRoute('public.warranty.ppf.download', ['installationId' => $latest->id, 'side' => 'back']) }}">
                      Download Back
                    </a>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      @endif

      {{-- Terms & Conditions (PPF) --}}
      <div class="col-12">
        <div class="soft-card p-3 p-md-4">
          <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary-subtle text-primary">Info</span>
                <h6 class="mb-0 fw-semibold">PPF Warranty Claim Terms &amp; Conditions</h6>
              </div>
              <div class="text-muted small">Claims are subject to inspection. Coverage applies only to confirmed workmanship defects.</div>
            </div>

            <a class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" href="#tcPpf" role="button" aria-expanded="false" aria-controls="tcPpf">
              View Details
            </a>
          </div>

          <div class="collapse mt-3" id="tcPpf">
            <div class="row g-3">
              <div class="col-lg-6">
                <div class="p-3 rounded-3 bg-light">
                  <div class="fw-semibold mb-2">A claim may be accepted if:</div>
                  <ul class="mb-0 small text-muted">
                    <li>Peeling/lifting edges due to confirmed installation workmanship or adhesion failure.</li>
                    <li>Bubbles/silvering caused by installation technique (not impact damage).</li>
                    <li>Wrinkles/creases introduced during installation.</li>
                    <li>Misalignment/poor fitment (coverage not aligned to installed panel record).</li>
                    <li>Contamination under film beyond tolerance, verified during inspection.</li>
                  </ul>
                </div>
              </div>

              <div class="col-lg-6">
                <div class="p-3 rounded-3 bg-light">
                  <div class="fw-semibold mb-2">A claim may be rejected if:</div>
                  <ul class="mb-0 small text-muted">
                    <li>The warranty is expired/void or record cannot be verified.</li>
                    <li>Damage is caused by accident/impact, scratches, vandalism, or misuse.</li>
                    <li>Damage is caused by chemicals / improper washing tools / conditions outside normal use.</li>
                    <li>The claimed panel is not part of the installation record.</li>
                    <li>The film has been repaired/removed/reinstalled/modified by a non-authorized party.</li>
                  </ul>
                </div>
              </div>

              <div class="col-12">
                <div class="p-3 rounded-3 border">
                  <div class="fw-semibold mb-2">Important Notes:</div>
                  <ul class="mb-0 small text-muted">
                    <li>All claims require inspection and verification by Acegard before approval.</li>
                    <li>If not covered, claim may be treated as chargeable and quotation will be provided.</li>
                    <li>By submitting a claim, customer agrees to inspection and documentation (photos/videos).</li>
                  </ul>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>

    </div>

    {{-- Installation History + Claim per Installation --}}
    <div class="soft-card p-3 p-md-4 mt-3">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h5 class="fw-bold mb-0">PPF Installation History</h5>
          <div class="text-muted small">Select an installation, describe the issue, and choose panels to claim.</div>
        </div>
        <span class="badge badge-soft">Total: {{ $installs->count() }}</span>
      </div>

      <hr class="my-3">

      @if($installs->isEmpty())
        <div class="text-muted">No installations found.</div>
      @else
        <div class="accordion" id="ppfInstallAcc">
          @foreach($installs as $idx => $ins)
            @php
              $installedPanels = is_array($ins->panels) ? $ins->panels : (json_decode($ins->panels ?? '[]', true) ?: []);
              $techNames = ($ins->technicians ?? collect())->pluck('name')->implode(', ');

              $activeRow = ($ins->warranty_status ?? 'active') === 'active'
                && !empty($ins->warranty_end)
                && $ins->warranty_end >= $today;

              $rowClass = $activeRow ? 'bg-success' : 'bg-danger';
              $rowText  = $activeRow ? 'ACTIVE' : 'EXPIRED';

              $accId = "ppfAcc{$ins->id}";
              $collapseId = "ppfCollapse{$ins->id}";
              $open = $idx === 0;
              $scope = "ppf-{$ins->id}";
            @endphp

            <div class="accordion-item border-0 mb-3">
              <h2 class="accordion-header" id="head{{ $accId }}">
                <button class="accordion-button {{ $open ? '' : 'collapsed' }} rounded-3"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}"
                        aria-expanded="{{ $open ? 'true' : 'false' }}"
                        aria-controls="{{ $collapseId }}">
                  <div class="w-100 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                      <div class="fw-bold">
                        {{ $ins->ppfProduct->name ?? 'PPF Product' }}
                        <span class="badge ms-2 {{ $rowClass }}">{{ $rowText }}</span>
                      </div>
                      <div class="text-muted small">
                        Date: {{ \Carbon\Carbon::parse($ins->install_date)->format('d M Y') }}
                        @if($ins->warranty_end)
                          • End: {{ \Carbon\Carbon::parse($ins->warranty_end)->format('d M Y') }}
                        @endif
                        @if($techNames)
                          • Team: {{ $techNames }}
                        @endif
                      </div>
                    </div>

                    <div class="d-flex gap-2">
                      <button type="button" class="btn btn-sm btn-outline-primary btn-pill" data-bs-toggle="modal" data-bs-target="#ppfCardModal{{ $ins->id }}">
                        View Card
                      </button>
                    </div>
                  </div>
                </button>
              </h2>

              <div id="{{ $collapseId }}"
                   class="accordion-collapse collapse {{ $open ? 'show' : '' }}"
                   aria-labelledby="head{{ $accId }}"
                   data-bs-parent="#ppfInstallAcc">

                <div class="accordion-body p-0 mt-2">
                  <div class="p-3 p-md-4 border rounded-3">

                    <form method="POST" action="{{ route('warranty.claim.store') }}" class="row g-3">
                      @csrf
                      <input type="hidden" name="type" value="ppf">
                      <input type="hidden" name="id" value="{{ $ppf->id }}">
                      <input type="hidden" name="card_number" value="{{ $card }}">
                      <input type="hidden" name="installation_id" value="{{ $ins->id }}">

                      <div class="col-12">
                        <label class="form-label fw-semibold">Complaint (for this installation)</label>
                        <textarea name="complaint" class="form-control" rows="3" required
                          placeholder="Example: PPF is peeling on ... / bubbles appeared ..."></textarea>
                      </div>

                      <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                          <label class="form-label fw-semibold mb-0">Select Claimed Panels</label>
                          <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-pill js-select-all" data-scope="{{ $scope }}">Select All</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm btn-pill js-clear" data-scope="{{ $scope }}">Clear</button>
                          </div>
                        </div>

                        @if(empty($installedPanels))
                          <div class="alert alert-warning mt-2 mb-0">No panel data found for this installation.</div>
                        @else
                          <div class="claim-panels d-flex flex-wrap gap-2 mt-2" data-scope="{{ $scope }}">
                            @foreach($installedPanels as $i => $k)
                              @php $pid = "ppf_panel_{$ins->id}_{$i}"; @endphp
                              <input class="btn-check" type="checkbox" name="ppf_panels[]" value="{{ $k }}" id="{{ $pid }}">
                              <label class="btn btn-outline-primary btn-sm" for="{{ $pid }}">
                                {{ $panelLabels[$k] ?? $k }}
                              </label>
                            @endforeach
                          </div>

                          <div class="text-muted small mt-2">
                            * Only panels included in this installation record are shown.
                          </div>
                        @endif
                      </div>

                      <div class="col-12 d-flex justify-content-end">
                        <button class="btn btn-primary btn-pill px-4">Submit Claim for This Installation</button>
                      </div>
                    </form>

                  </div>
                </div>

              </div>
            </div>

            {{-- Modal: PPF Card Preview + Download (Front + Back) --}}
            <div class="modal fade" id="ppfCardModal{{ $ins->id }}" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">PPF Warranty Card - Installation #{{ $ins->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>

                  <div class="modal-body">
                    <div class="row g-3">
                      <div class="col-md-6">
                        <div class="border rounded-3 p-2">
                          <div class="small fw-semibold mb-2">Front</div>
                          <img class="img-fluid rounded border"
                               src="{{ \Illuminate\Support\Facades\URL::signedRoute('public.warranty.ppf.preview', ['installationId' => $ins->id, 'side' => 'front', 'v' => time()]) }}"
                               alt="PPF Card Front">
                          <div class="mt-2">
                            <a class="btn btn-sm btn-primary"
                               href="{{ \Illuminate\Support\Facades\URL::signedRoute('public.warranty.ppf.download', ['installationId' => $ins->id, 'side' => 'front']) }}">
                              Download Front
                            </a>
                          </div>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <div class="border rounded-3 p-2">
                          <div class="small fw-semibold mb-2">Back</div>
                          <img class="img-fluid rounded border"
                               src="{{ \Illuminate\Support\Facades\URL::signedRoute('public.warranty.ppf.preview', ['installationId' => $ins->id, 'side' => 'back', 'v' => time()]) }}"
                               alt="PPF Card Back">
                          <div class="mt-2">
                            <a class="btn btn-sm btn-outline-primary"
                               href="{{ \Illuminate\Support\Facades\URL::signedRoute('public.warranty.ppf.download', ['installationId' => $ins->id, 'side' => 'back']) }}">
                              Download Back
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            </div>

          @endforeach
        </div>
      @endif
    </div>
  @endif

</div>

<script>
(function(){
  function scopeWrap(scope){
    return document.querySelector('.claim-panels[data-scope="'+scope+'"]');
  }

  document.addEventListener('click', function(e){
    const allBtn = e.target.closest('.js-select-all');
    const clrBtn = e.target.closest('.js-clear');

    if(allBtn){
      const scope = allBtn.getAttribute('data-scope');
      const wrap = scopeWrap(scope);
      if(!wrap) return;
      wrap.querySelectorAll('input[type="checkbox"]').forEach(chk => chk.checked = true);
    }

    if(clrBtn){
      const scope = clrBtn.getAttribute('data-scope');
      const wrap = scopeWrap(scope);
      if(!wrap) return;
      wrap.querySelectorAll('input[type="checkbox"]').forEach(chk => chk.checked = false);
    }
  });
})();
</script>
@endsection
