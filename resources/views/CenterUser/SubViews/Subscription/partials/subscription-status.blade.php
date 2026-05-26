@php
    $showExpired = $isExpired || session('center_expired');
@endphp

<div class="subscription-status-card {{ $showExpired ? 'subscription-status-card--expired' : 'subscription-status-card--active' }}">
    <div class="d-flex align-items-start gap-3">
        <span class="subscription-status-icon">
            <i class="ti ti-{{ $showExpired ? 'alert-triangle' : 'calendar-event' }}"></i>
        </span>
        <div class="flex-grow-1">
            @if ($showExpired)
                <p class="subscription-status-label mb-1">{{ __('api.center_expired') }}</p>
                @if ($expireDateFormatted)
                    <p class="subscription-status-date mb-1">
                        {{ __('general.subscription_expired_on') }}
                        <strong>{{ $expireDateFormatted }}</strong>
                    </p>
                @endif
                <p class="subscription-status-hint mb-0">{{ __('general.renew_subscription') }}</p>
            @else
                <p class="subscription-status-label mb-1">{{ __('field.payment') }}</p>
                @if ($expireDateFormatted)
                    <p class="subscription-status-date mb-0">
                        {{ __('general.subscription_active_until') }}
                        <strong>{{ $expireDateFormatted }}</strong>
                    </p>
                @endif
            @endif
        </div>
    </div>
</div>
