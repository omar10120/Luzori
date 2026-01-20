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
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="branch_id" class="form-label">{{ __('field.branch') }}</label>
                                    <select class="select2 form-control" name="branch_id" id="branch_id">
                                        @foreach ($branches as $branch)
                                            <option
                                                {{ $item ? ($item->branch_id == $branch->id ? 'selected' : null) : null }}
                                                value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="services" class="form-label">{{ __('field.services') }}</label>
                                    <select class="select2 form-control" name="services[]" id="services" multiple>
                                        @foreach ($services as $service)
                                            <option
                                                {{ $item ? (in_array($service->id, $item->services->pluck('service_id')->toArray()) ? 'selected' : null) : null }}
                                                value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="shift_id" class="form-label">{{ __('field.shift') }}</label>
                                    <select class="select2 form-control" name="shift_id" id="shift_id">
                                        @foreach ($shifts as $shift)
                                            <option
                                                {{ $item ? ($item->shift_id == $shift->id ? 'selected' : null) : null }}
                                                value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="name" class="form-label">{{ __('field.name') }} </label>
                                    <input type="text" id="name" class="form-control" name="name"
                                        placeholder="{{ __('field.name') }}" value="{{ $item ? $item->name : null }}" />
                                    <small class="text-muted">Enter the full name of the employee.</small>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="email" class="form-label">{{ __('field.email') }} </label>
                                    <input type="email" id="email" class="form-control" name="email"
                                        placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : null }}" />
                                    <small class="text-muted">Used for login and notifications.</small>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="phone" class="form-label">{{ __('field.phone') }} </label>
                                    <div class="d-flex">
                                        <label class="p-2" style="background: #80808045">+971</label>
                                        <input style="border-radius:0 1px 1px 0" type="phone" id="phone"
                                            class="form-control" name="phone" placeholder="{{ __('field.phone') }}"
                                            value="{{ $item ? $item->phone : null }}" />
                                    </div>
                                    <small class="text-muted">Enter the number without the country code.</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                @include('CenterUser.Components.image', [
                                    'item' => $item,
                                    'name' => 'image',
                                    'model' => 'worker',
                                ])
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-sm submitFrom">
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
    @include('CenterUser.Components.image-js')
@endsection
