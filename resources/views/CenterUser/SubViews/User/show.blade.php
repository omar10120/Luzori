@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-user-show .lz-profile-card {
            border: 0;
            border-radius: 18px;
            background: #fdfaf6;
            box-shadow: 0 8px 28px rgba(0, 43, 38, 0.07);
            overflow: hidden;
        }
        .lz-user-show .lz-profile-avatar {
            width: 148px;
            height: 148px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e8d5b5;
            background: #fff;
            box-shadow: 0 6px 18px rgba(0, 43, 38, 0.08);
        }
        .lz-user-show .lz-info-label {
            color: #7a8582;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .lz-user-show .lz-info-value {
            color: #002b26;
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0;
            word-break: break-word;
        }
        .lz-user-show .lz-info-value.is-muted {
            color: #9aa3a0;
            font-weight: 500;
        }
        .lz-user-show .lz-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1.25rem 1.5rem;
        }
        @media (max-width: 767.98px) {
            .lz-user-show .lz-info-grid {
                grid-template-columns: 1fr;
            }
            .lz-user-show .lz-profile-avatar {
                width: 120px;
                height: 120px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-user-show">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-user"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
            <div class="lz-page-actions">
                <a href="{{ $menu_link }}" class="btn btn-luzori-muted btn-sm px-3 py-2">
                    <i class="ti ti-arrow-left me-1"></i>{{ __('general.back') }}
                </a>
                @if(auth('center_user')->user()?->can('UPDATE_USERS', 'center_api'))
                    <a href="{{ route('center_user.users.create', ['id' => $item->id]) }}" class="btn btn-luzori-primary btn-sm px-3 py-2">
                        <i class="ti ti-pencil me-1"></i>{{ __('general.edit') }}
                    </a>
                @endif
            </div>
        </div>

        <div class="card lz-profile-card">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-md-4 col-lg-3 text-center text-md-start">
                        <img src="{{ $item->image }}" alt="{{ $item->name }}" class="lz-profile-avatar">
                    </div>
                    <div class="col-12 col-md-8 col-lg-9">
                        <div class="lz-info-grid">
                            <div>
                                <div class="lz-info-label">{{ __('field.name') }}</div>
                                <p class="lz-info-value">{{ $item->name ?: __('general.not_available') }}</p>
                            </div>
                            <div>
                                <div class="lz-info-label">{{ __('field.email') }}</div>
                                <p class="lz-info-value {{ $item->email ? '' : 'is-muted' }}">
                                    {{ $item->email ?: __('general.not_available') }}
                                </p>
                            </div>
                            <div>
                                <div class="lz-info-label">{{ __('field.phone') }}</div>
                                <p class="lz-info-value">
                                    @if($item->phone)
                                        @if($item->country_code)
                                            <span class="text-muted">{{ $item->country_code }}</span>
                                        @endif
                                        {{ $item->phone }}
                                    @else
                                        <span class="is-muted">{{ __('general.not_available') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <div class="lz-info-label">{{ __('field.created_at') }}</div>
                                <p class="lz-info-value {{ $item->created_at ? '' : 'is-muted' }}">
                                    {{ $item->created_at
                                        ? \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i')
                                        : __('general.not_available') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
