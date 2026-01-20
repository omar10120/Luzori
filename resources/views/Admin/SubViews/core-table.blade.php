@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('Admin.Components.datatable-css')
@endsection

@section('content')
    <div class="container">
        
        @if (\Session::has('success'))
            <div class="alert alert-success">
                <div>{!! \Session::get('success') !!}</div>
            </div>
        @endif
        @if (\Session::has('error'))
            <div class="alert alert-danger">
                <div>{!! \Session::get('error') !!}</div>
            </div>
        @endif
        
        <div class="card table-responsive">
            <div class="card-header d-flex justify-content-between">
                <h2>{{ __('general.manage') . ' ' . $title }}</h2>
            </div>
            <div class="card-body">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @include('Admin.Components.datatable-js')
@endsection

@section('page-script')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
@endsection