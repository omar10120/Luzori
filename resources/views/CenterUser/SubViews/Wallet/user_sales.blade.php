@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h2 class="mb-1">{{ __('general.sales_used_this_coupon') }}</h2>
                            <p class="mb-0 text-muted">{{ $user->name }} · {{ $wallet->code }}</p>
                        </div>
                        <a href="{{ route('center_user.users_wallets.showUsers', ['id' => $wallet->id]) }}"
                            class="btn btn-label-secondary">
                            {{ __('general.back') }}
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive text-center">
                            <table class="table table-striped table-head-custom table-checkable">
                                <thead>
                                    <tr>
                                        <th>{{ __('field.sale_id') }}</th>
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
                                            <td colspan="5">{{ __('general.no_sales_used_this_coupon') }}</td>
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
