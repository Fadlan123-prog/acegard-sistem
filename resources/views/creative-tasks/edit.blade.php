@extends('dashboard.index')

@section('title', 'Ubah Tugas')
@section('route-breadcrumb', 'creative-tasks.index')
@section('breadcrumb', 'Ubah Tugas')

@section('content')

<x-ui.card>
    @if($task->status === 'review' || $task->deliverables()->exists())
        <x-ui.alert type="info" class="mb-3" :dismissible="false">
            Tugas ini sudah punya hasil kerja. Mengubah brief atau deadline akan memberi tahu penerima; mengganti penerima
            memindahkan tugas ke orang lain (hasil kerja lama tetap tersimpan di riwayat).
        </x-ui.alert>
    @endif

    <form method="POST" action="{{ route('creative-tasks.update', $task) }}">
        @csrf
        @method('PUT')

        @include('creative-tasks._form-fields')

        <div class="d-flex gap-2 mt-3">
            <x-ui.button type="submit" variant="primary">Simpan Perubahan</x-ui.button>
            <x-ui.button :href="route('creative-tasks.show', $task)" variant="secondary">Batal</x-ui.button>
        </div>
    </form>
</x-ui.card>

@endsection
