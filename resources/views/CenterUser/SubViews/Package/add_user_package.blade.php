@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="luzori-page lz-package-add-user-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-user-plus"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }} <span class="text-muted fs-5">({{ $package->name }})</span></h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <input type="hidden" name="package_id" value="{{ $package->id }}">
            <div class="card lz-form-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="user" class="lz-field-label">{{ __('field.users') }}</label>
                            <select class="select2 form-select" name="user_id" id="user">
                                <option value="">{{ __('general.choose') }}</option>
                                @if ($users && $users->count() > 0)
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>{{ __('general.no_users') }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="package_type" class="lz-field-label">{{ __('field.type') }}</label>
                            <select class="form-select" name="package_type" id="package_type">
                                <option value="">{{ __('admin.Choose Type') }}</option>
                                @foreach ($paymentMethods as $paymentMethod)
                                    <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.packages.index') }}" class="btn btn-luzori-beige">
                        {{ __('general.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-luzori-primary submitFrom">
                        <i class="ti ti-check me-1"></i>
                        <span>{{ __('general.save') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')

    @include('CenterUser.Components.submit-form-ajax')
@endsection
