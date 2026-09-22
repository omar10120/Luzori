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

                            <hr class="my-2" />

                            <div class="d-flex align-items-center gap-2 mb-3 mt-4">
                                <i class="ti ti-printer fs-4 text-primary"></i>
                                <h5 class="mb-0 fw-semibold">{{ __('field.auto_print_sale') ?? 'Automatic sale printing' }}</h5>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="auto_print_sale" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="auto_print_sale"
                                    name="auto_print_sale" value="1" @checked($item->auto_print_sale)>
                                <label class="form-check-label" for="auto_print_sale">
                                    {{ __('general.auto_print_sale_after_payment') ?? 'Print the sale invoice automatically after payment' }}
                                </label>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-3 mt-4">
                                <i class="ti ti-mail fs-4 text-primary"></i>
                                <h5 class="mb-0 fw-semibold">{{ __('field.sms_allow') ?? 'Automatic sale printing' }}</h5>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input type="hidden" name="sms_allow" value="0">
                                <input class="form-check-input" type="checkbox" role="switch" id="sms_allow"
                                    name="sms_allow" value="1" @checked($item->sms_allow)>
                                <label class="form-check-label" for="sms_allow">
                                    {{ __('general.allow_sms') ?? 'Allow SMS notifications' }}
                                </label>
                                <div class="form-text">
                                    <!-- {{ __('general.sms_package') ?? 'SMS package' }}: 50, 150, 500, 1000 {{ __('general.requests') ?? 'SMS requests' }} / AED 50, 150, 500, 1000 -->
                                    <!-- <span class="mx-1">|</span> -->
                                    {{ __('general.sms_package_balance') ?? 'SMS package balance' }}: {{ number_format($smsPackageAmount) }}
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('CenterUser.SubViews.InvoiceSettings.sms-payment-modal')
@endsection

@section('page-script')
    <script>
        (function() {
            const form = document.getElementById('frmSubmit');
            const packageModalElement = document.getElementById('smsPackageModal');
            const paymentModalElement = document.getElementById('smsPaymentModal');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const sessionJsUrl = @json(env('MYFATOORAH_SESSION_JS_URL', 'https://demo.myfatoorah.com/sessions/v1/session.js'));
            const smsPackages = @json($smsPackages);
            let scriptPromise = null;
            let encryptionKey = null;
            let selectedPackage = null;

            function showModal(element) {
                element.classList.add('is-visible', 'show');
                element.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
            }

            function hideModal(element) {
                element.classList.remove('is-visible', 'show');
                element.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
            }

            function loadSdk() {
                if (window.myfatoorah) return Promise.resolve();
                if (scriptPromise) return scriptPromise;
                scriptPromise = new Promise(function(resolve, reject) {
                    const script = document.createElement('script');
                    script.src = sessionJsUrl;
                    script.onload = resolve;
                    script.onerror = function() { reject(new Error('Failed to load payment SDK.')); };
                    document.head.appendChild(script);
                });
                return scriptPromise;
            }

            function showPayment(response) {
                const loading = document.getElementById('sms-payment-loading');
                const error = document.getElementById('sms-payment-error');
                const packageData = smsPackages[selectedPackage];
                document.getElementById('selectedSmsPackageLabel').textContent = packageData.label;
                document.getElementById('selectedSmsPackageAmount').textContent = packageData.symbol + ' ' + Number(packageData.amount).toLocaleString();
                error.classList.add('d-none');
                loading.classList.remove('d-none');
                hideModal(packageModalElement);
                showModal(paymentModalElement);
                fetch(response.create_session_url, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ package: selectedPackage }),
                })
                    .then(function(result) { return result.json(); })
                    .then(function(data) {
                        if (!data.success) throw new Error(data.message || 'Unable to start payment.');
                        encryptionKey = data.encryption_key;
                        return loadSdk().then(function() {
                            loading.classList.add('d-none');
                            window.myfatoorah.init({
                                sessionId: data.session_id,
                                callback: function(paymentResponse) {
                                    if (!(paymentResponse.paymentCompleted ?? paymentResponse.PaymentCompleted)) return;
                                    return fetch(response.callback_url, {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                                        body: JSON.stringify({ paymentData: paymentResponse.paymentData ?? paymentResponse.PaymentData, encryptionKey: encryptionKey, paymentCompleted: true }),
                                    }).then(function(result) { return result.json(); }).then(function(callbackData) {
                                        if (!callbackData.success) throw new Error(callbackData.message || 'Payment verification failed.');
                                        window.location.reload();
                                    });
                                },
                                containerId: 'sms-embedded-sessions',
                                shouldHandlePaymentUrl: true,
                            });
                        });
                    }).catch(function(errorMessage) {
                        loading.classList.add('d-none');
                        error.textContent = errorMessage.message;
                        error.classList.remove('d-none');
                    });
            }

            form.addEventListener('submit', function(event) {
                event.preventDefault();
                const button = form.querySelector('.submitFrom');
                button.disabled = true;
                fetch(@json($requestUrl), { method: 'POST', headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, body: new FormData(form) })
                    .then(function(result) { return result.json().then(function(data) { return { result: result, data: data }; }); })
                    .then(function(response) {
                        if (response.result.status === 402 && response.data.requires_sms_payment) {
                            showModal(packageModalElement);
                            return;
                        }
                        if (!response.result.ok) throw new Error(response.data.message || 'Unable to save settings.');
                        window.location.href = response.data.data;
                    }).catch(function(error) {
                        if (typeof toastr !== 'undefined') toastr.error(error.message);
                    }).finally(function() { button.disabled = false; });
            });

            document.querySelectorAll('.sms-package-option').forEach(function(option) {
                option.addEventListener('click', function() {
                    selectedPackage = this.dataset.package;
                    document.querySelectorAll('.sms-package-option').forEach(function(item) {
                        item.classList.toggle('active', item === option);
                    });
                    document.getElementById('continueSmsPackage').disabled = false;
                });
            });

            document.getElementById('continueSmsPackage').addEventListener('click', function() {
                if (!selectedPackage) return;
                showPayment({
                    create_session_url: @json(route('center_user.subscription.sms.create-session')),
                    callback_url: @json(route('center_user.subscription.sms.callback')),
                });
            });

            document.getElementById('closeSmsPackageModal').addEventListener('click', function() {
                hideModal(packageModalElement);
            });
            document.getElementById('closeSmsPaymentModal').addEventListener('click', function() {
                hideModal(paymentModalElement);
            });
            packageModalElement.addEventListener('click', function(event) {
                if (event.target === packageModalElement) hideModal(packageModalElement);
            });
            paymentModalElement.addEventListener('click', function(event) {
                if (event.target === paymentModalElement) hideModal(paymentModalElement);
            });
        })();
    </script>
@endsection
