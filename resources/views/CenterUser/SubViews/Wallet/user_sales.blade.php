@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
    <style>
        .lz-wallet-sales-page .lz-filter-card {
            border: 0;
            border-radius: 16px;
            background: #fdfaf6;
            box-shadow: 0 8px 28px rgba(0, 43, 38, 0.06);
            margin-bottom: 1.25rem;
        }
        .lz-wallet-sales-page .lz-filter-card .lz-field-label {
            color: #7a8582;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        .lz-wallet-sales-table .lz-sale-id {
            font-weight: 700;
            color: #002b26;
        }
        .lz-wallet-sales-table .lz-amount {
            font-weight: 700;
            color: #002b26;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-wallet-sales-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-receipt"></i>
                </div>
                <div>
                    <h1 class="lz-page-title mb-0">{{ __('general.sales_used_this_coupon') }}</h1>
                    <p class="mb-0 text-muted small">{{ $user?->name ?? __('field.all_users') }} · {{ $wallet->code }}</p>
                </div>
            </div>
            <div class="lz-page-actions">
                <a href="{{ route('center_user.wallets.index') }}" class="btn btn-luzori-beige btn-sm">
                    {{ __('general.back') }}
                </a>
            </div>
        </div>

        <div class="card lz-filter-card">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('center_user.users_wallets.sales') }}" class="row g-2 align-items-end">
                    <input type="hidden" name="wallet_id" value="{{ $wallet->id }}">
                    <div class="col-md-6">
                        <label for="user_id" class="lz-field-label">{{ __('field.filter_by_user') }}</label>
                        <select name="user_id" id="user_id" class="form-select select2" onchange="this.form.submit()">
                            <option value="">{{ __('field.all_users') }}</option>
                            @foreach ($walletUsers as $walletUser)
                                <option value="{{ $walletUser->id }}" @selected((int) ($user?->id) === (int) $walletUser->id)>
                                    {{ $walletUser->name }}
                                    @if ($walletUser->phone)
                                        — {{ $walletUser->country_code }}{{ $walletUser->phone }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if ($user)
                        <div class="col-md-3">
                            <a href="{{ route('center_user.users_wallets.sales', ['wallet_id' => $wallet->id]) }}"
                               class="btn btn-luzori-muted btn-sm px-3 py-2">
                                <i class="ti ti-x"></i> {{ __('field.clear_filter') }}
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        @if ($user)
            <div class="alert alert-success mb-3">
                <i class="ti ti-ticket me-1"></i>
                {{ __('general.sales_used_this_coupon') }}:
                <strong>{{ $user->name }}</strong>
            </div>
        @endif

        <div class="card lz-table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table lz-wallet-sales-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('field.sale_id') }}</th>
                                @unless ($user)
                                    <th>{{ __('field.user') }}</th>
                                @endunless
                                <th>{{ __('field.date') }}</th>
                                <th>{{ __('field.total') }}</th>
                                <th>{{ __('general.wallet_used') }}</th>
                                <th class="text-end">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sales as $sale)
                                <tr>
                                    <td><span class="lz-sale-id">#{{ $sale->id }}</span></td>
                                    @unless ($user)
                                        <td>{{ $sale->client->name ?? '—' }}</td>
                                    @endunless
                                    <td>
                                        {{ is_string($sale->created_at) ? substr($sale->created_at, 0, 16) : ($sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '—') }}
                                    </td>
                                    <td><span class="lz-amount">{{ number_format((float) $sale->total, 2) }} {{ get_currency() }}</span></td>
                                    <td><span class="lz-amount">{{ number_format((float) $sale->wallet_used_amount, 2) }} {{ get_currency() }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                            <a href="{{ route('center_user.sales.show', $sale->id) }}"
                                                class="btn btn-luzori-beige btn-sm">{{ __('general.show') }}</a>
                                            <a href="{{ route('center_user.sales.print', $sale->id) }}"
                                                target="_blank" class="btn btn-luzori-primary btn-sm">{{ __('general.print') }}</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $user ? 5 : 6 }}" class="text-muted text-center py-4">
                                        {{ __('general.no_sales_used_this_coupon') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
@endsection
