@extends('dashboard.index')

@section('title', 'Supplier & Buyer Internasional')
@section('route-breadcrumb', 'international-partners.index')
@section('breadcrumb', 'Supplier & Buyer Internasional')

@section('content')

<x-ui.card class="mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
            <a href="{{ route('international-partners.index') }}" class="ui-btn ui-btn-sm {{ !request('type') ? 'ui-btn-primary' : 'ui-btn-secondary' }}">Semua</a>
            <a href="{{ route('international-partners.index', ['type' => 'supplier']) }}" class="ui-btn ui-btn-sm {{ request('type') == 'supplier' ? 'ui-btn-primary' : 'ui-btn-secondary' }}">Supplier</a>
            <a href="{{ route('international-partners.index', ['type' => 'buyer']) }}" class="ui-btn ui-btn-sm {{ request('type') == 'buyer' ? 'ui-btn-primary' : 'ui-btn-secondary' }}">Buyer</a>
        </div>
        <x-ui.button :href="route('international-partners.create')" variant="primary">
            <iconify-icon icon="mdi:plus-circle" class="me-1"></iconify-icon>Tambah Partner
        </x-ui.button>
    </div>
</x-ui.card>

<x-ui.card>
    @if($partners->isEmpty())
        <x-ui.empty-state icon='<iconify-icon icon="mdi:earth" style="font-size:26px;opacity:.5"></iconify-icon>' title="Belum ada partner" description="Tambahkan supplier atau buyer internasional untuk mulai membuat order." />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr><th>Nama</th><th>Tipe</th><th>Negara</th><th>Kontak</th><th>Mata Uang</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($partners as $partner)
                        <tr>
                            <td>{{ $partner->name }}</td>
                            <td><x-ui.badge :variant="$partner->type === 'supplier' ? 'info' : 'success'">{{ ucfirst($partner->type) }}</x-ui.badge></td>
                            <td>{{ $partner->country }}</td>
                            <td class="small">{{ $partner->contact_person }} @if($partner->phone) — {{ $partner->phone }} @endif</td>
                            <td>{{ $partner->default_currency }}</td>
                            <td class="text-end">
                                <a href="{{ route('international-partners.edit', $partner) }}" class="ui-btn ui-btn-sm ui-btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('international-partners.destroy', $partner) }}" class="d-inline" onsubmit="return confirm('Hapus partner ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ui-btn ui-btn-sm ui-btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $partners->links() }}</div>
    @endif
</x-ui.card>

@endsection
