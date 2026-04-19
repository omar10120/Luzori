<!-- Bookings/Services Pane -->
<div class="tab-pane fade show active" id="cart-booking-pane" role="tabpanel">
    @php
        $serviceItems = array_filter($state['items'] ?? [], function($item) {
            return ($item['type'] ?? '') === 'service';
        });
    @endphp

    @forelse($serviceItems as $index => $item)
        <div class="cart-item border-bottom py-2" data-index="{{ $index }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-0">{{ $item['name'] }}</h6>
                    <small class="text-muted d-block">
                        <i class="ti ti-user me-1"></i>{{ $item['worker_name'] ?? '' }}
                    </small>
                    <small class="text-muted d-block">
                        <i class="ti ti-calendar me-1"></i>{{ $item['date'] }} {{ $item['from_time'] }}
                    </small>
                    @if(!empty($item['discount_note']))
                        <small class="text-success d-block">{{ $item['discount_note'] }}</small>
                    @endif
                </div>
                <div class="text-end">
                    @if(isset($item['original_price']) && $item['original_price'] > $item['price'])
                        <del class="text-muted small d-block">{{ number_format($item['original_price'], 2) }}</del>
                    @endif
                    <span class="fw-bold">{{ number_format($item['price'], 2) }} {{ $state['currency'] }}</span>
                    <button type="button" class="btn btn-sm btn-icon text-danger remove-item" data-index="{{ $index }}">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-3">
            {{ __('field.no_services_in_cart') }}
        </div>
    @endforelse
</div>

<!-- Products Pane -->
<div class="tab-pane fade" id="cart-products-pane" role="tabpanel">
    @php
        $productItems = array_filter($state['items'] ?? [], function($item) {
            return ($item['type'] ?? '') === 'product';
        });
    @endphp

    @forelse($productItems as $index => $item)
        <div class="cart-item border-bottom py-2" data-index="{{ $index }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-0">{{ $item['name'] }}</h6>
                    <small class="text-muted d-block">
                        Qty: {{ $item['quantity'] }} x {{ number_format($item['price'] / $item['quantity'], 2) }}
                    </small>
                </div>
                <div class="text-end">
                    <span class="fw-bold">{{ number_format($item['price'], 2) }} {{ $state['currency'] }}</span>
                    <button type="button" class="btn btn-sm btn-icon text-danger remove-item" data-index="{{ $index }}">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-3">
            {{ __('field.no_products_in_cart') }}
        </div>
    @endforelse
</div>

<!-- Coupons Pane -->
<div class="tab-pane fade" id="cart-user-wallet-pane" role="tabpanel">
    @php
        $walletItems = array_filter($state['items'] ?? [], function($item) {
            return in_array(($item['type'] ?? ''), ['wallet', 'user_wallet']);
        });
    @endphp

    @forelse($walletItems as $index => $item)
        <div class="cart-item border-bottom py-2" data-index="{{ $index }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-0">{{ $item['code'] ?? 'Coupon' }}</h6>
                    <small class="text-muted d-block">
                        {{ ($item['type'] === 'user_wallet' ? __('field.assignment') : __('field.purchase')) }}
                    </small>
                </div>
                <div class="text-end">
                    <span class="fw-bold">{{ number_format($item['price'], 2) }} {{ $state['currency'] }}</span>
                    <button type="button" class="btn btn-sm btn-icon text-danger remove-item" data-index="{{ $index }}">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center text-muted py-3">
            {{ __('field.no_coupons_in_cart') }}
        </div>
    @endforelse
</div>

<div id="recalculation-totals" style="display:none;" 
     data-subtotal="{{ number_format($state['subtotal'], 2) }} {{ $state['currency'] }}"
     data-tax="{{ number_format($state['tax'], 2) }} {{ $state['currency'] }}"
     data-total="{{ number_format($state['total'], 2) }} {{ $state['currency'] }}"
     data-total-raw="{{ $state['total'] }}"
     data-booking-count="{{ count($serviceItems) }}"
     data-product-count="{{ count($productItems) }}"
     data-wallet-count="{{ count($walletItems) }}">
</div>
