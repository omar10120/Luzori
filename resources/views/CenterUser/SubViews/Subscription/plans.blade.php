@extends('layouts/layoutMaster')

@section('title', $title)

@section('page-style')
    <style>
        .subscription-page {
            --sub-cream: #f7f0e6;
            --sub-tan: #e8d5bc;
            --sub-tan-dark: #d4b896;
            --sub-green: #2f4f44;
            --sub-green-soft: #4a6b5f;
            --sub-ink: #1a2b26;
            --sub-muted: #6b7c76;
            --sub-white: #ffffff;
            --sub-radius: 1.25rem;
            --sub-radius-lg: 1.5rem;
            max-width: 520px;
            margin: 0 auto;
        }

        .subscription-section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--sub-ink);
            margin-bottom: 1rem;
        }

        .subscription-status-card {
            border-radius: var(--sub-radius-lg);
            padding: 1.125rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 20px rgba(47, 79, 68, 0.06);
        }

        .subscription-status-card--active {
            background: linear-gradient(135deg, #f0f7f4 0%, #e2ebe6 100%);
            border: 1px solid rgba(47, 79, 68, 0.12);
        }

        .subscription-status-card--expired {
            background: linear-gradient(135deg, #fff8ee 0%, #f5e6d0 100%);
            border: 1px solid rgba(180, 130, 60, 0.2);
        }

        .subscription-status-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.7);
            color: var(--sub-green);
            flex-shrink: 0;
        }

        .subscription-status-card--expired .subscription-status-icon {
            color: #b8860b;
        }

        .subscription-status-label {
            font-weight: 600;
            color: var(--sub-ink);
            font-size: 0.9375rem;
            margin: 0;
        }

        .subscription-status-date {
            font-size: 0.875rem;
            color: var(--sub-muted);
            margin: 0;
        }

        .subscription-status-date strong {
            color: var(--sub-ink);
            font-weight: 600;
        }

        .subscription-status-hint {
            font-size: 0.8125rem;
            color: var(--sub-muted);
        }

        .subscription-plan-card {
            background: linear-gradient(135deg, var(--sub-cream) 0%, var(--sub-tan) 55%, var(--sub-tan-dark) 100%);
            border-radius: var(--sub-radius-lg);
            padding: 1.5rem 1.375rem;
            margin-bottom: 1rem;
            box-shadow: 0 8px 28px rgba(90, 70, 45, 0.1);
            border: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }

        .subscription-plan-card:hover,
        .subscription-plan-card:focus-visible {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(90, 70, 45, 0.14);
            outline: none;
        }

        .subscription-plan-card.is-selected {
            box-shadow: 0 0 0 2px var(--sub-green), 0 12px 32px rgba(47, 79, 68, 0.15);
        }

        .subscription-plan-amount {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--sub-ink);
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .subscription-plan-label {
            font-size: 0.9375rem;
            color: var(--sub-muted);
            margin: 0.25rem 0 1.25rem;
        }

        .subscription-plan-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.125rem;
            border-radius: 999px;
            border: 1.5px solid var(--sub-ink);
            background: transparent;
            color: var(--sub-ink);
            font-size: 0.875rem;
            font-weight: 500;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .subscription-plan-action:hover {
            background: var(--sub-ink);
            color: var(--sub-white);
        }

        .subscription-plan-action-icon {
            width: 1.375rem;
            height: 1.375rem;
            border-radius: 50%;
            border: 1.5px solid currentColor;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .subscription-step {
            transition: opacity 0.25s ease, transform 0.25s ease;
        }

        .subscription-step.is-hidden {
            display: none !important;
        }

        .subscription-step.is-leaving {
            opacity: 0;
            transform: translateY(8px);
            pointer-events: none;
        }

        .subscription-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0;
            border: none;
            background: none;
            color: var(--sub-green);
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 1rem;
            cursor: pointer;
        }

        .subscription-back-link:hover {
            color: var(--sub-green-soft);
        }

        .subscription-payment-panel {
            background: var(--sub-white);
            border: 1px solid #e8e4de;
            border-radius: var(--sub-radius);
            padding: 1.25rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
        }

        .subscription-payment-summary {
            background: linear-gradient(135deg, var(--sub-cream) 0%, var(--sub-tan) 100%);
            border-radius: var(--sub-radius);
            padding: 1rem 1.125rem;
            margin-bottom: 1.25rem;
        }

        .subscription-payment-summary .summary-amount {
            font-size: 1.375rem;
            font-weight: 700;
            color: var(--sub-ink);
        }

        .subscription-payment-summary .summary-label {
            font-size: 0.875rem;
            color: var(--sub-muted);
            margin: 0;
        }

        #embedded-sessions {
            min-height: 120px;
        }

        .subscription-alert {
            border-radius: var(--sub-radius);
            font-size: 0.875rem;
            padding: 0.875rem 1rem;
            margin-bottom: 1rem;
        }

        .subscription-alert--success {
            background: #eef6f1;
            border: 1px solid rgba(47, 79, 68, 0.15);
            color: var(--sub-green);
        }

        .subscription-alert--error {
            background: #fdf0ee;
            border: 1px solid rgba(180, 80, 70, 0.2);
            color: #9b3d35;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="subscription-page">
            @if (!($isExpired || session('center_expired')))
                @include('CenterUser.Components.breadcrumbs')
            @endif

            @if (($isExpired || session('center_expired')) || $hasSubscription)
                @include('CenterUser.SubViews.Subscription.partials.subscription-status')
            @endif

            {{-- Step 1: Choose plan --}}
            <div id="subscription-plans-step" class="subscription-step">
                <h2 class="subscription-section-title">
                    @if ($isExpired || session('center_expired'))
                        {{ __('field.complete_payment') }}
                    @else
                        {{ __('general.choose') }} {{ __('field.payment') }}
                    @endif
                </h2>

                <div class="subscription-plans-list">
                    @foreach ($plans as $planKey => $plan)
                        <article class="subscription-plan-card" data-plan="{{ $planKey }}" tabindex="0"
                            role="button" aria-label="{{ $plan['label'] }}">
                            <div class="subscription-plan-amount">
                                {{ $plan['symbol'] }} {{ number_format($plan['amount']) }}
                            </div>
                            <p class="subscription-plan-label">{{ $plan['label'] }}</p>
                            <button type="button" class="subscription-plan-action btn-select-plan"
                                data-plan="{{ $planKey }}">
                                <span class="subscription-plan-action-icon"><i class="ti ti-plus"></i></span>
                                {{ __('field.continue_to_payment') }}
                            </button>
                        </article>
                    @endforeach
                </div>
            </div>

            {{-- Step 2: Pay inline (same page, not a modal) --}}
            <div id="subscription-payment-step" class="subscription-step is-hidden">
                <button type="button" class="subscription-back-link" id="btn-back-to-plans">
                    <i class="ti ti-chevron-left"></i>
                    {{ __('general.back') }}
                </button>

                <h2 class="subscription-section-title">{{ __('field.complete_payment') }}</h2>

                <div class="subscription-payment-summary d-none" id="selected-plan-summary">
                    <p class="summary-label mb-1" id="selected-plan-label"></p>
                    <p class="summary-amount mb-0" id="selected-plan-amount"></p>
                </div>

                <div class="subscription-payment-panel">
                    <div id="myfatoorah-payment-loading" class="text-center py-5 d-none">
                        <div class="spinner-border text-secondary" role="status" style="color: var(--sub-green) !important;">
                            <span class="visually-hidden">Loading</span>
                        </div>
                        <p class="text-muted mt-2 mb-0 small">{{ __('field.continue_to_payment') }}</p>
                    </div>
                    <div id="myfatoorah-payment-success" class="subscription-alert subscription-alert--success d-none" role="alert"></div>
                    <div id="myfatoorah-payment-error" class="subscription-alert subscription-alert--error d-none" role="alert"></div>
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
            const planSummary = document.getElementById('selected-plan-summary');
            const planSummaryLabel = document.getElementById('selected-plan-label');
            const planSummaryAmount = document.getElementById('selected-plan-amount');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            let sessionScriptPromise = null;
            let encryptionKey = null;
            let callbackHandled = false;

            function showStep(step) {
                if (step === 'payment') {
                    plansStep.classList.add('is-leaving');
                    setTimeout(function() {
                        plansStep.classList.add('is-hidden');
                        plansStep.classList.remove('is-leaving');
                        paymentStep.classList.remove('is-hidden');
                        paymentStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }, 200);
                } else {
                    paymentStep.classList.add('is-hidden');
                    plansStep.classList.remove('is-hidden');
                    plansStep.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

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
                planSummary.classList.add('d-none');
                document.querySelectorAll('.subscription-plan-card.is-selected').forEach(function(el) {
                    el.classList.remove('is-selected');
                });
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
                    body: JSON.stringify({ plan: planKey }),
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Payment session could not be created.');
                }
                encryptionKey = data.encryption_key;
                planSummaryLabel.textContent = data.label;
                planSummaryAmount.textContent = (data.symbol || '') + ' ' + Number(data.amount).toLocaleString();
                planSummary.classList.remove('d-none');
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

            async function startCheckout(planKey, cardEl) {
                resetPaymentUi();
                if (cardEl) {
                    cardEl.classList.add('is-selected');
                }
                showStep('payment');
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
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const card = this.closest('.subscription-plan-card');
                    startCheckout(this.dataset.plan, card);
                });
            });

            document.querySelectorAll('.subscription-plan-card').forEach(function(card) {
                card.addEventListener('click', function(e) {
                    if (e.target.closest('.btn-select-plan')) {
                        return;
                    }
                    const btn = this.querySelector('.btn-select-plan');
                    if (btn) {
                        startCheckout(btn.dataset.plan, this);
                    }
                });
                card.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const btn = this.querySelector('.btn-select-plan');
                        if (btn) {
                            startCheckout(btn.dataset.plan, this);
                        }
                    }
                });
            });

            document.getElementById('btn-back-to-plans').addEventListener('click', function() {
                resetPaymentUi();
                showLoading(false);
                showStep('plans');
            });

            @if ($isExpired || session('center_expired'))
                document.querySelector('.subscription-plan-card')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            @endif
        })();
    </script>
@endpush
