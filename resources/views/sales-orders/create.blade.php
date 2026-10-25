@extends('dashboard.index')

@section('title', 'Buat Sales Order')
@section('route-breadcrumb', 'sales-orders.index')
@section('breadcrumb', 'Buat Sales Order')

@section('content')
<form action="{{ route('sales-orders.store') }}" method="POST">
    @csrf
    @include('sales-orders._form')
</form>
@endsection
