@extends('dashboard.index')
@section('title','Edit Karyawan')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Edit Karyawan</h5>
    <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
  </div>
  <div class="card-body">
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
      </div>
    @endif

    <form method="post" action="{{ route('employees.update',$employee) }}">
      @csrf @method('PUT')
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control" value="{{ old('name',$employee->name) }}" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Posisi (opsional)</label>
        <input type="text" name="job_position" class="form-control" value="{{ old('job_position',$employee->job_position) }}">
      </div>

      <button class="btn btn-primary">Update</button>
    </form>
  </div>
</div>
@endsection
