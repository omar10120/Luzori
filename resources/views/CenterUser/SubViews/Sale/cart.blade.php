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
            <div class="col-lg-8">
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
                                                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                                                    @endforeach
                                                                </select>
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
                                                        <h5>{{ __('field.discount_codes') }}</h5>
                                                        <div class="row mb-4">
                                                            @foreach ($discounts as $discount)
                                                                <div class="col-md-3 mb-2">
                                                                    <div class="form-check" style="width: 200px;padding: 10px;color: #fff;background-color: #428bca;border-color: #357ebd;text-align: center;display: flex;justify-content: space-between;font-size: 14px;">
                                                                        <label class="form-check-label" for="booking-discounts{{ $discount->id }}">
                                                                            {{ $discount->code . ' [' . $discount->amount . '%]' }}
                                                                        </label>
                                                                        <input class="form-check-input" type="radio" name="discount_id" data-name="discount_id" value="{{ $discount->id }}" id="booking-discounts{{ $discount->id }}">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div id="booking-walletsElement"></div>
                                                    <div id="booking-membershipsElement"></div>
                                                    <div id="booking-servicesTable"></div>
                                                    <div class="row mb-4">
                                                        <div class="col-md-12">
                                                            <div class="mb-1">
                                                                <label for="booking-payment_type" class="form-label">{{ __('field.payment_method') }}</label>
                                                                <select name="payment_type" id="booking-payment_type" class="form-control">
                                                                    <option value="">{{ __('field.Choose Payment Method') }}</option>
                                                                    @foreach($paymentMethods as $paymentMethod)
                                                                        <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
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
                                                    <option value="">{{ __('field.Select Products') }}</option>
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
                                                    <option value="">{{ __('field.Choose Discount') }}</option>
                                                    @for ($i = 1; $i <= 15; $i++)
                                                        <option value="{{ $i }}">{{ $i . '%' }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <div class="mb-1">
                                                <label for="product-sales_worker" class="form-label">{{ __('field.sales_worker') }}</label>
                                                <select class="select2 form-control" name="sales_worker_id" id="product-sales_worker">
                                                    <option value="">{{ __('field.Choose Sales Worker') }}</option>
                                                    @foreach ($workers as $worker)
                                                        <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <div class="mb-1">
                                                <label for="product-worker" class="form-label">{{ __('field.worker') }}</label>
                                                <select class="select2 form-control" name="worker_id" id="product-worker">
                                                    <option value="">{{ __('field.Choose Worker') }}</option>
                                                    @foreach ($workers as $worker)
                                                        <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2" style="display: none;" id="product-commission-div">
                                            <div class="mb-1">
                                                <label for="product-commission" class="form-label">{{ __('field.commission') }} <span class="text-danger">*</span></label>
                                                <select class="form-control" name="commission" id="product-commission" required>
                                                    <option value="">{{ __('admin.Choose Commission') }}</option>
                                                    @for ($i = 1; $i <= 100; $i++)
                                                        <option value="{{ $i }}">{{ $i }}%</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-2">
                                            <div class="mb-1">
                                                <label for="product-payment_type" class="form-label">{{ __('field.payment_method') }}</label>
                                                <select name="payment_type" id="product-payment_type" class="form-control">
                                                    <option value="">{{ __('field.Choose Payment Method') }}</option>
                                                    @foreach($productPaymentMethods as $paymentMethod)
                                                        <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                                    @endforeach
                                                </select>
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
                                                        <th style="font-size: 10px;">{{ __('field.amount') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.invoiced_amount') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.start_at') }}</th>
                                                        <th style="font-size: 10px;">{{ __('field.end_at') }}</th>
                                                        <th style="font-size: 10px;">{{ __('general.actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($wallets as $wallet)
                                                        <tr style="font-size: 10px;">
                                                            <td>{{ $wallet->code }}</td>
                                                            <td>{{ number_format($wallet->amount, 2) }} {{ get_currency() }}</td>
                                                            <td>{{ number_format($wallet->invoiced_amount, 2) }} {{ get_currency() }}</td>
                                                            <td>{{ $wallet->start_at ? \Carbon\Carbon::parse($wallet->start_at)->format('Y-m-d') : '-' }}</td>
                                                            <td>{{ $wallet->end_at ? \Carbon\Carbon::parse($wallet->end_at)->format('Y-m-d') : '-' }}</td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-primary add-wallet-user-btn" style="font-size: 10px;" data-wallet-id="{{ $wallet->id }}" data-wallet-code="{{ $wallet->code }}" data-wallet-amount="{{ $wallet->amount }}" data-wallet-invoiced="{{ $wallet->invoiced_amount }}" data-wallet-start="{{ $wallet->start_at }}" data-wallet-end="{{ $wallet->end_at }}" data-bs-toggle="modal" data-bs-target="#addWalletUserModal">
                                                                    <i class="ti ti-user-plus me-1"></i>
                                                                    {{ __('field.add_user') }}
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted">{{ __('field.no_coupons_available') }}</td>
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
            <div class="col-lg-4">
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
                                <a class="nav-link" id="cart-wallet-tab" data-bs-toggle="tab" href="#cart-wallet-pane" role="tab">
                                    <i class="ti ti-wallet me-1"></i>
                                    <span class="d-none d-sm-inline">{{ __('field.coupons') }}</span>
                                    <span class="badge rounded-pill bg-label-primary ms-1" id="cart-wallet-count" style="display:none">0</span>
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-0">
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

                            <!-- Wallets Pane -->
                            <div class="tab-pane fade" id="cart-wallet-pane" role="tabpanel">
                                <div id="cart-items-wallet"></div>
                                <div id="cart-empty-wallet" class="text-center text-muted py-3" style="display: none;">
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
                        <select class="select2 form-control" id="select-customer-dropdown" style="width: 100%;">
                            <option value="">{{ __('field.search_by_name_phone_or_email') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                    data-name="{{ $user->name }}"
                                    data-email="{{ $user->email ?? '' }}"
                                    data-phone="{{ $user->phone ?? $user->full_phone ?? '' }}"
                                    data-image="{{ $user->image ?? '' }}">
                                    {{ $user->name }} 
                                    @if($user->phone || $user->full_phone)
                                        - {{ $user->phone ?? $user->full_phone }}
                                    @endif
                                    @if($user->email)
                                        - {{ $user->email }}
                                    @endif
                                </option>
                            @endforeach
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
                   
                        
                        @if($users->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="ti ti-users-off" style="font-size: 2rem;"></i>
                                <p class="mt-2">{{ __('field.no_customers_found') }}</p>
                            </div>
                        @endif
                    </div>
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
                                <input type="number" maxlength="7" id="quick_customer_phone" class="form-control" name="phone" required />
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
                                    <select class="select2 form-control" name="user_id" id="modal-wallet-user" required>
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
                                        <option value="">{{ __('admin.Choose Type') }}</option>
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
                                        <option value="">{{ __('admin.Choose Worker') }}</option>
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
                                        <option value="">{{ __('admin.Choose Commission') }}</option>
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
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-selects.js', 'resources/assets/js/app-ecommerce-product-add.js', 'resources/assets/js/form-wizard-icons.js'])
    @include('CenterUser.Components.translation-js')

    <script>
        $(document).ready(function() {
            let cart = @json($cart['items'] ?? []);
            let selectedCustomerId = @json($cart['client_id'] ?? null);
            let selectedCustomerName = null; // Will be set on load or selection
            let selectedCustomerPhone = null; // Will be set on load or selection
            let bookingWizardData = {};
            let bookingIds = {};

            // Store service prices from loaded services
            let servicesData = {};
            @foreach ($services as $service)
                servicesData[{{ $service->id }}] = {
                    id: {{ $service->id }},
                    name: '{{ addslashes($service->name) }}',
                    price: {{ $service->price ?? 0 }},
                    has_commission: {{ $service->has_commission ? 'true' : 'false' }}
                };
            @endforeach

            // Initialize Select2
            $('#booking-services, #product-products, #product-sales_worker, #product-worker, #modal-wallet-user').select2();
            
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
            
            // Combine prefix with phone number on form submit in Add Customer Modal
            $('#save-quick-customer-btn').on('click', function(e) {
                const countryCodeSelect = $('#addCustomerModal').find('select[name="country_code"]');
                const phonePrefixSelect = $('#quick_customer_phone_prefix');
                const phoneInput = $('#quick_customer_phone');
                
                if (countryCodeSelect.length && countryCodeSelect.val() === '+971' && phonePrefixSelect.length && phoneInput.length) {
                    const prefix = phonePrefixSelect.val();
                    const phone = phoneInput.val();
                    if (prefix && phone) {
                        // Combine prefix with phone number
                        phoneInput.val(prefix + phone);
                    }
                }
            });
            
            // Initialize Select2 for customer dropdown in modal
            $('#select-customer-dropdown').select2({
                dropdownParent: $('#selectCustomerModal'),
                placeholder: '{{ __('field.search_by_name_phone_or_email') }}',
                allowClear: true,
                language: {
                    noResults: function() {
                        return '{{ __('field.no_customers_found') }}';
                    },
                    searching: function() {
                        return '{{ __('field.searching') }}...';
                    }
                }
            });

            // Initialize Booking Wizard Stepper
            const bookingStepper = new Stepper(document.querySelector('.bs-stepper'));

            // Booking Step 1: Services Selection
            $('#booking-services').on('change', function() {
                $('#booking-nextStep1').prop('disabled', $(this).val().length === 0);
            });

            $('#booking-nextStep1').on('click', function(e) {
                e.preventDefault();
                let services = $('#booking-services').val();
                if (!services || services.length === 0) {
                    alert('Please select at least one service.');
                    return false;
                }

                bookingIds = services;
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
                        service_info += `<option value="${worker.id}">${worker.name}</option>`;
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
                    
                    @if(has_commission_permission())
                        @php
                            $allowedBookingType = get_allowed_commission_type('booking');
                        @endphp
                        @if($allowedBookingType)
                        service_info += `
                            <div class="col-md-2">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.commission_type') }}</label>
                                    <input type="hidden" name="service[${service.id}][commission_type]" value="{{ $allowedBookingType }}">
                                    <select class="form-control commission-type-select" name="service[${service.id}][commission_type_display]" data-service-id="${service.id}" disabled>
                                        <option value="{{ $allowedBookingType }}" selected>
                                            @if($allowedBookingType == 'percentage')
                                                {{ __('field.percentage') }}
                                            @else
                                                {{ __('field.fixed_value') }}
                                            @endif
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.commission') }}</label>`;
                        @if($allowedBookingType == 'percentage')
                        service_info += `
                                    <select class="form-control commission-percentage-select" name="service[${service.id}][commission]" id="booking-commission_percentage_${service.id}">
                                        <option value="">{{ __('admin.Choose Commission') }}</option>`;
                        for (let i = 1; i <= 100; i++) {
                            service_info += `<option value="${i}">${i}%</option>`;
                        }
                        service_info += `
                                    </select>`;
                        @else
                        service_info += `
                                    <input type="number" class="form-control commission-fixed-input" name="service[${service.id}][commission]" id="booking-commission_fixed_${service.id}" placeholder="{{ __('field.commission') }}" step="0.01" min="0" max="${servicePrice}" data-service-price="${servicePrice}">
                                    <small class="text-muted commission-max-hint" id="booking-commission_max_hint_${service.id}">{{ __('field.max_commission') }}: ${parseFloat(servicePrice).toFixed(5)} {{ get_currency() }}</small>`;
                        @endif
                        service_info += `
                                </div>
                            </div>`;
                        @endif
                    @endif
                    
                    service_info += `</div>`;

                    $('#booking-service-container').append(service_info);
                    
                    // Add real-time validation for fixed commission input after element is appended
                    @if(has_commission_permission())
                        @php
                            $allowedBookingType = get_allowed_commission_type('booking');
                        @endphp
                        @if($allowedBookingType == 'fixed')
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
                                            $hint.removeClass('text-muted').addClass('text-danger').text('{{ __('field.commission_cannot_exceed_service_price') }}');
                                        }
                                    } else {
                                        $(this).removeClass('is-invalid');
                                        if ($hint.length) {
                                            $hint.removeClass('text-danger').addClass('text-muted').html('{{ __('field.max_commission') }}: ' + parseFloat(currentServicePrice).toFixed(5) + ' {{ get_currency() }}');
                                        }
                                    }
                                    // Check all commission fields and enable/disable Next button
                                    checkBookingCommissionValidation();
                                });
                                // Initial check when element is created
                                setTimeout(function() {
                                    checkBookingCommissionValidation();
                                }, 50);
                            }
                        }, 10);
                        @endif
                    @endif
                });

                bookingStepper.next();
            });

            // Function to check commission validation and enable/disable Next button
            function checkBookingCommissionValidation() {
                @if(has_commission_permission())
                    @php
                        $allowedBookingType = get_allowed_commission_type('booking');
                    @endphp
                    @if($allowedBookingType == 'fixed')
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
                    @endif
                @endif
            }

            // Booking Step 2: Booking Details
            $('#booking-nextStep2').on('click', function(e) {
                e.preventDefault();
                
                // Check commission validation before proceeding
                @if(has_commission_permission())
                    @php
                        $allowedBookingType = get_allowed_commission_type('booking');
                    @endphp
                    @if($allowedBookingType == 'fixed')
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
                                    $hint.removeClass('text-muted').addClass('text-danger').text('{{ __('field.commission_cannot_exceed_service_price') }}');
                                }
                            }
                        }
                    });
                    if (hasInvalid) {
                        alert('{{ __('field.commission_cannot_exceed_service_price') }}');
                        return false;
                    }
                    @endif
                @endif

                var servicesArray = [];
                let isValid = true;

                bookingIds.forEach(service => {
                    var date = $('input[name="service[' + service + '][date]"]').val();
                    var worker_id = $('select[name="service[' + service + '][worker_id]"]').val();
                    var from_time = $('input[name="service[' + service + '][from_time]"]').val();
                    var to_time = $('input[name="service[' + service + '][to_time]"]').val();
                    var commission = '';
                    var commissionType = '';
                    
                    @if(has_commission_permission())
                        @php
                            $allowedBookingType = get_allowed_commission_type('booking');
                        @endphp
                        @if($allowedBookingType)
                        commissionType = '{{ $allowedBookingType }}';
                        if (commissionType === 'percentage') {
                            commission = $('#booking-commission_percentage_' + service).val();
                        } else if (commissionType === 'fixed') {
                            commission = $('#booking-commission_fixed_' + service).val();
                            // Validate fixed commission doesn't exceed service price
                            var servicePrice = parseFloat($('#booking-commission_fixed_' + service).data('service-price')) || 0;
                            var commissionValue = parseFloat(commission) || 0;
                            if (commission && commissionValue > servicePrice) {
                                alert('{{ __('field.commission_cannot_exceed_service_price') }}. {{ __('field.service_price') }}: ' + parseFloat(servicePrice).toFixed(5) + ' {{ get_currency() }}');
                                isValid = false;
                                return false;
                            }
                        }
                        @endif
                    @endif

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
                
                // Validate customer selection before proceeding to Step 3
                if (!selectedCustomerId) {
                     // Check if a customer is already selected in the UI but maybe logic didn't catch it (unlikely but safe)
                     // If really no customer, stop and alert
                     if (typeof toastr !== 'undefined') {
                         toastr.error('{{ __('field.customer_required') }}');
                     } else {
                         alert('{{ __('field.customer_required') }}');
                     }
                     
                     // Highlight the customer card
                     $('html, body').animate({
                        scrollTop: $('.card-header:contains("{{ __('field.customer') }}")').offset().top - 100
                     }, 500);
                     return;
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
            });

            // Booking Step 3: Customer Details - validation removed as it relies on global customer now
            // Just ensure payment type is selected if needed, though mostly review now
            
            // Enable next button by default since customer is already pre-validated coming into this step
            $('#booking-nextStep3').prop('disabled', false);

            $('#booking-nextStep3').on('click', function(e) {
                e.preventDefault();
                // name and mobile are already in bookingWizardData from Step 2 transition
                var paymentType = $('#booking-payment_type').val();

                if (!bookingWizardData.name) {
                    alert('Customer information missing. Please select a customer.');
                    return false;
                }

                if (!bookingWizardData.services || bookingWizardData.services.length === 0) {
                    alert('Please complete step 2 (Booking Details) first.');
                    return false;
                }

                bookingWizardData.payment_type = paymentType;

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
                    reviewHtml += `<tr>
                        <td>${item.name.trim()}</td>
                        <td>${service ? service.price : 'N/A'}</td>
                        <td>${item.date}</td>
                        <td>${worker ? worker.name : 'N/A'}</td>
                        <td>${item.from_time}</td>
                        <td>${item.to_time}</td>
                    </tr>`;
                });
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
                    <td colspan="5">${bookingWizardData.payment_type || '{{ __('field.not_selected') }}'}</td>
                </tr></tbody></table>`;

                $('#booking-review-content').html(reviewHtml);
                bookingStepper.next();
            });

            // Booking Step 4: Add to Cart
            $('#addBookingToCart').on('click', function() {
                if (!bookingWizardData.services || bookingWizardData.services.length === 0) {
                    alert('Please complete all steps first.');
                    return;
                }

                // Add each service to cart
                bookingWizardData.services.forEach(service => {
                    const worker = get_worker(service.worker_id);
                    const serviceData = get_service(service.id);
                    
                    // Check if service already in cart
                    if (cart.some(item => item.type === 'service' && item.id == service.id)) {
                        return; // Skip if already in cart
                    }

                    cart.push({
                        type: 'service',
                        id: service.id,
                        name: service.name,
                        price: serviceData ? serviceData.price : 0,
                        worker_id: service.worker_id,
                        worker_name: worker ? worker.name : '',
                        date: service.date,
                        from_time: service.from_time,
                        to_time: service.to_time,
                        commission: service.commission || null,
                        commission_type: service.commission_type || null,
                        client_name: bookingWizardData.name,
                        client_mobile: bookingWizardData.mobile,
                        payment_type: bookingWizardData.payment_type || null
                    });
                });

                renderCart();
                resetBookingWizard();

                if (typeof toastr !== 'undefined') {
                    toastr.success('{{ __('locale.services') }} added to cart');
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
                // Payment method will be selected in the payment section, not here
                const discount = $('#product-discount').val();
                const salesWorkerId = $('#product-sales_worker').val();
                const workerId = $('#product-worker').val();
                const commission = $('#product-commission').val();
                const paymentType = $('#product-payment_type').val();

                // Validation
                if (!selectedProducts || selectedProducts.length === 0) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.please_select_product') }}');
                    }
                    return;
                }

                if (!salesWorkerId) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error('{{ __('field.please_select_sales_worker') }}');
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
                        const salesWorker = get_worker(salesWorkerId);
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
                            sales_worker_id: salesWorkerId,
                            sales_worker_name: salesWorker ? salesWorker.name : '',
                            worker_id: workerId || null,
                            worker_name: worker ? worker.name : '',
                            commission: commission || null
                        });
    
                        productsAdded++;
                    });
    
                    if (productsAdded > 0) {
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
            function calculateTotals() {
                let subtotal = 0;
                cart.forEach(item => {
                    if (item.type === 'wallet') {
                        // Include wallet/coupon amount in subtotal
                        subtotal += parseFloat(item.amount || 0);
                    } else {
                        const price = parseFloat(item.price || 0);
                        const quantity = parseInt(item.quantity || 1);
                        subtotal += price * quantity;
                    }
                });
                $('#cart-subtotal').text(subtotal.toFixed(2) + ' {{ get_currency() }}');
                $('#cart-total').text(subtotal.toFixed(2) + ' {{ get_currency() }}');
                // Disable button if cart is empty OR no customer is selected
                $('#continueToPayment').prop('disabled', cart.length === 0 || !selectedCustomerId);
            }

            function renderCart() {
                let serviceHtml = '';
                let productHtml = '';
                let walletHtml = '';
                let serviceCount = 0;
                let productCount = 0;
                let walletCount = 0;

                cart.forEach((item, index) => {
                    // Get item name
                    let itemName = item.name || '{{ __('field.item') }}';
                    if (item.type === 'wallet') {
                        itemName = '{{ __('field.coupon') }} / {{ __('locale.wallets') }}';
                    }
                    
                    let itemHtml = `<div class="cart-item mb-3 p-2 border rounded" data-index="${index}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${itemName}</h6>`;
                    
                    if (item.type === 'service') {
                        itemHtml += `<small class="text-muted">
                            {{ __('field.worker') }}: ${item.worker_name || ''}<br>
                            {{ __('field.date') }}: ${item.date}<br>
                            ${item.from_time} - ${item.to_time}
                        </small>`;
                    } else if (item.type === 'product') {
                        itemHtml += `<small class="text-muted">
                            {{ __('field.quantity') }}: ${item.quantity}`;
                        if (item.sales_worker_id) {
                            const salesWorker = get_worker(item.sales_worker_id);
                            itemHtml += `<br>{{ __('field.sales_worker') }}: ${salesWorker ? salesWorker.name : ''}`;
                        }
                        if (item.worker_id && item.worker_name) {
                            itemHtml += `<br>{{ __('field.worker') }}: ${item.worker_name}`;
                        }
                        if (item.commission) {
                            itemHtml += `<br>{{ __('field.commission') }}: ${item.commission}%`;
                        }
                        itemHtml += `</small>`;
                    } else if (item.type === 'wallet') {
                        itemHtml += `<small class="text-muted">
                            {{ __('field.amount') }}: ${item.amount || 0} {{ get_currency() }}`;
                        if (item.start_at) {
                            itemHtml += `<br>{{ __('field.start_at') }}: ${item.start_at}`;
                        }
                        if (item.end_at) {
                            itemHtml += `<br>{{ __('field.end_at') }}: ${item.end_at}`;
                        }
                        itemHtml += `</small>`;
                    }
                    
                    // Calculate and display price
                    let displayPrice = 0;
                    if (item.type === 'wallet') {
                        displayPrice = parseFloat(item.amount || 0);
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
                    } else if (item.type === 'wallet') {
                        walletHtml += itemHtml;
                        walletCount++;
                    }
                });

                // Update DOM
                $('#cart-items-service').html(serviceHtml);
                $('#cart-items-product').html(productHtml);
                $('#cart-items-wallet').html(walletHtml);

                // Update Counts
                updateTabCount('#cart-booking-count', serviceCount);
                updateTabCount('#cart-products-count', productCount);
                updateTabCount('#cart-wallet-count', walletCount);

                // Handle Empty States
                $('#cart-empty-service').toggle(serviceCount === 0);
                $('#cart-empty-product').toggle(productCount === 0);
                $('#cart-empty-wallet').toggle(walletCount === 0);
                
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
            function loadCustomerServices(user_phone) {
                 if (!user_phone) {
                     $('#booking-servicesTable, #booking-walletsElement, #booking-membershipsElement').html('');
                     return;
                 }

                 var response = get_services(user_phone);
                 if (response.status) {
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
                                servicesTable += `<td>${service.name}</td>`;
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
                            walletsElement += `<hr /><h5>Wallet</h5><div class="row">`;
                            $.each(wallets, function(index, item) {
                                var wallet = item.wallet;
                                walletsElement += `<div class="col-md-4">
                                    <div class="form-check" style="width: 200px;padding: 10px;color: #fff;background-color: #428bca;border-color: #357ebd;text-align: center;display: flex;justify-content: space-between;font-size: 14px;">
                                        <label class="form-check-label" for="booking-wallets${wallet.id}">
                                            ${wallet.code + ' [' + wallet.amount + ' AED]'}
                                        </label>
                                        <input class="form-check-input" type="radio" name="discount_id" data-name="discount_id" value="${wallet.id}" id="booking-wallets${wallet.id}">
                                    </div>
                                </div>`;
                            });
                            walletsElement += `</div>`;
                            $('#booking-walletsElement').html(walletsElement);
                        }
                    }

                    if (response.memberships) {
                        var memberships = response.memberships;
                        let membershipsElement = ``;
                        $('#booking-membershipsElement').html(membershipsElement);
                        if (memberships.length != 0) {
                            membershipsElement += `<hr /><h5>MemberShip Cards</h5><div class="row">`;
                            $.each(memberships, function(index, item) {
                                membershipsElement += `<div class="col-md-4">
                                    <div class="form-check" style="width: 200px;padding: 10px;color: #fff;background-color: #428bca;border-color: #357ebd;text-align: center;display: flex;justify-content: space-between;font-size: 14px;">
                                        <label class="form-check-label" for="booking-memberships${item.id}">
                                            ${item.membership_no + ' [' + item.percent + '%]'}
                                        </label>
                                        <input class="form-check-input" type="radio" name="discount_id" data-name="discount_id" value="${item.id}" id="booking-memberships${item.id}">
                                    </div>
                                </div>`;
                            });
                            membershipsElement += `</div>`;
                            $('#booking-membershipsElement').html(membershipsElement);
                        }
                    }
                } else {
                    $('#booking-servicesTable, #booking-walletsElement, #booking-membershipsElement').html('');
                }
            }

            function get_services(user_phone) {
                var services = [];
                $.ajax({
                    url: "{{ route('center_user.bookings.get-services-by-user') }}",
                    method: 'GET',
                    async: false,
                    data: {
                        _token: '{{ csrf_token() }}',
                        user_phone: user_phone,
                    },
                    success: function(response) {
                        services = response;
                    }
                });
                return services;
            }

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
                            const newOption = new Option(serviceData.name, serviceData.id, false, false);
                            $servicesSelect.append(newOption).trigger('change');

                            if (typeof servicesData !== 'undefined') {
                                servicesData[serviceData.id] = {
                                    id: serviceData.id,
                                    name: serviceData.name,
                                    price: serviceData.price || 0,
                                    has_commission: serviceData.has_commission || false
                                };
                            }

                            $('#addServiceModal').modal('hide');
                            $('#quick-add-service-form')[0].reset();
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
            $(document).on('click', '.add-wallet-user-btn', function() {
                const walletId = $(this).data('wallet-id');
                const walletCode = $(this).data('wallet-code');
                const walletAmount = parseFloat($(this).data('wallet-amount')) || 0;
                const walletInvoiced = parseFloat($(this).data('wallet-invoiced')) || 0;
                const walletStart = $(this).data('wallet-start') || null;
                const walletEnd = $(this).data('wallet-end') || null;

                // Set wallet ID in hidden field
                $('#modal-wallet-id').val(walletId);
                
                // Update modal title to show wallet code
                $('#addWalletUserModalLabel').text('{{ __('locale.add_users_to') }} {{ __('locale.wallets') }} (' + walletCode + ')');
                
                // Reset form
                $('#add-wallet-user-form')[0].reset();
                $('#modal-wallet-user').val(null).trigger('change');
                $('#modal-wallet-commission-div').hide();
                $('#modal-wallet-commission').prop('required', false);
                
                // Initialize Select2 for user field if not already initialized
                if (!$('#modal-wallet-user').hasClass('select2-hidden-accessible')) {
                    $('#modal-wallet-user').select2({
                        dropdownParent: $('#addWalletUserModal')
                    });
                }
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

                // Create user wallet via AJAX
                $.ajax({
                    url: '{{ route("center_user.users_wallets.updateOrCreate") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        wallet_id: walletId,
                        user_id: userId,
                        wallet_type: walletType,
                        worker_id: workerId || null,
                        commission: commission || null
                    },
                    success: function(response) {
                        if (response.message === 'redirect_to_home') {
                            // Add wallet to cart after successful creation
                            cart.push({
                                type: 'wallet',
                                wallet_id: walletId,
                                code: walletCode,
                                amount: walletAmount,
                                invoiced_amount: walletInvoiced,
                                start_at: walletStart,
                                end_at: walletEnd,
                                user_id: userId,
                                wallet_type: walletType,
                                worker_id: workerId,
                                commission: commission
                            });

                            renderCart();

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
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            // Add Coupon Quick Action - handled in modal

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
                        if (typeof toastr !== 'undefined') {
                            toastr.success('{{ __('field.customer_removed') }}');
                        }
                    }
                });
            });

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

            // Quick add customer
            $('#save-quick-customer-btn').on('click', function(e) {
                e.preventDefault();
                const form = $('#quick-add-customer-form')[0];
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
                                    
                                    // Add to selection dropdowns
                                    let label = userName;
                                    if (userPhone) label += ' - ' + userPhone;
                                    if (userEmail) label += ' - ' + userEmail;

                                    const newOption = new Option(label, userData.id, false, false);
                                    $(newOption).attr('data-name', userName);
                                    $(newOption).attr('data-phone', userPhone);
                                    $(newOption).attr('data-email', userEmail);
                                    $(newOption).attr('data-image', userImage);
                                    
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
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                    },
                    complete: function() {
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