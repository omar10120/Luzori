<style>
    .sms-dialog {
        background: rgba(11, 15, 18, 0.6);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        padding: 1rem;
    }

    .sms-dialog.is-visible {
        display: block;
    }

    .sms-dialog .modal-dialog {
        max-width: 620px;
    }

    .sms-dialog .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 1.25rem;
        background: #ffffff;
        box-shadow:
            0 2px 4px rgba(11, 15, 18, 0.05),
            0 24px 60px rgba(11, 15, 18, 0.35);
    }

    /* ---------- Header (dark) ---------- */
    .sms-dialog .modal-header {
        position: relative;
        padding: 1.5rem 1.75rem 1.75rem;
        color: #ffffff;
        background: linear-gradient(135deg, #0f1418 0%, #1a2127 50%, #232b32 100%);
        border: 0;
        border-bottom: 2px solid #0d9488; /* subtle accent tying it to dialog */
        overflow: hidden;
        align-items: flex-start;
    }

    /* faint glow in the corner for depth */
    .sms-dialog .modal-header::after {
        content: "";
        position: absolute;
        top: -80px;
        right: -60px;
        width: 220px;
        height: 220px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(13, 148, 136, 0.22), transparent 70%);
        pointer-events: none;
    }

    /* subtle noise-free diagonal sheen */
    .sms-dialog .modal-header::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, rgba(255, 255, 255, 0.04) 0%, transparent 40%);
        pointer-events: none;
    }

    .sms-dialog .modal-header .small {
        position: relative;
        z-index: 1;
        font-size: .72rem !important;
        font-weight: 600;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.55) !important;
    }

    .sms-dialog .modal-title {
        position: relative;
        z-index: 1;
        font-size: 1.35rem;
        font-weight: 700;
        letter-spacing: -0.01em;
        color: #ffffff;
    }

    .sms-dialog .modal-header .btn-close {
        position: absolute;
        top: 1.1rem;
        right: 1.1rem;
        z-index: 2;
        width: 2rem;
        height: 2rem;
        padding: 0;
        margin: 0;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.08);
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e");
        background-size: 0.85rem;
        background-position: center;
        background-repeat: no-repeat;
        border: 1px solid rgba(255, 255, 255, 0.12);
        filter: none;
        opacity: 1;
        transition: background-color .18s ease, transform .18s ease, border-color .18s ease;
    }

    .sms-dialog .modal-header .btn-close:hover,
    .sms-dialog .modal-header .btn-close:focus {
        background-color: rgba(13, 148, 136, 0.35);
        border-color: rgba(13, 148, 136, 0.6);
        transform: rotate(90deg);
        opacity: 1;
        box-shadow: none;
    }

    /* ---------- Body ---------- */
    .sms-dialog .modal-body {
        padding: 1.5rem 1.75rem 1.75rem;
    }

    .sms-dialog .modal-body > p.text-muted {
        font-size: .92rem;
        line-height: 1.55;
        color: #5b6b66 !important;
        margin-bottom: 1.25rem;
    }

    /* ---------- Package grid ---------- */
    .sms-package-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .85rem;
        margin: 0 0 1.35rem;
    }

    .sms-package-card {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        min-height: 148px;
        padding: 1.15rem 1.15rem 1.05rem;
        text-align: left;
        color: #0b1614;
        background: #f8faf9;
        border: 1.5px solid #e2e8e5;
        border-radius: .9rem;
        cursor: pointer;
        overflow: hidden;
        transition:
            border-color .2s ease,
            box-shadow .2s ease,
            transform .2s ease,
            background-color .2s ease;
    }

    .sms-package-card::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(13, 148, 136, 0.06), transparent 60%);
        opacity: 0;
        transition: opacity .2s ease;
        pointer-events: none;
    }

    .sms-package-card:hover {
        border-color: #99d6cd;
        background: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(15, 118, 110, 0.12);
    }

    .sms-package-card:hover::before { opacity: 1; }

    .sms-package-card.is-selected {
        border-color: #0d9488;
        background: #f0fbf8;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        transform: translateY(-3px);
    }

    .sms-package-card.is-selected::before { opacity: 1; }

    .sms-package-card .package-check {
        position: absolute;
        top: .75rem;
        right: .75rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 50%;
        background: #0d9488;
        color: #ffffff !important;
        font-size: .85rem;
        opacity: 0;
        transform: scale(.5);
        transition: opacity .18s ease, transform .18s ease;
    }

    .sms-package-card.is-selected .package-check {
        display: inline-flex;
        opacity: 1;
        transform: scale(1);
    }

    .sms-package-requests {
        display: block;
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.02em;
        color: #0b1614;
    }

    .sms-package-card .small {
        display: block;
        margin-top: .15rem;
        font-size: .78rem !important;
        font-weight: 500;
        color: #6c7b76 !important;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .sms-package-price {
        display: inline-block;
        margin-top: auto;
        padding-top: .85rem;
        font-size: 1rem;
        font-weight: 700;
        color: #0f766e;
    }

    /* ---------- CTA button ---------- */
    .sms-dialog .btn-primary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: .85rem 1.25rem;
        font-size: .95rem;
        font-weight: 600;
        color: #ffffff;
        background: linear-gradient(135deg, #1a2127, #232b32);
        border: 1px solid #0d9488;
        border-radius: .75rem;
        box-shadow: 0 6px 16px rgba(11, 15, 18, 0.25);
        transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease, filter .18s ease, background .18s ease;
    }

    .sms-dialog .btn-primary:hover:not(:disabled),
    .sms-dialog .btn-primary:focus:not(:disabled) {
        background: linear-gradient(135deg, #0d9488, #0f766e);
        border-color: #0d9488;
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(13, 148, 136, 0.35);
        color: #ffffff;
    }

    .sms-dialog .btn-primary:disabled {
        background: #cbd5d1;
        border-color: #cbd5d1;
        box-shadow: none;
        opacity: .75;
        cursor: not-allowed;
    }

    /* ---------- Payment summary (bg-light box) ---------- */
    .sms-dialog .modal-body .bg-light {
        background: linear-gradient(135deg, #f4f6f7, #eceff1) !important;
        border: 1px solid #dfe4e6;
        border-radius: .85rem;
        padding: 1rem 1.15rem !important;
        margin-bottom: 1.25rem !important;
    }

    .sms-dialog #selectedSmsPackageLabel {
        color: #0b1614;
        font-size: .95rem;
    }

    .sms-dialog #selectedSmsPackageAmount {
        color: #0f766e;
        font-size: 1.25rem;
        font-weight: 800 !important;
        letter-spacing: -0.02em;
    }

    /* ---------- Misc ---------- */
    .sms-dialog #sms-payment-error {
        border-radius: .75rem;
        font-size: .88rem;
    }

    .sms-dialog .spinner-border.text-primary {
        color: #0d9488 !important;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 480px) {
        .sms-package-grid {
            grid-template-columns: 1fr;
        }

        .sms-dialog .modal-header {
            padding: 1.35rem 1.35rem 1.6rem;
        }

        .sms-dialog .modal-body {
            padding: 1.25rem 1.35rem 1.5rem;
        }
    }
