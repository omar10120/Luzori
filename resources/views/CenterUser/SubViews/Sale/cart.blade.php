@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss'])
@endsection

@section('content')
    <div class="container-fluid">
        @include('CenterUser.Components.breadcrumbs')
        

        <!-- Customer Selection Card -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('field.customer') }} <span class="text-danger">*</span></h5>
                    </div>
                    <div class="card-body">
                        <div id="selected-customer-display" style="{{ empty($cart['client_id']) ? 'display: none;' : '' }}">
                            @if(!empty($cart['client_id']))
                                @php
                                    $selectedUser = $users->firstWhere('id', $cart['client_id']);
                                @endphp
                                @if($selectedUser)
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-lg me-3">
                                            <img src="{{ $selectedUser->image ?? asset('assets/img/avatars/1.png') }}" 
                                                 alt="{{ $selectedUser->name }}" 
                                                 class="rounded-circle" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        </div>
                                        <div>
                                            <h5 class="mb-0">{{ $selectedUser->name }}</h5>
                                            <small class="text-muted d-block">{{ $selectedUser->email ?? $selectedUser->full_phone }}</small>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="button" class="btn btn-outline-primary w-100 w-sm-auto" id="selectCustomerBtn" data-bs-toggle="modal" data-bs-target="#selectCustomerModal">
                                    <i class="ti ti-user me-1"></i>
                                    <span class="d-none d-sm-inline">{{ empty($cart['client_id']) ? __('field.select_customer') : __('field.change_customer') }}</span>
                                    <span class="d-inline d-sm-none">{{ empty($cart['client_id']) ? __('field.select') : __('field.change') }}</span>
                                </button>
                                <button type="button" class="btn btn-primary w-100 w-sm-auto" id="addCustomerBtn" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                    <i class="ti ti-plus me-1"></i>
                                    <span class="d-none d-sm-inline">{{ __('general.add') }} {{ __('field.customer') }}</span>
                                    <span class="d-inline d-sm-none">{{ __('general.add') }}</span>
                                </button>
                                @if(!empty($cart['client_id']))
                                    <button type="button" class="btn btn-info w-100 w-sm-auto" id="editCustomerBtn" data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                                        <i class="ti ti-edit me-1"></i>
                                        <span class="d-none d-sm-inline">{{ __('general.edit') }}</span>
                                        <span class="d-inline d-sm-none">{{ __('general.edit') }}</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger w-100 w-sm-auto" id="removeCustomerBtn">
                                        <i class="ti ti-x me-1"></i>
                                        <span class="d-none d-sm-inline">{{ __('field.remove') }}</span>
                                        <span class="d-inline d-sm-none">{{ __('field.remove') }}</span>
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div id="no-customer-display" class="text-center py-3" style="{{ !empty($cart['client_id']) ? 'display: none;' : '' }}">
                            <i class="ti ti-user-off" style="font-size: 3rem; color: #ff6b6b;"></i>
                            <p class="text-danger mb-0 mt-2"><strong>{{ __('field.customer_required') }}</strong></p>
                            <p class="text-muted mb-0">{{ __('field.please_select_or_add_customer') }}</p>
                            <div class="d-flex flex-column flex-sm-row gap-2 mt-3 justify-content-center">
                                <button type="button" class="btn btn-outline-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#selectCustomerModal">
                                    <i class="ti ti-user me-1"></i>
                                    {{ __('field.select_customer') }}
                                </button>
                                <button type="button" class="btn btn-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                    <i class="ti ti-plus me-1"></i>
                                    {{ __('general.add') }} {{ __('field.customer') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    

        <div class="row">
            <!-- Left Panel: Add Items -->
            <div class="col-12 col-xl-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="booking-tab" data-bs-toggle="tab" href="#booking" role="tab">
                                    <i class="ti ti-calendar me-1"></i>
                                    {{ __('locale.bookings') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="products-tab" data-bs-toggle="tab" href="#products" role="tab">
                                    <i class="ti ti-package me-1"></i>
                                    {{ __('locale.products') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="wallet-tab" data-bs-toggle="tab" href="#wallet" role="tab">
                                    <i class="ti ti-wallet me-1"></i>
                                        {{ __('field.coupons') }} ({{ __('locale.wallets') }})
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="package-tab" data-bs-toggle="tab" href="#package" role="tab">
                                    <i class="ti ti-box me-1"></i>
                                    {{ __('locale.packages') }}
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Booking Tab - Full 4-Step Wizard -->
                            <div class="tab-pane fade show active" id="booking" role="tabpanel">
                                <form class="pt-0" id="bookingWizardForm">
                                    @csrf
                                    <div class="col-12 mb-4">
                                        <div class="bs-stepper wizard-icons wizard-icons-example mt-2">
                                            <div class="bs-stepper-header">
                                                <div class="step" data-target="#booking-first-step">
                                                    <button type="button" class="step-trigger" disabled>
                                                        <span class="bs-stepper-label">{{ __('locale.services') }}</span>
                                                    </button>
                                                </div>
                                                <div class="line">
                                                    <i class="ti ti-chevron-right"></i>
                                                </div>
                                                <div class="step" data-target="#booking-second-step">
                                                    <button type="button" class="step-trigger" disabled>
                                                        <span class="bs-stepper-label">{{__('field.booking_details')}}</span>
                                                    </button>
                                                </div>
                                                <div class="line">
                                                    <i class="ti ti-chevron-right"></i>
                                                </div>
                                                <div class="step" data-target="#booking-third-step">
                                                    <button type="button" class="step-trigger" disabled>
                                                        <span class="bs-stepper-label">{{__('field.customers_details')}}</span>
                                                    </button>
                                                </div>
                                                <div class="line">
                                                    <i class="ti ti-chevron-right"></i>
                                                </div>
                                                <div class="step" data-target="#booking-fourth-step">
                                                    <button type="button" class="step-trigger" disabled>
                                                        <span class="bs-stepper-label">{{__('field.overview')}}</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="bs-stepper-content">
                                                <!-- Step 1: Services -->
                                                <div id="booking-first-step" class="content">
                                                    <div class="row mb-4">
                                                        <div class="col-md-12">
                                                                    <div class="row align-items-end">
                                                                        <div class="col-md-6">
                                                                            @include('CenterUser.Components.category-tree-select', [
                                                                                'categoriesJson' => $categoriesJson,
                                                                                'selectedId' => null,
                                                                                'selectedName' => null,
                                                                                'name' => 'wizard_category_id',
                                                                                'label' => __('field.category'),
                                                                                'id' => 'wizard_category_tree'
                                                                            ])
                                                                        </div>
                                                                        <div class="col-md-6" id="wizard-services-container" style="display: none;">
                                                                            <div class="mb-1">
                                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                                    <label for="booking-services" class="form-label mb-0">{{ __('field.services') }}</label>
                                                                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addServiceQuickBtn" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                                                                                        <i class="ti ti-plus me-1"></i>
                                                                                        {{ __('general.add') }} {{ __('locale.services') }}
                                                                                    </button>
                                                                                </div>
                                                                                <select class="select2 form-control " name="services[]" id="booking-services" multiple>
                                                                                    @foreach ($services as $service)
                                                                                        <option value="{{ $service->id }}" data-category-id="{{ $service->category_id }}">{{ $service->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                                <!-- <div class="mt-3 mb-1">
                                                                                    <label for="booking-packages" class="form-label mb-0">{{ __('locale.packages') }}</label>
                                                                                    <select class="select2 form-control " name="packages[]" id="booking-packages" multiple>
                                                                                        @foreach ($packages as $package)
                                                                                                <option value="{{ $package->id }}" data-price="{{ $package->price }}">{{ $package->name }} ({{ $package->price }} {{ get_currency() }})</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div> -->
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 d-flex justify-content-between">
                                                        <button type="button" class="btn btn-label-secondary btn-prev" disabled>
                                                            <i class="ti ti-arrow-left me-sm-1"></i>
                                                            <span class="align-middle d-sm-inline-block d-none">{{ __('field.previous') }}</span>
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-next" id="booking-nextStep1" disabled>
                                                            <span class="align-middle d-sm-inline-block d-none me-sm-1">{{ __('field.next') }}</span>
                                                            <i class="ti ti-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Step 2: Booking Details -->
                                                <div id="booking-second-step" class="content">
                                                    <div id="booking-service-container"></div>
                                                    <div class="col-12 mt-4 d-flex justify-content-between">
                                                        <button type="button" class="btn btn-label-secondary btn-prev" id="booking-prevStep2">
                                                            <i class="ti ti-arrow-left me-sm-1"></i>
                                                            <span class="align-middle d-sm-inline-block d-none">{{ __('field.previous') }}</span>
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-next" id="booking-nextStep2">
                                                            <span class="align-middle d-sm-inline-block d-none me-sm-1">{{ __('field.next') }}</span>
                                                            <i class="ti ti-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <!-- Step 3: Customer Details -->
                                                <div id="booking-third-step" class="content">
                                                    <div class="row mb-4">
                                                        <div class="col-md-12">
                                                            <div id="booking-customer-info-display" class="alert alert-info">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="ti ti-user me-3 fs-4"></i>
                                                                    <div>
                                                                        <h6 class="alert-heading mb-1">{{ __('field.customer') }}</h6>
                                                                        <p class="mb-0" id="booking-step3-customer-name"></p>
                                                                        <small id="booking-step3-customer-mobile"></small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <hr />
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h5 class="mb-0">{{ __('field.discount_codes') }}</h5>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-discount-selection" style="display: none;">
                                                                <i class="ti ti-x me-1"></i>{{ __('general.clear') }}
                                                            </button>
                                                        </div>
                                                        <div class="row g-2 mb-4">
                                                            @foreach ($discounts as $discount)
                                                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                                                    <div class="form-check discount-item" style="padding: 10px;color: #fff;background-color: #428bca;border-color: #357ebd;border-radius: 4px;min-height: 50px;display: flex;align-items: center;gap: 10px;font-size: 14px;width: 100%;">
                                                                        <label class="form-check-label flex-grow-1 text-start" for="booking-discounts{{ $discount->id }}" style="word-break: break-word;white-space: normal;overflow: hidden;min-width: 0;margin: 0;">
                                                                            {{ $discount->code . ' [' . $discount->amount . '%]' }}
                                                                        </label>
                                                                        <input class="form-check-input flex-shrink-0 booking-discount-radio" type="radio" name="discount_id" data-name="discount_id" value="{{ $discount->id }}" id="booking-discounts{{ $discount->id }}" data-discount-amount="{{ $discount->amount }}" data-discount-type="{{ $discount->type }}" style="margin-top: 0;width: 18px;height: 18px;flex-shrink: 0;">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div id="booking-walletsElement"></div>
                                                    <div id="booking-membershipsElement"></div>
                                                    <div id="booking-packagesElement"></div>
                                                    <div id="booking-servicesTable"></div>
                                                    <div class="row mb-2" id="booking-multiple-payments-container">
                                                        <div class="col-md-12">
                                                            <div class="form-check form-switch mb-2">
                                                                <input class="form-check-input" type="checkbox" id="booking-multiple_payments_toggle">
                                                                <label class="form-check-label" for="booking-multiple_payments_toggle">{{ __('field.multiple_payment_methods') }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-4" id="booking-payment-method-container">
                                                        <div class="col-md-12">
                                                            <div class="mb-1">
                                                                <label for="booking-payment_type" class="form-label">{{ __('field.payment_method') }} <span class="text-danger">*</span></label>
                                                                <select name="payment_type" id="booking-payment_type" class="form-control" required>
                                                                    <option value="">{{ __('field.select_payment_method') }}</option>
                                                                    @foreach($paymentMethods as $paymentMethod)
                                                                        <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <div class="invalid-feedback"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-4" id="booking-multiple-payments-container" style="display: none;">
                                                        <div class="col-md-12">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <label class="form-label mb-0">{{ __('field.payment_method') }} & {{ __('field.amount') }} <span class="text-danger">*</span></label>
                                                                <div class="badge bg-label-info pb-1 pt-1" style="font-size: 14px;">
                                                                    {{ __('field.total_to_pay') }}: <span id="booking-multi-payment-total-display">0.00</span> {{ get_currency() }}
                                                                </div>
                                                            </div>
                                                            <div id="booking-multiple-payments-list">
                                                                <div class="d-flex mb-2 booking-payment-row">
                                                                    <select class="form-control booking-multi-payment-type flex-grow-1 me-2">
                                                                        <option value="">{{ __('field.select_payment_method') }}</option>
                                                                        @foreach($paymentMethods as $paymentMethod)
                                                                            <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="number" class="form-control booking-multi-payment-amount" placeholder="{{ __('field.amount') }}" step="0.01" style="width: 120px;">
                                                                    <button type="button" class="btn btn-outline-danger ms-2 btn-remove-booking-payment"><i class="ti ti-trash"></i></button>
                                                                </div>
                                                            </div>
                                                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="btn-add-booking-payment">
                                                                <i class="ti ti-plus me-1"></i> {{ __('general.add') }}
                                                            </button>
                                                            <div class="text-danger mt-2" style="display: none;" id="booking-multiple-payments-error"></div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 mt-4 d-flex justify-content-between">
                                                        <button type="button" class="btn btn-label-secondary btn-prev" id="booking-prevStep3">
                                                            <i class="ti ti-arrow-left me-sm-1"></i>
                                                            <span class="align-middle d-sm-inline-block d-none">{{ __('field.previous') }}</span>
                                                        </button>
                                                        <button type="button" class="btn btn-primary btn-next" id="booking-nextStep3" disabled>
                                                            <span class="align-middle d-sm-inline-block d-none me-sm-1">{{ __('field.next') }}</span>
                                                            <i class="ti ti-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Step 4: Review -->
                                                <div id="booking-fourth-step" class="content">
                                                    <div id="booking-review-content"></div>
                                                    <div class="col-12 mt-4 d-flex justify-content-between">
                                                        <button type="button" class="btn btn-label-secondary btn-prev" id="booking-prevStep4">
                                                            <i class="ti ti-arrow-left me-sm-1"></i>
                                                            <span class="align-middle d-sm-inline-block d-none">{{ __('field.previous') }}</span>
                                                        </button>
                                                        <button type="button" class="btn btn-success" id="addBookingToCart">
                                                            <i class="ti ti-shopping-cart me-1"></i>
                                                            {{ __('field.add_to_cart') }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Products Tab - Match BuyProduct Structure -->
                            <div class="tab-pane fade" id="products" role="tabpanel">
                                <form class="pt-0" id="productForm">
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <div class="mb-1">
                                                <label for="product-products" class="form-label">{{ __('locale.products') }}</label>
                                                <select class="select2 form-control" name="products[]" id="product-products" multiple>
                                                    <option value="">{{ __('field.select_products') }}</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <div class="mb-1">
                                                <label for="product-discount" class="form-label">{{ __('field.discount_codes') }}</label>
                                                <select name="discount" id="product-discount" class="form-control">
                                                    <option value="">{{ __('field.select_discount') }}</option>
                                                    @for ($i = 1; $i <= 15; $i++)
                                                        <option value="{{ $i }}">{{ $i . '%' }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                     
                                       
                                        <div class="col-md-12 mb-2">
                                            <div class="mb-1">
                                                <label for="product-worker" class="form-label">{{ __('field.worker') }}</label>
                                                <select class="select2 form-control" name="worker_id" id="product-worker">
                                                    <option value="">{{ __('field.select_worker') }}</option>
                                                    @foreach ($workers as $worker)
                                                        <option value="{{ $worker->id }}">{{ $worker->name }} - {{ $worker->phone }} {{ $worker->is_center_user ? '('. ($centerUser->name ?? '') .' - reception)' : '' }} </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2" style="display: none;" id="product-commission-div">
                                            <div class="mb-1">
                                                <label for="product-commission" class="form-label">{{ __('field.commission') }} <span class="text-danger">*</span></label>
                                                <select class="form-control" name="commission" id="product-commission" required>
                                                    <option value="">{{ __('field.select_commission') }}</option>
                                                    @for ($i = 1; $i <= 100; $i++)
                                                        <option value="{{ $i }}">{{ $i }}%</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <div class="mb-1">
                                                <label for="product-payment_type" class="form-label">{{ __('field.payment_method') }} <span class="text-danger">*</span></label>
                                                <select name="payment_type" id="product-payment_type" class="form-control" required>
                                                    <option value="">{{ __('field.select_payment_method') }}</option>
                                                    @foreach($productPaymentMethods as $paymentMethod)
                                                        <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-primary" id="addProductBtn">
                                                <i class="ti ti-shopping-cart me-1"></i>
                                                {{ __('field.add_to_cart') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            

                            <!-- Wallet Tab - Show Coupons Table, Add User Wallet, and Add New Coupon -->
                            <div class="tab-pane fade" id="wallet" role="tabpanel">
                                <!-- Available Coupons Table -->
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">{{ __('locale.wallets') }} ({{ __('field.available_coupons') }})</h5>
                                        <button type="button" class="btn btn-primary btn-sm" id="addCouponBtn" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                                            <i class="ti ti-plus me-1"></i>
                                            {{ __('general.add') }} {{ __('field.new_coupon') }}
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover " id="wallets-table">
                                                <thead>
                                                    <tr >
                                                        <th style="font-size: 10px;">{{ __('field.code') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.client') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.amount') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.invoiced_amount') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.start_at') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.end_at') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.created_by') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.status') }}</th>
                                                        <th style="font-size: 10px;">{{ __('general.actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($wallets as $wallet)
                                                        <tr style="font-size: 10px;">
                                                            <td>{{ $wallet->code }}</td>
                                                            <td>
                                                                @if($wallet->users && $wallet->users->count() > 0)
                                                                    @foreach($wallet->users as $userWallet)
                                                                        <span class="badge bg-label-info mb-1">{{ $userWallet->user->name ?? '-' }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ number_format($wallet->amount, 2) }} {{ get_currency() }}</td>
                                                            <td>{{ number_format($wallet->invoiced_amount, 2) }} {{ get_currency() }}</td>
                                                            <td>{{ $wallet->start_at ? \Carbon\Carbon::parse($wallet->start_at)->format('Y-m-d') : '-' }}</td>
                                                            <td>{{ $wallet->end_at ? \Carbon\Carbon::parse($wallet->end_at)->format('Y-m-d') : '-' }}</td>
                                                            <td>{{ $wallet->created_by_user->name ?? '-' }}</td>
                                                            <td>
                                                                @if($wallet->used)
                                                                    <span class="badge bg-label-danger">{{ __('field.used') }}</span>
                                                                @else
                                                                    <span class="badge bg-label-success">{{ __('field.active') }}</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-primary add-wallet-user-btn" style="font-size: 10px;" data-wallet-id="{{ $wallet->id }}" data-wallet-code="{{ $wallet->code }}" data-wallet-amount="{{ $wallet->amount }}" data-wallet-invoiced="{{ $wallet->invoiced_amount }}" data-wallet-start="{{ $wallet->start_at }}" data-wallet-end="{{ $wallet->end_at }}" data-bs-toggle="modal" data-bs-target="#addWalletUserModal">
                                                                    <i class="ti ti-user-plus me-1"></i>
                                                                    {{ __('field.add_user') }}
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="9" class="text-center text-muted">{{ __('field.no_coupons_available') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Packages Tab - Show Packages Table and Add User Package -->
                            <div class="tab-pane fade" id="package" role="tabpanel">
                                <div class="card mb-4">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">{{ __('locale.packages') }} ({{ __('field.available') ?? 'Available' }})</h5>
                                        <button type="button" class="btn btn-primary btn-sm" id="addPackageBtn" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                                            <i class="ti ti-plus me-1"></i>
                                            {{ __('general.add') }} {{ __('locale.packages') }}
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="packages-table">
                                                <thead>
                                                    <tr>
                                                        <th style="font-size: 10px;">#</th>
                                                        <th style="font-size: 10px;">{{ __('field.name') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.price') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.paid_services') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.free_services') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.users') }}</th>
                                                        <th style="font-size: 10px;">{{ __('general.actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($packages as $package)
                                                        <tr style="font-size: 10px;">
                                                            <td>{{ $package->id }}</td>
                                                            <td>{{ $package->name }}</td>
                                                            <td>{{ number_format($package->price ?? 0, 2) }} {{ get_currency() }}</td>
                                                            <td>
                                                                @if($package->packageServicePaid && $package->packageServicePaid->count() > 0)
                                                                    @foreach($package->packageServicePaid as $paidService)
                                                                        <span class="badge bg-label-primary mb-1">{{ $paidService->service->name ?? '-' }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($package->packageServiceFree && $package->packageServiceFree->count() > 0)
                                                                    @foreach($package->packageServiceFree as $freeService)
                                                                        <span class="badge bg-label-success mb-1">{{ $freeService->service->name ?? '-' }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($package->usersPackages && $package->usersPackages->count() > 0)
                                                                    @foreach($package->usersPackages as $userPackage)
                                                                        <span class="badge bg-label-info mb-1">{{ $userPackage->user->name ?? '-' }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-primary add-package-user-btn"
                                                                    style="font-size: 10px;"
                                                                    data-package-id="{{ $package->id }}"
                                                                    data-package-name="{{ $package->name }}"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#addPackageUserModal"
                                                                >
                                                                    <i class="ti ti-user-plus me-1"></i>
                                                                    {{ __('field.add_user') }}
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">{{ __('field.no_data_found') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Cart Summary -->
            <div class="col-12 col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('field.cart') }}</h5>
                    </div>
                    <div class="card-body">
                        <!-- Cart Tabs -->
                        <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="cart-booking-tab" data-bs-toggle="tab" href="#cart-booking-pane" role="tab">
                                    <i class="ti ti-calendar me-1"></i>
                                    {{ __('locale.bookings') }}
                                    <span class="badge rounded-pill bg-label-primary ms-1" id="cart-booking-count" style="display:none">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="cart-products-tab" data-bs-toggle="tab" href="#cart-products-pane" role="tab">
                                    <i class="ti ti-package me-1"></i>
                                    {{ __('locale.products') }}
                                    <span class="badge rounded-pill bg-label-primary ms-1" id="cart-products-count" style="display:none">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="cart-user-wallet-tab" data-bs-toggle="tab" href="#cart-user-wallet-pane" role="tab">
                                    <i class="ti ti-wallet me-1"></i>
                                    <span class="d-none d-sm-inline">{{ __('field.coupons') }}</span>
                                    <span class="badge rounded-pill bg-label-primary ms-1" id="cart-user-wallet-count" style="display:none">0</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Bookings/Services Pane -->
                            <div class="tab-pane fade show active" id="cart-booking-pane" role="tabpanel">
                                <div id="cart-items-service"></div>
                                <div id="cart-empty-service" class="text-center text-muted py-3" style="display: none;">
                                    {{ __('field.no_services_in_cart') }}
                                </div>
                            </div>

                            <!-- Products Pane -->
                            <div class="tab-pane fade" id="cart-products-pane" role="tabpanel">
                                <div id="cart-items-product"></div>
                                <div id="cart-empty-product" class="text-center text-muted py-3" style="display: none;">
                                    {{ __('field.no_products_in_cart') }}
                                </div>
                            </div>

                            <!-- Coupons Pane -->
                            <div class="tab-pane fade" id="cart-user-wallet-pane" role="tabpanel">
                                <div id="cart-items-user-wallet"></div>
                                <div id="cart-empty-user-wallet" class="text-center text-muted py-3" style="display: none;">
                                    {{ __('field.no_coupons_in_cart') }}
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('field.subtotal') }}:</span>
                            <strong id="cart-subtotal">0 {{ get_currency() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('field.tax') }}:</span>
                            <strong id="cart-tax">0 {{ get_currency() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>{{ __('field.total') }}:</span>
                            <strong id="cart-total" class="text-primary fs-5">0 {{ get_currency() }}</strong>
                        </div>

                        <button type="button" class="btn btn-success w-100" id="continueToPayment" {{ (empty($cart['items']) || empty($cart['client_id'])) ? 'disabled' : '' }}>
                            <i class="ti ti-arrow-right me-1"></i>
                            {{ __('field.continue_to_payment') }}
                        </button>
                    </div>
                </div>
            </div>  
        </div>
    </div>

    <!-- Select Customer Modal -->
    <div class="modal fade" id="selectCustomerModal" tabindex="-1" aria-labelledby="selectCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectCustomerModalLabel">{{ __('field.select_customer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                     
                    <label for="select-customer-dropdown" class="form-label">{{ __('field.search_customer') }}</label>
                        <select class="js-customer-select form-control" id="select-customer-dropdown" style="width: 100%;"
    data-search-url="{{ route('center_user.sales.search-customers') }}"
    data-dropdown-parent="#selectCustomerModal"
    data-placeholder="{{ __('field.search_by_name_phone_or_email') }}"
    data-no-results="{{ __('field.no_customers_found') }}"
    data-searching="{{ __('field.searching') }}">
    <option value="">{{ __('field.search_by_name_phone_or_email') }}</option>
</select>
                    </div>
                    <div id="selected-customer-info" style="display: none;" class="mt-3 p-3 border rounded bg-light">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3" id="selected-customer-avatar">
                                <img id="selected-customer-img" src="" alt="" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0" id="selected-customer-name"></h6>
                                <small class="text-muted d-block" id="selected-customer-email"></small>
                                <small class="text-muted d-block" id="selected-customer-phone"></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="confirm-select-customer" disabled>
                        <i class="ti ti-check me-1"></i>
                        {{ __('field.select') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Quick Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">{{ __('general.add') }} {{ __('field.customer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quick-add-customer-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quick_customer_first_name" class="form-label">
                                    {{ __('field.first_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="quick_customer_first_name" class="form-control" name="first_name" required />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quick_customer_last_name" class="form-label">
                                    {{ __('field.last_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="quick_customer_last_name" class="form-control" name="last_name" required />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quick_customer_email" class="form-label">
                                    {{ __('field.email') }}
                                </label>
                                <input type="email" id="quick_customer_email" class="form-control" name="email" />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-2 mb-3">
                                @include('Admin.Components.country_code', ['item' => null])
                            </div>
                            <div class="col-md-2 mb-3" id="quick_customer_phone_prefix_container" style="display: none;">
                                <label for="quick_customer_phone_prefix" class="form-label">
                                    Prefix
                                </label>
                                <select class="form-control" name="phone_prefix" id="quick_customer_phone_prefix">
                                    @php
                                        $prefixes = ['50', '52', '54', '55', '56', '58'];
                                    @endphp
                                    @foreach ($prefixes as $prefix)
                                        <option value="{{ $prefix }}">{{ $prefix }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3" id="quick_customer_phone_input_container">
                                <label for="quick_customer_phone" class="form-label">
                                    {{ __('field.mobile_number') }} <span class="text-danger">*</span>
                                </label>
                                <input type="tel" maxlength="7" id="quick_customer_phone" class="form-control" name="phone" required pattern="[0-9]{7}" title="{{ __('field.phone_must_be_7_digits') }}" />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="quick_customer_image" class="form-label">
                                {{ __('field.image') }}
                            </label>
                            <input type="file" id="quick_customer_image" class="form-control" name="image" accept="image/*" />
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="save-quick-customer-btn">
                        <i class="ti ti-check me-1"></i>
                        {{ __('general.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">{{ __('general.edit') }} {{ __('field.customer') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="edit-customer-form">
                        @csrf
                        <input type="hidden" id="edit_customer_id" name="id" value="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_customer_first_name" class="form-label">
                                    {{ __('field.first_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="edit_customer_first_name" class="form-control" name="first_name" required />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_customer_last_name" class="form-label">
                                    {{ __('field.last_name') }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="edit_customer_last_name" class="form-control" name="last_name" required />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_customer_email" class="form-label">
                                    {{ __('field.email') }} <span class="text-danger">*</span>
                                </label>
                                <input type="email" id="edit_customer_email" class="form-control" name="email" required />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-2 mb-3">
                                @include('Admin.Components.country_code', ['item' => null, 'id_prefix' => 'edit_customer_'])
                            </div>
                            <div class="col-md-2 mb-3" id="edit_customer_phone_prefix_container" style="display: none;">
                                <label for="edit_customer_phone_prefix" class="form-label">
                                    Prefix
                                </label>
                                <select class="form-control" name="phone_prefix" id="edit_customer_phone_prefix">
                                    @php
                                        $prefixes = ['50', '52', '54', '55', '56', '58'];
                                    @endphp
                                    @foreach ($prefixes as $prefix)
                                        <option value="{{ $prefix }}">{{ $prefix }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4 mb-3" id="edit_customer_phone_input_container">
                                <label for="edit_customer_phone" class="form-label">
                                    {{ __('field.mobile_number') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" maxlength="7" id="edit_customer_phone" class="form-control" name="phone" required />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_customer_image" class="form-label">
                                {{ __('field.image') }}
                            </label>
                            <input type="file" id="edit_customer_image" class="form-control" name="image" accept="image/*" />
                            <div class="invalid-feedback"></div>
                            <div id="edit_customer_current_image" class="mt-2" style="display:none;">
                                <img id="edit_customer_image_preview" src="" alt="Current image" style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="save-edit-customer-btn">
                        <i class="ti ti-check me-1"></i>
                        {{ __('general.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Service Quick Modal - Same as Booking Page -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addServiceModalLabel">{{ __('general.add') }} {{ __('locale.services') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quick-add-service-form">
                        @csrf
                        <ul class="nav nav-tabs" role="tablist">
                            @foreach (Config::get('translatable.locales') as $locale)
                                <li class="nav-item">
                                    <a class="nav-link {{ $loop->first ? 'active' : null }}"
                                        id="quick-service-{{ $locale }}-tab-link" data-bs-toggle="tab"
                                        href="#quick-service-{{ $locale }}-add" aria-controls="quick-service-{{ $locale }}-add"
                                        role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                        <i class="menu-icon tf-icons ti ti-flag"></i>
                                        {{ Str::upper($locale) }}</a>
                                </li>
                            @endforeach
                        </ul>
                        
                        <div class="tab-content mb-3">
                            @foreach (Config::get('translatable.locales') as $locale)
                                <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="quick-service-{{ $locale }}-add"
                                    aria-labelledby="quick-service-{{ $locale }}-tab-link" role="tabpanel">
                                    <div class="mb-3">
                                        <label for="quick_service_name_{{ $locale }}" class="form-label">
                                            {{ __('field.name') }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="quick_service_name_{{ $locale }}" class="form-control"
                                            name="{{ $locale }}[name]"
                                            placeholder="{{ __('field.name') }}" required />
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quick_service_description_{{ $locale }}" class="form-label">
                                            {{ __('field.description') }}
                                        </label>
                                        <textarea id="quick_service_description_{{ $locale }}" class="form-control"
                                            name="{{ $locale }}[description]"
                                            placeholder="{{ __('field.description') }}" rows="3"></textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quick_service_rooms_no" class="form-label">
                                    {{ __('field.rooms_no') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="quick_service_rooms_no" class="form-control" name="rooms_no"
                                    placeholder="{{ __('field.rooms_no') }}" required />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quick_service_free_book" class="form-label">
                                    {{ __('field.free_book') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="quick_service_free_book" class="form-control" name="free_book"
                                    placeholder="{{ __('field.free_book') }}" required />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quick_service_price" class="form-label">
                                    {{ __('field.price') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="quick_service_price" class="form-control" name="price"
                                    placeholder="{{ __('field.price') }}" step="0.01" required />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex gap-4 mt-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="quick_service_is_top" name="is_top" />
                                        <label class="form-check-label" for="quick_service_is_top">{{ __('field.is_top') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="quick_service_has_commission" name="has_commission" />
                                        <label class="form-check-label" for="quick_service_has_commission">{{ __('field.commission') }}</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                @include('CenterUser.Components.category-tree-select', [
                                    'categoriesJson' => $categoriesJson,
                                    'selectedId' => null,
                                    'selectedName' => null,
                                    'name' => 'category_id',
                                    'label' => __('field.category'),
                                    'id' => 'quick_service_category_tree'
                                ])
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                {{ __('field.image') }} <span class="text-danger">*</span>
                            </label>
                            <input type="file" id="quick_service_image" class="form-control" name="image" accept="image/*" required />
                            <div class="invalid-feedback" id="quick-service-image-error"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">  
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="save-quick-service-btn">
                        <i class="ti ti-check me-1"></i>
                        {{ __('general.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Coupon Quick Modal -->
    <div class="modal fade" id="addCouponModal" tabindex="-1" aria-labelledby="addCouponModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCouponModalLabel">{{ __('general.add') }} {{ __('field.new_coupon') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quick-add-coupon-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="quick_coupon_amount" class="form-label">
                                    {{ __('field.amount') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" id="quick_coupon_amount" class="form-control" name="amount" 
                                    placeholder="{{ __('field.amount') }}" step="0.01" required />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="quick_coupon_invoiced_amount" class="form-label">
                                    {{ __('field.invoiced_amount') }}
                                </label>
                                <input type="number" id="quick_coupon_invoiced_amount" class="form-control" name="invoiced_amount" 
                                    placeholder="{{ __('field.invoiced_amount') }}" step="0.01" />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quick_coupon_start_at" class="form-label">
                                    {{ __('field.start_at') }}
                                </label>
                                <input type="date" id="quick_coupon_start_at" class="form-control" name="start_at" />
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quick_coupon_end_at" class="form-label">
                                    {{ __('field.end_at') }}
                                </label>
                                <input type="date" id="quick_coupon_end_at" class="form-control" name="end_at" />
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="save-quick-coupon-btn">
                        <i class="ti ti-check me-1"></i>
                        {{ __('general.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Package Quick Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1" aria-labelledby="addPackageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPackageModalLabel">{{ __('general.add') }} {{ __('locale.packages') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quick-add-package-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quick_package_name_en" class="form-label">{{ __('field.name') }} (EN) <span class="text-danger">*</span></label>
                                <input type="text" id="quick_package_name_en" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quick_package_name_ar" class="form-label">{{ __('field.name') }} (AR) <span class="text-danger">*</span></label>
                                <input type="text" id="quick_package_name_ar" class="form-control" dir="rtl" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="quick_package_price" class="form-label">{{ __('field.price') }} <span class="text-danger">*</span></label>
                                <input type="number" id="quick_package_price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="quick_package_paid_services" class="form-label">{{ __('field.paid_services') }} <span class="text-danger">*</span></label>
                                <select id="quick_package_paid_services" class="select2 form-control" multiple required>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="quick_package_free_services" class="form-label">{{ __('field.free_services') }}</label>
                                <select id="quick_package_free_services" class="select2 form-control" multiple>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="save-quick-package-btn">
                        <i class="ti ti-check me-1"></i>
                        {{ __('general.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Wallet User Modal -->
    <div class="modal fade" id="addWalletUserModal" tabindex="-1" aria-labelledby="addWalletUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addWalletUserModalLabel">{{ __('locale.add_users_to') }} {{ __('locale.wallets') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-wallet-user-form">
                        @csrf
                        <input type="hidden" name="wallet_id" id="modal-wallet-id">
                        <div class="row">
                            <div class="col-md-9 mb-3">
                                <div class="mb-1">
                                    <label for="modal-wallet-user" class="form-label">{{ __('field.users') }} <span class="text-danger">*</span></label>
                                    <select class="js-customer-select form-control" name="user_id" id="modal-wallet-user" required
                                        data-search-url="{{ route('center_user.sales.search-customers') }}"
                                        data-dropdown-parent="#addWalletUserModal"
                                        data-placeholder="{{ __('field.search_by_name_phone_or_email') }}"
                                        data-no-results="{{ __('field.no_customers_found') }}"
                                        data-searching="{{ __('field.searching') }}">
                                        <option value="">{{ __('field.select_user') }}</option>
                                        @if($users && $users->count() > 0)
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone ?? $user->full_phone ?? '' }})
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>{{ __('field.no_users_available') }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="mb-1">
                                    <label for="modal-wallet-type" class="form-label">{{ __('field.type') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" name="wallet_type" id="modal-wallet-type" required>
                                        <option value="">{{ __('field.select_type') }}</option>
                                        @foreach($walletPaymentMethods as $paymentMethod)
                                            <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-9 mb-3">
                                <div class="mb-1">
                                    <label for="modal-wallet-worker" class="form-label">{{ __('locale.workers') }}</label>
                                    <select class="form-control" name="worker_id" id="modal-wallet-worker">
                                        <option value="">{{ __('field.select_worker') }}</option>
                                        @foreach ($workers as $worker)
                                            <option value="{{ $worker->id }}">{{ $worker->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3" style="display: none;" id="modal-wallet-commission-div">
                                <div class="mb-1">
                                    <label for="modal-wallet-commission" class="form-label">{{ __('field.commission') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" name="commission" id="modal-wallet-commission" required>
                                        <option value="">{{ __('field.select_commission') }}</option>
                                        @for ($i = 1; $i <= 100; $i++)
                                            <option value="{{ $i }}">{{ $i }}%</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="save-wallet-user-btn">
                        <i class="ti ti-check me-1"></i>
                        {{ __('general.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Package User Modal -->
    <div class="modal fade" id="addPackageUserModal" tabindex="-1" aria-labelledby="addPackageUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPackageUserModalLabel">{{ __('locale.add_users_to') }} {{ __('locale.packages') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-package-user-form">
                        @csrf
                        <input type="hidden" name="package_id" id="modal-package-id">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <div class="mb-1">
                                    <label for="modal-package-user" class="form-label">{{ __('field.users') }} <span class="text-danger">*</span></label>
                                    <select class="js-customer-select form-control" name="user_id" id="modal-package-user" required
                                        data-search-url="{{ route('center_user.sales.search-customers') }}"
                                        data-dropdown-parent="#addPackageUserModal"
                                        data-placeholder="{{ __('field.search_by_name_phone_or_email') }}"
                                        data-no-results="{{ __('field.no_customers_found') }}"
                                        data-searching="{{ __('field.searching') }}">
                                        <option value="">{{ __('field.select_user') }}</option>
                                        @if($users && $users->count() > 0)
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone ?? $user->full_phone ?? '' }})</option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>{{ __('field.no_users_available') }}</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="mb-1">
                                    <label for="modal-package-type" class="form-label">{{ __('field.type') }} <span class="text-danger">*</span></label>
                                    <select class="form-control" name="package_type" id="modal-package-type" required>
                                        <option value="">{{ __('field.select_type') }}</option>
                                        @foreach($walletPaymentMethods as $paymentMethod)
                                            <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="save-package-user-btn">
                        <i class="ti ti-check me-1"></i>
                        {{ __('general.save') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-selects.js', 'resources/assets/js/app-ecommerce-product-add.js', 'resources/assets/js/form-wizard-icons.js', 'resources/assets/js/sales-customer-select.js'])
    @include('CenterUser.Components.translation-js')

    <script>
        $(document).ready(function() {
            let cart = @json($cart['items'] ?? []);
            let selectedCustomerId = @json($cart['client_id'] ?? null);
            let selectedCustomerName = null; // Will be set on load or selection
            let selectedCustomerPhone = null; // Will be set on load or selection
            let bookingWizardData = {};
            let bookingIds = []; // Should be array, not object
            let bookingPackageIds = [];
            
            // Configuration from PHP to avoid mixing PHP and JS logic below
            const posConfig = {
                hasCommissionPermission: {{ has_commission_permission() ? 'true' : 'false' }},
                allowedCommissionType: '{{ get_allowed_commission_type("booking") }}',
                currency: '{{ get_currency() }}',
                translations: {
                    max_commission: '{{ __("field.max_commission") }}',
                    commission_cannot_exceed: '{{ __("field.commission_cannot_exceed_service_price") }}',
                    select_commission: '{{ __("field.select_commission") }}'
                }
            };

            // Store service prices from loaded services
            let servicesData = {};
            @foreach ($services as $service)
                servicesData[{{ $service->id }}] = {
                    id: {{ $service->id }},
                    name: '{{ addslashes($service->name) }}',
                    price: {{ $service->price ?? 0 }},
                    category_id: {{ $service->category_id ?? 'null' }},
                    has_commission: {{ $service->has_commission ? 'true' : 'false' }}
                };
            @endforeach

            // Initialize Select2
            $('#booking-services, #product-products, #product-sales_worker, #product-worker').select2();
            
            // Clear invalid state when payment method is selected
            $('#booking-payment_type, #product-payment_type').on('change', function() {
                $(this).removeClass('is-invalid');
                if (this.id === 'booking-payment_type') {
                    validateBookingPayments();
                }
            });
            
            // Phone prefix toggle for UAE (+971) in Add Customer Modal
            function toggleQuickCustomerPhonePrefix() {
                const countryCodeSelect = $('#addCustomerModal').find('select[name="country_code"]');
                const phonePrefixContainer = $('#quick_customer_phone_prefix_container');
                const phoneInputContainer = $('#quick_customer_phone_input_container');
                
                if (countryCodeSelect.length && countryCodeSelect.val() === '+971') {
                    phonePrefixContainer.show();
                    phoneInputContainer.removeClass('col-md-4').addClass('col-md-2');
                } else {
                    phonePrefixContainer.hide();
                    phoneInputContainer.removeClass('col-md-2').addClass('col-md-4');
                }
            }
            
            // Listen for country code changes in Add Customer Modal
            $(document).on('change', '#addCustomerModal select[name="country_code"]', function() {
                toggleQuickCustomerPhonePrefix();
            });
            
            // Initial check when modal is opened
            $('#addCustomerModal').on('shown.bs.modal', function() {
                toggleQuickCustomerPhonePrefix();
            });
            
            // Real-time phone validation for quick add customer
            function validateQuickCustomerPhone() {
                const phoneInput = $('#quick_customer_phone');
                const countryCode = $('#addCustomerModal').find('select[name="country_code"]').val();
                const value = (phoneInput.val() || '').trim();
                const feedbackEl = phoneInput.siblings('.invalid-feedback');
                
                if (!value) {
                    phoneInput.removeClass('is-invalid is-valid');
                    if (feedbackEl.length) feedbackEl.text('');
                    return false;
                }
                
                let isValid = false;
                if (countryCode === '+971') {
                    // For UAE, must be exactly 7 digits
                    isValid = value.length === 7 && /^[0-9]+$/.test(value);
                    
                } else {
                    // For other countries, 6-10 digits
                    isValid = /^[0-9]+$/.test(value) && value.length >= 6 && value.length <= 10;
                    if (!isValid && feedbackEl.length) {
                        feedbackEl.text('{{ __('field.mobile_number') }} must be between 6 and 10 digits');
                    }
                }
                
                if (isValid) {
                    phoneInput.removeClass('is-invalid').addClass('is-valid');
                    if (feedbackEl.length) feedbackEl.text('');
                } else {
                    phoneInput.removeClass('is-valid').addClass('is-invalid');
                }
                
                return isValid;
            }
            
            // Attach real-time validation
            $(document).on('input keyup blur', '#quick_customer_phone', function() {
                validateQuickCustomerPhone();
            });
            
            // Re-validate when country code changes
            $(document).on('change', '#addCustomerModal select[name="country_code"]', function() {
                validateQuickCustomerPhone();
            });

            
            // Initialize Booking Wizard Stepper
            const bookingStepper = new Stepper(document.querySelector('.bs-stepper'));

            // Store original services options for filtering
            let $allServicesOptions = $('#booking-services').find('option').clone();

            // Booking Step 1: Category Selection & Service Filtering
            $('#wizard_category_tree_jstree').on("select_node.jstree", function (e, data) {
                const selectedCategoryId = data.node.id;
                filterWizardServices(selectedCategoryId);
            });

            function filterWizardServices(categoryId) {
                const $servicesSelect = $('#booking-services');
                const $servicesContainer = $('#wizard-services-container');
                
                if (categoryId && categoryId !== "") {
                    $servicesContainer.show();
                } else {
                    $servicesContainer.hide();
                    $servicesSelect.val(null).trigger('change');
                    return;
                }

                // Capture currently selected values
                const currentSelections = $servicesSelect.val() || [];
                
                // Clear existing selection and options
                $servicesSelect.empty();
                
                // Track which IDs we've added to avoid duplicates
                const addedIds = new Set();

                // 1. Add currently selected services (from any category)
                $allServicesOptions.each(function() {
                    const optionId = $(this).val();
                    if (currentSelections.includes(optionId)) {
                        $servicesSelect.append($(this).clone());
                        addedIds.add(optionId);
                    }
                });
                
                // 2. Add all services from the selected category (if not already added)
                $allServicesOptions.each(function() {
                    const optionId = $(this).val();
                    const optionCategoryId = $(this).data('category-id');
                    if (!addedIds.has(optionId) && (categoryId === "" || optionCategoryId == categoryId)) {
                        $servicesSelect.append($(this).clone());
                        addedIds.add(optionId);
                    }
                });
                
                // Restore selection and refresh Select2
                $servicesSelect.val(currentSelections).trigger('change');
            }

            $('#booking-services' ).on('change', function() {
                $('#booking-nextStep1').prop('disabled', (!$(this).val() || $(this).val().length === 0));
            });
            $('#booking-packages' ).on('change', function() {
                $('#booking-nextStep1').prop('disabled', (!$(this).val() || $(this).val().length === 0));
            });

            // Auto-select category in Quick Add modal based on wizard selection
            $('#addServiceQuickBtn').on('click', function() {
                const selectedWizardCategory = $('#wizard_category_tree_input').val();
                if (selectedWizardCategory) {
                    const tree = $('#quick_service_category_tree_jstree').jstree(true);
                    if (tree) {
                        tree.deselect_all();
                        tree.select_node(selectedWizardCategory);
                        const node = tree.get_node(selectedWizardCategory);
                        if (node) {
                            $('#quick_service_category_tree_selected_text').text(node.text);
                            $('#quick_service_category_tree_input').val(selectedWizardCategory);
                        }
                    }
                }
            });

            $('#booking-nextStep1').on('click', function(e) {
                e.preventDefault();
                let services = $('#booking-services').val() || [];
                let packages = $('#booking-packages').val() || [];
                
                if (services.length === 0 && packages.length === 0) {
                    alert('Please select at least one service or package.');
                    return false;
                }

                bookingIds = services;
                bookingPackageIds = packages; // Track chosen packages
                
                let servicesArray = [];
                services.forEach(service => {
                    var serviceData = servicesData[service] || {};
                    var serviceInfo = {
                        id: service,
                        name: serviceData.name || $('#booking-services').find('option[value="' + service + '"]').text(),
                        price: serviceData.price || 0,
                        has_commission: serviceData.has_commission || false
                    };
                    servicesArray.push(serviceInfo);
                });
                
                let packagesArray = [];
                packages.forEach(pkg => {
                    var pkgOption = $('#booking-packages').find('option[value="' + pkg + '"]');
                    var pkgInfo = {
                        id: pkg,
                        name: pkgOption.text() || 'Package',
                        price: parseFloat(pkgOption.data('price')) || 0
                    };
                    packagesArray.push(pkgInfo);
                });

                $('#booking-service-container').empty();
                servicesArray.forEach(service => {
                    var servicePrice = service.price || 0;
                    var hasCommission = service.has_commission || false;
                    var workers = get_workers(service.id);

                    var service_info = `
                        <div class="row mb-4">
                            <h2>${service.name}</h2>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.date') }}</label>
                                    <input type="date" class="form-control" name="service[${service.id}][date]" value="{{ Carbon\Carbon::now()->toDateString() }}" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.worker') }}</label>
                                    <select class="form-control" name="service[${service.id}][worker_id]">`;
                    $.each(workers, function(index, worker) {
                        const centerUserName = '{{ $centerUser->name ?? "" }}';
                        const workerPhone = worker.phone || '';
                        const displayText = `${worker.name} - ${workerPhone} (${centerUserName})`;
                        service_info += `<option value="${worker.id}">${displayText}</option>`;
                    });
                    service_info += `</select></div></div>
                        <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.from') }}</label>
                                    <input type="time" class="form-control" name="service[${service.id}][from_time]" value="{{ Carbon\Carbon::now()->format('H:i') }}" />
                                </div>
                            </div>
                        <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.to') }}</label>
                                    <input type="time" class="form-control" name="service[${service.id}][to_time]" value="{{ Carbon\Carbon::now()->addHour()->format('H:i') }}" />
                                </div>
                            </div>`;    
                    
                    if (posConfig.hasCommissionPermission && posConfig.allowedCommissionType) {
                        service_info += `
                            <div class="col-md-2">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.commission_type') }}</label>
                                    <input type="hidden" name="service[${service.id}][commission_type]" value="${posConfig.allowedCommissionType}">
                                    <select class="form-control commission-type-select" name="service[${service.id}][commission_type_display]" data-service-id="${service.id}" disabled>
                                        <option value="${posConfig.allowedCommissionType}" selected>
                                            ${posConfig.allowedCommissionType === 'percentage' ? '{{ __("field.percentage") }}' : '{{ __("field.fixed_value") }}'}
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.commission') }}</label>`;
                        
                        if (posConfig.allowedCommissionType === 'percentage') {
                            service_info += `
                                        <select class="form-control commission-percentage-select" name="service[${service.id}][commission]" id="booking-commission_percentage_${service.id}">
                                            <option value="">${posConfig.translations.select_commission}</option>`;
                            for (let i = 1; i <= 100; i++) {
                                service_info += `<option value="${i}">${i}%</option>`;
                            }
                            service_info += `
                                        </select>`;
                        } else {
                            service_info += `
                                        <input type="number" class="form-control commission-fixed-input" name="service[${service.id}][commission]" id="booking-commission_fixed_${service.id}" placeholder="{{ __('field.commission') }}" step="0.01" min="0" max="${servicePrice}" data-service-price="${servicePrice}">
                                        <small class="text-muted commission-max-hint" id="booking-commission_max_hint_${service.id}">${posConfig.translations.max_commission}: ${parseFloat(servicePrice).toFixed(5)} ${posConfig.currency}</small>`;
                        }
                        service_info += `
                                </div>
                            </div>`;
                    }
                    
                    service_info += `</div>`;

                    $('#booking-service-container').append(service_info);
                    
                    // Add real-time validation for fixed commission input after element is appended
                    if (posConfig.hasCommissionPermission && posConfig.allowedCommissionType === 'fixed') {
                        setTimeout(function() {
                            var currentServiceId = service.id;
                            var currentServicePrice = servicePrice;
                            var $fixedInput = $('#booking-commission_fixed_' + currentServiceId);
                            var $hint = $('#booking-commission_max_hint_' + currentServiceId);
                            
                            if ($fixedInput.length) {
                                $fixedInput.on('input', function() {
                                    var commissionValue = parseFloat($(this).val()) || 0;
                                    
                                    if (commissionValue > currentServicePrice) {
                                        $(this).addClass('is-invalid');
                                        if ($hint.length) {
                                            $hint.removeClass('text-muted').addClass('text-danger').text(posConfig.translations.commission_cannot_exceed);
                                        }
                                    } else {
                                        $(this).removeClass('is-invalid');
                                        if ($hint.length) {
                                            $hint.removeClass('text-danger').addClass('text-muted').html(posConfig.translations.max_commission + ': ' + parseFloat(currentServicePrice).toFixed(5) + ' ' + posConfig.currency);
                                        }
                                    }
                                    checkBookingCommissionValidation();
                                });
                                setTimeout(function() {
                                    checkBookingCommissionValidation();
                                }, 50);
                            }
                        }, 10);
                    }
                });
                
                packagesArray.forEach(pkg => {
                    var pkgHtml = `
                        <div class="row mb-4">
                            <h2>${pkg.name}</h2>
                            <div class="col-md-12">
                                <div class="alert alert-info border-0 mb-0 py-2">
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <span>{{ __('field.package_does_not_need_schedule') ?? 'This package will be added to the customer account for future use and requires no scheduling.' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    $('#booking-service-container').append(pkgHtml);
                });

                bookingWizardData.packages = packagesArray; // Save it
                bookingStepper.next();
            });

            // Function to check commission validation and enable/disable Next button
            function checkBookingCommissionValidation() {
                if (posConfig.hasCommissionPermission && posConfig.allowedCommissionType === 'fixed') {
                    var hasInvalidCommission = false;
                    if (bookingIds && bookingIds.length > 0) {
                        bookingIds.forEach(function(serviceId) {
                            var $fixedInput = $('#booking-commission_fixed_' + serviceId);
                            if ($fixedInput.length) {
                                var commissionValue = parseFloat($fixedInput.val()) || 0;
                                var servicePrice = parseFloat($fixedInput.data('service-price')) || 0;
                                if (commissionValue > 0 && commissionValue > servicePrice) {
                                    hasInvalidCommission = true;
                                }
                            }
                        });
                    }
                    // Disable/enable Next button based on validation
                    var $nextButton = $('#booking-nextStep2');
                    if (hasInvalidCommission) {
                        $nextButton.prop('disabled', true).addClass('disabled');
                    } else {
                        $nextButton.prop('disabled', false).removeClass('disabled');
                    }
                }
            }

            // Booking Step 2: Booking Details
            $('#booking-nextStep2').on('click', function(e) {
                e.preventDefault();
                
                // Check commission validation before proceeding
                if (posConfig.hasCommissionPermission && posConfig.allowedCommissionType === 'fixed') {
                    var hasInvalid = false;
                    bookingIds.forEach(function(serviceId) {
                        var $fixedInput = $('#booking-commission_fixed_' + serviceId);
                        if ($fixedInput.length) {
                            var commissionValue = parseFloat($fixedInput.val()) || 0;
                            var servicePrice = parseFloat($fixedInput.data('service-price')) || 0;
                            if (commissionValue > servicePrice) {
                                hasInvalid = true;
                                $fixedInput.addClass('is-invalid');
                                var $hint = $('#booking-commission_max_hint_' + serviceId);
                                if ($hint.length) {
                                    $hint.removeClass('text-muted').addClass('text-danger').text(posConfig.translations.commission_cannot_exceed);
                                }
                            }
                        }
                    });
                    if (hasInvalid) {
                        alert(posConfig.translations.commission_cannot_exceed);
                        return false;
                    }
                }
                
                if (bookingIds.length === 0 && bookingPackageIds && bookingPackageIds.length > 0) {
                    // Packages only, skip service validations
                    bookingWizardData.services = [];
                    finishStep2Validation();
                    return;
                }

                var servicesArray = [];
                let isValid = true;

                bookingIds.forEach(service => {
                    var date = $('input[name="service[' + service + '][date]"]').val();
                    var worker_id = $('select[name="service[' + service + '][worker_id]"]').val();
                    var from_time = $('input[name="service[' + service + '][from_time]"]').val();
                    var to_time = $('input[name="service[' + service + '][to_time]"]').val();
                    var commission = '';
                    var commissionType = '';
                    
                    if (posConfig.hasCommissionPermission && posConfig.allowedCommissionType) {
                        commissionType = posConfig.allowedCommissionType;
                        if (commissionType === 'percentage') {
                            commission = $('#booking-commission_percentage_' + service).val();
                        } else if (commissionType === 'fixed') {
                            commission = $('#booking-commission_fixed_' + service).val();
                            var servicePrice = parseFloat($('#booking-commission_fixed_' + service).data('service-price')) || 0;
                            var commissionValue = parseFloat(commission) || 0;
                            if (commission && commissionValue > servicePrice) {
                                alert(posConfig.translations.commission_cannot_exceed + '. {{ __("field.service_price") }}: ' + parseFloat(servicePrice).toFixed(5) + ' ' + posConfig.currency);
                                isValid = false;
                                return false;
                            }
                        }
                    }

                    if (!date || !worker_id || !from_time || !to_time) {
                        alert('Please fill all fields for each service.');
                        isValid = false;
                        return false;
                    }

                    var serviceInfo = {
                        id: service,
                        name: $('#booking-services').find('option[value="' + service + '"]').text(),
                        date: date,
                        worker_id: worker_id,
                        from_time: from_time,
                        to_time: to_time,
                        commission: commission,
                        commission_type: commissionType
                    };

                    servicesArray.push(serviceInfo);
                });

                if (!isValid) return;
                bookingWizardData.services = servicesArray;
                
                finishStep2Validation();
                
                function finishStep2Validation() {
                // Validate customer selection before proceeding to Step 3
                // Note: This validation should not block if user is just selecting discount/wallet/membership
                // Only redirect if truly no customer is selected
                if (!selectedCustomerId && !selectedCustomerName) {
                     // Check if a customer is already selected in the UI but maybe logic didn't catch it (unlikely but safe)
                     // If really no customer, stop and alert
                     if (typeof toastr !== 'undefined') {
                         toastr.error('{{ __('field.customer_required') }}');
                     } else {
                         alert('{{ __('field.customer_required') }}');
                     }
                     
                     // Redirect to Step 1 (Services) - index 0
                     bookingStepper.to(0);
                     
                     // Highlight the customer card
                     $('html, body').animate({
                        scrollTop: $('.card-header:contains("{{ __('field.customer') }}")').offset().top - 100
                     }, 500);
                     return false;
                }

                // Proceed to Step 3
                
                // Auto-populate customer data
                bookingWizardData.name = selectedCustomerName;
                bookingWizardData.mobile = selectedCustomerPhone;
                
                // Update Step 3 Display
                $('#booking-step3-customer-name').text(selectedCustomerName);
                $('#booking-step3-customer-mobile').text(selectedCustomerPhone || '{{ __('field.no_mobile') }}');
                
                // Auto-load services/wallets
                if (selectedCustomerPhone) {
                    loadCustomerServices(selectedCustomerPhone);
                } else {
                     $('#booking-servicesTable, #booking-walletsElement, #booking-membershipsElement').html('');
                }

                bookingStepper.next();
                
                // Trigger validation immediately to ensure Next button starts in correct state
                if (typeof validateBookingPayments === 'function') {
                    validateBookingPayments();
                }
                } // End of finishStep2Validation
            });

            // Enable next button will be handled by validateBookingPayments based on selections

            // Handle Multiple Payments Toggle
            $(document).on('change', '#booking-multiple_payments_toggle', function() {
                if ($(this).is(':checked')) {
                    $('#booking-payment-method-container').hide();
                    $('#booking-multiple-payments-container').show();
                    $('#booking-payment_type').prop('required', false);
                    
                    // Reset multiple payments list with one row pre-filled with total
                    $('#booking-multiple-payments-list').empty();
                    const totalToPay = getCurrentBookingTotal();
                    $('#booking-multi-payment-total-display').text(totalToPay.toFixed(2));
                    
                    $('#btn-add-booking-payment').trigger('click');
                } else {
                    $('#booking-payment-method-container').show();
                    $('#booking-multiple-payments-container').hide();
                    $('#booking-payment_type').prop('required', true);
                }
                validateBookingPayments();
            });

            // Helper to get total for current booking
            function getCurrentBookingTotal() {
                let total = 0;
                
                // Track remaining slots locally for this calculation pass
                let tempPackageSlots = JSON.parse(JSON.stringify(userPackagesData));
                let selectedPackageIds = [];
                $('.booking-package-radio:checked').each(function() {
                    selectedPackageIds.push($(this).val());
                });

                if (bookingWizardData.services && bookingWizardData.services.length > 0) {
                    bookingWizardData.services.forEach(function(item) {
                        var serviceData = get_service(item.id);
                        if (serviceData) {
                            total += calculateDiscountedServicePrice(serviceData.price, item.id, tempPackageSlots, selectedPackageIds);
                        }
                    });
                }
                if (bookingWizardData.packages && bookingWizardData.packages.length > 0) {
                    bookingWizardData.packages.forEach(function(item) {
                        total += parseFloat(item.price) || 0;
                    });
                }
                return total;
            }

            // Real-time validation for booking payments
            function validateBookingPayments() {
                const isMultiple = $('#booking-multiple_payments_toggle').is(':checked');
                const $nextBtn = $('#booking-nextStep3');
                const $errorMsg = $('#booking-multiple-payments-error');
                let isValid = true;
                const hasPackageSelected = $('.booking-package-radio:checked').length > 0;

                // If package is selected, package type/payment method is auto-applied.
                if (hasPackageSelected) {
                    $errorMsg.hide();
                    $nextBtn.prop('disabled', false);
                    return true;
                }

                if (isMultiple) {
                    const totalToPay = getCurrentBookingTotal();
                    let totalPaid = 0;
                    let hasEmptyFields = false;

                    $('#booking-multiple-payments-list .booking-payment-row').each(function() {
                        const type = $(this).find('.booking-multi-payment-type').val();
                        const amount = parseFloat($(this).find('.booking-multi-payment-amount').val()) || 0;
                        
                        if (!type || amount <= 0) {
                            hasEmptyFields = true;
                        }
                        totalPaid += amount;
                    });

                    const diff = Math.abs(totalPaid - totalToPay);
                    if (hasEmptyFields) {
                        $errorMsg.text('{{ __('field.please_fill_all_required_fields') }}').show();
                        isValid = false;
                    } else if (diff > 0.01) {
                        const amountText = totalPaid.toFixed(2) + ' / ' + totalToPay.toFixed(2);
                        if (totalPaid < totalToPay) {
                            $errorMsg.text('{{ __('field.remaining_amount') }}: ' + (totalToPay - totalPaid).toFixed(2)).show();
                        } else {
                            $errorMsg.text('{{ __('field.excess_amount') }}: ' + (totalPaid - totalToPay).toFixed(2)).show();
                        }
                        isValid = false;
                    } else {
                        $errorMsg.hide();
                    }
                } else {
                    const paymentType = $('#booking-payment_type').val();
                    const hasWalletSelected = $('input[name="discount_id"].booking-wallet-radio:checked').length > 0;
                    
                    // Only a wallet is considered a full payment source here. 
                    // Memberships and Discount Codes only provide discounts and require a payment method for the remaining balance.
                    if (!hasWalletSelected && (!paymentType || paymentType === '')) {
                        isValid = false;
                    }
                    $errorMsg.hide();
                }

                $nextBtn.prop('disabled', !isValid);
                return isValid;
            }

            function getSelectedPackagePaymentType() {
                const selectedPackage = $('.booking-package-radio:checked');
                if (selectedPackage.length === 0) {
                    return null;
                }
                const packageType = selectedPackage.data('package-type') || null;
                if (packageType) {
                    return packageType;
                }
                return resolvePackagePaymentMethodFromList();
            }

            function resolvePackagePaymentMethodFromList() {
                const $paymentSelect = $('#booking-payment_type');
                const options = $paymentSelect.find('option').toArray();
                const packageOption = options.find(function(option) {
                    const value = String(option.value || '').toLowerCase();
                    const text = String($(option).text() || '').toLowerCase();
                    return value.includes('package') || text.includes('package');
                });

                return packageOption ? packageOption.value : 'package';
            }

            function syncBookingPaymentUiByPackageSelection() {
                const selectedPackage = $('.booking-package-radio:checked');
                const hasPackageSelected = selectedPackage.length > 0;
                const packagePaymentType = getSelectedPackagePaymentType();
                const $multipleToggle = $('#booking-multiple_payments_toggle');
                const $multipleToggleRow = $multipleToggle.closest('.row');

                if (hasPackageSelected) {
                    // Package payment behavior: hide manual payment controls and lock to package type when available.
                    $multipleToggle.prop('checked', false);
                    $('#booking-multiple-payments-container').hide();
                    $multipleToggleRow.hide();
                    $('#booking-payment-method-container').hide();
                    if (packagePaymentType) {
                        $('#booking-payment_type').val(packagePaymentType);
                    }
                    $('#booking-payment_type').prop('required', false);
                } else {
                    $multipleToggleRow.show();
                    if ($multipleToggle.is(':checked')) {
                        $('#booking-payment-method-container').hide();
                        $('#booking-multiple-payments-container').show();
                        $('#booking-payment_type').prop('required', false);
                    } else {
                        $('#booking-payment-method-container').show();
                        $('#booking-multiple-payments-container').hide();
                        $('#booking-payment_type').prop('required', true);
                    }
                }

                validateBookingPayments();
            }

            // Function to update dropdowns to disable selected options
            function updatePaymentDropdowns() {
                const selectedMethods = [];
                $('.booking-multi-payment-type').each(function() {
                    const val = $(this).val();
                    if (val) selectedMethods.push(val);
                });

                $('.booking-multi-payment-type').each(function() {
                    const currentVal = $(this).val();
                    $(this).find('option').each(function() {
                        const optVal = $(this).val();
                        if (optVal && optVal !== currentVal && selectedMethods.includes(optVal)) {
                            $(this).prop('disabled', true).hide();
                        } else {
                            $(this).prop('disabled', false).show();
                        }
                    });
                });
            }

            // Add Multiple Payment Row
            $(document).on('click', '#btn-add-booking-payment', function() {
                const totalToPay = getCurrentBookingTotal();
                let alreadyPaid = 0;
                $('.booking-multi-payment-amount').each(function() {
                    alreadyPaid += parseFloat($(this).val()) || 0;
                });
                
                const remaining = Math.max(0, totalToPay - alreadyPaid);

                const newRow = `
                    <div class="d-flex mb-2 booking-payment-row">
                        <select class="form-control booking-multi-payment-type flex-grow-1 me-2" required>
                            <option value="">{{ __('field.select_payment_method') }}</option>
                            @foreach($paymentMethods as $paymentMethod)
                                <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" class="form-control booking-multi-payment-amount" placeholder="{{ __('field.amount') }}" step="0.01" style="width: 120px;" value="${remaining > 0 ? remaining.toFixed(2) : ''}">
                        <button type="button" class="btn btn-outline-danger ms-2 btn-remove-booking-payment"><i class="ti ti-trash"></i></button>
                    </div>`;
                $('#booking-multiple-payments-list').append(newRow);
                updatePaymentDropdowns();
                validateBookingPayments();
            });

            // Handle changes in multi-payment rows
            $(document).on('change', '.booking-multi-payment-type', function() {
                updatePaymentDropdowns();
                validateBookingPayments();
            });

            $(document).on('input', '.booking-multi-payment-amount', function() {
                validateBookingPayments();
            });

            // Remove Multiple Payment Row
            $(document).on('click', '.btn-remove-booking-payment', function() {
                if ($('#booking-multiple-payments-list .booking-payment-row').length > 1) {
                    $(this).closest('.booking-payment-row').remove();
                    updatePaymentDropdowns();
                    validateBookingPayments();
                } else {
                    alert('At least one payment method is required.');
                }
            });

            $('#booking-nextStep3').on('click', function(e) {
                e.preventDefault();
                
                // Final validation before proceeding
                if (!validateBookingPayments()) {
                    return false;
                }

                // name and mobile are already in bookingWizardData from Step 2 transition
                // But if not set, try to get from global customer variables
                if (!bookingWizardData.name && selectedCustomerName) {
                    bookingWizardData.name = selectedCustomerName;
                    bookingWizardData.mobile = selectedCustomerPhone;
                }
                
                var paymentType = $('#booking-payment_type').val();
                const $paymentTypeField = $('#booking-payment_type');

                // Check both bookingWizardData.name and selectedCustomerName/selectedCustomerId
                if (!bookingWizardData.name && !selectedCustomerName && !selectedCustomerId) {
                    alert('Customer information missing. Please select a customer.');
                    // Redirect to Step 1 (Services) - index 0
                    bookingStepper.to(0);
                    return false;
                }
                
                // Ensure bookingWizardData has customer info
                if (!bookingWizardData.name && selectedCustomerName) {
                    bookingWizardData.name = selectedCustomerName;
                    bookingWizardData.mobile = selectedCustomerPhone;
                }

                if ((!bookingWizardData.services || bookingWizardData.services.length === 0) && (!bookingWizardData.packages || bookingWizardData.packages.length === 0)) {
                    alert('Please complete step 2 (Booking Details) first.');
                    return false;
                }

                // Check if wallet, membership, or discount code is selected
                var hasWalletSelected = $('input[name="discount_id"].booking-wallet-radio:checked').length > 0;
                var hasMembershipSelected = $('input[name="discount_id"].booking-membership-radio:checked').length > 0;
                var hasDiscountSelected = $('input[name="discount_id"].booking-discount-radio:checked').length > 0;

                var hasPackageSelected = $('.booking-package-radio:checked').length > 0;
                var packagePaymentType = getSelectedPackagePaymentType();
                var isMultiple = !hasPackageSelected && $('#booking-multiple_payments_toggle').is(':checked');
                var paymentMethods = [];
                
                if (isMultiple) {
                    $('#booking-multiple-payments-list .booking-payment-row').each(function() {
                        const type = $(this).find('.booking-multi-payment-type').val();
                        const amount = parseFloat($(this).find('.booking-multi-payment-amount').val()) || 0;
                        paymentMethods.push({ method: type, amount: amount });
                    });
                    
                    bookingWizardData.payment_type = 'multiple';
                    bookingWizardData.payment_methods = paymentMethods;
                } else if (hasPackageSelected) {
                    bookingWizardData.payment_type = packagePaymentType || resolvePackagePaymentMethodFromList();
                    bookingWizardData.payment_methods = null;
                } else {
                    bookingWizardData.payment_type = paymentType;
                    bookingWizardData.payment_methods = null;
                }
                
                // Get selected wallet, membership, or discount code info
                var selectedWallet = $('input[name="discount_id"].booking-wallet-radio:checked');
                var selectedMembership = $('input[name="discount_id"].booking-membership-radio:checked');
                var selectedDiscount = $('input[name="discount_id"].booking-discount-radio:checked');
                var paymentMethodDisplay = '';
                var selectedPackage = $('.booking-package-radio:checked');
                
                // Store wallet/membership info if selected
                if (selectedWallet.length > 0) {
                    var walletLabel = selectedWallet.closest('.wallet-item').find('label').text().trim();
                    paymentMethodDisplay = 'Wallet: ' + walletLabel;
                    bookingWizardData.wallet_id = selectedWallet.val();
                } else {
                    bookingWizardData.wallet_id = null;
                }
                
                if (selectedMembership.length > 0) {
                    var membershipLabel = selectedMembership.closest('.membership-item').find('label').text().trim();
                    if (!paymentMethodDisplay) {
                        paymentMethodDisplay = 'Membership: ' + membershipLabel;
                    }
                    bookingWizardData.membership_id = selectedMembership.val();
                    bookingWizardData.membership_percent = selectedMembership.data('membership-percent') || 0;
                    bookingWizardData.membership_no = selectedMembership.data('membership-no') || '';
                } else {
                    bookingWizardData.membership_id = null;
                    bookingWizardData.membership_percent = null;
                    bookingWizardData.membership_no = null;
                }
                
                if (!paymentMethodDisplay) {
                    if (bookingWizardData.payment_type === 'multiple' && bookingWizardData.payment_methods) {
                        paymentMethodDisplay = bookingWizardData.payment_methods.map(m => m.method + ': ' + m.amount.toFixed(2)).join(', ');
                    } else if (selectedPackage.length > 0) {
                        var packageLabel = selectedPackage.closest('.package-item').find('label').first().text().trim();
                        if (packagePaymentType) {
                            paymentMethodDisplay = 'Package (' + packagePaymentType + '): ' + packageLabel;
                        } else {
                            paymentMethodDisplay = 'Package: ' + packageLabel;
                        }
                    } else {
                        paymentMethodDisplay = bookingWizardData.payment_type || '{{ __('field.not_selected') }}';
                    }
                }
                
                // Store discount code info if selected
                if (selectedDiscount.length > 0) {
                    var discountLabel = selectedDiscount.closest('.discount-item').find('label').text().trim();
                    bookingWizardData.discount_code = discountLabel;
                    bookingWizardData.discount_id = selectedDiscount.val();
                } else {
                    bookingWizardData.discount_code = null;
                    bookingWizardData.discount_id = null;
                }

                // Build review HTML
                let reviewHtml = `<table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="fw-bolder" scope="col">{{__('field.services')}}</th>
                            <th class="fw-bolder" scope="col">{{__('field.price')}}</th>
                            <th class="fw-bolder" scope="col">{{__('field.date')}}</th>
                            <th class="fw-bolder" scope="col">{{__('field.worker')}}</th>
                            <th class="fw-bolder" scope="col">{{__('field.from')}}</th>
                            <th class="fw-bolder" scope="col">{{__('field.to')}}</th>
                        </tr>
                    </thead>
                    <tbody>`;
                $.each(bookingWizardData.services, function(index, item) {
                    worker = get_worker(item.worker_id);
                    service = get_service(item.id);
                    var servicePrice = service ? service.price : 0;
                    var discountedPrice = calculateDiscountedServicePrice(servicePrice);
                    reviewHtml += `<tr>
                        <td>${item.name.trim()}</td>
                        <td class="review-item-price">${servicePrice.toFixed(2)} {{ get_currency() }}</td>
                        <td>${item.date}</td>
                        <td>${worker ? worker.name : 'N/A'}</td>
                        <td>${item.from_time}</td>
                        <td>${item.to_time}</td>
                    </tr>`;
                });
                
                if (bookingWizardData.packages && bookingWizardData.packages.length > 0) {
                    $.each(bookingWizardData.packages, function(index, pkg) {
                        var pkgPrice = parseFloat(pkg.price) || 0;
                        reviewHtml += `<tr class="table-info">
                            <td><i class="ti ti-package me-1"></i> ${pkg.name.trim()} (New Purchase)</td>
                            <td class="review-item-price">${pkgPrice.toFixed(2)} {{ get_currency() }}</td>
                            <td colspan="4" class="text-center">{{ __('field.package_does_not_need_schedule') ?? 'No Scheduling Needed' }}</td>
                        </tr>`;
                    });
                }
                // Calculate total with discount
                var totalAmount = 0;
                let tempPackageSlots = JSON.parse(JSON.stringify(userPackagesData));
                let selectedPackageIds = [];
                $('.booking-package-radio:checked').each(function() {
                    selectedPackageIds.push($(this).val());
                });

                $.each(bookingWizardData.services, function(index, item) {
                    var serviceData = get_service(item.id);
                    if (serviceData) {
                        var discountedPrice = calculateDiscountedServicePrice(serviceData.price, item.id, tempPackageSlots, selectedPackageIds);
                        totalAmount += discountedPrice;
                    }
                });
                if (bookingWizardData.packages && bookingWizardData.packages.length > 0) {
                    $.each(bookingWizardData.packages, function(index, pkg) {
                        totalAmount += parseFloat(pkg.price) || 0;
                    });
                }
                
                reviewHtml += `<tr>
                    <th class="fw-bolder" scope="row">{{__('field.full_name')}}</th>
                    <td colspan="5">${bookingWizardData.name}</td>
                </tr>
                <tr>
                    <th class="fw-bolder" scope="row">{{__('field.mobile')}}</th>
                    <td colspan="5">${bookingWizardData.mobile}</td>
                </tr>
                <tr>
                    <th class="fw-bolder" scope="row">{{__('field.payment_method')}}</th>
                    <td colspan="5">${paymentMethodDisplay}</td>
                </tr>`;
                
                // Show discount code if selected
                if (bookingWizardData.discount_code) {
                    reviewHtml += `<tr>
                        <th class="fw-bolder" scope="row">{{__('field.discount_code')}}</th>
                        <td colspan="5">${bookingWizardData.discount_code}</td>
                    </tr>`;
                }
                
                reviewHtml += `<tr class="table-active">
                    <th class="fw-bolder" scope="row">{{__('field.subtotal')}}</th>
                    <td colspan="5" class="fw-bolder" id="booking-review-subtotal">${totalAmount.toFixed(2)} {{ get_currency() }}</td>
                </tr></tbody></table>`;

                $('#booking-review-content').html(reviewHtml);
                // Apply discount to service prices in review table
                updateBookingReviewServicePrices();
                bookingStepper.next();
            });

            // Booking Step 4: Add to Cart (one booking with all services)
            $('#addBookingToCart').on('click', function() {
                if ((!bookingWizardData.services || bookingWizardData.services.length === 0) && (!bookingWizardData.packages || bookingWizardData.packages.length === 0)) {
                    alert('Please complete all steps first.');
                    return;
                }

                var serviceRows = [];
                if (bookingWizardData.services) {
                    bookingWizardData.services.forEach(function(service) {
                        var worker = get_worker(service.worker_id);
                        var serviceData = get_service(service.id);
                        var originalPrice = serviceData ? serviceData.price : 0;
                        var discountedPrice = calculateDiscountedServicePrice(originalPrice);
                        serviceRows.push({
                            id: service.id,
                            name: service.name,
                            price: discountedPrice,
                            original_price: originalPrice,
                            worker_id: service.worker_id,
                            worker_name: worker ? worker.name : '',
                            date: service.date,
                            from_time: service.from_time,
                            to_time: service.to_time,
                            commission: service.commission || null,
                            commission_type: service.commission_type || null
                        });
                    });
                }
                
                var packageRows = [];
                if (bookingWizardData.packages) {
                    bookingWizardData.packages.forEach(function(pkg) {
                        packageRows.push({
                            id: pkg.id,
                            name: pkg.name,
                            price: pkg.price
                        });
                    });
                }

                let selectedPackageIds = [];
                $('.booking-package-radio:checked').each(function() {
                    selectedPackageIds.push($(this).val());
                });

                cart.push({
                    type: 'service',
                    services: serviceRows,
                    packages: packageRows,
                    client_name: bookingWizardData.name,
                    client_mobile: bookingWizardData.mobile,
                    payment_type: bookingWizardData.payment_type || null,
                    payment_methods: bookingWizardData.payment_methods || null,
                    wallet_id: bookingWizardData.wallet_id || null,
                    membership_id: bookingWizardData.membership_id || null,
                    discount_id: bookingWizardData.discount_id || null,
                    user_package_ids: selectedPackageIds
                });

                saveCartToSession();
                renderCart();
                resetBookingWizard();

                if (typeof toastr !== 'undefined') {
                    toastr.success('{{ __('locale.bookings') }} added to cart');
                }
            });

            // Helper functions
            function get_service(service_id) {
                return servicesData[service_id] || null;
            }

            function get_worker(worker_id) {
                var worker = '';
                $.ajax({
                    url: "{{ route('center_user.workers.info') }}",
                    method: 'GET',
                    async: false,
                    data: {
                        _token: '{{ csrf_token() }}',
                        worker_id: worker_id,
                    },
                    success: function(response) {
                        worker = response;
                    }
                });
                return worker;
            }

            function get_workers(service_id) {
                var workers = [];
                $.ajax({
                    url: "{{ route('center_user.workers.get-workers-by-service') }}",
                    method: 'GET',
                    async: false,
                    data: {
                        _token: '{{ csrf_token() }}',
                        service_id: service_id,
                    },
                    success: function(response) {
                        workers = response;
                    }
                });
                return workers;
            }

            function resetBookingWizard() {
                $('#booking-services').val(null).trigger('change');
                // Hide services container
                $('#wizard-services-container').hide();
                // Reset category tree
                $('#wizard_category_tree_jstree').jstree("deselect_all");
                $('#wizard_category_tree_selected_text').text('{{ __("general.choose") }}');
                $('#wizard_category_tree_input').val('');

                // Name/Mobile are reset, but if customer is still selected, next booking should use them again
                // So we don't clear global selectedCustomer vars, just the wizard local usage
                
                $('#booking-payment_type').val('');
                $('#booking-service-container, #booking-review-content').empty();
                // Clear the loaded wallet/membership HTML to prevent stale data
                $('#booking-servicesTable, #booking-walletsElement, #booking-membershipsElement').empty();
                
                bookingWizardData = {};
                bookingIds = {};
                bookingStepper.to(1);
            }

            // Product Tab Functions - Match BuyProduct Structure
            // Store product data
            let productsData = {};
            @foreach ($products as $product)
                @php
                    $price = $product->retail_price && $product->retail_price > 0 
                        ? $product->retail_price 
                        : ($product->supply_price ?? 0);
                @endphp
                productsData[{{ $product->id }}] = {
                    id: {{ $product->id }},
                    name: '{{ addslashes($product->name) }}',
                    price: {{ $price }},
                    supply_price: {{ $product->supply_price ?? 0 }},
                    retail_price: {{ $product->retail_price ?? 0 }}
                };
            @endforeach

            $('#addProductBtn').on('click', function() {
                const selectedProducts = $('#product-products').val();
                const discount = $('#product-discount').val();
                const workerId = $('#product-worker').val();
                const commission = $('#product-commission').val();
                const paymentType = $('#product-payment_type').val();
                const $paymentTypeField = $('#product-payment_type');

                // Validation
                if (!selectedProducts || selectedProducts.length === 0) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.please_select_product') }}');
                    }
                    return;
                }


                // Commission is required if worker is selected
                if (workerId && workerId != '' && !commission) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.commission_is_required_when_worker_selected') }}');
                    }
                    $('#product-commission').focus();
                    return;
                }

                // Validate payment method
                if (!paymentType || paymentType === '') {
                    $paymentTypeField.addClass('is-invalid');
                    $paymentTypeField.siblings('.invalid-feedback').text('{{ __('field.payment_method') }} is required');
                    $paymentTypeField.focus();
                    return;
                } else {
                    $paymentTypeField.removeClass('is-invalid');
                }

                // Add loading state
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                // Small timeout to allow UI update
                setTimeout(function() {
                    // Get branch for stock validation  
                    const branchId = {{ auth('center_user')->user()->branch_id ?? 'null' }};
    
                    // Add each selected product to cart
                    let productsAdded = 0;
                    selectedProducts.forEach(productId => {
                        const productData = productsData[productId];
                        if (!productData) return;
    
                        // Check if product already in cart (as part of a buy_product group)
                        // For now, we'll allow multiple products in one buy_product entry
                        // But check if this exact product is already in cart individually
                        const existingIndex = cart.findIndex(item => 
                            item.type === 'product' && 
                            item.id == productId && 
                            !item.is_buy_product_group
                        );
    
                        if (existingIndex !== -1) {
                            // Product already in cart individually, skip
                            return;
                        }
    
                        // Validate stock if needed
                        // Note: Stock validation would need to be done server-side for accuracy
                        // For now, we'll add it and validate on payment
    
                        // Get worker name for display
                        const worker = workerId ? get_worker(workerId) : null;
    
                        cart.push({
                            type: 'product',
                            id: productId,
                            name: productData.name,
                            price: productData.price,
                            quantity: 1, // Each product is one unit in BuyProduct
                            is_buy_product_group: false,
                            payment_type: paymentType || null,
                            discount: discount || null,
                            worker_id: workerId || null,
                            worker_name: worker ? worker.name : '',
                            commission: commission || null
                        });
    
                        productsAdded++;
                    });
    
                    if (productsAdded > 0) {
                        saveCartToSession();
                        renderCart();
                        // Reset form
                        $('#product-products').val(null).trigger('change');
                        $('#product-discount').val('');
                        $('#product-payment_type').val('');
                        $('#product-sales_worker, #product-worker').val(null).trigger('change');
                        $('#product-commission-div').hide();
                        $('#product-commission').prop('required', false).val('');
    
                        if (typeof toastr !== 'undefined') {
                            toastr.success(productsAdded + ' {{ __('locale.products') }} added to cart');
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.warning('{{ __('field.all_selected_products_already_in_cart') }}');
                        }
                    }

                    // Remove loading state
                    $btn.prop('disabled', false).html(originalHtml);
                }, 100);
            });

            // Cart Functions
            function saveCartToSession() {
                $.ajax({
                    url: '{{ route("center_user.sales.cart") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cart: cart,
                        client_id: selectedCustomerId
                    },
                    async: false
                });
            }

            function calculateTotals() {
                let subtotal = 0;
                cart.forEach(item => {
                    if (item.type === 'user_wallet') {
                        subtotal += parseFloat(item.invoiced_amount || item.amount || 0);
                    } else if (item.type === 'service' && ((item.services && item.services.length) || (item.packages && item.packages.length))) {
                        if (item.services) {
                            item.services.forEach(function(svc) {
                                // Use original_price if available, otherwise use price
                                var originalPrice = parseFloat(svc.original_price || svc.price || 0);
                                // Recalculate discounted price based on current discount selection
                                var discountedPrice = calculateDiscountedServicePrice(originalPrice);
                                subtotal += discountedPrice;
                            });
                        }
                        if (item.packages) {
                            item.packages.forEach(function(pkg) {
                                subtotal += parseFloat(pkg.price || 0);
                            });
                        }
                    } else {
                        const price = parseFloat(item.price || 0);
                        const quantity = parseInt(item.quantity || 1);
                        subtotal += price * quantity;
                    }
                });
                $('#cart-subtotal').text(subtotal.toFixed(2) + ' {{ get_currency() }}');
                $('#cart-total').text(subtotal.toFixed(2) + ' {{ get_currency() }}');
                // Enable button if cart has items (including wallets) AND customer is selected
                // Allow proceeding with just coupons/wallets
                $('#continueToPayment').prop('disabled', cart.length === 0 || !selectedCustomerId);
            }

            function renderCart() {
                let serviceHtml = '';
                let productHtml = '';
                let walletHtml = '';
                let userWalletHtml = '';
                let serviceCount = 0;
                let productCount = 0;
                let walletCount = 0;
                let userWalletCount = 0;

                cart.forEach((item, index) => {
                    // Get item name
                    let itemName = item.name || '{{ __('field.item') }}';
                    if (item.type === 'user_wallet') {
                        itemName = '{{ __('field.coupon') }}';
                    }
                    if (item.type === 'service' && item.services && item.services.length) {
                        itemName = item.services.length === 1 ? (item.services[0].name || '{{ __('locale.bookings') }}') : ('{{ __('locale.bookings') }} (' + item.services.length + ' {{ __('locale.services') }})');
                    }
                    
                    let itemHtml = `<div class="cart-item mb-3 p-2 border rounded" data-index="${index}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${itemName}</h6>`;
                    
                    if (item.type === 'service') {
                        if (item.services && item.services.length) {
                            item.services.forEach(function(svc) {
                                // Use original_price if available, otherwise use price
                                var originalPrice = parseFloat(svc.original_price || svc.price || 0);
                                // Recalculate discounted price based on current discount selection
                                var servicePrice = calculateDiscountedServicePrice(originalPrice);
                                itemHtml += `<small class="text-muted d-block">
                                    ${svc.name || ''} &ndash; {{ __('field.worker') }}: ${svc.worker_name || ''}<br>
                                    {{ __('field.date') }}: ${svc.date || ''} &nbsp; ${(svc.from_time || '')} - ${(svc.to_time || '')} &nbsp; ${servicePrice.toFixed(2)} {{ get_currency() }}
                                </small>`;
                            });
                        }
                        
                        if (item.packages && item.packages.length) {
                            item.packages.forEach(function(pkg) {
                                var pkgPrice = parseFloat(pkg.price || 0);
                                itemHtml += `<small class="text-muted d-block">
                                    <i class="ti ti-package me-1"></i> ${pkg.name || ''} &nbsp; ${pkgPrice.toFixed(2)} {{ get_currency() }}
                                </small>`;
                            });
                        }
                    } else if (item.type === 'product') {
                        itemHtml += `<small class="text-muted">
                            {{ __('field.quantity') }}: ${item.quantity}`;
                        if (item.worker_id && item.worker_name) {
                            itemHtml += `<br>{{ __('field.worker') }}: ${item.worker_name}`;
                        }
                        if (item.commission) {
                            itemHtml += `<br>{{ __('field.commission') }}: ${item.commission}%`;
                        }
                        itemHtml += `</small>`;
                    } else if (item.type === 'user_wallet') {
                        itemHtml += `<small class="text-muted">
                            {{ __('field.code') }}: ${item.code || ''}<br>
                            {{ __('field.amount') }}: ${item.amount || 0} {{ get_currency() }}`;
                        if (item.invoiced_amount) {
                            itemHtml += `<br>{{ __('field.invoiced_amount') }}: ${item.invoiced_amount || 0} {{ get_currency() }}`;
                        }
                        if (item.wallet_type) {
                            itemHtml += `<br>{{ __('field.type') }}: ${item.wallet_type}`;
                        }
                        if (item.worker_name) {
                            itemHtml += `<br>{{ __('field.worker') }}: ${item.worker_name}`;
                        }
                        if (item.commission) {
                            itemHtml += `<br>{{ __('field.commission') }}: ${item.commission}%`;
                        }
                        itemHtml += `</small>`;
                    }
                    
                    // Calculate and display price
                    let displayPrice = 0;
                    if (item.type === 'user_wallet') {
                        displayPrice = parseFloat(item.invoiced_amount || item.amount || 0);
                    } else if (item.type === 'service') {
                        if (item.services && item.services.length) {
                            item.services.forEach(function(svc) {
                                // Use original_price if available, otherwise use price
                                var originalPrice = parseFloat(svc.original_price || svc.price || 0);
                                // Recalculate discounted price based on current discount selection
                                var discountedPrice = calculateDiscountedServicePrice(originalPrice);
                                displayPrice += discountedPrice;
                            });
                        }
                        if (item.packages && item.packages.length) {
                            item.packages.forEach(function(pkg) {
                                displayPrice += parseFloat(pkg.price || 0);
                            });
                        }
                    } else {
                        const price = parseFloat(item.price || 0);
                        const quantity = parseInt(item.quantity || 1);
                        displayPrice = price * quantity;
                    }
                    
                    itemHtml += `<div class="mt-1">
                            <strong>${displayPrice.toFixed(2)} {{ get_currency() }}</strong>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
            </div>`;

                    // Distribution
                    if (item.type === 'service') {
                        serviceHtml += itemHtml;
                        serviceCount++;
                    } else if (item.type === 'product') {
                        productHtml += itemHtml;
                        productCount++;
                    } else if (item.type === 'user_wallet') {
                        userWalletHtml += itemHtml;
                        userWalletCount++;
                    }
                });

                // Update DOM
                $('#cart-items-service').html(serviceHtml);
                $('#cart-items-product').html(productHtml);
                $('#cart-items-user-wallet').html(userWalletHtml);

                // Update Counts
                updateTabCount('#cart-booking-count', serviceCount);
                updateTabCount('#cart-products-count', productCount);
                updateTabCount('#cart-user-wallet-count', userWalletCount);

                // Handle Empty States
                $('#cart-empty-service').toggle(serviceCount === 0);
                $('#cart-empty-product').toggle(productCount === 0);
                $('#cart-empty-user-wallet').toggle(userWalletCount === 0);
                
                calculateTotals();
            }

            function updateTabCount(selector, count) {
                const $badge = $(selector);
                $badge.text(count);
                if (count > 0) {
                    $badge.show();
                } else {
                    $badge.hide();
                }
            }

            $(document).on('click', '.remove-item', function() {
                const index = $(this).data('index');
                cart.splice(index, 1);
                saveCartToSession();
                renderCart();
                if (typeof toastr !== 'undefined') {
                    toastr.success('{{ __('field.item_removed') }}');
                }
            });

            $('#continueToPayment').on('click', function() {
                if (cart.length === 0) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.cart_is_empty') }}');
                    }
                    return;
                }

                if (!selectedCustomerId) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.customer_required') }}');
                    }
                    return;
                }

                $.ajax({
                    url: '{{ route("center_user.sales.cart") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cart: cart,
                        client_id: selectedCustomerId
                    },
                    success: function() {
                        window.location.href = '{{ route("center_user.sales.payment") }}';
                    },
                    error: function() {
                        if (typeof toastr !== 'undefined') {
                            toastr.error('{{ __('admin.an_error_occurred') }}');
                        }
                    }
                });
            });

            // Refactored reusable function to load customer services/wallets
            let userPackagesData = [];
            const customerDataCache = {};
            let customerServicesRequest = null;

            function clearCustomerBookingSections() {
                $('#booking-servicesTable, #booking-walletsElement, #booking-membershipsElement, #booking-packagesElement').html('');
                userPackagesData = [];
            }

            function showCustomerBookingLoading() {
                var loadingHtml = '<div class="text-center py-3 text-muted"><i class="ti ti-loader-2 ti-spin me-1"></i>{{ __("field.searching") }}...</div>';
                $('#booking-servicesTable').html(loadingHtml);
                $('#booking-walletsElement, #booking-membershipsElement, #booking-packagesElement').html('');
            }

            function get_services(user_phone) {
                return $.ajax({
                    url: "{{ route('center_user.bookings.get-services-by-user') }}",
                    method: 'GET',
                    data: {
                        _token: '{{ csrf_token() }}',
                        user_phone: user_phone,
                    }
                });
            }

            function loadCustomerServices(user_phone) {
                 if (!user_phone) {
                     clearCustomerBookingSections();
                     return;
                 }

                 if (customerDataCache[user_phone]) {
                     renderCustomerBookingData(customerDataCache[user_phone]);
                     return;
                 }

                 showCustomerBookingLoading();
                 if (customerServicesRequest && customerServicesRequest.abort) {
                     customerServicesRequest.abort();
                 }
                 customerServicesRequest = get_services(user_phone);
                 customerServicesRequest
                     .done(function(response) {
                         customerDataCache[user_phone] = response;
                         renderCustomerBookingData(response);
                     })
                     .fail(function(xhr) {
                         if (xhr.statusText === 'abort') return;
                         clearCustomerBookingSections();
                     });
            }

            function renderCustomerBookingData(response) {
                 if (response && response.status) {
                     // Note: name is already handled globally, this updates the service/wallet sections
                     if (response.services) {
                         var services = response.services;
                         let servicesTable = ``;
                         $('#booking-servicesTable').html(servicesTable);
                         if (services.length != 0) {
                            servicesTable += `<hr /><h5>User Services</h5>
                                <table class="table table-bordered mb-4">
                                    <thead>
                                        <tr>
                                            <th class="fw-bolder" scope="col">{{__('field.services')}}</th>
                                            <th class="fw-bolder" scope="col">1</th>
                                            <th class="fw-bolder" scope="col">2</th>
                                            <th class="fw-bolder" scope="col">3</th>
                                            <th class="fw-bolder" scope="col">4</th>
                                            <th class="fw-bolder" scope="col">5</th>
                                            <th class="fw-bolder" scope="col">{{__('field.free')}}</th>
                                            <th class="fw-bolder" scope="col">{{__('field.more_than')}} 5</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;
                            $.each(services, function(index, item) {
                                var service = item[0].service;
                                var categoryName = '';
                                if (service.category) {
                                    if (service.category.translation && service.category.translation.name) {
                                        categoryName = service.category.translation.name;
                                    } else {
                                        categoryName = service.category.name || 'N/A';
                                    }
                                }
                                servicesTable += `<td>${service.name} ${categoryName ? '(' + categoryName + ')' : ''}</td>`;
                                if (item.length <= 5) {
                                    for (let i = 1; i <= item.length; i++) {
                                        servicesTable += `<td style='background: #2ff92f5e'>Yes</td>`;
                                    }
                                    for (let i = 1; i <= 5 - item.length; i++) {
                                        servicesTable += `<td>No</td>`;
                                    }
                                    servicesTable += `<td>No</td><td>No</td>`;
                                } else {
                                    for (let i = 1; i <= 5; i++) {
                                        servicesTable += `<td style='background: #2ff92f5e'>Yes</td>`;
                                    }
                                    servicesTable += `<td>No</td><td>${item.length}</td>`;
                                }
                                servicesTable += `</tr>`;
                            });
                            servicesTable += `</tbody></table>`;
                            $('#booking-servicesTable').html(servicesTable);
                        }
                    }

                    if (response.wallets) {
                        var wallets = response.wallets;
                        let walletsElement = ``;
                        $('#booking-walletsElement').html(walletsElement);
                        if (wallets.length != 0) {
                            walletsElement += `<hr /><div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">Wallet</h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary clear-wallet-selection" style="display: none;">
                                    <i class="ti ti-x me-1"></i>{{ __('general.clear') }}
                                </button>
                            </div><div class="row g-2">`;
                            $.each(wallets, function(index, item) {
                                var wallet = item.wallet;
                                var balance = parseFloat(item.remaining_balance || 0);
                                
                                walletsElement += `<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                    <div class="form-check wallet-item" style="padding: 10px;color: #fff;background-color: #428bca;border-color: #357ebd;border-radius: 4px;min-height: 50px;display: flex;align-items: center;gap: 10px;font-size: 10px;width: 100%;">
                                        <label class="form-check-label flex-grow-1 text-start" for="booking-wallets${wallet.id}" style="word-break: break-word;white-space: normal;overflow: hidden;min-width: 0;margin: 0;">
                                            ${wallet.code + ' [' + balance.toFixed(2) + ' AED]'}
                                        </label>
                                        <input class="form-check-input flex-shrink-0 booking-wallet-radio" type="radio" name="discount_id" data-name="discount_id" value="${wallet.id}" id="booking-wallets${wallet.id}" data-wallet-amount="${balance}" style="margin-top: 0;width: 18px;height: 18px;flex-shrink: 0;">
                                    </div>
                                </div>`;
                            });
                            walletsElement += `</div>`;
                            $('#booking-walletsElement').html(walletsElement);
                            // Attach event listeners for wallet radios
                            attachRadioClearHandlers();
                        }
                    }

                    if (response.memberships) {
                        var memberships = response.memberships;
                        let membershipsElement = ``;
                        $('#booking-membershipsElement').html(membershipsElement);
                        if (memberships.length != 0) {
                            membershipsElement += `<hr /><div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">MemberShip Cards</h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary clear-membership-selection" style="display: none;">
                                    <i class="ti ti-x me-1"></i>{{ __('general.clear') }}
                                </button>
                            </div><div class="row g-2">`;
                            $.each(memberships, function(index, item) {
                                membershipsElement += `<div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                    <div class="form-check membership-item" style="padding: 10px;color: #fff;background-color: #428bca;border-color: #357ebd;border-radius: 4px;min-height: 50px;display: flex;align-items: center;gap: 10px;font-size: 14px;width: 100%;">
                                        <label class="form-check-label flex-grow-1 text-start" for="booking-memberships${item.id}" style="word-break: break-word;white-space: normal;overflow: hidden;min-width: 0;margin: 0;">
                                            ${item.membership_no + ' [' + item.percent + '%]'}
                                        </label>
                                        <input class="form-check-input flex-shrink-0 booking-membership-radio" type="radio" name="discount_id" data-name="discount_id" value="${item.id}" data-membership-percent="${item.percent}" data-membership-no="${item.membership_no}" id="booking-memberships${item.id}" style="margin-top: 0;width: 18px;height: 18px;flex-shrink: 0;">
                                    </div>
                                </div>`;
                            });
                            membershipsElement += `</div>`;
                            $('#booking-membershipsElement').html(membershipsElement);
                            // Attach event listeners for membership radios
                            attachRadioClearHandlers();
                        }
                    }
                    if (response.packages) {
                        var allPackages = response.packages;
                        userPackagesData = allPackages; // Store ALL globally for calculation

                        // Get selected service IDs from Step 1 (bookingIds is set on nextStep1)
                        var selectedServiceIds = (bookingIds || []).map(function(id) { return String(id); });

                        // Filter: only show packages that have at least 1 remaining slot for a selected service
                        var matchingPackages = allPackages.filter(function(userPackage) {
                            if (!userPackage.remaining_services || userPackage.remaining_services.length === 0) return false;
                            return userPackage.remaining_services.some(function(srv) {
                                return srv.remaining > 0 && selectedServiceIds.includes(String(srv.service_id));
                            });
                        });

                        let packagesElement = ``;
                        $('#booking-packagesElement').html(packagesElement);

                        if (matchingPackages.length > 0) {
                            packagesElement += `<hr /><div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">{{ __('field.packages') }} <small class="text-muted fs-6">({{ __('field.matching_selected_services') ?? 'matching your selected services' }})</small></h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary clear-package-selection" style="display: none;">
                                    <i class="ti ti-x me-1"></i>{{ __('general.clear') }}
                                </button>
                            </div><div class="row g-2">`;

                            $.each(matchingPackages, function(index, userPackage) {
                                // Build services list — highlight matching ones, grey out non-matching
                                let servicesList = '';
                                $.each(userPackage.remaining_services, function(i, srv) {
                                    var svcName = srv.service ? srv.service.name : '';
                                    var isMatch = selectedServiceIds.includes(String(srv.service_id)) && srv.remaining > 0;
                                    if (isMatch) {
                                        servicesList += `<li style="color:#6c757d; margin-bottom: 3px; font-size: 12px;">
                                            <i class="ti ti-circle-check" style="color: #1e5351; font-size: 12px; margin-right: 2px;"></i> ${svcName} (${srv.remaining} left).
                                        </li>`;
                                    } else {
                                        servicesList += `<li style="color:#8a8a8a; margin-bottom: 3px; font-size: 12px;">
                                            <span style="font-size: 14px; line-height: 0; vertical-align: middle; margin-right: 4px;">•</span> ${svcName} (${srv.remaining} left).
                                        </li>`;
                                    }
                                });

                                const packageType = userPackage.package_type || '';

                          packagesElement += `
                                <div class="col-12 col-md-11 mx-auto mb-3">
                                    <div class="package-card" style="background-color: #f2e9de; border-radius: 12px; padding: 14px 16px; cursor: pointer; transition: all 0.2s ease; border: 1px solid transparent;">
                                        <div class="d-flex align-items-start gap-3">
                                            <input 
                                                class="form-check-input booking-package-radio flex-shrink-0 mt-1" 
                                                type="radio" 
                                                name="user_package_ids[]" 
                                                value="${userPackage.id}" 
                                                data-package-type="${packageType}" 
                                                id="booking-package${userPackage.id}"
                                                style="cursor: pointer; width: 1.2rem; height: 1.2rem; border-color: #1e5351; accent-color: #1e5351;"
                                            >

                                            <label 
                                                class="form-check-label w-100" 
                                                for="booking-package${userPackage.id}"
                                                style="cursor: pointer;"
                                            >
                                                <div class="package-title" style="color: #1e5351; font-weight: 600; font-size: 14px; margin-bottom: 8px;">
                                                    ${userPackage.package ? userPackage.package.name : 'Package #' + userPackage.id}
                                                </div>

                                                <ul class="package-services" style="list-style: none; padding: 0; margin: 0;">
                                                    ${servicesList}
                                                </ul>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            `;
                            });

                            packagesElement += `</div>`;
                            $('#booking-packagesElement').html(packagesElement);

                            // Attach event listeners for packages
                            $('.booking-package-radio').on('change', function() {
                                $('.clear-package-selection').toggle($('.booking-package-radio:checked').length > 0);
                                if ($('.booking-package-radio:checked').length > 0) {
                                    $('input[name="discount_id"]').prop('checked', false);
                                    $('.clear-wallet-selection').hide();
                                    $('.clear-membership-selection').hide();
                                    $('#clear-discount-selection').hide();
                                }
                                if (typeof window.togglePaymentMethodVisibility === 'function') {
                                    window.togglePaymentMethodVisibility();
                                }
                                if (typeof syncBookingPaymentUiByPackageSelection === 'function') {
                                    syncBookingPaymentUiByPackageSelection();
                                }
                                updateBookingReviewServicePrices();
                                renderCart();
                                calculateTotals();
                            });

                            $('.clear-package-selection').on('click', function() {
                                $('.booking-package-radio').prop('checked', false);
                                $(this).hide();
                                if (typeof window.togglePaymentMethodVisibility === 'function') {
                                    window.togglePaymentMethodVisibility();
                                }
                                if (typeof syncBookingPaymentUiByPackageSelection === 'function') {
                                    syncBookingPaymentUiByPackageSelection();
                                }
                                updateBookingReviewServicePrices();
                                renderCart();
                                calculateTotals();
                            });
                        } else {
                            // No matching packages — clear the section silently (or show a small note)
                            $('#booking-packagesElement').html('');
                            syncBookingPaymentUiByPackageSelection();
                        }
                    }
                } else {
                    $('#booking-servicesTable, #booking-walletsElement, #booking-membershipsElement, #booking-packagesElement').html('');
                    userPackagesData = [];
                    syncBookingPaymentUiByPackageSelection();
                }
            }

            // Function to calculate discounted service price
            // Note: Wallets and memberships are payment methods, not discounts - they don't reduce the price
            // They deduct from wallet/membership balance on the backend
            function calculateDiscountedServicePrice(originalPrice, serviceId = null, tempSlots = null, packageIds = null) {
                if (!originalPrice) {
                    return parseFloat(originalPrice || 0);
                }
                
                var discountedPrice = parseFloat(originalPrice);

                // 1. Check User Packages first (Treat as 100% discount for that slot)
                if (serviceId && tempSlots && packageIds && packageIds.length > 0) {
                    for (let pkgId of packageIds) {
                        let userPackage = tempSlots.find(p => p.id == pkgId);
                        if (userPackage && userPackage.remaining_services) {
                            let slot = userPackage.remaining_services.find(s => s.service_id == serviceId && s.remaining > 0);
                            if (slot) {
                                slot.remaining--; // Use one slot
                                return 0; // Service is free via package
                            }
                        }
                    }
                }
                
                var selectedDiscount = $('input[name="discount_id"].booking-discount-radio:checked');
                if (selectedDiscount.length > 0) {
                    var discountAmount = parseFloat(selectedDiscount.data('discount-amount') || 0);
                    var discountType = selectedDiscount.data('discount-type') || 'percentage';
                    
                    if (discountAmount > 0) {
                        if (discountType === 'percentage') {
                            discountedPrice = discountedPrice * (1 - discountAmount / 100);
                        } else {
                            discountedPrice = Math.max(0, discountedPrice - discountAmount);
                        }
                    }
                }
                
                var selectedMembership = $('input[name="discount_id"].booking-membership-radio:checked');
                if (selectedMembership.length > 0) {
                    var membershipPercent = parseFloat(selectedMembership.data('membership-percent') || 0);
                    if (membershipPercent > 0) {
                        discountedPrice = discountedPrice * (1 - membershipPercent / 100);
                    }
                }
                
                return discountedPrice;
            }
            
            // Function to update service prices in booking review table
            function updateBookingReviewServicePrices() {
                var reviewTable = $('#booking-review-content').find('table tbody');
                if (reviewTable.length === 0) return;
                
                var totalAmount = 0;
                let tempPackageSlots = JSON.parse(JSON.stringify(userPackagesData));
                let selectedPackageIds = [];
                $('.booking-package-radio:checked').each(function() {
                    selectedPackageIds.push($(this).val());
                });

                reviewTable.find('tr').each(function() {
                    var $row = $(this);
                    var $priceCell = $row.find('td.review-item-price');
                    if ($priceCell.length && $row.find('th').length === 0) {
                        // Get original name (handle both service and package)
                        var fullText = $row.find('td').first().text().trim();
                        var isPackage = fullText.includes('(New Purchase)');
                        var itemName = isPackage ? fullText.replace('(New Purchase)', '').trim() : fullText;

                        if (isPackage) {
                            // Find package in bookingWizardData
                            if (bookingWizardData.packages && bookingWizardData.packages.length > 0) {
                                var matchingPkg = bookingWizardData.packages.find(function(pkg) {
                                    return pkg.name.trim() === itemName;
                                });
                                if (matchingPkg) {
                                    totalAmount += parseFloat(matchingPkg.price) || 0;
                                }
                            }
                        } else {
                            // Find matching service in bookingWizardData
                            if (bookingWizardData.services && bookingWizardData.services.length > 0) {
                                var matchingService = bookingWizardData.services.find(function(svc) {
                                    return svc.name.trim() === itemName;
                                });
                                if (matchingService) {
                                    var serviceData = get_service(matchingService.id);
                                    if (serviceData) {
                                        var discountedPrice = calculateDiscountedServicePrice(serviceData.price, matchingService.id, tempPackageSlots, selectedPackageIds);
                                        let priceText = serviceData.price.toFixed(2);
                                        if (discountedPrice === 0 && serviceData.price > 0) {
                                            priceText = `<del>${serviceData.price.toFixed(2)}</del> <span class="text-success">0.00 (Package)</span>`;
                                        } else if (discountedPrice < serviceData.price) {
                                            priceText = `<del>${serviceData.price.toFixed(2)}</del> <span class="text-primary">${discountedPrice.toFixed(2)}</span>`;
                                        } else {
                                            priceText = priceText + ' {{ get_currency() }}';
                                        }
                                        $priceCell.html(priceText);
                                        totalAmount += discountedPrice;
                                    }
                                }
                            }
                        }
                    }
                });
                
                // Update subtotal
                var $subtotalCell = $('#booking-review-subtotal');
                if ($subtotalCell.length) {
                    $subtotalCell.text(totalAmount.toFixed(2) + ' {{ get_currency() }}');
                }
            }

            // Function to handle radio button clear functionality
            function attachRadioClearHandlers() {
                // Function to toggle clear button visibility based on selection
                function toggleClearButtons() {
                    var hasDiscountSelected = $('input[name="discount_id"].booking-discount-radio:checked').length > 0;
                    var hasWalletSelected = $('input[name="discount_id"].booking-wallet-radio:checked').length > 0;
                    var hasMembershipSelected = $('input[name="discount_id"].booking-membership-radio:checked').length > 0;
                    
                    $('#clear-discount-selection').toggle(hasDiscountSelected);
                    $('.clear-wallet-selection').toggle(hasWalletSelected);
                    $('.clear-membership-selection').toggle(hasMembershipSelected);
                }

                // Clear discount codes selection
                $(document).off('click', '#clear-discount-selection').on('click', '#clear-discount-selection', function() {
                    $('input[name="discount_id"].booking-discount-radio').prop('checked', false);
                    toggleClearButtons();
                    if (typeof window.togglePaymentMethodVisibility === 'function') {
                        window.togglePaymentMethodVisibility();
                    }
                    // Update service prices in review table (remove discount)
                    updateBookingReviewServicePrices();
                    // Re-render cart to update prices (reset to original)
                    renderCart();
                    calculateTotals();
                });

                // Clear wallet selection
                $(document).off('click', '.clear-wallet-selection').on('click', '.clear-wallet-selection', function() {
                    $('input[name="discount_id"].booking-wallet-radio').prop('checked', false);
                    toggleClearButtons();
                    if (typeof window.togglePaymentMethodVisibility === 'function') {
                        window.togglePaymentMethodVisibility();
                    }
                    // Wallet is a payment method, doesn't affect prices - no need to re-render cart
                });

                // Clear membership selection
                $(document).off('click', '.clear-membership-selection').on('click', '.clear-membership-selection', function() {
                    $('input[name="discount_id"].booking-membership-radio').prop('checked', false);
                    toggleClearButtons();
                    if (typeof window.togglePaymentMethodVisibility === 'function') {
                        window.togglePaymentMethodVisibility();
                    }
                    updateBookingReviewServicePrices();
                    renderCart();
                    calculateTotals();
                });

                // Listen for radio button changes (discount, wallet, membership)
                $(document).off('change', 'input[name="discount_id"]').on('change', 'input[name="discount_id"]', function() {
                    if ($('input[name="discount_id"]:checked').length > 0) {
                        $('.booking-package-radio').prop('checked', false);
                        $('.clear-package-selection').hide();
                        if (typeof syncBookingPaymentUiByPackageSelection === 'function') {
                            syncBookingPaymentUiByPackageSelection();
                        }
                    }

                    toggleClearButtons();
                    if (typeof window.togglePaymentMethodVisibility === 'function') {
                        window.togglePaymentMethodVisibility();
                    }
                    
                    var isDiscountCode = $(this).hasClass('booking-discount-radio');
                    var isMembership = $(this).hasClass('booking-membership-radio');
                    if (isDiscountCode || isMembership) {
                        updateBookingReviewServicePrices();
                        renderCart();
                        calculateTotals();
                    }
                    // Wallets and memberships are payment methods - they don't change displayed prices
                    // The backend will deduct the booking amount from wallet/membership balance
                    
                    // Step 3 Next button state is updated via togglePaymentMethodVisibility which calls updateStep3NextButtonState
                });

                // Function to toggle payment method visibility based on wallet/membership/discount selection
                window.togglePaymentMethodVisibility = function() {
                    var hasWalletSelected = $('input[name="discount_id"].booking-wallet-radio:checked').length > 0;
                    var hasMembershipSelected = $('input[name="discount_id"].booking-membership-radio:checked').length > 0;
                    var hasDiscountSelected = $('input[name="discount_id"].booking-discount-radio:checked').length > 0;
                    var hasPackageSelected = $('.booking-package-radio:checked').length > 0;
                    
                    if (hasWalletSelected || hasMembershipSelected || hasDiscountSelected || hasPackageSelected) {
                        $('#booking-multiple-payments-container').hide();
                        $('#booking-multiple_payments_toggle').prop('checked', false);
                    } else {
                        $('#booking-multiple-payments-container').show();
                    }

                    // Hide payment method if wallet or membership is selected (they act as payment methods)
                    // For discount codes, keep payment method visible but make it optional to allow proceeding
                    if (hasWalletSelected || hasPackageSelected) {
                        $('#booking-payment-method-container').hide();
                        $('#booking-payment_type').val('').removeClass('is-invalid');
                        $('#booking-payment_type').prop('required', false);
                    } else if (hasDiscountSelected || hasMembershipSelected) {
                        // Show payment method when discount code or membership is selected (they only give discounts)
                        $('#booking-payment-method-container').show();
                        $('#booking-payment_type').prop('required', false);
                        $('#booking-payment_type').removeClass('is-invalid');
                    } else {
                        $('#booking-payment-method-container').show();
                        $('#booking-payment_type').prop('required', true);
                    }
                    
                    if (typeof validateBookingPayments === 'function') {
                        validateBookingPayments();
                    }
                };


                // Listen for payment method changes to clear wallet selection
                $(document).off('change', '#booking-payment_type').on('change', '#booking-payment_type', function() {
                    if ($(this).val() && $(this).val() !== '') {
                        $('input[name="discount_id"].booking-wallet-radio:checked').prop('checked', false);
                        toggleClearButtons();
                        if (typeof window.togglePaymentMethodVisibility === 'function') {
                            window.togglePaymentMethodVisibility();
                        }
                    }
                    if (typeof validateBookingPayments === 'function') {
                        validateBookingPayments();
                    }
                });

                // Initial check
                toggleClearButtons();
                if (typeof window.togglePaymentMethodVisibility === 'function') {
                    window.togglePaymentMethodVisibility();
                }
            }

            // Attach handlers on page load
            attachRadioClearHandlers();


            // Quick Add Service Modal
            $('#save-quick-service-btn').on('click', function(e) {
                e.preventDefault();
                const form = $('#quick-add-service-form')[0];
                const formData = new FormData(form);
                formData.append('quick_add', '1');

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.services.updateOrCreate") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.message === 'redirect_to_home' || response.service) {
                            const serviceData = response.service || response.data;
                            const $servicesSelect = $('#booking-services');
                            const selectedCategoryIdForNewService = formData.get('category_id');
                            
                            const newOption = new Option(serviceData.name, serviceData.id, false, false);
                            $(newOption).attr('data-category-id', selectedCategoryIdForNewService);
                            
                            // Add to the main select only if it matches current filter or no filter
                            const currentWizardCategory = $('#wizard_category_tree_input').val();
                            if (!currentWizardCategory || currentWizardCategory == selectedCategoryIdForNewService) {
                                $servicesSelect.append(newOption).trigger('change');
                            }

                            // Update the global cache for future filtering
                            const $newOptionClone = $(newOption).clone();
                            if (typeof $allServicesOptions !== 'undefined') {
                                $allServicesOptions = $allServicesOptions.add($newOptionClone);
                            }

                            if (typeof servicesData !== 'undefined') {
                                servicesData[serviceData.id] = {
                                    id: serviceData.id,
                                    name: serviceData.name,
                                    price: serviceData.price || 0,
                                    category_id: selectedCategoryIdForNewService,
                                    has_commission: serviceData.has_commission || false
                                };
                            }

                             $('#addServiceModal').modal('hide');
                             $('#quick-add-service-form')[0].reset();
                             // Reset category tree
                             $('#quick_service_category_tree_jstree').jstree("deselect_all");
                             $('#quick_service_category_tree_selected_text').text('{{ __("general.choose") }}');
                             $('#quick_service_category_tree_input').val('');

                             if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __('admin.operation_done_successfully') }}');
                            }
                            $servicesSelect.val(serviceData.id).trigger('change');
                        }
                    },
                    error: function(xhr) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Quick Add Customer Modal
            $('#save-quick-customer-btn').on('click', function(e) {
                e.preventDefault();
                const form = $('#quick-add-customer-form')[0];
                if (!form.reportValidity()) {
                    return;
                }
                const formData = new FormData(form);
                
                // Handle UAE Phone Prefix
                const countryCodeSelect = formData.get('country_code');
                const phonePrefix = formData.get('phone_prefix');
                const rawPhone = formData.get('phone');
                
                if (phonePrefix && rawPhone && rawPhone.length === 7) {
                    formData.set('phone', phonePrefix + rawPhone);
                }
                
                formData.append('quick_add', '1');

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.users.updateOrCreate") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.message === 'redirect_to_home' || response.user || response.data) {
                            const userData = response.user || response.data;
                            
                            // Get the full name and phone to display
                            const fullName = (userData.first_name + ' ' + (userData.last_name || '')).trim();
                            const phone = userData.phone || formData.get('phone');
                            
                            // Initialize customer in Select2 if it exists, or update UI directly
                            if (typeof loadCustomerServices === 'function') {
                                // Close modal
                                $('#addCustomerModal').modal('hide');
                                $('#quick-add-customer-form')[0].reset();
                                
                                // Call global customer selection logic if available (might need manual trigger depending on UI implementation)
                                // But since this is a custom search box sometimes, we will trigger whatever logic is needed here
                                selectedCustomerPhone = phone;
                                selectedCustomerId = userData.id;
                                selectedCustomerName = fullName;
                                $('#customer-search').val(fullName); // Update search input
                                $('#selected-customer-details').html(`
                                    <strong>${fullName}</strong><br>
                                    <small class="text-muted"><i class="ti ti-phone"></i> ${phone}</small>
                                `);
                                $('#selected-customer-info').show();
                                $('#customer-search-results').hide();
                                $('#customer-search').val('');
                                
                                if ($('#booking-third-step').hasClass('active')) {
                                    loadCustomerServices(phone);
                                }
                                
                                if (typeof toastr !== 'undefined') {
                                    toastr.success('{{ __('admin.operation_done_successfully') }}');
                                }
                            } else {
                                // Fallback
                                window.location.reload();
                            }
                        }
                    },
                    error: function(xhr) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Products Tab Functions
            // Show/hide commission when worker is selected
            $(document).on('change', '#product-worker', function() {
                const workerId = $(this).val();
                const $commissionField = $('#product-commission');
                const $commissionDiv = $('#product-commission-div');
                
                if (workerId == '') {
                    $commissionDiv.hide();
                    $commissionField.prop('required', false);
                    $commissionField.val('');
                } else {
                    $commissionDiv.show();
                    $commissionField.prop('required', true);
                }
            });

            // Wallet Tab Functions

            // Handle Add User button from table - open modal with wallet data
            // This ONLY associates a user with an EXISTING wallet, does NOT create new wallet
            $(document).on('click', '.add-wallet-user-btn', function() {
                const walletId = $(this).data('wallet-id');
                const walletCode = $(this).data('wallet-code');
                const walletAmount = parseFloat($(this).data('wallet-amount')) || 0;
                const walletInvoiced = parseFloat($(this).data('wallet-invoiced')) || 0;
                const walletStart = $(this).data('wallet-start') || null;
                const walletEnd = $(this).data('wallet-end') || null;

                // Set wallet ID in hidden field - this is the EXISTING wallet ID
                $('#modal-wallet-id').val(walletId);
                
                // Update modal title to show wallet code
                $('#addWalletUserModalLabel').text('{{ __('locale.add_users_to') }} {{ __('locale.wallets') }} (' + walletCode + ')');
                
                // Reset form
                $('#add-wallet-user-form')[0].reset();
                $('#modal-wallet-user').val(null).trigger('change');
                $('#modal-wallet-commission-div').hide();
                $('#modal-wallet-commission').prop('required', false);
            });

            // Show/hide commission when worker is selected in modal
            $(document).on('change', '#modal-wallet-worker', function() {
                const workerId = $(this).val();
                const $commissionField = $('#modal-wallet-commission');
                const $commissionDiv = $('#modal-wallet-commission-div');
                
                if (workerId == '') {
                    $commissionDiv.hide();
                    $commissionField.prop('required', false);
                    $commissionField.val('');
                } else {
                    $commissionDiv.show();
                    $commissionField.prop('required', true);
                }
            });

            // Add User Wallet
            // Save Wallet User (from modal)
            $(document).on('click', '#save-wallet-user-btn', function(e) {
                e.preventDefault();
                const $form = $('#add-wallet-user-form');
                const userId = $('#modal-wallet-user').val();
                const walletId = $('#modal-wallet-id').val();
                const walletType = $('#modal-wallet-type').val();
                const workerId = $('#modal-wallet-worker').val();
                const commission = $('#modal-wallet-commission').val();

                // Set commission required attribute based on worker selection
                if (workerId && workerId != '') {
                    $('#modal-wallet-commission').prop('required', true);
                } else {
                    $('#modal-wallet-commission').prop('required', false);
                }

                // Validate form
                if (!$form[0].checkValidity()) {
                    $form[0].reportValidity();
                    return false;
                }

                // Additional validation
                if (!userId) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.please_select_user') }}');
                    }
                    return false;
                }

                if (!walletId) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('admin.an_error_occurred') }}');
                    }
                    return false;
                }

                if (!walletType) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.please_select_type') }}');
                    }
                    return false;
                }

                // Commission is required if worker is selected
                if (workerId && workerId != '' && !commission) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.commission_is_required_when_worker_selected') }}');
                    }
                    $('#modal-wallet-commission').focus();
                    return false;
                }

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                // Get wallet data from button that opened the modal
                const $walletBtn = $('.add-wallet-user-btn[data-wallet-id="' + walletId + '"]');
                const walletCode = $walletBtn.data('wallet-code');
                const walletAmount = parseFloat($walletBtn.data('wallet-amount')) || 0;
                const walletInvoiced = parseFloat($walletBtn.data('wallet-invoiced')) || 0;
                const walletStart = $walletBtn.data('wallet-start') || null;
                const walletEnd = $walletBtn.data('wallet-end') || null;

                // Assign existing wallet to user (creates UserWallet association, NOT a new Wallet)
                $.ajax({
                    url: '{{ route("center_user.users_wallets.updateOrCreate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        wallet_id: walletId, // Existing wallet ID - NOT creating new wallet
                        user_id: userId,
                        wallet_type: walletType,
                        worker_id: workerId || null,
                        commission: commission || null
                    },
                    success: function(response) {
                        // Check if user-wallet association was successful
                        // This only creates UserWallet record, never creates new Wallet
                        if (response.message === 'redirect_to_home' || response.message === '{{ __('admin.operation_done_successfully') }}') {
                            // Update selected customer if not already set
                            if (!selectedCustomerId && userId) {
                                selectedCustomerId = userId;
                                // Save customer selection to session
                                $.ajax({
                                    url: '{{ route("center_user.sales.cart") }}',
                                    type: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        cart: cart,
                                        client_id: userId
                                    },
                                    async: false
                                });
                            }
                            
                            // Check if this user-wallet assignment is already in cart
                            const existingWalletIndex = cart.findIndex(item => 
                                item.type === 'user_wallet' && 
                                item.wallet_id == walletId && 
                                item.user_id == userId
                            );
                            
                            // Only add to cart if not already there
                            if (existingWalletIndex === -1) {
                                // Get worker name if worker is selected
                                let workerName = '';
                                if (workerId) {
                                    const worker = get_worker(workerId);
                                    workerName = worker ? worker.name : '';
                                }
                                
                            cart.push({
                                    type: 'user_wallet', // Separate type for user-wallet assignments
                                    wallet_id: walletId, // Existing wallet ID - NOT creating new wallet
                                code: walletCode,
                                amount: walletAmount,
                                invoiced_amount: walletInvoiced,
                                start_at: walletStart,
                                end_at: walletEnd,
                                user_id: userId,
                                wallet_type: walletType,
                                worker_id: workerId,
                                    worker_name: workerName,
                                commission: commission
                            });

                                saveCartToSession();
                            renderCart();
                            } else {
                                // Wallet already in cart, just update the display
                                renderCart();
                            }

                            // Close modal and reset form
                            $('#addWalletUserModal').modal('hide');
                            $('#add-wallet-user-form')[0].reset();
                            $('#modal-wallet-user').val(null).trigger('change');
                            $('#modal-wallet-commission-div').hide();
                            $('#modal-wallet-commission').prop('required', false);

                            if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __('field.user_wallet_added_successfully') }}');
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || '{{ __('admin.an_error_occurred') }}');
                            }
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 403) {
                            if (typeof toastr !== 'undefined') {
                                toastr.error('{{ __('admin.you_do_not_have_permission_to_perform_this_action') }}');
                            } else {
                                alert('{{ __('admin.you_do_not_have_permission_to_perform_this_action') }}');
                            }
                        } else if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            let errorMessages = [];
                            $.each(errors, function(key, value) {
                                errorMessages.push(value[0]);
                            });
                            if (typeof toastr !== 'undefined') {
                                toastr.error(errorMessages.join('<br>'));
                            } else {
                                alert(errorMessages.join('\n'));
                            }
                        } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                            } else {
                                alert(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                            }
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Add Coupon Quick Action - handled in modal

            // Packages Tab Functions
            $(document).on('click', '.add-package-user-btn', function() {
                const packageId = $(this).data('package-id');
                const packageName = $(this).data('package-name');

                $('#modal-package-id').val(packageId);
                $('#addPackageUserModalLabel').text('{{ __('locale.add_users_to') }} {{ __('locale.packages') }} (' + packageName + ')');
                $('#add-package-user-form')[0].reset();
                $('#modal-package-user').val(null).trigger('change');
            });

            $('#addPackageModal').on('shown.bs.modal', function() {
                if (!$('#quick_package_paid_services').hasClass('select2-hidden-accessible')) {
                    $('#quick_package_paid_services').select2({
                        dropdownParent: $('#addPackageModal')
                    });
                }
                if (!$('#quick_package_free_services').hasClass('select2-hidden-accessible')) {
                    $('#quick_package_free_services').select2({
                        dropdownParent: $('#addPackageModal')
                    });
                }
            });

            $(document).on('click', '#save-quick-package-btn', function(e) {
                e.preventDefault();
                const $btn = $(this);
                const originalHtml = $btn.html();

                const nameEn = $('#quick_package_name_en').val();
                const nameAr = $('#quick_package_name_ar').val();
                const price = $('#quick_package_price').val();
                const paidServices = $('#quick_package_paid_services').val() || [];
                const freeServices = $('#quick_package_free_services').val() || [];

                if (!nameEn || !nameAr || !price || paidServices.length === 0) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('admin.an_error_occurred') }}');
                    }
                    return false;
                }

                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.packages.updateOrCreate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        en: { name: nameEn },
                        ar: { name: nameAr },
                        price: price,
                        paid_services: paidServices,
                        free_services: freeServices
                    },
                    success: function(response) {
                        if (response.message === 'redirect_to_home' || response.data) {
                            $('#addPackageModal').modal('hide');
                            $('#quick-add-package-form')[0].reset();
                            $('#quick_package_paid_services').val(null).trigger('change');
                            $('#quick_package_free_services').val(null).trigger('change');

                            if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __('admin.operation_done_successfully') }}');
                            }

                            setTimeout(function() {
                                location.reload();
                            }, 400);
                        } else if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errors = xhr.responseJSON.errors;
                            const errorMessages = Object.values(errors).map(function(values) {
                                return values[0];
                            });
                            if (typeof toastr !== 'undefined') {
                                toastr.error(errorMessages.join('<br>'));
                            }
                        } else if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            $(document).on('click', '#save-package-user-btn', function(e) {
                e.preventDefault();

                const $form = $('#add-package-user-form');
                const packageId = $('#modal-package-id').val();
                const userId = $('#modal-package-user').val();
                const packageType = $('#modal-package-type').val();

                if (!$form[0].checkValidity()) {
                    $form[0].reportValidity();
                    return false;
                }

                if (!packageId || !userId || !packageType) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('admin.an_error_occurred') }}');
                    }
                    return false;
                }

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.users_packages.updateOrCreate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        package_id: packageId,
                        user_id: userId,
                        package_type: packageType
                    },
                    success: function(response) {
                        if (response.message === 'redirect_to_home' || response.message === '{{ __('admin.operation_done_successfully') }}') {
                            $('#addPackageUserModal').modal('hide');
                            $('#add-package-user-form')[0].reset();
                            $('#modal-package-user').val(null).trigger('change');

                            if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __('admin.operation_done_successfully') }}');
                            }

                            setTimeout(function() {
                                window.location.reload();
                            }, 250);
                        } else if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            const errors = xhr.responseJSON.errors;
                            const errorMessages = Object.values(errors).map(function(values) {
                                return values[0];
                            });
                            if (typeof toastr !== 'undefined') {
                                toastr.error(errorMessages.join('<br>'));
                            }
                        } else if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Initial render
            renderCart();
            
            // Initial check for continue button state (customer required)
            $('#continueToPayment').prop('disabled', cart.length === 0 || !selectedCustomerId);

            // Wizard navigation
            $('.btn-prev').on('click', function() {
                bookingStepper.previous();
            });

            // Customer Selection Functions
            // Handle customer selection from dropdown for PREVIEW (on change)
             $('#select-customer-dropdown').on('change', function() {
                const userId = $(this).val();
                
                const $selectedOption = $(this).find('option:selected');
                
                // This logic only updates the modal preview, DOES NOT set global state yet until confirmed
                if (userId && userId !== '') {
                    const userName = $selectedOption.data('name') || $selectedOption.text().split(' - ')[0];
                    const userEmail = $selectedOption.data('email') || '';
                    const userPhone = $selectedOption.data('phone') || '';
                    const userImage = $selectedOption.data('image') || '{{ asset('assets/img/avatars/1.png') }}';
                    
                    // Show selected customer info
                    $('#selected-customer-info').show();
                    $('#selected-customer-name').text(userName);
                    $('#selected-customer-img').attr('src', userImage).attr('alt', userName);
                    
                    // Show email
                    if (userEmail) {
                        $('#selected-customer-email').html('<i class="ti ti-mail me-1"></i>' + userEmail).show();
                    } else {
                        $('#selected-customer-email').hide();
                    }
                    
                    // Show phone
                    if (userPhone) {
                        $('#selected-customer-phone').html('<i class="ti ti-phone me-1"></i>' + userPhone).show();
                    } else {
                        $('#selected-customer-phone').hide();
                    }
                    
                    // Enable confirm button
                    $('#confirm-select-customer').prop('disabled', false);
                } else {
                    // Hide selected customer info
                    $('#selected-customer-info').hide();
                    $('#confirm-select-customer').prop('disabled', true);
                }
            });

            // Confirm customer selection
            $('#confirm-select-customer').on('click', function() {
                const userId = $('#select-customer-dropdown').val();
                if (!userId || userId === '') {
                    return;
                }
                
                const $selectedOption = $('#select-customer-dropdown').find('option:selected');
                const userName = $selectedOption.data('name') || $selectedOption.text().split(' - ')[0];
                const userEmail = $selectedOption.data('email') || '';
                const userPhone = $selectedOption.data('phone') || '';
                const userImage = $selectedOption.data('image') || '{{ asset('assets/img/avatars/1.png') }}';
                
                selectedCustomerId = userId;
                selectedCustomerName = userName;
                selectedCustomerPhone = userPhone;
                
                // Save to session
                $.ajax({
                    url: '{{ route("center_user.sales.cart") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cart: cart,
                        client_id: userId
                    },
                    success: function() {
                        // Update display
                        updateCustomerDisplay(userId, userName, userEmail || userPhone, userImage , userPhone);
                        
                        // Update worker dropdowns based on customer's branch
                        const branchId = $selectedOption.data('branch-id') || null;
                        updateWorkersByBranch(branchId);
                        
                        $('#selectCustomerModal').modal('hide');
                        
                        // Reset modal
                        $('#select-customer-dropdown').val(null).trigger('change');
                        $('#selected-customer-info').hide();
                        $('#confirm-select-customer').prop('disabled', true);
                        
                        // If we are already on step 3 of booking wizard, update the info immediately
                        if ($('#booking-third-step').hasClass('active')) {
                             $('#booking-step3-customer-name').text(selectedCustomerName);
                             $('#booking-step3-customer-mobile').text(selectedCustomerPhone || '{{ __('field.no_mobile') }}');
                             loadCustomerServices(selectedCustomerPhone);
                        }

                        if (typeof toastr !== 'undefined') {
                            toastr.success('{{ __('field.customer_selected') }}');
                        }
                    }
                });
            });

            // Reset modal when closed
            $('#selectCustomerModal').on('hidden.bs.modal', function() {
                $('#select-customer-dropdown').val(null).trigger('change');
                $('#selected-customer-info').hide();
                $('#confirm-select-customer').prop('disabled', true);
            });

            // Remove customer
            $(document).on('click', '#removeCustomerBtn', function() {
                selectedCustomerId = null;
                
                
                $.ajax({
                    url: '{{ route("center_user.sales.cart") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cart: cart,
                        client_id: null
                    },
                    success: function() {
                        updateCustomerDisplay(null, null, null, null, null);
                        
                        // Update worker dropdowns - use logged-in user's branch
                        const branchId = {{ auth('center_user')->user()->branch_id ?? 'null' }};
                        updateWorkersByBranch(branchId);
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.success('{{ __('field.customer_removed') }}');
                        }
                    }
                });
            });

            // Update worker dropdowns based on branch
            function updateWorkersByBranch(branchId) {
                $.ajax({
                    url: '{{ route("center_user.workers.get-workers-by-branch") }}',
                    type: 'GET',
                    data: {
                        _token: '{{ csrf_token() }}',
                        branch_id: branchId
                    },
                    success: function(workers) {
                        const centerUserName = '{{ $centerUser->name ?? '' }}';
                        
                        // Update product-sales_worker dropdown
                        const $salesWorkerSelect = $('#product-sales_worker');
                        const currentSalesWorker = $salesWorkerSelect.val();
                        $salesWorkerSelect.empty().append('<option value="">{{ __('field.select_sales_worker') }}</option>');
                        $.each(workers, function(index, worker) {
                            let label = worker.name + ' - ' + (worker.phone || '');
                            if (worker.is_center_user) {
                                label += ' (' + centerUserName + ' - reception)';
                            }
                            const option = new Option(label, worker.id, false, false);
                            if (worker.id == currentSalesWorker) {
                                option.selected = true;
                            }
                            $salesWorkerSelect.append(option);
                        });
                        $salesWorkerSelect.trigger('change');
                        
                        // Update product-worker dropdown
                        const $workerSelect = $('#product-worker');
                        const currentWorker = $workerSelect.val();
                        $workerSelect.empty().append('<option value="">{{ __('field.select_worker') }}</option>');
                        $.each(workers, function(index, worker) {
                            let label = worker.name + ' - ' + (worker.phone || '');
                            if (worker.is_center_user) {
                                label += ' (' + centerUserName + ' - reception)';
                            }

                            if (worker.id == currentWorker) {
                                option.selected = true;
                            }
                            $workerSelect.append(option);
                        });
                        $workerSelect.trigger('change');
                    },
                    error: function() {
                        if (typeof toastr !== 'undefined') {
                            toastr.error('{{ __('admin.an_error_occurred') }}');
                        }
                    }
                });
            }

            // Update customer display
            function updateCustomerDisplay(userId, name, contact, image , phone) {
                // Update global tracking vars
                selectedCustomerId = userId;
                selectedCustomerName = name;
                selectedCustomerPhone = phone;

                if (userId && name ) {
                    $('#selected-customer-display').show();
                    $('#no-customer-display').hide();
                    $('#selected-customer-display').html(`
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg me-3">
                                <img src="${image || '{{ asset('assets/img/avatars/1.png') }}'}" 
                                     alt="${name}" 
                                     class="rounded-circle" 
                                     style="width: 50px; height: 50px; object-fit: cover;">
                            </div>
                            <div>
                                <h5 class="mb-0">${name}</h5>
                                <small class="text-muted d-block"><i class="ti ti-mail me-1"></i> ${contact}</small>
                                <small class="text-muted d-block"><i class="ti ti-phone me-1"></i> ${phone}</small>
                            </div>
                        </div>
                        <div class="d-flex flex-column flex-sm-row gap-2">
                            <button type="button" class="btn btn-outline-primary w-100 w-sm-auto" id="selectCustomerBtn" data-bs-toggle="modal" data-bs-target="#selectCustomerModal">
                                <i class="ti ti-user me-1"></i>
                                <span class="d-none d-sm-inline">{{ __('field.change_customer') }}</span>
                                <span class="d-inline d-sm-none">{{ __('field.change') }}</span>
                            </button>
                            <button type="button" class="btn btn-primary w-100 w-sm-auto" id="addCustomerBtn" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                <i class="ti ti-plus me-1"></i>
                                <span class="d-none d-sm-inline">{{ __('general.add') }} {{ __('field.customer') }}</span>
                                <span class="d-inline d-sm-none">{{ __('general.add') }}</span>
                            </button>
                            <button type="button" class="btn btn-info w-100 w-sm-auto" id="editCustomerBtn" data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                                <i class="ti ti-edit me-1"></i>
                                <span class="d-none d-sm-inline">{{ __('general.edit') }}</span>
                                <span class="d-inline d-sm-none">{{ __('general.edit') }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-danger w-100 w-sm-auto" id="removeCustomerBtn">
                                <i class="ti ti-x me-1"></i>
                                {{ __('field.remove') }}
                            </button>
                        </div>
                    `);
                } else {
                    $('#selected-customer-display').hide();
                    $('#no-customer-display').show();
                    // Clear wizard fields if no customer
                    $('#booking-step3-customer-name').text('');
                    $('#booking-step3-customer-mobile').text('');
                }
                // Update continue button state based on customer and cart
                $('#continueToPayment').prop('disabled', cart.length === 0 || !userId);
            }

            // Edit Customer Modal - Load customer data when modal opens
            $('#editCustomerModal').on('show.bs.modal', function() {
                if (!selectedCustomerId) {
                    return;
                }
                
                function fillEditFromOption($selectedOption, extra) {
                    extra = extra || {};
                    const userName = extra.name || $selectedOption.data('name') || '';
                    const userEmail = extra.email || $selectedOption.data('email') || '';
                    const userPhone = extra.phone || $selectedOption.data('phone') || '';
                    const userImage = extra.image || $selectedOption.data('image') || '';
                    const countryCode = extra.country_code || '+971';

                    const nameParts = userName.split(' ');
                    const firstName = extra.first_name || nameParts[0] || '';
                    const lastName = extra.last_name || nameParts.slice(1).join(' ') || '';

                    $('#edit_customer_id').val(selectedCustomerId);
                    $('#edit_customer_first_name').val(firstName);
                    $('#edit_customer_last_name').val(lastName);
                    $('#edit_customer_email').val(userEmail);
                    $('#editCustomerModal select[name="country_code"]').val(countryCode);

                    if (countryCode === '+971') {
                        const phoneStr = String(userPhone);
                        let phonePrefix = '';
                        let phoneWithoutPrefix = phoneStr;
                        const prefixes = ['50', '52', '54', '55', '56', '58'];
                        for (const prefix of prefixes) {
                            if (phoneStr.startsWith(prefix)) {
                                phonePrefix = prefix;
                                phoneWithoutPrefix = phoneStr.substring(prefix.length);
                                break;
                            }
                        }
                        $('#edit_customer_phone_prefix').val(phonePrefix || '50');
                        $('#edit_customer_phone').val(phoneWithoutPrefix);
                        $('#edit_customer_phone_prefix_container').show();
                        $('#edit_customer_phone_input_container').removeClass('col-md-4').addClass('col-md-2');
                    } else {
                        $('#edit_customer_phone').val(userPhone);
                        $('#edit_customer_phone_prefix_container').hide();
                        $('#edit_customer_phone_input_container').removeClass('col-md-2').addClass('col-md-4');
                    }

                    if (userImage && userImage !== '{{ asset('assets/img/avatars/1.png') }}') {
                        $('#edit_customer_image_preview').attr('src', userImage);
                        $('#edit_customer_current_image').show();
                    } else {
                        $('#edit_customer_current_image').hide();
                    }
                }

                const $selectedOption = $('#select-customer-dropdown').find(`option[value="${selectedCustomerId}"]`);
                if ($selectedOption.length) {
                    const userName = $selectedOption.data('name') || '';
                    const userEmail = $selectedOption.data('email') || '';
                    const userPhone = $selectedOption.data('phone') || '';
                    const userImage = $selectedOption.data('image') || '';
                    
                    // Split name into first and last name
                    const nameParts = userName.split(' ');
                    const firstName = nameParts[0] || '';
                    const lastName = nameParts.slice(1).join(' ') || '';
                    
                    // Set form values
                    $('#edit_customer_id').val(selectedCustomerId);
                    $('#edit_customer_first_name').val(firstName);
                    $('#edit_customer_last_name').val(lastName);
                    $('#edit_customer_email').val(userEmail);
                    
                    // Get customer data via AJAX to get full details including country code
                    $.ajax({
                        url: '{{ route("center_user.users.create") }}',
                        type: 'GET',
                        data: { id: selectedCustomerId },
                        success: function(response) {
                            // Parse the HTML response to extract data
                            const $response = $(response);
                            const countryCode = $response.find('select[name="country_code"]').val() || '+971';
                            
                            // Set country code
                            $('#editCustomerModal select[name="country_code"]').val(countryCode);
                            
                            // Handle phone prefix for +971
                            if (countryCode === '+971') {
                                const phoneStr = String(userPhone);
                                let phonePrefix = '';
                                let phoneWithoutPrefix = phoneStr;
                                
                                // Check if phone starts with a known prefix
                                const prefixes = ['50', '52', '54', '55', '56', '58'];
                                for (const prefix of prefixes) {
                                    if (phoneStr.startsWith(prefix)) {
                                        phonePrefix = prefix;
                                        phoneWithoutPrefix = phoneStr.substring(prefix.length);
                                        break;
                                    }
                                }
                                
                                $('#edit_customer_phone_prefix').val(phonePrefix || '50');
                                $('#edit_customer_phone').val(phoneWithoutPrefix);
                                $('#edit_customer_phone_prefix_container').show();
                                $('#edit_customer_phone_input_container').removeClass('col-md-4').addClass('col-md-2');
                            } else {
                                $('#edit_customer_phone').val(userPhone);
                                $('#edit_customer_phone_prefix_container').hide();
                                $('#edit_customer_phone_input_container').removeClass('col-md-2').addClass('col-md-4');
                            }
                            
                            // Show current image if exists
                            if (userImage && userImage !== '{{ asset('assets/img/avatars/1.png') }}') {
                                $('#edit_customer_image_preview').attr('src', userImage);
                                $('#edit_customer_current_image').show();
                            } else {
                                $('#edit_customer_current_image').hide();
                            }
                        }
                    });
                } else {
                    $.get('{{ route('center_user.sales.get-customer', ['id' => '__ID__']) }}'.replace('__ID__', selectedCustomerId), function(user) {
                        const option = new Option(user.text || user.name, user.id, true, true);
                        $(option).attr('data-name', user.name || '');
                        $(option).attr('data-email', user.email || '');
                        $(option).attr('data-phone', user.phone || '');
                        $(option).attr('data-image', user.image || '');
                        $(option).attr('data-branch-id', user.branch_id || '');
                        $('#select-customer-dropdown').append(option);
                        $('#edit_customer_id').val(user.id);
                        $('#edit_customer_first_name').val(user.first_name || '');
                        $('#edit_customer_last_name').val(user.last_name || '');
                        $('#edit_customer_email').val(user.email || '');
                        $('#editCustomerModal select[name="country_code"]').val(user.country_code || '+971');
                        const phoneStr = String(user.phone || '');
                        if ((user.country_code || '+971') === '+971') {
                            let phonePrefix = '';
                            let phoneWithoutPrefix = phoneStr;
                            ['50', '52', '54', '55', '56', '58'].forEach(function(prefix) {
                                if (!phonePrefix && phoneStr.startsWith(prefix)) {
                                    phonePrefix = prefix;
                                    phoneWithoutPrefix = phoneStr.substring(prefix.length);
                                }
                            });
                            $('#edit_customer_phone_prefix').val(phonePrefix || '50');
                            $('#edit_customer_phone').val(phoneWithoutPrefix);
                            $('#edit_customer_phone_prefix_container').show();
                            $('#edit_customer_phone_input_container').removeClass('col-md-4').addClass('col-md-2');
                        } else {
                            $('#edit_customer_phone').val(phoneStr);
                            $('#edit_customer_phone_prefix_container').hide();
                            $('#edit_customer_phone_input_container').removeClass('col-md-2').addClass('col-md-4');
                        }
                    });
                }
            });
            
            // Phone prefix toggle for UAE (+971) in Edit Customer Modal
            function toggleEditCustomerPhonePrefix() {
                const countryCodeSelect = $('#editCustomerModal').find('select[name="country_code"]');
                const phonePrefixContainer = $('#edit_customer_phone_prefix_container');
                const phoneInputContainer = $('#edit_customer_phone_input_container');
                
                if (countryCodeSelect.length && countryCodeSelect.val() === '+971') {
                    phonePrefixContainer.show();
                    phoneInputContainer.removeClass('col-md-4').addClass('col-md-2');
                } else {
                    phonePrefixContainer.hide();
                    phoneInputContainer.removeClass('col-md-2').addClass('col-md-4');
                }
            }
            
            // Listen for country code changes in Edit Customer Modal
            $(document).on('change', '#editCustomerModal select[name="country_code"]', function() {
                toggleEditCustomerPhonePrefix();
            });
            
            // Save edited customer
            $('#save-edit-customer-btn').on('click', function(e) {
                e.preventDefault();
                const form = $('#edit-customer-form')[0];
                const formData = new FormData(form);
                
                // Combine phone prefix if UAE
                const countryCodeSelect = $('#editCustomerModal').find('select[name="country_code"]');
                const phonePrefixSelect = $('#edit_customer_phone_prefix');
                const phoneInput = $('#edit_customer_phone');
                
                if (countryCodeSelect.length && countryCodeSelect.val() === '+971' && phonePrefixSelect.length && phoneInput.length) {
                    const prefix = phonePrefixSelect.val();
                    const phone = phoneInput.val();
                    if (prefix && phone) {
                        // Replace phone in formData with combined value
                        formData.set('phone', prefix + phone);
                    }
                }

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.users.updateOrCreate") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.message !== '{{ __('admin.an_error_occurred') }}') {
                            const userData = response.data;
                            if (!userData || typeof userData !== 'object') {
                                location.reload();
                                return;
                            }

                            const userName = userData.name || (userData.first_name + ' ' + (userData.last_name || ''));
                            const userEmail = userData.email || '';
                            const userPhone = userData.phone || userData.full_phone || '';
                            const userImage = userData.image || '';

                            // Update display
                            selectedCustomerName = userName;
                            selectedCustomerPhone = userPhone;
                            updateCustomerDisplay(userData.id, userName, userEmail || userPhone, userImage, userPhone);
                            
                            // Update worker dropdowns based on customer's branch
                            const branchId = userData.branch_id || null;
                            updateWorkersByBranch(branchId);
                            
                            // Update the dropdown option
                            const $option = $('#select-customer-dropdown').find(`option[value="${userData.id}"]`);
                            if ($option.length) {
                                let label = userName;
                                if (userPhone) label += ' - ' + userPhone;
                                if (userEmail) label += ' - ' + userEmail;
                                
                                $option.text(label);
                                $option.attr('data-name', userName);
                                $option.attr('data-phone', userPhone);
                                $option.attr('data-email', userEmail);
                                $option.attr('data-image', userImage);
                                $option.attr('data-branch-id', userData.branch_id || '');
                            }
                            
                            $('#editCustomerModal').modal('hide');
                            $('#edit-customer-form')[0].reset();
                            if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __('admin.operation_done_successfully') }}');
                            }
                        }
                    },
                    error: function(xhr) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Quick add customer
            $('#save-quick-customer-btn').on('click', function(e) {
                e.preventDefault();
                const $form = $('#quick-add-customer-form');
                const form = $form[0]; // DOM element for FormData
                
                // Clear previous errors
                $('.invalid-feedback').text('');
                $('.form-control').removeClass('is-invalid');
                
                // Client-side validation
                let isValid = true;
                let firstErrorField = null;
                
                // Validate first name
                const firstName = $('#quick_customer_first_name').val().trim();
                if (!firstName) {
                    $('#quick_customer_first_name').addClass('is-invalid');
                    $('#quick_customer_first_name').siblings('.invalid-feedback').text('{{ __('field.first_name') }} is required');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $('#quick_customer_first_name');
                }
                
                // Validate last name
                const lastName = $('#quick_customer_last_name').val().trim();
                if (!lastName) {
                    $('#quick_customer_last_name').addClass('is-invalid');
                    $('#quick_customer_last_name').siblings('.invalid-feedback').text('{{ __('field.last_name') }} is required');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $('#quick_customer_last_name');
                }
                
                // Validate email format (if provided)
                const email = $('#quick_customer_email').val().trim();
                if (email) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        $('#quick_customer_email').addClass('is-invalid');
                        $('#quick_customer_email').siblings('.invalid-feedback').text('{{ __('field.email') }} must be a valid email address');
                        isValid = false;
                        if (!firstErrorField) firstErrorField = $('#quick_customer_email');
                    }
                }
                
                // Validate phone
                const countryCode = $('#addCustomerModal').find('select[name="country_code"]').val();
                const phonePrefix = $('#quick_customer_phone_prefix').val() || '';
                const phoneInputValue = $('#quick_customer_phone').val().trim();
                
                if (!phoneInputValue) {
                    $('#quick_customer_phone').addClass('is-invalid');
                    $('#quick_customer_phone').siblings('.invalid-feedback').text('{{ __('field.mobile_number') }} is required');
                    isValid = false;
                    if (!firstErrorField) firstErrorField = $('#quick_customer_phone');
                } else {
                    // For UAE (+971), phone must be exactly 7 digits (without prefix)
                    if (countryCode === '+971') {
                        if (!/^\d{7}$/.test(phoneInputValue)) {
                            $('#quick_customer_phone').addClass('is-invalid');
                            $('#quick_customer_phone').siblings('.invalid-feedback').text('{{ __('field.phone_must_be_7_digits') }}');
                            isValid = false;
                            if (!firstErrorField) firstErrorField = $('#quick_customer_phone');
                        } else {
                            $('#quick_customer_phone').removeClass('is-invalid');
                        }
                    } else {
                        // For other countries, validate 6-10 digits
                        if (!/^\d+$/.test(phoneInputValue)) {
                            $('#quick_customer_phone').addClass('is-invalid');
                            $('#quick_customer_phone').siblings('.invalid-feedback').text('{{ __('field.mobile_number') }} must be numeric');
                            isValid = false;
                            if (!firstErrorField) firstErrorField = $('#quick_customer_phone');
                        } else {
                            const phoneLength = phoneInputValue.length;
                            if (phoneLength < 6 || phoneLength > 10) {
                                $('#quick_customer_phone').addClass('is-invalid');
                                $('#quick_customer_phone').siblings('.invalid-feedback').text('{{ __('field.mobile_number') }} must be between 6 and 10 digits');
                                isValid = false;
                                if (!firstErrorField) firstErrorField = $('#quick_customer_phone');
                            } else {
                                $('#quick_customer_phone').removeClass('is-invalid');
                            }
                        }
                    }
                }
                
                if (!isValid) {
                    // Scroll to first error field
                    if (firstErrorField) {
                        $('html, body').animate({
                            scrollTop: firstErrorField.offset().top - 100
                        }, 500);
                    }
                    if (typeof toastr !== 'undefined') {
                        toastr.error('Please fill all required fields correctly');
                    }
                    return false;
                }
                
                // Combine prefix with phone number if UAE (+971) - but don't modify visible input
                const countryCodeSelect = $('#addCustomerModal').find('select[name="country_code"]');
                const phonePrefixSelect = $('#quick_customer_phone_prefix');
                const phoneInputField = $('#quick_customer_phone');
                
                if (countryCodeSelect.length && countryCodeSelect.val() === '+971' && phonePrefixSelect.length && phoneInputField.length) {
                    const prefix = phonePrefixSelect.val();
                    const phone = phoneInputField.val().trim();
                    if (prefix && phone) {
                        // Remove any existing hidden phone field
                        const existingHidden = $form.find('input[name="phone"][type="hidden"]');
                        if (existingHidden.length) {
                            existingHidden.remove();
                        }
                        
                        // Temporarily rename the visible input so it's not submitted
                        phoneInputField.attr('name', 'phone_display_only');
                        
                        // Create hidden input with the full number (prefix + phone)
                        const hiddenInput = $('<input>', {
                            type: 'hidden',
                            name: 'phone',
                            value: prefix + phone
                        });
                        $form.append(hiddenInput);
                    }
                }
                
                const formData = new FormData(form);

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.users.updateOrCreate") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.message !== '{{ __('admin.an_error_occurred') }}') {
                            const userData = response.data;
                            if (!userData || typeof userData !== 'object') {
                                // If for some reason we still get a redirect or bad data, reload as fallback
                                location.reload();
                                return;
                            }

                            selectedCustomerId = userData.id;
                            
                            // Save to session
                            $.ajax({
                                url: '{{ route("center_user.sales.cart") }}',
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    cart: cart,
                                    client_id: userData.id
                                },
                                success: function() {
                                    const userName = userData.name || (userData.first_name + ' ' + (userData.last_name || ''));
                                    const userEmail = userData.email || '';
                                    const userPhone = userData.phone || userData.full_phone || '';
                                    const userImage = userData.image || '';

                                    // Update display
                                    updateCustomerDisplay(userData.id, userName, userEmail || userPhone, userImage , userPhone);
                                    
                                    // Update worker dropdowns based on customer's branch
                                    const branchId = userData.branch_id || null;
                                    updateWorkersByBranch(branchId);
                                    
                                    // Add to selection dropdowns
                                    let label = userName;
                                    if (userPhone) label += ' - ' + userPhone;
                                    if (userEmail) label += ' - ' + userEmail;

                                    const newOption = new Option(label, userData.id, false, false);
                                    $(newOption).attr('data-name', userName);
                                    $(newOption).attr('data-phone', userPhone);
                                    $(newOption).attr('data-email', userEmail);
                                    $(newOption).attr('data-image', userImage);
                                    $(newOption).attr('data-branch-id', userData.branch_id || '');
                                    
                                    $('#select-customer-dropdown').append(newOption);
                                    
                                    // Also add to wallet user modal if exists
                                    if ($('#modal-wallet-user').length) {
                                        const walletOption = new Option(label, userData.id, false, false);
                                        $('#modal-wallet-user').append(walletOption);
                                    }

                                    // If we are already on step 3 of booking wizard, update the info immediately
                                    if ($('#booking-third-step').hasClass('active')) {
                                         $('#booking-step3-customer-name').text(selectedCustomerName);
                                         $('#booking-step3-customer-mobile').text(selectedCustomerPhone || '{{ __('field.no_mobile') }}');
                                         loadCustomerServices(selectedCustomerPhone);
                                    }
                                    
                                    // Clean up hidden phone field and restore input name before reset
                                    const hiddenPhoneField = $('#quick-add-customer-form').find('input[name="phone"][type="hidden"]');
                                    if (hiddenPhoneField.length) {
                                        hiddenPhoneField.remove();
                                    }
                                    const phoneInput = $('#quick_customer_phone');
                                    if (phoneInput.attr('name') === 'phone_display_only') {
                                        phoneInput.attr('name', 'phone');
                                    }
                                    
                                    $('#addCustomerModal').modal('hide');
                                    $('#quick-add-customer-form')[0].reset();
                                    if (typeof toastr !== 'undefined') {
                                        toastr.success('{{ __('admin.operation_done_successfully') }}');
                                    }
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        // Clean up hidden phone field and restore input name on error
                        const hiddenPhoneField = $('#quick-add-customer-form').find('input[name="phone"][type="hidden"]');
                        if (hiddenPhoneField.length) {
                            hiddenPhoneField.remove();
                        }
                        const phoneInput = $('#quick_customer_phone');
                        if (phoneInput.attr('name') === 'phone_display_only') {
                            phoneInput.attr('name', 'phone');
                        }
                        
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            let firstErrorField = null;
                            
                            $.each(errors, function(key, value) {
                                const fieldId = 'quick_customer_' + key.replace(/_/g, '_');
                                const $field = $('#' + fieldId);
                                
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    $field.siblings('.invalid-feedback').text(value[0]);
                                    if (!firstErrorField) firstErrorField = $field;
                                }
                            });
                            
                            // Scroll to first error field
                            if (firstErrorField) {
                                $('html, body').animate({
                                    scrollTop: firstErrorField.offset().top - 100
                                }, 500);
                            }
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.error('Please fix the validation errors');
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                            }
                        }
                    },
                    complete: function() {
                        // Clean up hidden phone field and restore input name in complete handler (in case of any issues)
                        const hiddenPhoneField = $('#quick-add-customer-form').find('input[name="phone"][type="hidden"]');
                        if (hiddenPhoneField.length) {
                            hiddenPhoneField.remove();
                        }
                        const phoneInput = $('#quick_customer_phone');
                        if (phoneInput.attr('name') === 'phone_display_only') {
                            phoneInput.attr('name', 'phone');
                        }
                        
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Add Coupon Quick Action
            $(document).on('click', '#save-quick-coupon-btn', function(e) {
                e.preventDefault();
                const $form = $('#quick-add-coupon-form');
                const $btn = $(this);
                const originalHtml = $btn.html();
                
                // Validate form
                if (!$form[0].checkValidity()) {
                    $form[0].reportValidity();
                    return false;
                }

                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.wallets.updateOrCreate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        amount: $('#quick_coupon_amount').val(),
                        invoiced_amount: $('#quick_coupon_invoiced_amount').val() || 0,
                        start_at: $('#quick_coupon_start_at').val() || null,
                        end_at: $('#quick_coupon_end_at').val() || null
                    },
                    success: function(response) {
                        if (response.message === 'redirect_to_home' || response.data) {
                            // Close modal and reset form
                            $('#addCouponModal').modal('hide');
                            $('#quick-add-coupon-form')[0].reset();
                            
                            if (typeof toastr !== 'undefined') {
                                toastr.success('{{ __('admin.operation_done_successfully') }}');
                            }
                            
                            // Reload page to refresh wallets table
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || '{{ __('admin.an_error_occurred') }}');
                            }
                        }
                    },
                    error: function(xhr) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });

            });

            // Auto-translation for Quick Add Service is now handled globally via translation-js.blade.php

            
        });
    </script>
@endsection