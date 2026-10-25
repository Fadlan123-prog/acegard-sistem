@extends('dashboard.index')

@section('title', 'Laporan Komisi')
@section('route-breadcrumb', 'commission.index')
@section('breadcrumb', 'Komisi')

@section('content')

@php
    $roleConfig = [
        'installer'    => ['label' => 'Installer',    'color' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#1d4ed8', 'icon' => 'mdi:hammer-wrench'],
        'teknisi'      => ['label' => 'Teknisi',      'color' => '#f0fdf4', 'border' => '#86efac', 'text' => '#15803d', 'icon' => 'mdi:tools'],
        'kenek'        => ['label' => 'Kenek',        'color' => '#fefce8', 'border' => '#fde047', 'text' => '#a16207', 'icon' => 'mdi:account-hard-hat'],
        'detailer'     => ['label' => 'Detailer PPF', 'color' => '#f0fdfa', 'border' => '#5eead4', 'text' => '#0f766e', 'icon' => 'mdi:car-wash'],
        'marketing'    => ['label' => 'Marketing',    'color' => '#fdf4ff', 'border' => '#e9d5ff', 'text' => '#7c3aed', 'icon' => 'mdi:bullhorn'],
        'adit_override'=> ['label' => 'Override Adit','color' => '#fff7ed', 'border' => '#fdba74', 'text' => '#c2410c', 'icon' => 'mdi:star'],
        'admin'        => ['label' => 'Admin PPF',    'color' => '#f0f9ff', 'border' => '#7dd3fc', 'text' => '#0369a1', 'icon' => 'mdi:account-tie'],
    ];

    $activeSource   = $filters['source_type'] ?? '';
    $totalSemua     = $roleSummary->sum('total');
    $totalInstaller = ($roleSummary->get('installer')?->total ?? 0)
                    + ($roleSummary->get('teknisi')?->total  ?? 0)
                    + ($roleSummary->get('kenek')?->total    ?? 0)
                    + ($roleSummary->get('detailer')?->total ?? 0);
    $totalMarketing = $roleSummary->get('marketing')?->total ?? 0;
    $totalAdmin     = $roleSummary->get('admin')?->total ?? 0;
    $totalOverride  = $roleSummary->get('adit_override')?->total ?? 0;
    $cntSemua       = $roleSummary->sum('cnt');

    // Base params for tab links (strip source_type and page)
    $tabParams = array_filter($filters, fn($v, $k) => !in_array($k, ['source_type', 'per_page']) && $v, ARRAY_FILTER_USE_BOTH);
@endphp

{{-- ── ACTIVE BRANCH BADGE ── --}}
@if($branch)
<div class="d-flex align-items-center gap-2 mb-3">
    <iconify-icon icon="proicons-branch" style="font-size:16px;color:#64748b;"></iconify-icon>
    <span class="text-muted small">Menampilkan data cabang:</span>
    <span class="badge px-3 py-2 fw-semibold" style="background:#0f172a;color:#fff;font-size:.8rem;">
        {{ $branch->name }}
    </span>
    <span class="text-muted small">{{ $branch->city ?? '' }}</span>
</div>
@endif

{{-- ── SOURCE TYPE TABS ── --}}
<div class="mb-4">
    <ul class="nav nav-pills gap-1 flex-wrap" style="border-bottom:2px solid #e2e8f0;padding-bottom:8px;">
        <li class="nav-item">
            <a class="nav-link px-3 py-2 fw-semibold {{ $activeSource === '' ? 'active' : '' }}"
               href="{{ route('commission.index', $tabParams ?: []) }}" style="font-size:.85rem;">
                <iconify-icon icon="mdi:view-list" class="me-1" style="font-size:14px;"></iconify-icon>
                Semua
            </a>
        </li>
        @foreach($sourceTypes as $key => $st)
        <li class="nav-item">
            <a class="nav-link px-3 py-2 fw-semibold {{ $activeSource === $key ? 'active' : '' }}"
               href="{{ route('commission.index', array_merge($tabParams, ['source_type' => $key])) }}"
               style="font-size:.85rem;">
                <iconify-icon icon="{{ $st['icon'] }}" class="me-1" style="font-size:14px;"></iconify-icon>
                {{ $st['label'] }}
            </a>
        </li>
        @endforeach
    </ul>
</div>

{{-- ── SUMMARY CARDS ── --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Total Komisi" :value="'Rp'.number_format($totalSemua, 0, ',', '.')" color="blue" accent>
            <span>{{ $cntSemua }} transaksi</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Installer" :value="'Rp'.number_format($totalInstaller, 0, ',', '.')" color="blue" accent>
            <span>teknisi + kenek</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Marketing" :value="'Rp'.number_format($totalMarketing, 0, ',', '.')" color="purple" accent>
            <span>{{ $roleSummary->get('marketing')?->cnt ?? 0 }} transaksi</span>
        </x-ui.stats-card>
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-ui.stats-card label="Override Adit" :value="'Rp'.number_format($totalOverride, 0, ',', '.')" color="amber" accent>
            <span>{{ $roleSummary->get('adit_override')?->cnt ?? 0 }} transaksi</span>
        </x-ui.stats-card>
    </div>
</div>

<div class="row g-3">

    {{-- ── KOLOM KIRI: Filter + Tabel ── --}}
    <div class="col-xl-9">

        {{-- FILTER --}}
        <x-ui.card class="mb-3">
            <form method="GET" action="{{ route('commission.index') }}" class="row g-2 align-items-end">
                {{-- Preserve active tab --}}
                @if($activeSource)
                    <input type="hidden" name="source_type" value="{{ $activeSource }}">
                @endif
                <div class="col-md-3">
                    <label class="form-label fw-semibold small mb-1">Karyawan</label>
                    <select name="employee_id" class="form-select form-select-sm">
                        <option value="">Semua Karyawan</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" @selected(($filters['employee_id'] ?? '') == $emp->id)>
                                {{ $emp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">Peran</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">Semua Peran</option>
                        @foreach($roleConfig as $key => $cfg)
                            <option value="{{ $key }}" @selected(($filters['role'] ?? '') === $key)>{{ $cfg['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">Dari</label>
                    <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold small mb-1">Sampai</label>
                    <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-1">
                    <label class="form-label fw-semibold small mb-1">Urutan</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="date_desc"   @selected(($filters['sort'] ?? '') === 'date_desc')>Terbaru</option>
                        <option value="date_asc"    @selected(($filters['sort'] ?? '') === 'date_asc')>Terlama</option>
                        <option value="amount_desc" @selected(($filters['sort'] ?? '') === 'amount_desc')>Nominal ↓</option>
                        <option value="amount_asc"  @selected(($filters['sort'] ?? '') === 'amount_asc')>Nominal ↑</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <x-ui.button type="submit" variant="primary" size="sm" class="flex-fill">
                        <iconify-icon icon="mdi:filter" class="me-1"></iconify-icon>Filter
                    </x-ui.button>
                    <x-ui.button :href="route('commission.index', $activeSource ? ['source_type' => $activeSource] : [])" variant="secondary" size="sm">
                        <iconify-icon icon="mdi:refresh"></iconify-icon>
                    </x-ui.button>
                    <x-ui.button :href="route('commission.export', array_filter($filters))" variant="success" size="sm" title="Export Excel">
                        <iconify-icon icon="mdi:microsoft-excel"></iconify-icon>
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        {{-- INFO TOTAL --}}
        <div class="d-flex justify-content-between align-items-center mb-2 px-1">
            <div class="d-flex gap-3">
                <span class="text-muted small">
                    Total halaman: <strong class="text-dark">Rp{{ number_format($pageTotal, 0, ',', '.') }}</strong>
                </span>
                <span class="text-muted small">
                    Total filter: <strong class="text-dark">Rp{{ number_format($totalFiltered, 0, ',', '.') }}</strong>
                </span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Per hal.</span>
                <form method="GET" action="{{ route('commission.index') }}" class="d-inline">
                    @foreach(array_filter($filters, fn($v, $k) => $k !== 'per_page', ARRAY_FILTER_USE_BOTH) as $k => $v)
                        @if($v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endif
                    @endforeach
                    <select name="per_page" class="form-select form-select-sm" style="width:70px;" onchange="this.form.submit()">
                        @foreach([10, 20, 50, 100] as $n)
                            <option value="{{ $n }}" @selected(($filters['per_page'] ?? 20) == $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        {{-- TABEL --}}
        <x-ui.card>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:120px;">Tanggal</th>
                            <th>Karyawan</th>
                            <th>Customer</th>
                            <th style="width:100px;">
                                @if($activeSource === 'ppf') No. Invoice PPF
                                @elseif($activeSource === 'gedung') No. Invoice Gedung
                                @else No. Invoice
                                @endif
                            </th>
                            @if($activeSource === '')
                            <th style="width:80px;">Jenis</th>
                            @endif
                            <th style="width:110px;">Peran</th>
                            <th class="text-end" style="width:130px;">Nominal</th>
                            <th style="width:90px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $c)
                        @php
                            $rc = $roleConfig[$c->role] ?? ['label'=>$c->role,'color'=>'#f8fafc','border'=>'#e2e8f0','text'=>'#64748b','icon'=>'mdi:tag'];
                            $isActive    = (bool)($c->is_active ?? true);
                            $isMarketing = in_array($c->role, ['marketing', 'adit_override']);
                            $canToggle   = $isMarketing && (auth()->user()->hasRole('superadmin') || auth()->user()->hasRole('admin'));

                            // Determine source for this row
                            if (!empty($c->ppf_invoice_id))     $rowSource = 'ppf';
                            elseif (!empty($c->customer_id))    $rowSource = 'mobil';
                            else                                 $rowSource = 'gedung';

                            // Appropriate invoice number & date
                            $displayNo   = $rowSource === 'ppf' ? ($c->ppf_invoice_number ?? '-') : ($c->invoice_number ?? '-');
                            $displayDate = $rowSource === 'ppf' ? ($c->ppf_invoice_date ?? null) : ($c->invoice_date ?? null);
                        @endphp
                        <tr style="{{ !$isActive ? 'opacity:.5;' : '' }}">
                            <td class="ps-3 text-nowrap">
                                <div class="fw-semibold small">{{ \Carbon\Carbon::parse($c->created_at)->format('d M Y') }}</div>
                                <div class="text-muted" style="font-size:.7rem;">{{ \Carbon\Carbon::parse($c->created_at)->format('H:i') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold {{ !$isActive ? 'text-decoration-line-through text-muted' : '' }}">{{ $c->employee_name ?? '-' }}</div>
                                <div class="text-muted small">{{ $c->employee_job ?? '-' }}</div>
                            </td>
                            <td>
                                <div class="small">{{ $c->customer_name ?? '-' }}</div>
                                @if($displayDate)
                                <div class="text-muted" style="font-size:.7rem;">{{ \Carbon\Carbon::parse($displayDate)->format('d M Y') }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $displayNo }}</span>
                            </td>
                            @if($activeSource === '')
                            <td>
                                @if($rowSource === 'mobil')
                                    <x-ui.badge variant="primary" size="sm"><iconify-icon icon="mdi:car-outline" class="me-1"></iconify-icon>Mobil</x-ui.badge>
                                @elseif($rowSource === 'ppf')
                                    <x-ui.badge variant="success" size="sm"><iconify-icon icon="mdi:shield-outline" class="me-1"></iconify-icon>PPF</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning" size="sm"><iconify-icon icon="mdi:office-building-outline" class="me-1"></iconify-icon>Gedung</x-ui.badge>
                                @endif
                            </td>
                            @endif
                            <td>
                                <span class="badge fw-semibold" style="background:{{ $rc['color'] }};color:{{ $rc['text'] }};border:1px solid {{ $rc['border'] }};font-size:.7rem;">
                                    <iconify-icon icon="{{ $rc['icon'] }}" class="me-1" style="font-size:11px;"></iconify-icon>
                                    {{ $rc['label'] }}
                                </span>
                                @if($isMarketing && !$isActive)
                                <x-ui.badge variant="neutral" size="sm" class="ms-1">Nonaktif</x-ui.badge>
                                @endif
                            </td>
                            <td class="text-end fw-semibold text-nowrap">
                                Rp{{ number_format($c->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex gap-1 justify-content-end">
                                    <x-ui.button :href="route('commission.move', $c->id)" variant="secondary" size="sm" title="Pindahkan komisi ke karyawan lain">
                                        <iconify-icon icon="mdi:account-switch-outline"></iconify-icon>
                                    </x-ui.button>
                                    @if($canToggle)
                                    <form action="{{ route('commission.toggle-active', $c->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" :variant="$isActive ? 'warning' : 'success'" size="sm"
                                                title="{{ $isActive ? 'Nonaktifkan komisi ini' : 'Aktifkan kembali' }}"
                                                onclick="return confirm('{{ $isActive ? 'Nonaktifkan komisi ini?' : 'Aktifkan kembali komisi ini?' }}')">
                                            <iconify-icon icon="{{ $isActive ? 'mdi:toggle-switch' : 'mdi:toggle-switch-off' }}"></iconify-icon>
                                        </x-ui.button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $activeSource === '' ? 8 : 7 }}" class="py-5">
                                <x-ui.empty-state title="Tidak ada data komisi{{ $activeSource && isset($sourceTypes[$activeSource]) ? ' '.$sourceTypes[$activeSource]['label'] : '' }}." />
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @slot('footer')
            @if($commissions->hasPages())
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    Menampilkan {{ $commissions->firstItem() }}–{{ $commissions->lastItem() }}
                    dari {{ $commissions->total() }} data
                </small>
                {{ $commissions->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
            @endif
            @endslot
        </x-ui.card>

    </div>{{-- end col-xl-9 --}}

    {{-- ── KOLOM KANAN: Print + Ringkasan per Role ── --}}
    <div class="col-xl-3">

        {{-- PRINT KOMISI --}}
        <x-ui.card class="mb-3">
            @slot('header')
            <div class="ui-card-title">
                <iconify-icon icon="mdi:printer" class="me-2"></iconify-icon>Print Slip Komisi
            </div>
            @endslot
            <form action="{{ route('commission.print') }}" method="GET" target="_blank">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Karyawan <span class="text-danger">*</span></label>
                    <select name="employee_id" class="form-select form-select-sm" required>
                        <option value="">-- Pilih --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold small">Dari <span class="text-danger">*</span></label>
                        <input type="date" name="from" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold small">Sampai <span class="text-danger">*</span></label>
                        <input type="date" name="to" class="form-control form-control-sm" required>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold small">Potongan / Tambahan</label>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label small text-muted mb-1">Kasbon</label>
                        <input type="number" name="kasbon" class="form-control form-control-sm" value="0" min="0">
                    </div>
                    <div class="col-4">
                        <label class="form-label small text-muted mb-1">Motor</label>
                        <input type="number" name="uang_motor" class="form-control form-control-sm" value="0" min="0">
                    </div>
                    <div class="col-4">
                        <label class="form-label small text-muted mb-1">Makan</label>
                        <input type="number" name="uang_makan" class="form-control form-control-sm" value="0" min="0">
                    </div>
                </div>
                <x-ui.button type="submit" variant="primary" size="sm" class="w-100">
                    <iconify-icon icon="mdi:printer" class="me-1"></iconify-icon>Cetak PDF
                </x-ui.button>
            </form>
        </x-ui.card>

        {{-- RINGKASAN PER ROLE --}}
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">
                    <iconify-icon icon="mdi:chart-bar" class="me-2"></iconify-icon>Ringkasan Peran
                    @if($activeSource && isset($sourceTypes[$activeSource]))
                        <x-ui.badge variant="neutral" size="sm" class="ms-1">{{ $sourceTypes[$activeSource]['label'] }}</x-ui.badge>
                    @endif
                </h6>
                @if(!empty($filters['from']) || !empty($filters['to']) || !empty($filters['employee_id']))
                <small class="text-muted d-block mt-1">Sesuai filter aktif</small>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($roleSummary as $role => $summary)
                @php $rc = $roleConfig[$role] ?? ['label'=>$role,'color'=>'#f8fafc','border'=>'#e2e8f0','text'=>'#64748b','icon'=>'mdi:tag']; @endphp
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge" style="background:{{ $rc['color'] }};color:{{ $rc['text'] }};border:1px solid {{ $rc['border'] }};font-size:.7rem;">
                            <iconify-icon icon="{{ $rc['icon'] }}" class="me-1" style="font-size:10px;"></iconify-icon>
                            {{ $rc['label'] }}
                        </span>
                        <span class="text-muted small">{{ $summary->cnt }}x</span>
                    </div>
                    <span class="fw-semibold small">Rp{{ number_format($summary->total, 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="text-center text-muted py-4 small">Tidak ada data</div>
                @endforelse
                @if($roleSummary->isNotEmpty())
                <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-light">
                    <span class="fw-semibold small">Total</span>
                    <span class="fw-bold">Rp{{ number_format($totalSemua, 0, ',', '.') }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- TOP 3 MARKETING --}}
        <div class="card mt-3">
            <div class="card-header py-2">
                <h6 class="mb-0 fw-semibold" style="font-size:.85rem;">
                    <iconify-icon icon="mdi:bullhorn" class="me-1" style="color:#7c3aed;"></iconify-icon>
                    Top 3 Marketing
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($topMarketing as $i => $row)
                <div class="d-flex align-items-center gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                         style="width:28px;height:28px;font-size:.75rem;
                         background:{{ $i === 0 ? '#fef9c3' : ($i === 1 ? '#f1f5f9' : '#fff7ed') }};
                         color:{{ $i === 0 ? '#a16207' : ($i === 1 ? '#475569' : '#c2410c') }};">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-semibold text-truncate" style="font-size:.82rem;">{{ $row->employee_name ?? '-' }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ $row->cnt }} transaksi</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold" style="font-size:.82rem;color:#7c3aed;">Rp{{ number_format($row->total, 0, ',', '.') }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3 small">Belum ada data</div>
                @endforelse
            </div>
        </div>

        {{-- TOP 3 INSTALLER --}}
        <div class="card mt-3">
            <div class="card-header py-2">
                <h6 class="mb-0 fw-semibold" style="font-size:.85rem;">
                    <iconify-icon icon="mdi:hammer-wrench" class="me-1" style="color:#1d4ed8;"></iconify-icon>
                    Top 3 Installer
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($topInstaller as $i => $row)
                <div class="d-flex align-items-center gap-3 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                         style="width:28px;height:28px;font-size:.75rem;
                         background:{{ $i === 0 ? '#fef9c3' : ($i === 1 ? '#f1f5f9' : '#fff7ed') }};
                         color:{{ $i === 0 ? '#a16207' : ($i === 1 ? '#475569' : '#c2410c') }};">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-fill overflow-hidden">
                        <div class="fw-semibold text-truncate" style="font-size:.82rem;">{{ $row->employee_name ?? '-' }}</div>
                        <div class="text-muted" style="font-size:.7rem;">{{ $row->job_position ?? '-' }} &middot; {{ $row->cnt }}x</div>
                    </div>
                    <div class="text-end flex-shrink-0">
                        <div class="fw-bold" style="font-size:.82rem;color:#1d4ed8;">Rp{{ number_format($row->total, 0, ',', '.') }}</div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-3 small">Belum ada data</div>
                @endforelse
            </div>
        </div>

    </div>{{-- end col-xl-3 --}}

</div>{{-- end row --}}

@endsection
