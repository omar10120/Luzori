<!-- Wallet Tab - Show Coupons Table, Add User Wallet, and Add New Coupon -->
<div class="tab-pane fade" id="wallet" role="tabpanel">
    <!-- Available Coupons Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('locale.wallets') }} ({{ __('field.available_coupons') }})</h5>
            <button type="button" class="btn btn-primary btn-sm" id="addCouponBtn" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                <i class="ti ti-plus me-1"></i>
                {{ __('general.add') }} {{ __('field.new_coupon') }}
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover " id="wallets-table">
                    <thead>
                        <tr >
                            <th style="font-size: 10px;">{{ __('field.code') }}</th>
                            <th style="font-size: 10px;">{{ __('field.client') }}</th>
                            <th style="font-size: 10px;">{{ __('field.amount') }}</th>
                            <th style="font-size: 10px;">{{ __('field.invoiced_amount') }}</th>
                            <th style="font-size: 10px;">{{ __('field.start_at') }}</th>
                            <th style="font-size: 10px;">{{ __('field.end_at') }}</th>
                            <th style="font-size: 10px;">{{ __('field.created_by') }}</th>
                            <th style="font-size: 10px;">{{ __('field.status') }}</th>
                            <th style="font-size: 10px;">{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wallets as $wallet)
                            <tr style="font-size: 10px;">
                                <td>{{ $wallet->code }}</td>
                                <td>
                                    @if($wallet->users && $wallet->users->count() > 0)
                                        @foreach($wallet->users as $userWallet)
                                            <span class="badge bg-label-info mb-1">{{ $userWallet->user->name ?? '-' }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ number_format($wallet->amount, 2) }} {{ get_currency() }}</td>
                                <td>{{ number_format($wallet->invoiced_amount, 2) }} {{ get_currency() }}</td>
                                <td>{{ $wallet->start_at ? \Carbon\Carbon::parse($wallet->start_at)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $wallet->end_at ? \Carbon\Carbon::parse($wallet->end_at)->format('Y-m-d') : '-' }}</td>
                                <td>{{ $wallet->created_by_user->name ?? '-' }}</td>
                                <td>
                                    @if($wallet->used)
                                        <span class="badge bg-label-danger">{{ __('field.used') }}</span>
                                    @else
                                        <span class="badge bg-label-success">{{ __('field.active') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary add-wallet-user-btn" style="font-size: 10px;" data-wallet-id="{{ $wallet->id }}" data-wallet-code="{{ $wallet->code }}" data-wallet-amount="{{ $wallet->amount }}" data-wallet-invoiced="{{ $wallet->invoiced_amount }}" data-wallet-start="{{ $wallet->start_at }}" data-wallet-end="{{ $wallet->end_at }}" data-bs-toggle="modal" data-bs-target="#addWalletUserModal">
                                        <i class="ti ti-user-plus me-1"></i>
                                        {{ __('field.add_user') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">{{ __('field.no_coupons_available') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
