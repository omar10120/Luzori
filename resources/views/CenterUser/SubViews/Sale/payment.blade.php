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
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <!-- Selected Customer Display -->
                        @if($selectedCustomer)
                            <div class="alert alert-info mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md me-3">
                                        <img src="{{ $selectedCustomer->image ?? asset('assets/img/avatars/1.png') }}" 
                                             alt="{{ $selectedCustomer->name }}" 
                                             class="rounded-circle" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                    </div>
                                    <div>
                                        <strong>{{ __('field.customer') }}:</strong> {{ $selectedCustomer->name }}<br>
                                        <small class="text-muted">{{ $selectedCustomer->email ?? $selectedCustomer->full_phone }}</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                <i class="ti ti-user-off me-1"></i>
                                {{ __('field.no_customer_selected') }} - {{ __('field.optional_for_walk_ins') }}
                            </div>
                        @endif

                        <form id="paymentForm">
                            @csrf
                            
                            <!-- Worker Selection (for tip) -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="worker_id" class="form-label">
                                        {{ __('field.worker') }}
                                        <small class="text-muted">({{ __('field.tip_will_be_for_this_worker') }})</small>
                                    </label>
                                    <select class="select2 form-control" id="worker_id" name="worker_id" required>
                                        <option value="">{{ __('field.Choose Worker') }}</option>
                                        @foreach ($workers as $worker)
                                            <option value="{{ $worker->id }}" 
                                                {{ $cart['worker_id'] == $worker->id ? 'selected' : '' }}>
                                                {{ $worker->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Tip -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="tip" class="form-label">
                                        {{ __('field.tip') }} ({{ __('field.between_0_200') }})
                                    </label>
                                    <input type="number" class="form-control" id="tip" name="tip" 
                                        value="{{ $cart['tip'] ?? 0 }}" min="0" max="200" step="0.01">
                                </div>
                            </div>

                            <!-- Tax -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="tax" class="form-label">{{ __('field.tax') }}</label>
                                    <input type="number" class="form-control" id="tax" name="tax" 
                                        value="{{ $cart['tax'] ?? 0 }}" min="0" step="0.01">
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5>{{ __('field.order_summary') }}</h5>
                    </div>
                    <div class="card-body">
                        <div id="cart-summary">
                            @foreach($cart['items'] as $item)
                                @php
                                    $itemName = $item['name'] ?? __('field.item');
                                    if($item['type'] === 'wallet') {
                                        $itemName = __('field.coupon') . ' / ' . __('locale.wallets');
                                    }
                                    
                                    if($item['type'] === 'wallet') {
                                        $displayPrice = $item['amount'] ?? 0;
                                    } else {
                                        $price = $item['price'] ?? 0;
                                        $quantity = $item['quantity'] ?? 1;
                                        $displayPrice = $price * $quantity;
                                    }
                                @endphp
                                <div class="mb-2 pb-2 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $itemName }}</span>
                                        <strong>{{ number_format($displayPrice, 2) }} {{ get_currency() }}</strong>
                                    </div>
                                    @if($item['type'] === 'service')
                                        <small class="text-muted">
                                            {{ $item['date'] }} • {{ $item['from_time'] }} - {{ $item['to_time'] }}
                                        </small>
                                    @elseif($item['type'] === 'product')
                                        <small class="text-muted">
                                            {{ __('field.quantity') }}: {{ $item['quantity'] }}
                                        </small>
                                    @elseif($item['type'] === 'wallet')
                                        <small class="text-muted">
                                            {{ __('field.amount') }}: {{ $item['amount'] ?? 0 }} {{ get_currency() }}
                                            @if(isset($item['start_at']))
                                                <br>{{ __('field.start_at') }}: {{ $item['start_at'] }}
                                            @endif
                                            @if(isset($item['end_at']))
                                                <br>{{ __('field.end_at') }}: {{ $item['end_at'] }}
                                            @endif
                                        </small>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        @php
                            $subtotal = 0;
                            foreach($cart['items'] as $item) {
                                if($item['type'] === 'wallet') {
                                    // Include wallet/coupon amount in subtotal
                                    $subtotal += $item['amount'] ?? 0;
                                } else {
                                    $price = $item['price'] ?? 0;
                                    $quantity = $item['quantity'] ?? 1;
                                    $subtotal += $price * $quantity;
                                }
                            }
                            $tax = $cart['tax'] ?? 0;
                            $tip = $cart['tip'] ?? 0;
                            $total = $subtotal + $tax + $tip;
                        @endphp
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('field.subtotal') }}:</span>
                            <strong id="summary-subtotal">{{ number_format($subtotal, 2) }} {{ get_currency() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('field.tax') }}:</span>
                            <strong id="summary-tax">{{ number_format($tax, 2) }} {{ get_currency() }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('field.tip') }}:</span>
                            <strong id="summary-tip">{{ number_format($tip, 2) }} {{ get_currency() }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="fs-5">{{ __('field.total') }}:</span>
                            <strong id="summary-total" class="fs-5 text-primary">
                                {{ number_format($total, 2) }} {{ get_currency() }}
                            </strong>
                        </div>

                        <button type="button" class="btn btn-success w-100" id="processPaymentBtn">
                            <i class="ti ti-check me-1"></i>
                            {{ __('field.complete_payment') }}
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
            // Calculate totals
            function calculateTotals() {
                const subtotal = {{ $subtotal }};
                const tax = parseFloat($('#tax').val()) || 0;
                const tip = parseFloat($('#tip').val()) || 0;
                const total = subtotal + tax + tip;

                $('#summary-tax').text(tax.toFixed(2) + ' {{ get_currency() }}');
                $('#summary-tip').text(tip.toFixed(2) + ' {{ get_currency() }}');
                $('#summary-total').text(total.toFixed(2) + ' {{ get_currency() }}');
            }

            // Update totals on change
            $('#tax, #tip').on('input', calculateTotals);

            // Process Payment
            $('#processPaymentBtn').on('click', function() {
                const form = $('#paymentForm')[0];
                
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="ti ti-loader-2 me-1"></i>{{ __('admin.sending') }}');

                $.ajax({
                    url: '{{ route("center_user.sales.process-payment") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        worker_id: $('#worker_id').val(),
                        tip: $('#tip').val() || 0,
                        tax: $('#tax').val() || 0
                    },
                    success: function(response) {
                        if (response.message === 'redirect_to_home') {
                            window.location.href = response.data;
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || '{{ __('admin.an_error_occurred') }}');
                            }
                            $btn.prop('disabled', false).html(originalHtml);
                        }
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

