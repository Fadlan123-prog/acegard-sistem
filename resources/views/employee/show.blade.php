@extends('dashboard.index')
@section('title','Detail Karyawan')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Detail Karyawan</h5>
    <div>
      <a href="{{ route('employees.edit',$employee) }}" class="btn btn-sm btn-primary">Edit</a>
      <a href="{{ route('employees.index') }}" class="btn btn-sm btn-secondary">Kembali</a>
    </div>
  </div>
  <div class="card-body">
    <dl class="row">
      <dt class="col-sm-3">Nama</dt>
      <dd class="col-sm-9">{{ $employee->name }}</dd>

      <dt class="col-sm-3">Posisi</dt>
      <dd class="col-sm-9">{{ $employee->job_position ?: '-' }}</dd>
    </dl>
  </div>
</div>
@endsection
