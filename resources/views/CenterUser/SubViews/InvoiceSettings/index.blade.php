@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <div class="card">
                    {{-- Card Header: Title + Save Button top-right --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ $title }}</h2>
                            <small class="text-muted">{{ __('general.manage_your_invoice_settings') ?? 'Manage your invoice contact details.' }}</small>
                        </div>
                        <button type="submit" class="btn btn-primary submitFrom d-flex align-items-center gap-1">
                            <i class="ti ti-check"></i>
                            <span>{{ __('general.save') }}</span>
                        </button>
                    </div>

                    <div class="card-body">
                        <div class="container">

                            {{-- Section: Phone Numbers --}}
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <i class="ti ti-phone fs-4 text-primary"></i>
                                <h5 class="mb-0 fw-semibold">{{ __('field.phone_number') ?? 'Phone Numbers' }}</h5>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="phone_number_1">
                                        {{ __('field.phone_number') }} 1
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ti ti-phone"></i>
                                        </span>
                                        <input type="text" class="form-control" id="phone_number_1" name="phone_number_1"
                                            placeholder="{{ __('field.phone_number') }} 1"
                                            value="{{ $item->phone_number_1 }}" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="phone_number_2">
                                        {{ __('field.phone_number') }} 2
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ti ti-phone"></i>
                                        </span>
                                        <input type="text" class="form-control" id="phone_number_2" name="phone_number_2"
                                            placeholder="{{ __('field.phone_number') }} 2"
                                            value="{{ $item->phone_number_2 }}" />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" for="phone_number_3">
                                        {{ __('field.phone_number') }} 3
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ti ti-phone"></i>
                                        </span>
                                        <input type="text" class="form-control" id="phone_number_3" name="phone_number_3"
                                            placeholder="{{ __('field.phone_number') }} 3"
                                            value="{{ $item->phone_number_3 }}" />
                                    </div>
                                </div>
                            </div>

                            <hr class="my-2" />

                            {{-- Section: Address & Tax --}}
                            <div class="d-flex align-items-center gap-2 mb-3 mt-4">
                                <i class="ti ti-file-invoice fs-4 text-primary"></i>
                                <h5 class="mb-0 fw-semibold">{{ __('field.invoice_details') ?? 'Invoice Details' }}</h5>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="emirate">
                                        {{ __('field.emirate') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ti ti-map-pin"></i>
                                        </span>
                                        <input type="text" class="form-control" id="emirate" name="emirate"
                                            placeholder="{{ __('field.emirate') }}"
                                            value="{{ $item->emirate }}" />
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label" for="tax_number">
                                        {{ __('field.tax_number') }}
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="ti ti-receipt-tax"></i>
                                        </span>
                                        <input type="text" class="form-control" id="tax_number" name="tax_number"
                                            placeholder="{{ __('field.tax_number') }}"
                                            value="{{ $item->tax_number }}" />
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')
@endsection
