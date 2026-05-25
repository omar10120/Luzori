@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if ($isExpired || session('center_expired'))
            @include('CenterUser.SubViews.Subscription.partials.subscription-status')
        @else
            @include('CenterUser.Components.breadcrumbs')
            @if ($hasSubscription)
                @include('CenterUser.SubViews.Subscription.partials.subscription-status')
            @endif
        @endif

        <div id="subscription-plans-step">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">
                        @if ($isExpired || session('center_expired'))
                            {{ __('field.complete_payment') }}
                        @else
                            {{ __('field.payment') }} — {{ __('general.choose') }} {{ __('field.payment') }}
                        @endif
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        @foreach ($plans as $planKey => $plan)
                            <div class="col-md-4">
                                <div class="card h-100 border shadow-none plan-card cursor-pointer"
                                    data-plan="{{ $planKey }}" role="button" tabindex="0">
                                    <div class="card-body text-center d-flex flex-column">
                                        <h3 class="text-primary mb-1">{{ $plan['symbol'] }} {{ number_format($plan['amount']) }}</h3>
                                        <p class="text-muted mb-3">{{ $plan['label'] }}</p>
                                        <button type="button"
                                            class="btn btn-primary mt-auto btn-select-plan"
                                            data-plan="{{ $planKey }}">
                                            {{ __('field.continue_to_payment') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div id="subscription-payment-step" class="d-none">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0" id="selected-plan-title">{{ __('field.complete_payment') }}</h4>
                    @if (!($isExpired || session('center_expired')))
                        <button type="button" class="btn btn-sm btn-label-secondary" id="btn-back-to-plans">
                            <i class="ti ti-arrow-left me-1"></i>{{ __('general.back') }}
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    @if ($hasSubscription)
                        <div id="subscription-status-payment" class="mb-4">
                            @include('CenterUser.SubViews.Subscription.partials.subscription-status')
                        </div>
                    @endif
                    <div id="myfatoorah-payment-loading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading</span>
                        </div>
                        <p class="text-muted mt-2 mb-0">{{ __('field.continue_to_payment') }}</p>
                    </div>
                    <div id="myfatoorah-payment-success" class="alert alert-success d-none mb-3" role="alert"></div>
                    <div id="myfatoorah-payment-error" class="alert alert-danger d-none mb-3" role="alert"></div>
                    <div id="embedded-sessions"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const createSessionUrl = @json($createSessionUrl);
            const callbackUrl = @json($callbackUrl);
            const sessionJsUrl = @json($myfatoorahSessionJsUrl);

            const plansStep = document.getElementById('subscription-plans-step');
            const paymentStep = document.getElementById('subscription-payment-step');
            const loadingEl = document.getElementById('myfatoorah-payment-loading');
            const errorEl = document.getElementById('myfatoorah-payment-error');
            const successEl = document.getElementById('myfatoorah-payment-success');
            const containerEl = document.getElementById('embedded-sessions');
            const selectedPlanTitle = document.getElementById('selected-plan-title');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            let sessionScriptPromise = null;
            let encryptionKey = null;
            let callbackHandled = false;

            function showLoading(show) {
                loadingEl.classList.toggle('d-none', !show);
            }

            function showError(message) {
                errorEl.textContent = message;
                errorEl.classList.remove('d-none');
            }

            function hideError() {
                errorEl.classList.add('d-none');
                errorEl.textContent = '';
            }

            function showSuccess(message) {
                successEl.textContent = message;
                successEl.classList.remove('d-none');
            }

            function hideSuccess() {
                successEl.classList.add('d-none');
                successEl.textContent = '';
            }

            function resetPaymentUi() {
                hideError();
                hideSuccess();
                containerEl.innerHTML = '';
                encryptionKey = null;
                callbackHandled = false;
            }

            function loadMyFatoorahScript() {
                if (window.myfatoorah) {
                    return Promise.resolve();
                }

                if (sessionScriptPromise) {
                    return sessionScriptPromise;
                }

                sessionScriptPromise = new Promise(function(resolve, reject) {
                    const existing = document.querySelector('script[data-myfatoorah-session="1"]');
                    if (existing) {
                        existing.addEventListener('load', function() {
                            resolve();
                        });
                        existing.addEventListener('error', function() {
                            reject(new Error('Failed to load MyFatoorah payment SDK.'));
                        });
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = sessionJsUrl;
                    script.setAttribute('data-myfatoorah-session', '1');
                    script.onload = function() {
                        resolve();
                    };
                    script.onerror = function() {
                        reject(new Error('Failed to load MyFatoorah payment SDK.'));
                    };
                    document.head.appendChild(script);
                });

                return sessionScriptPromise;
            }

            function initEmbeddedSession(sessionId) {
                if (!window.myfatoorah || typeof window.myfatoorah.init !== 'function') {
                    throw new Error('MyFatoorah SDK is not available.');
                }

                window.myfatoorah.init({
                    sessionId: sessionId,
                    callback: onPaymentComplete,
                    containerId: 'embedded-sessions',
                    shouldHandlePaymentUrl: true,
                });
            }

            async function createSession(planKey) {
                const response = await fetch(createSessionUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        plan: planKey
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Payment session could not be created.');
                }

                encryptionKey = data.encryption_key;
                selectedPlanTitle.textContent =
                    '{{ __('field.complete_payment') }} — ' + data.amount + ' (' + data.label + ')';

                return data.session_id;
            }

            async function submitCallback(response) {
                const payload = {
                    paymentData: response.paymentData ?? response.PaymentData ?? null,
                    encryptionKey: encryptionKey,
                    paymentCompleted: response.paymentCompleted ?? response.PaymentCompleted ?? false,
                };

                const res = await fetch(callbackUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(payload),
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Payment verification failed.');
                }

                return data;
            }

            async function onPaymentComplete(response) {
                const paymentCompleted = !!(response.paymentCompleted ?? response.PaymentCompleted);
                const paymentData = response.paymentData ?? response.PaymentData ?? null;

                // SDK invokes callback during the flow (e.g. card entry) — wait for final completion.
                if (!paymentCompleted || !paymentData) {
                    return;
                }

                if (callbackHandled) {
                    return;
                }
                callbackHandled = true;

                try {
                    showLoading(true);
                    hideError();
                    const result = await submitCallback(response);
                    showLoading(false);
                    containerEl.innerHTML = '';
                    showSuccess(result.message + (result.new_expiry ? ' — ' + result.new_expiry : ''));
                    @if ($isExpired || session('center_expired'))
                        setTimeout(function() {
                            window.location.href = @json(route('center_user.cp'));
                        }, 2500);
                    @endif
                } catch (error) {
                    callbackHandled = false;
                    showLoading(false);
                    showError(error.message || 'Payment callback failed.');
                }
            }

            async function startCheckout(planKey) {
                resetPaymentUi();
                plansStep.classList.add('d-none');
                paymentStep.classList.remove('d-none');
                showLoading(true);

                try {
                    const sessionId = await createSession(planKey);
                    await loadMyFatoorahScript();
                    showLoading(false);
                    initEmbeddedSession(sessionId);
                } catch (error) {
                    showLoading(false);
                    showError(error.message || 'Unable to start payment.');
                }
            }

            document.querySelectorAll('.btn-select-plan').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    startCheckout(this.dataset.plan);
                });
            });

            const btnBackToPlans = document.getElementById('btn-back-to-plans');
            if (btnBackToPlans) {
                btnBackToPlans.addEventListener('click', function() {
                    resetPaymentUi();
                    showLoading(false);
                    paymentStep.classList.add('d-none');
                    plansStep.classList.remove('d-none');
                });
            }

            @if ($isExpired || session('center_expired'))
                document.querySelector('.btn-select-plan')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            @endif
        })();
    </script>
@endpush
