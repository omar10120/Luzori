@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-settings-hub .lz-settings-card {
            border: none !important;
            border-radius: 18px !important;
            box-shadow: 0 10px 28px rgba(0, 43, 38, 0.07) !important;
            background: #fff !important;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .lz-settings-hub .lz-settings-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 32px rgba(0, 43, 38, 0.12) !important;
        }
        .lz-settings-hub .lz-settings-card .card-body {
            padding: 1.4rem 1.45rem 1.35rem !important;
        }
        .lz-settings-hub .lz-settings-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: #eef5f3;
            border: 1px solid #dceae6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        .lz-settings-hub .lz-settings-icon img {
            width: 24px;
            height: 24px;
            object-fit: contain;
        }
        .lz-settings-hub .lz-settings-title {
            color: #002b26;
            font-weight: 700;
            font-size: 1.05rem;
            margin-bottom: 0.45rem;
        }
        .lz-settings-hub .lz-settings-desc {
            color: #7a8582;
            font-size: 0.875rem;
            line-height: 1.6;
            margin-bottom: 1.25rem;
            flex-grow: 1;
        }
        .lz-settings-hub .btn-luzori-manage {
            background: #f3ebe0 !important;
            border: 1px solid #e5ddd2 !important;
            color: #002b26 !important;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.5rem 1.1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
        }
        .lz-settings-hub .btn-luzori-manage:hover {
            background: #002b26 !important;
            border-color: #002b26 !important;
            color: #fff !important;
        }
        .lz-settings-hub .btn-luzori-manage:hover i {
            color: #c5a070;
        }
        .lz-settings-hub .lz-hub-subtitle {
            color: #7a8582;
            margin: 0;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-settings-hub">
        @include('CenterUser.Components.breadcrumbs', [
            'title' => $title,
            'menu' => null,
        ])

        <div class="lz-page-toolbar mb-4">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-settings"></i>
                </div>
                <div>
                    <h1 class="lz-page-title mb-1">{{ $title }}</h1>
                    <p class="lz-hub-subtitle">{{ __('general.manage_all_system_preferences') ?? 'Manage all your system preferences and configurations' }}</p>
                </div>
            </div>
        </div>

        @php
            $arrowClass = app()->getLocale() === 'ar' ? 'ti-arrow-left' : 'ti-arrow-right';
        @endphp

        <div class="row g-4">
            {{-- Contact Infos --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card lz-settings-card">
                    <div class="card-body d-flex flex-column">
                        <div class="lz-settings-icon">
                            <img src="{{ asset('assets/icons/contact_info.svg') }}" alt="">
                        </div>
                        <h5 class="lz-settings-title">{{ __('locale.infos') ?? 'Contact Infos' }}</h5>
                        <p class="lz-settings-desc">
                            {{ __('general.contact_infos_desc') ?? 'Manage your contact information and social media links displayed to your customers.' }}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('center_user.infos.index') }}" class="btn btn-luzori-manage">
                                {{ __('general.manage') ?? 'Manage' }}
                                <i class="ti {{ $arrowClass }}"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Center Details --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card lz-settings-card">
                    <div class="card-body d-flex flex-column">
                        <div class="lz-settings-icon">
                            <img src="{{ asset('assets/icons/center_deatils.svg') }}" alt="">
                        </div>
                        <h5 class="lz-settings-title">{{ __('locale.center_info') ?? 'Center Details' }}</h5>
                        <p class="lz-settings-desc">
                            {{ __('general.center_details_desc') ?? 'Update your center information, bank details and business specifications.' }}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('center_user.center.index') }}" class="btn btn-luzori-manage">
                                {{ __('general.manage') ?? 'Manage' }}
                                <i class="ti {{ $arrowClass }}"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pages --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card lz-settings-card">
                    <div class="card-body d-flex flex-column">
                        <div class="lz-settings-icon">
                            <img src="{{ asset('assets/icons/pages.svg') }}" alt="">
                        </div>
                        <h5 class="lz-settings-title">{{ __('locale.pages') ?? 'Pages' }}</h5>
                        <p class="lz-settings-desc">
                            {{ __('general.pages_desc') ?? 'Create and manage your static pages like About Us, Privacy Policy, Terms & Conditions.' }}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('center_user.pages.index') }}" class="btn btn-luzori-manage">
                                {{ __('general.manage') ?? 'Manage' }}
                                <i class="ti {{ $arrowClass }}"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Settings --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card lz-settings-card">
                    <div class="card-body d-flex flex-column">
                        <div class="lz-settings-icon">
                            <img src="{{ asset('assets/icons/settings_new.svg') }}" alt="">
                        </div>
                        <h5 class="lz-settings-title">{{ __('locale.settings') ?? 'Settings' }}</h5>
                        <p class="lz-settings-desc">
                            {{ __('general.settings_desc') ?? 'Configure general system settings and preferences that control your application.' }}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('center_user.settings.index') }}" class="btn btn-luzori-manage">
                                {{ __('general.manage') ?? 'Manage' }}
                                <i class="ti {{ $arrowClass }}"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Invoice Settings --}}
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card lz-settings-card">
                    <div class="card-body d-flex flex-column">
                        <div class="lz-settings-icon">
                            <img src="{{ asset('assets/icons/invoice_settings.svg') }}" alt="">
                        </div>
                        <h5 class="lz-settings-title">{{ __('locale.InvoiceSettings') ?? 'Invoice Settings' }}</h5>
                        <p class="lz-settings-desc">
                            {{ __('general.invoice_settings_desc') ?? 'Customize your invoice template, numbering and other invoice preferences.' }}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('center_user.invoice_settings.index') }}" class="btn btn-luzori-manage">
                                {{ __('general.manage') ?? 'Manage' }}
                                <i class="ti {{ $arrowClass }}"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
