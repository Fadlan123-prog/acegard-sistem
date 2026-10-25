@extends('dashboard.index')

@section('title', 'Edit Lead')
@section('route-breadcrumb', 'leads.index')
@section('breadcrumb', 'Edit Lead')

@section('content')
<form action="{{ route('leads.update', $lead) }}" method="POST">
    @csrf @method('PUT')
    @include('leads._form')
</form>
@endsection
