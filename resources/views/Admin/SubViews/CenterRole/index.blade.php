@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="luzori-page container-fluid px-3 px-md-4">
        @include('Admin.Components.breadcrumbs')

        <form class="pt-0" id="frmSubmit">
            @csrf
            <input type="hidden" name="guard_name" value="center">
            <div class="card lz-form-card">
                <div class="card-header">
                    <h2>{{ $title }}</h2>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" for="role_name">
                                {{ __('field.name') }}
                                <span class="text-muted">({{ __('field.permissions_name') }})</span>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="role_name" name="name"
                                placeholder="{{ __('field.name') }}"
                                value="{{ $item ? $item->name : '' }}" required />
                        </div>
                    </div>

                    @php
                        $selectedKeys = $item
                            ? array_map('strval', array_column($item->permissions->toArray(), 'id'))
                            : [];
                    @endphp
                    @include('Admin.Components.permissions-form', [
                        'permissionGroups' => $permissions,
                        'selectedKeys' => $selectedKeys,
                        'checkBy' => 'id',
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
