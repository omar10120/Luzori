@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-wallet-users-table .lz-user-name {
            font-weight: 700;
            color: #002b26;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-wallet-users-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-users"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }} <span class="text-muted fs-5">({{ $wallet->code }})</span></h1>
            </div>
            <div class="lz-page-actions">
                <a href="{{ route('center_user.users_wallets.sales', ['wallet_id' => $wallet->id]) }}"
                    class="btn btn-luzori-primary btn-sm">
                    <i class="ti ti-receipt me-1"></i>{{ __('general.all_users_sales_history') }}
                </a>
            </div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table lz-wallet-users-table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('field.users') }}</th>
                                <th class="text-end">{{ __('general.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($wallet->users as $user)
                                <tr>
                                    <td><span class="lz-user-name">{{ $user->user->name }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                                            <a href="{{ route('center_user.users_wallets.print', ['user_id' => $user->user->id, 'wallet_id' => $wallet->id]) }}"
                                                target="_blank" class="btn btn-luzori-primary btn-sm">
                                                <i class="ti ti-printer me-1"></i>{{ __('general.print') }}
                                            </a>
                                            <a href="{{ route('center_user.users_wallets.sales', ['user_id' => $user->user->id, 'wallet_id' => $wallet->id]) }}"
                                                class="btn btn-luzori-beige btn-sm">
                                                <i class="ti ti-history me-1"></i>{{ __('general.sales_history') }}
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-muted text-center py-4">{{ __('general.no_users') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
