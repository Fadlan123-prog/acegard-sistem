@extends('dashboard.index')

@section('title', 'Edit Complain')
@section('route-breadcrumb', 'film-complaints.index')
@section('breadcrumb', 'Edit Complain')

@section('content')

@if ($errors->any())
    <x-ui.alert type="danger" class="mb-3">
        <strong>Terjadi kesalahan.</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-ui.alert>
@endif

<x-ui.card>
    @slot('header')
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="ui-card-title">Edit Complain</div>
                <small class="text-muted">
                    {{ $complaint->complaint_number }} — item &amp; stok yang sudah dipotong tidak bisa diubah di sini.
                </small>
            </div>

            <x-ui.button :href="route('film-complaints.show', $complaint->id)" variant="secondary" size="sm">
                Kembali
            </x-ui.button>
        </div>
    @endslot

    <form action="{{ route('film-complaints.update', $complaint->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Tanggal Complain <span class="text-danger">*</span></label>
                <input type="date" name="complaint_date" class="form-control"
                       value="{{ old('complaint_date', $complaint->complaint_date?->format('Y-m-d')) }}" required>
            </div>

            <div class="col-md-8 mb-3">
                <label class="form-label">Alasan</label>
                <input type="text" name="reason" class="form-control"
                       value="{{ old('reason', $complaint->reason) }}" placeholder="Alasan complain...">
            </div>

            <div class="col-12 mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="3"
                          placeholder="Catatan tambahan...">{{ old('notes', $complaint->notes) }}</textarea>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <x-ui.button type="submit" variant="primary">Simpan Perubahan</x-ui.button>
            <x-ui.button :href="route('film-complaints.show', $complaint->id)" variant="outline">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
