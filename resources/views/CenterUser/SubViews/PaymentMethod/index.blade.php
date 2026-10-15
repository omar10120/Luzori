@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
    @include('CenterUser.Components.settings-form-styles')
    <style>
        .lz-payment-form-page .lz-suggest-chip {
            border-radius: 999px !important;
            border: 1px solid #e5ddd2 !important;
            background: #fbfaf7 !important;
            color: #002b26 !important;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.35rem 0.85rem !important;
        }
        .lz-payment-form-page .lz-suggest-chip:hover,
        .lz-payment-form-page .lz-suggest-chip.is-active {
            background: #002b26 !important;
            border-color: #002b26 !important;
            color: #fff !important;
        }
        .lz-payment-form-page .select2-container--default .select2-selection--multiple {
            border-radius: 12px !important;
            border-color: #e5ddd2 !important;
            min-height: 44px;
            padding: 0.25rem 0.4rem;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-settings-page lz-payment-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-credit-card"></i>
                </div>
                <h1 class="lz-page-title">
                    @if (is_null($item))
                        {{ __('general.add_new') }} {{ __('locale.payment_methods') }}
                    @else
                        {{ __('general.edit') }} {{ __('locale.payment_methods') }}
                    @endif
                </h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_name_of_the_payment_method') }}</small>
                            <input type="text" id="name" class="form-control"
                                name="name" placeholder="{{ __('field.name') }}"
                                value="{{ $item ? $item->name : '' }}" />
                            <div class="mt-2">
                                <small class="lz-field-help">{{ __('field.suggestions') }}</small>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <button type="button" class="btn btn-sm lz-suggest-chip payment-name-suggestion"
                                        data-name="service_cash">Service Cash</button>
                                    <button type="button" class="btn btn-sm lz-suggest-chip payment-name-suggestion"
                                        data-name="service_visa">Service Visa</button>
                                    <button type="button" class="btn btn-sm lz-suggest-chip payment-name-suggestion"
                                        data-name="sales_cash">Sales Cash</button>
                                    <button type="button" class="btn btn-sm lz-suggest-chip payment-name-suggestion"
                                        data-name="sales_visa">Sales Visa</button>
                                    <button type="button" class="btn btn-sm lz-suggest-chip payment-name-suggestion"
                                        data-name="sales_cash_cp">Sales Cash (CP)</button>
                                    <button type="button" class="btn btn-sm lz-suggest-chip payment-name-suggestion"
                                        data-name="sales_visa_cp">Sales Visa (CP)</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="lz-field-label">{{ __('field.types') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.select_a_types_of_the_payment_method') }}</small>
                            <select class="select2 form-control" name="types[]" id="types" multiple>
                                @foreach(\App\Models\PaymentMethod::getTypes() as $key => $value)
                                    <option value="{{ $key }}"
                                        {{ $item && in_array($key, $item->types ?? []) ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.payment_methods.index') }}" class="btn btn-luzori-beige">
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
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')

    <script>
        $(document).ready(function() {
            // Initialize Select2 for types dropdown
            $('#types').select2({
                placeholder: "{{ __('field.Choose Types') }}",
                allowClear: true,
                width: '100%'
            });

            // Handle payment name suggestions
            $('.payment-name-suggestion').on('click', function() {
                const name = $(this).data('name');
                $('#name').val(name);
                $('.payment-name-suggestion').removeClass('is-active');
                $(this).addClass('is-active');
            });
        });
    </script>
@endsection
