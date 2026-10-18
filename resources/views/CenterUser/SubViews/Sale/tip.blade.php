@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
    <style>
        .lz-tip-page {
            --lz-forest: #002b26;
            --lz-forest-soft: #0a3a34;
            --lz-cream: #fdfaf6;
            --lz-gold: #c5a070;
            --lz-gold-deep: #9a6923;
            --lz-muted: #7a8582;
        }
        .lz-tip-page .lz-panel {
            border: 0;
            border-radius: 18px;
            background: var(--lz-cream);
            box-shadow: 0 8px 28px rgba(0, 43, 38, 0.07);
            height: 100%;
        }
        .lz-tip-page .lz-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            flex-wrap: wrap;
            padding: 1.15rem 1.35rem 0.5rem;
        }
        .lz-tip-page .lz-panel-title {
            margin: 0;
            color: var(--lz-forest);
            font-weight: 800;
            font-size: 1.35rem;
        }
        .lz-tip-page .lz-panel-body { padding: 0.75rem 1.35rem 1.35rem; }
        .lz-tip-page .lz-customer-chip {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            background: #fff;
            border: 1px solid #efe6d8;
            border-radius: 14px;
            padding: 0.9rem 1rem;
            margin-bottom: 1.1rem;
        }
        .lz-tip-page .lz-customer-chip strong { color: var(--lz-forest); }
        .lz-tip-page .lz-employee-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
            padding: 0.75rem 0.9rem;
            background: #fff;
            border: 1px solid #efe6d8;
            border-radius: 12px;
        }
        .lz-tip-page .lz-employee-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.3rem 0.7rem;
            border-radius: 999px;
            background: #e6f3ee;
            color: #1a6b5a;
            font-size: 0.78rem;
            font-weight: 700;
        }
        .lz-tip-page .lz-field-label {
            color: var(--lz-forest);
            font-weight: 700;
            font-size: 0.88rem;
            margin-bottom: 0.4rem;
        }
        .lz-tip-page .form-control,
        .lz-tip-page .select2-container--default .select2-selection--single {
            border-radius: 12px !important;
            border-color: #e5ddd2 !important;
            min-height: 42px;
        }
        .lz-tip-page .form-control:focus {
            border-color: var(--lz-gold) !important;
            box-shadow: 0 0 0 3px rgba(197, 160, 112, 0.18) !important;
        }
        .lz-tip-page .lz-summary-item {
            padding: 0.65rem 0;
            border-bottom: 1px solid #efe6d8;
        }
        .lz-tip-page .lz-summary-item:last-of-type { border-bottom: 0; }
        .lz-tip-page .lz-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            color: var(--lz-forest);
            font-size: 0.92rem;
            margin-bottom: 0.45rem;
        }
        .lz-tip-page .lz-summary-row span:first-child { color: var(--lz-muted); font-weight: 600; }
        .lz-tip-page .lz-summary-row span:last-child { font-weight: 700; }
        .lz-tip-page .lz-summary-total {
            margin-top: 0.75rem;
            padding-top: 0.85rem;
            border-top: 1px solid rgba(197, 160, 112, 0.35);
        }
        .lz-tip-page .lz-summary-total span:first-child {
            color: var(--lz-forest);
            font-size: 1.05rem;
            font-weight: 800;
        }
        .lz-tip-page .lz-summary-total span:last-child {
            color: var(--lz-forest-soft);
            font-size: 1.2rem;
            font-weight: 800;
        }
        .lz-tip-page .lz-summary-meta {
            display: block;
            margin-top: 0.2rem;
            color: var(--lz-muted);
            font-size: 0.78rem;
        }
        .lz-tip-page .btn-luzori-primary {
            background: var(--lz-forest);
            border-color: var(--lz-forest);
            color: #fff;
            border-radius: 12px;
            font-weight: 700;
        }
        .lz-tip-page .btn-luzori-primary:hover {
            background: var(--lz-forest-soft);
            border-color: var(--lz-forest-soft);
            color: #fff;
        }
        .lz-tip-page .btn-luzori-muted {
            background: #f3ebe0;
            border-color: #e5ddd2;
            color: var(--lz-forest);
            border-radius: 10px;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    @php
        $currency = trim((string) get_currency());
    @endphp
    <div class="luzori-page lz-tip-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card lz-panel">
                    <div class="lz-panel-head">
                        <h1 class="lz-panel-title">{{ $title }}</h1>
                        <a href="{{ route('center_user.sales.index') }}" class="btn btn-sm btn-luzori-muted">
                            <i class="ti ti-arrow-left me-1"></i>{{ __('general.back') }}
                        </a>
                    </div>
                    <div class="lz-panel-body">
                        @if($sale->client)
                            <div class="lz-customer-chip">
                                <div class="avatar avatar-md">
                                    <img src="{{ $sale->client->image ?? asset('assets/img/avatars/1.png') }}"
                                         alt="{{ $sale->client->name }}"
                                         class="rounded-circle"
                                         style="width: 44px; height: 44px; object-fit: cover;">
                                </div>
                                <div>
                                    <strong>{{ __('field.customer') }}:</strong> {{ $sale->client->name }}
                                    <div class="text-muted small">{{ $sale->client->email ?? $sale->client->full_phone }}</div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($bookingEmployees))
                            <div class="mb-3">
                                <label class="lz-field-label">{{ __('field.employee') }} ({{ __('locale.bookings') }})</label>
                                <div class="lz-employee-wrap">
                                    @foreach($bookingEmployees as $employee)
                                        <span class="lz-employee-badge">{{ $employee }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <form id="tipForm">
                            @csrf

                            <div class="mb-3">
                                <label for="worker_id" class="lz-field-label">
                                    {{ __('field.worker') }}
                                    <small class="text-muted fw-normal">({{ __('field.optional') }} - {{ __('field.tip_will_be_for_this_worker') }})</small>
                                </label>
                                <select class="select2 form-control" id="worker_id" name="worker_id">
                                    <option value="">{{ __('field.Choose Worker') }}</option>
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}" @selected((int) $preferredWorkerId === (int) $worker->id)>
                                            {{ $worker->name }} - {{ $worker->phone }}
                                            {{ $worker->is_center_user ? '(' . ($centerUser->name ?? '') . ' - reception)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-1">
                                <label for="tip" class="lz-field-label">
                                    {{ __('field.tip') }} ({{ __('field.between_0_800') }})
                                </label>
                                <input type="number" class="form-control" id="tip" name="tip"
                                       value="0" min="0.01" max="800" step="0.01" required>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card lz-panel">
                    <div class="lz-panel-head">
                        <h5 class="lz-panel-title" style="font-size:1.05rem;">{{ __('field.order_summary') }}</h5>
                    </div>
                    <div class="lz-panel-body">
                        @foreach($sale->bookings as $booking)
                            @foreach($booking->details as $detail)
                                <div class="lz-summary-item">
                                    <div class="d-flex justify-content-between gap-2">
                                        <span class="fw-semibold" style="color:#002b26;">
                                            {{ $detail->service?->translation?->name ?? $detail->service?->name ?? __('locale.bookings') }}
                                        </span>
                                        <span class="fw-bold" style="color:#0a3a34;white-space:nowrap;">
                                            {{ number_format((float) $detail->price, 2) }} {{ $currency }}
                                        </span>
                                    </div>
                                    <span class="lz-summary-meta">
                                        {{ $detail->_date ?? $booking->booking_date }}
                                        {{ $detail->from_time }} - {{ $detail->to_time }}
                                        @if($detail->worker)
                                            · {{ $detail->worker->name }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        @endforeach

                        <div class="lz-summary-row mt-3">
                            <span>{{ __('field.subtotal') }}</span>
                            <span id="summary-subtotal">{{ number_format((float) $sale->subtotal, 2) }} {{ $currency }}</span>
                        </div>
                        <div class="lz-summary-row">
                            <span>{{ __('field.tax') }}</span>
                            <span id="summary-tax">{{ number_format((float) $sale->tax, 2) }} {{ $currency }}</span>
                        </div>
                        <div class="lz-summary-row">
                            <span>{{ __('field.tip') }}</span>
                            <span id="summary-tip">0.00 {{ $currency }}</span>
                        </div>
                        <div class="lz-summary-row lz-summary-total">
                            <span>{{ __('field.total') }}</span>
                            <span id="summary-total">
                                {{ number_format((float) $sale->subtotal + (float) $sale->tax, 2) }} {{ $currency }}
                            </span>
                        </div>

                        <button type="button" class="btn btn-luzori-primary w-100 mt-3" id="saveTipBtn">
                            <i class="ti ti-check me-1"></i>
                            {{ __('field.add_tip') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')

    <script>
        $(document).ready(function() {
            const subtotal = {{ number_format((float) $sale->subtotal, 2, '.', '') }};
            const tax = {{ number_format((float) $sale->tax, 2, '.', '') }};
            const currency = @json($currency);

            function calculateTotals() {
                const tip = parseFloat($('#tip').val()) || 0;
                const total = subtotal + tax + tip;
                $('#summary-tip').text(tip.toFixed(2) + ' ' + currency);
                $('#summary-total').text(total.toFixed(2) + ' ' + currency);
            }

            $('#tip').on('input', calculateTotals);
            calculateTotals();

            $('#saveTipBtn').on('click', function() {
                const tip = parseFloat($('#tip').val()) || 0;
                if (tip <= 0) {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(@json(__('field.tip_required')));
                    }
                    return;
                }

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.sales.tip.store", ["id" => $sale->id]) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        worker_id: $('#worker_id').val() || null,
                        tip: tip
                    },
                    success: function(response) {
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || '{{ __('general.success') }}');
                        }
                        window.location.href = response.data || '{{ route("center_user.sales.index") }}';
                    },
                    error: function(xhr) {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(xhr.responseJSON?.message || '{{ __('general.error') }}');
                        }
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
@endsection
