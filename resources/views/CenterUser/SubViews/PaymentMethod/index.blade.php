@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
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
                        <div class="container">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="mb-1">
                                        <label class="form-label">{{__('field.name')}} <span class="text-danger">*</span></label>
                                        <small class="text-muted">{{__('general.enter_the_name_of_the_payment_method')}}</small>
                                        <input type="text" id="name" class="form-control"
                                            name="name" placeholder="{{__('field.name')}}"
                                            value="{{ $item ? $item->name : '' }}" />
                                        <div class="mt-2">
                                            <small class="text-muted">{{__('field.suggestions')}}</small>
                                            <div class="d-flex flex-wrap gap-2 mt-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary payment-name-suggestion" 
                                                    data-name="service_cash">Service Cash</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary payment-name-suggestion" 
                                                    data-name="service_visa">Service Visa</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary payment-name-suggestion" 
                                                    data-name="sales_cash">Sales Cash</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary payment-name-suggestion" 
                                                    data-name="sales_visa">Sales Visa</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary payment-name-suggestion" 
                                                    data-name="sales_cash_cp">Sales Cash (CP)</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary payment-name-suggestion" 
                                                    data-name="sales_visa_cp">Sales Visa (CP)</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="mb-1">
                                        <label class="form-label">{{__('field.types')}} <span class="text-danger">*</span></label>
                                        <small class="text-muted">{{__('general.select_a_types_of_the_payment_method')}}</small>
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
            });
        });
    </script>
@endsection
