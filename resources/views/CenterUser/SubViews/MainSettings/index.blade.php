@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page Header --}}
    <div class="d-flex align-items-start justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1">{{ $title }}</h4>
            <p class="text-muted mb-0">{{ __('general.manage_all_system_preferences') ?? 'Manage all your system preferences and configurations' }}</p>
        </div>
    </div>

    {{-- Settings Cards Grid --}}
    <div class="row g-4">

        {{-- Contact Infos --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 settings-card">
                <div class="card-body d-flex flex-column p-4">
                    <div class="settings-icon-wrap mb-3">
                        <div class="settings-icon-box">
                            <img src="{{ asset('assets/icons/contact_info.svg') }}" alt="icon" style="width: 25px; height: 25px; object-fit: contain;">    
                        </div>
                    </div>
                    <h5 class="settings-card-title mb-2">{{ __('locale.infos') ?? 'Contact Infos' }}</h5>
                    <p class="settings-card-desc text-muted mb-4">
                        {{ __('general.contact_infos_desc') ?? 'Manage your contact information and social media links displayed to your customers.' }}
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('center_user.infos.index') }}" class="btn btn-outline-secondary settings-manage-btn">
                            {{ __('general.manage') ?? 'Manage' }} &nbsp;<i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Center Details --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 settings-card">
                <div class="card-body d-flex flex-column p-4">
                    <div class="settings-icon-wrap mb-3">
                        <div class="settings-icon-box">
                            <img src="{{ asset('assets/icons/center_deatils.svg') }}" alt="icon" style="width: 25px; height: 25px; object-fit: contain;">    
                        </div>
                    </div>
                    <h5 class="settings-card-title mb-2">{{ __('locale.center_info') ?? 'Center Details' }}</h5>
                    <p class="settings-card-desc text-muted mb-4">
                        {{ __('general.center_details_desc') ?? 'Update your center information, bank details and business specifications.' }}
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('center_user.center.index') }}" class="btn btn-outline-secondary settings-manage-btn">
                            {{ __('general.manage') ?? 'Manage' }} &nbsp;<i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pages --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 settings-card">
                <div class="card-body d-flex flex-column p-4">
                    <div class="settings-icon-wrap mb-3">
                        <div class="settings-icon-box">
                            <img src="{{ asset('assets/icons/pages.svg') }}" alt="icon" style="width: 25px; height: 25px; object-fit: contain;">    
                        </div>
                    </div>
                    <h5 class="settings-card-title mb-2">{{ __('locale.pages') ?? 'Pages' }}</h5>
                    <p class="settings-card-desc text-muted mb-4">
                        {{ __('general.pages_desc') ?? 'Create and manage your static pages like About Us, Privacy Policy, Terms & Conditions.' }}
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('center_user.pages.index') }}" class="btn btn-outline-secondary settings-manage-btn">
                            {{ __('general.manage') ?? 'Manage' }} &nbsp;<i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Settings --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 settings-card">
                <div class="card-body d-flex flex-column p-4">
                    <div class="settings-icon-wrap mb-3">
                        <div class="settings-icon-box">
                            <img src="{{ asset('assets/icons/settings_new.svg') }}" alt="icon" style="width: 25px; height: 25px; object-fit: contain;">    
                        </div>
                    </div>
                    <h5 class="settings-card-title mb-2">{{ __('locale.settings') ?? 'Settings' }}</h5>
                    <p class="settings-card-desc text-muted mb-4">
                        {{ __('general.settings_desc') ?? 'Configure general system settings and preferences that control your application.' }}
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('center_user.settings.index') }}" class="btn btn-outline-secondary settings-manage-btn">
                            {{ __('general.manage') ?? 'Manage' }} &nbsp;<i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Invoice Settings --}}
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 settings-card">
                <div class="card-body d-flex flex-column p-4">
                    <div class="settings-icon-wrap mb-3">
                        <div class="settings-icon-box">
                            <img src="{{ asset('assets/icons/invoice_settings.svg') }}" alt="icon" style="width: 25px; height: 25px; object-fit: contain;">    
                        </div>
                    </div>
                    <h5 class="settings-card-title mb-2">{{ __('locale.InvoiceSettings') ?? 'Invoice Settings' }}</h5>
                    <p class="settings-card-desc text-muted mb-4">
                        {{ __('general.invoice_settings_desc') ?? 'Customize your invoice template, numbering and other invoice preferences.' }}
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('center_user.invoice_settings.index') }}" class="btn btn-outline-secondary settings-manage-btn">
                            {{ __('general.manage') ?? 'Manage' }} &nbsp;<i class="ti ti-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('page-style')
<style>
    .settings-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: default;
    }

    .settings-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    .settings-icon-wrap {
        display: flex;
        align-items: flex-start;
    }

    .settings-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        background: rgba(115, 103, 240, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #7367f0;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .settings-card-title {
        font-size: 1.05rem;
        font-weight: 600;
        margin-bottom: 0;
    }

    .settings-card-desc {
        font-size: 0.875rem;
        line-height: 1.6;
        flex-grow: 1;
    }

    .settings-manage-btn {
        border-radius: 8px;
        font-weight: 500;
        font-size: 0.875rem;
        padding: 0.45rem 1.1rem;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .settings-manage-btn:hover {
        background-color: #7367f0;
        border-color: #7367f0;
        color: #fff;
    }

    .settings-manage-btn i {
        font-size: 1rem;
        transition: transform 0.2s ease;
    }

    .settings-manage-btn:hover i {
        transform: translateX(3px);
    }
</style>
@endsection
