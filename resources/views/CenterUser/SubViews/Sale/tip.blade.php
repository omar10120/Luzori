@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">{{ $title }}</h2>
                        <a href="{{ route('center_user.sales.index') }}" class="btn btn-outline-secondary btn-sm">
                            {{ __('general.back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        @if($sale->client)
                            <div class="alert alert-info mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md me-3">
                                        <img src="{{ $sale->client->image ?? asset('assets/img/avatars/1.png') }}"
                                             alt="{{ $sale->client->name }}"
                                             class="rounded-circle"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    </div>
                                    <div>
                                        <strong>{{ __('field.customer') }}:</strong> {{ $sale->client->name }}<br>
                                        <small class="text-muted">{{ $sale->client->email ?? $sale->client->full_phone }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($bookingEmployees))
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">{{ __('field.employee') }} ({{ __('locale.bookings') }})</label>
                                    <div class="alert alert-info mb-0">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($bookingEmployees as $employee)
                                                <span class="badge bg-label-primary">{{ $employee }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form id="tipForm">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="worker_id" class="form-label">
                                        {{ __('field.worker') }}
                                        <small class="text-muted">({{ __('field.optional') }} - {{ __('field.tip_will_be_for_this_worker') }})</small>
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
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="tip" class="form-label">
                                        {{ __('field.tip') }} ({{ __('field.between_0_800') }})
                                    </label>
                                    <input type="number" class="form-control" id="tip" name="tip"
                                           value="0" min="0.01" max="800" step="0.01" required>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('field.order_summary') }}</h5>
                    </div>
                    <div class="card-body">
                        @foreach($sale->bookings as $booking)
                            @foreach($booking->details as $detail)
                                <div class="mb-2 pb-2 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">
                                            {{ $detail->service?->translation?->name ?? $detail->service?->name ?? __('locale.bookings') }}
                                        </span>
                                        <span>{{ number_format((float) $detail->price, 2) }} {{ get_currency() }}</span>
                                    </div>
                                    <small class="text-muted">
                                        {{ $detail->_date ?? $booking->booking_date }}
                                        {{ $detail->from_time }} - {{ $detail->to_time }}
                                        @if($detail->worker)
                                            · {{ $detail->worker->name }}
                                        @endif
                                    </small>
                                </div>
                            @endforeach
                        @endforeach

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('field.subtotal') }}:</span>
                            <strong id="summary-subtotal">{{ number_format((float) $sale->subtotal, 2) }} {{ get_currency() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('field.tax') }}:</span>
                            <strong id="summary-tax">{{ number_format((float) $sale->tax, 2) }} {{ get_currency() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('field.tip') }}:</span>
                            <strong id="summary-tip">0.00 {{ get_currency() }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fs-5">{{ __('field.total') }}:</span>
                            <strong id="summary-total" class="fs-5 text-primary">
                                {{ number_format((float) $sale->subtotal + (float) $sale->tax, 2) }} {{ get_currency() }}
                            </strong>
                        </div>

                        <button type="button" class="btn btn-success w-100" id="saveTipBtn">
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
            const currency = @json(trim(get_currency()));

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
                            toastr.error(xhr.responseJSON?.message || '{{ __('admin.an_error_occurred') }}');
                        }
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
@endsection
