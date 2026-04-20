@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package->id }}">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }} ({{ $package->name }})</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="mb-1">
                                    <label for="user" class="form-label">{{ __('field.users') }}</label>
                                    <select class="select2 form-control" name="user_id" id="user">
                                        <option value="">Select User</option>
                                        @if($users && $users->count() > 0)
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>No users available</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label for="package_type" class="form-label">{{ __('field.type') }}</label>
                                    <select class="form-control" name="package_type" id="package_type">
                                        <option value="">{{ __('admin.Choose Type') }}</option>
                                        @foreach($paymentMethods as $paymentMethod)
                                            <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary submitFrom">
                            <i class="menu-icon tf-icons ti ti-check"></i>
                            <span>{{ __('general.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')

    @include('CenterUser.Components.submit-form-ajax')
@endsection
