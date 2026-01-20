@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('Admin.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <input type="hidden" name="id" value="{{ $id }}">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($allPermissions as $group => $permission)
                                <hr>
                                <h2 class="mb-2">{{ $group }}</h2>
                                @foreach ($permission as $perm)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check form-check-custom form-check-solid mb-2">
                                            <input class="form-check-input" type="checkbox" value="{{ $perm['name'] }}"
                                                name="permissions[]"
                                                {{ in_array($perm['name'], array_column($centerPermissions->toArray(), 'name')) ? 'checked' : null }} />
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{ $perm['name_ar'] }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
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
@endsection

@section('page-script')
    @include('Admin.Components.submit-form-ajax')
@endsection
