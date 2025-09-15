@extends('dashboard.index')
@section('title', 'Add Employee')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Tambah Karyawan</h5>
    <a href="{{ route('employee.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="post" action="{{ route('employee.store') }}">
      @csrf
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Posisi (opsional)</label>
        <select type="text" name="job_position" class="form-control" placeholder="tukang / kenek / dll">
          <option value="">-- Pilih Posisi --</option>
          <option value="Teknisi">Teknisi</option>
          <option value="Kenek">Kenek</option>
          <option value="Marketing">Marketing</option>
          <option value="lainnya">Lainnya</option>
        </select>
      </div>

      <button class="btn btn-primary">Simpan</button>
    </form>
  </div>
</div>
@endsection
