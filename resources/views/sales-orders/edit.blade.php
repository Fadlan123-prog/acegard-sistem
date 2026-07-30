@extends('dashboard.index')

@section('title', 'Edit Sales Order')
@section('route-breadcrumb', 'sales-orders.index')
@section('breadcrumb', 'Edit Sales Order')

@section('content')
<form action="{{ route('sales-orders.update', $salesOrder) }}" method="POST">
    @csrf @method('PUT')
    @include('sales-orders._form')
</form>
@endsection
