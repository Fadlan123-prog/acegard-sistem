@extends('dashboard.index')

@section('title', 'Warranty Claims')
@section('route-breadcrumb', 'admin.claims.index')
@section('breadcrumb', 'Warranty Claims')

@section('content')
<x-ui.card class="basic-data-table">
    @slot('header')
        <div class="ui-card-title">Daftar Warranty Claim</div>
    @endslot

    
    

    <form method="GET" action="{{ route('admin.claims.index') }}" class="d-flex flex-wrap gap-2 mb-3" id="filterForm">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
               placeholder="Cari claim/card/kode..." style="max-width:260px;">

        <select name="status" class="form-select" style="max-width:200px;" onchange="filterForm.submit()">
            <option value="">-- Semua Status --</option>
            @foreach(['submitted','inspected','approved','rejected','in_progress','completed','closed'] as $st)
                <option value="{{ $st }}" {{ request('status')===$st ? 'selected' : '' }}>{{ strtoupper($st) }}</option>
            @endforeach
        </select>

        <select name="type" class="form-select" style="max-width:220px;" onchange="filterForm.submit()">
            <option value="">-- Semua Tipe --</option>
            <option value="car_film" {{ request('type')==='car_film'?'selected':'' }}>Kaca Film Mobil</option>
            <option value="building_film" {{ request('type')==='building_film'?'selected':'' }}>Kaca Film Gedung</option>
            <option value="ppf" {{ request('type')==='ppf'?'selected':'' }}>PPF</option>
        </select>

        @if(auth()->user()->hasRole('superadmin'))
        <select name="branch_id" class="form-select" style="max-width:220px;" onchange="filterForm.submit()">
            <option value="">-- Semua Cabang --</option>
            @foreach($branches as $b)
                <option value="{{ $b->id }}" {{ request('branch_id')==$b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
        </select>
        @endif

        <x-ui.button type="submit" variant="primary">Search</x-ui.button>

        @if(request('branch_id') || request('search') || request('status') || request('type'))
            <x-ui.button :href="route('admin.claims.index')" variant="secondary">Reset</x-ui.button>
        @endif
    </form>

    <table class="table bordered-table mb-0">
        <thead>
            <tr>
                <th>No Claim</th>
                <th>Tipe</th>
                <th>Card Number</th>
                <th>Status</th>
                <th>Charge</th>
                <th>Invoice</th>
                <th>Tgl Claim</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($claims as $c)
            <tr>
                <td>{{ $c->claim_number }}</td>
                <td>
                    <x-ui.badge variant="neutral">
                        {{ $c->warranty_type === 'car_film' ? 'MOBIL' : ($c->warranty_type === 'building_film' ? 'BUILDING' : 'PPF') }}
                    </x-ui.badge>
                </td>
                <td>{{ $c->card_number }}</td>
                <td>
                    @php
                        $map = [
                            'submitted'=>'neutral','inspected'=>'warning','approved'=>'primary',
                            'rejected'=>'danger','in_progress'=>'neutral','completed'=>'success','closed'=>'neutral'
                        ];
                        $cls = $map[$c->status] ?? 'neutral';
                    @endphp
                    <x-ui.badge :variant="$cls">{{ strtoupper($c->status) }}</x-ui.badge>
                </td>
                <td>
                    @if($c->is_chargeable)
                        <x-ui.badge variant="danger">Chargeable</x-ui.badge>
                        <div class="small text-muted">Rp{{ number_format($c->quote_total,0,',','.') }}</div>
                    @else
                        <x-ui.badge variant="success">Free</x-ui.badge>
                    @endif
                </td>
                <td>
                    @if($c->invoice)
                        <x-ui.button :href="route('admin.claim-invoices.show', $c->invoice->id)" variant="success" size="sm">Lihat</x-ui.button>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $c->claim_date }}</td>
                <td>
                    <a href="{{ route('admin.claims.show', $c->id) }}"
                       class="w-32-px h-32-px bg-primary-light text-primary-600 rounded-circle d-inline-flex align-items-center justify-content-center"
                       title="Detail">
                        <iconify-icon icon="iconamoon:eye-light"></iconify-icon>
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="py-5"><x-ui.empty-state title="Belum ada claim." /></td></tr>
        @endforelse
        </tbody>
    </table>

    @slot('footer'){{ $claims->withQueryString()->links() }}@endslot
</x-ui.card>
@endsection
