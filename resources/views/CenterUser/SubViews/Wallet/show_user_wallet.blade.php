@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }} ({{ $wallet->code }})</h2>
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
                                            <td>
                                                <a href="{{ route('center_user.users_wallets.print', ['user_id' => $user->user->id, 'wallet_id' => $wallet->id]) }}"
                                                    target="_blank" class="btn btn-primary text-white">{{ __('general.print') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('vendor-script')
@endsection
