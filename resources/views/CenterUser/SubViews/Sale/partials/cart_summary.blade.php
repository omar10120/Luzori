@inject('salesService', 'App\Services\SalesService')
<!-- Right Panel: Cart Summary -->
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
                <a class="nav-link" id="cart-user-wallet-tab" data-bs-toggle="tab" href="#cart-user-wallet-pane" role="tab">
                    <i class="ti ti-wallet me-1"></i>
                    <span class="d-none d-sm-inline">{{ __('field.coupons') }}</span>
                    <span class="badge rounded-pill bg-label-primary ms-1" id="cart-user-wallet-count" style="display:none">0</span>
                </a>
            </li>
        </ul>

        <div id="cart-summary-container">
            @include('CenterUser.SubViews.Sale.partials.cart_summary_content', [
                'state' => $salesService->calculateCartState($cart, $cart['client_id'] ?? null)
            ])
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
