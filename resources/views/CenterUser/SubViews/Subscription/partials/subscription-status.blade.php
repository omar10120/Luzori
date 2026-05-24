@php
    $showExpired = $isExpired || session('center_expired');
@endphp

<div class="card mb-4 border-2 {{ $showExpired ? 'border-warning' : 'border-success' }}">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3">
            <div class="avatar flex-shrink-0">
                <span class="avatar-initial rounded-circle bg-label-{{ $showExpired ? 'warning' : 'success' }}">
                    <i class="ti ti-{{ $showExpired ? 'alert-triangle' : 'calendar-event' }} ti-md"></i>
                </span>
            </div>
            <div class="flex-grow-1">
                @if ($showExpired)
                    <h5 class="mb-1 text-warning">{{ __('api.center_expired') }}</h5>
                    @if ($expireDateFormatted)
                        <p class="mb-1">
                            <strong>{{ __('general.subscription_expired_on') }}:</strong>
                            <span class="text-body">{{ $expireDateFormatted }}</span>
                        </p>
                    @endif
                    <p class="mb-0 text-muted small">{{ __('general.renew_subscription') }}</p>
                @else
                    <h5 class="mb-1 text-success">{{ __('field.payment') }}</h5>
                    @if ($expireDateFormatted)
                        <p class="mb-0">
                            <strong>{{ __('general.subscription_active_until') }}:</strong>
                            <span class="text-body">{{ $expireDateFormatted }}</span>
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
