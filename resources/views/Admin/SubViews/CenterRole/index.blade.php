@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('Admin.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <input type="hidden" name="guard_name" value="center">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-4">
                                <label class="form-label">{{__('field.name')}} ( {{__('field.permissions_name')}}) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="{{__('field.name')}}"
                                    value="{{ $item ? $item->name : '' }}" />
                            </div>
                        </div>
                        <div class="row">
                            @foreach ($permissions as $group => $permission)
                                <hr>
                                <h2 class="mb-2">{{ $group }}</h2>
                                @foreach ($permission as $perm)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check form-check-custom form-check-solid mb-2">
                                            <input class="form-check-input" type="checkbox" value="{{ $perm->name }}"
                                                name="permissions[]"
                                                {{ $item ? (in_array($perm->id, array_column($item->permissions->toArray(), 'id')) ? 'checked' : null) : null }} />
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{ $perm->name_ar }}
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
