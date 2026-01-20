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
                                    <label for="products" class="form-label">{{ __('locale.products') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_product_from_the_list')}}</small>
                                    <select class="select2 form-control" name="products[]" id="products" multiple>
                                        <option value="">Select Products</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="payment_type" class="form-label">{{ __('field.payment_method') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_payment_method_from_the_list')}}</small>
                                    <select name="payment_type" id="payment_type" class="form-control">
                                        <option value="">{{ __('field.Choose Payment Method') }}</option>
                                        @foreach($paymentMethods as $paymentMethod)
                                            <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="discount" class="form-label">{{ __('field.discount_codes') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_discount_code_from_the_list')}}</small>
                                    <select name="discount" id="discount" class="form-control">
                                        <option value="">{{ __('field.Choose Discount') }}</option>
                                        @for ($i = 1; $i <= 15; $i++)
                                            <option value="{{ $i }}">{{ $i . '%' }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="sales_worker" class="form-label">{{ __('field.sales_worker') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_sales_worker_from_the_list')}}</small>
                                    <select class="select2 form-control" name="sales_worker_id" id="sales_worker">
                                        <option value="">{{ __('field.Choose Sales Worker') }}</option>
                                        @foreach ($workers as $worker)
                                            <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="worker" class="form-label">{{ __('field.worker') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_worker_from_the_list')}}</small>
                                    <select class="select2 form-control" name="worker_id" id="worker">
                                        <option value="">{{ __('field.Choose Worker') }}</option>
                                        @foreach ($workers as $worker)
                                            <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2" style="display: none;" id="commission-div">
                                <div class="mb-1">
                                    <label for="commission" class="form-label">{{ __('field.commission') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" name="commission" id="commission" required>
                                        <option value="">{{ __('admin.Choose Commission') }}</option>
                                        @for ($i = 1; $i <= 100; $i++)
                                            <option value="{{ $i }}">{{ $i }}%</option>
                                        @endfor
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

    @include('CenterUser.Components.image-js')
    @include('CenterUser.Components.submit-form-ajax')

    <script>
        $(document).ready(function() {
            // Show/hide commission when worker is selected
            $('#worker').on('change', function() {
                const workerId = $(this).val();
                const $commissionField = $('#commission');
                const $commissionDiv = $('#commission-div');
                
                if (workerId == '') {
                    $commissionDiv.hide();
                    $commissionField.prop('required', false);
                    $commissionField.val('');
                } else {
                    $commissionDiv.show();
                    $commissionField.prop('required', true);
                }
            });

            // Validate commission before form submission
            $('#frmSubmit').on('submit', function(e) {
                const workerId = $('#worker').val();
                const commission = $('#commission').val();

                // Commission is required if worker is selected
                if (workerId && workerId != '' && !commission) {
                    e.preventDefault();
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.commission_is_required_when_worker_selected') }}');
                    } else {
                        alert('{{ __('field.commission_is_required_when_worker_selected') }}');
                    }
                    $('#commission').focus();
                    return false;
                }
            });
        });
    </script>
@endsection
