@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="luzori-page container-fluid px-3 px-md-4">
        @include('Admin.Components.breadcrumbs')

        <form class="pt-0" id="frmSubmit">
            @csrf
            <input type="hidden" name="id" value="{{ $id }}">
            <div class="card lz-form-card">
                <div class="card-header">
                    <h2>{{ $title }}</h2>
                </div>
                <div class="card-body">
                    @php
                        $selectedKeys = array_map('strval', array_column($centerPermissions->toArray(), 'name'));
                    @endphp
                    @include('Admin.Components.permissions-form', [
                        'permissionGroups' => $allPermissions,
                        'selectedKeys' => $selectedKeys,
                        'checkBy' => 'name',
                        'valueBy' => 'name',
                    ])
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary submitFrom">
                        <i class="ti ti-check me-1"></i>
                        <span>{{ __('general.save') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('vendor-script')
@endsection

@section('page-script')
    @include('Admin.Components.submit-form-ajax')
@endsection
