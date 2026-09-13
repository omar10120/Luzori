@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h2 class="mb-1">{{ __('general.sales_used_this_coupon') }}</h2>
                            <p class="mb-0 text-muted">{{ $user?->name ?? __('field.all_users') }} · {{ $wallet->code }}</p>
                        </div>
                        <a href="{{ route('center_user.wallets.index') }}" class="btn btn-label-secondary">
                            {{ __('general.back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('center_user.users_wallets.sales') }}" class="row g-3 mb-4 align-items-end">
                            <input type="hidden" name="wallet_id" value="{{ $wallet->id }}">
                            <div class="col-md-6">
                                <label for="user_id" class="form-label">{{ __('field.filter_by_user') }}</label>
                                <select name="user_id" id="user_id" class="form-control select2" onchange="this.form.submit()">
                                    <option value="">{{ __('field.all_users') }}</option>
                                    @foreach ($walletUsers as $walletUser)
                                        <option value="{{ $walletUser->id }}" @selected((int) ($user?->id) === (int) $walletUser->id)>
                                            {{ $walletUser->name }}
                                            @if($walletUser->phone)
                                                — {{ $walletUser->country_code }}{{ $walletUser->phone }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if($user)
                                <div class="col-md-3">
                                    <a href="{{ route('center_user.users_wallets.sales', ['wallet_id' => $wallet->id]) }}"
                                       class="btn btn-outline-secondary">
                                        {{ __('field.clear_filter') }}
                                    </a>
                                </div>
                            @endif
                        </form>

                        @if($user)
                            <div class="alert alert-info mb-3">
                                <i class="ti ti-ticket me-1"></i>
                                {{ __('general.sales_used_this_coupon') }}:
                                <strong>{{ $user->name }}</strong>
                            </div>
                        @endif

                        <div class="table-responsive text-center">
                            <table class="table table-striped table-head-custom table-checkable">
                                <thead>
                                    <tr>
                                        <th>{{ __('field.sale_id') }}</th>
                                        @unless ($user)
                                            <th>{{ __('field.user') }}</th>
                                        @endunless
                                        <th>{{ __('field.date') }}</th>
                                        <th>{{ __('field.total') }}</th>
                                        <th>{{ __('general.wallet_used') }}</th>
                                        <th>{{ __('general.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($sales as $sale)
                                        <tr>
                                            <td>#{{ $sale->id }}</td>
                                            @unless ($user)
                                                <td>{{ $sale->client->name ?? '-' }}</td>
                                            @endunless
                                            <td>
                                                {{ is_string($sale->created_at) ? substr($sale->created_at, 0, 16) : ($sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '-') }}
                                            </td>
                                            <td>{{ number_format((float) $sale->total, 2) }} {{ get_currency() }}</td>
                                            <td>{{ number_format((float) $sale->wallet_used_amount, 2) }} {{ get_currency() }}</td>
                                            <td class="d-flex justify-content-center gap-2 flex-wrap">
                                                <a href="{{ route('center_user.sales.show', $sale->id) }}"
                                                    class="btn btn-sm btn-outline-primary">{{ __('general.show') }}</a>
                                                <a href="{{ route('center_user.sales.print', $sale->id) }}"
                                                    target="_blank" class="btn btn-sm btn-primary">{{ __('general.print') }}</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $user ? 5 : 6 }}">{{ __('general.no_sales_used_this_coupon') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
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
@endsection
