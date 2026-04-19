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
                                            <div class="mt-3 mb-1">
                                                <label for="booking-packages" class="form-label mb-0">{{ __('locale.packages') }}</label>
                                                <select class="select2 form-control " name="packages[]" id="booking-packages" multiple>
                                                    @foreach ($packages as $package)
                                                        <option value="{{ $package->id }}" data-price="{{ $package->price }}">{{ $package->name }} ({{ $package->price }} {{ get_currency() }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
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
                        <div class="row mb-2">
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
