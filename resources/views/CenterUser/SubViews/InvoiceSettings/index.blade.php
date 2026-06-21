@extends('layouts/layoutMaster')

@section('title', $title)

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
                        <div class="container">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="phone_number_1">{{ __('field.phone_number') }} 1</label>
                                        <input type="text" class="form-control" id="phone_number_1" name="phone_number_1"
                                            placeholder="{{ __('field.phone_number') }} 1"
                                            value="{{ $item->phone_number_1 }}" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="phone_number_2">{{ __('field.phone_number') }} 2</label>
                                        <input type="text" class="form-control" id="phone_number_2" name="phone_number_2"
                                            placeholder="{{ __('field.phone_number') }} 2"
                                            value="{{ $item->phone_number_2 }}" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="phone_number_3">{{ __('field.phone_number') }} 3</label>
                                        <input type="text" class="form-control" id="phone_number_3" name="phone_number_3"
                                            placeholder="{{ __('field.phone_number') }} 3"
                                            value="{{ $item->phone_number_3 }}" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="emirate">{{ __('field.emirate') }}</label>
                                        <input type="text" class="form-control" id="emirate" name="emirate"
                                            placeholder="{{ __('field.emirate') }}"
                                            value="{{ $item->emirate }}" />
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="mb-1">
                                        <label class="form-label" for="tax_number">{{ __('field.tax_number') }}</label>
                                        <input type="text" class="form-control" id="tax_number" name="tax_number"
                                            placeholder="{{ __('field.tax_number') }}"
                                            value="{{ $item->tax_number }}" />
                                    </div>
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

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')
@endsection
