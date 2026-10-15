@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-notif-show .lz-detail-label {
            color: #7a8582;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 0.35rem;
        }
        .lz-notif-show .lz-detail-value {
            color: #002b26;
            font-weight: 600;
            margin-bottom: 1.25rem;
        }
        .lz-notif-users-table .lz-user-name {
            font-weight: 700;
            color: #002b26;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-notif-show">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-bell"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
            <div class="lz-page-actions">
                <a href="{{ route('center_user.notifications.index') }}" class="btn btn-luzori-beige btn-sm">
                    {{ __('general.back') }}
                </a>
            </div>
        </div>

        <div class="card lz-form-card mb-3">
            <div class="card-body">
                <div class="row">
                    @foreach (Config::get('translatable.locales') as $locale)
                        <div class="col-md-6">
                            <div class="lz-detail-label">{{ __('field.title') }} ({{ Str::upper($locale) }})</div>
                            <div class="lz-detail-value">{{ $item->translate($locale)->title ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="row">
                    @foreach (Config::get('translatable.locales') as $locale)
                        <div class="col-md-6">
                            <div class="lz-detail-label">{{ __('field.text') }} ({{ Str::upper($locale) }})</div>
                            <div class="lz-detail-value">{{ $item->translate($locale)->text ?? '—' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if (count($item->users) > 0)
            <div class="card lz-table-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table lz-notif-users-table mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('field.name') }}</th>
                                    <th>{{ __('field.email') }}</th>
                                    <th>{{ __('field.phone') }}</th>
                                    <th>{{ __('field.created_at') }}</th>
                                    <th class="text-end">{{ __('general.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item->users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td><span class="lz-user-name">{{ $user->name }}</span></td>
                                        <td>{{ $user->email ?: '—' }}</td>
                                        <td>{{ $user->phone ?: '—' }}</td>
                                        <td>{{ $user->created_at }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('center_user.users.show', ['id' => $user->id]) }}"
                                                class="btn btn-luzori-beige btn-sm"
                                                title="{{ __('general.show') }}">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