</style>

<div class="modal fade sms-dialog" id="smsPackageModal" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="small text-white-50 mb-1">{{ __('general.sms_package') ?? 'SMS package' }}</div>
                    <h5 class="modal-title mb-0">{{ __('general.choose_sms_package') ?? 'Choose your SMS package' }}</h5>
                </div>
                <button type="button" class="btn-close" id="closeSmsPackageModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">{{ __('general.sms_package_required') ?? 'Purchase a package to enable SMS notifications.' }}</p>
                <div class="sms-package-grid" id="sms-package-list">
                    @foreach ($smsPackages as $packageKey => $package)
                        <button type="button" class="sms-package-card sms-package-option" data-package="{{ $packageKey }}">
                            <i class="ti ti-check package-check"></i>
                            <span class="sms-package-requests">{{ number_format($package['requests']) }}</span>
                            <span class="text-muted small">{{ __('general.requests') ?? 'SMS requests' }}</span>
                            <span class="sms-package-price">{{ $package['symbol'] }} {{ number_format($package['amount']) }}</span>
                        </button>
                    @endforeach
                </div>
                <button type="button" class="btn btn-primary w-100" id="continueSmsPackage" disabled>
                    {{ __('field.continue_to_payment') ?? 'Continue to payment' }}
                    <i class="ti ti-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade sms-dialog" id="smsPaymentModal" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="small text-white-50 mb-1">{{ __('general.sms_package') ?? 'SMS package' }}</div>
                    <h5 class="modal-title mb-0">{{ __('field.complete_payment') ?? 'Complete payment' }}</h5>
                </div>
                <button type="button" class="btn-close" id="closeSmsPaymentModal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center p-3 mb-3 rounded bg-light">
                    <span id="selectedSmsPackageLabel" class="fw-semibold"></span>
                    <span id="selectedSmsPackageAmount" class="fw-bold"></span>
                </div>
                <div id="sms-payment-loading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading</span></div>
                    <p class="text-muted small mt-2 mb-0">{{ __('field.continue_to_payment') }}</p>
                </div>
                <div id="sms-payment-error" class="alert alert-danger d-none"></div>
                <div id="sms-embedded-sessions"></div>
            </div>
        </div>
    </div>
</div>
