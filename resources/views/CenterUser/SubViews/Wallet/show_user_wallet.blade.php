@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="mb-0">{{ $title }} ({{ $wallet->code }})</h2>
                    <a href="{{ route('center_user.users_wallets.sales', ['wallet_id' => $wallet->id]) }}"
                        class="btn btn-outline-primary">{{ __('general.all_users_sales_history') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive text-center">
                        <table class="table table-striped table-head-custom table-checkable" id="dtTable">
                            <thead>
                                <tr>
                                    <th>{{ __('field.users') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($wallet->users as $user)
                                    <tr>
                                        <td>{{ $user->user->name }}</td>
                                        <td class="d-flex justify-content-center gap-2 flex-wrap">
                                            <a href="{{ route('center_user.users_wallets.print', ['user_id' => $user->user->id, 'wallet_id' => $wallet->id]) }}"
                                                target="_blank" class="btn btn-primary text-white">{{ __('general.print') }}</a>
                                            <a href="{{ route('center_user.users_wallets.sales', ['user_id' => $user->user->id, 'wallet_id' => $wallet->id]) }}"
                                                class="btn btn-outline-primary">{{ __('general.sales_history') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection