@extends('layouts/layoutMaster')

@section('title', __('admin.control_panel'))

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-logistics-dashboard.scss')
@endsection

@section('content')
@push('styles')
<style>
.clickable-stat-card {
    cursor: pointer !important;
}
</style>
@endpush
    <!-- Main Statistics Row -->
    <div class="container mb-4">
        <div class="row">
            <div class="col-12 mb-3">
                <h4 class="card-title">{{ __('locale.statistics') }}</h4>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100 clickable-stat-card    " data-type="services">
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            <div class="avatar bg-light-primary me-3">
                                <div class="avatar-content">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-trending-up avatar-icon">
                                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                        <polyline points="17 6 23 6 23 12"></polyline>
                                    </svg>
                                </div>
                            </div>
                            <div class="my-auto">
                                <h4 class="fw-bolder mb-0">{{ $statistics['services_count'] }}</h4>
                                <p class="card-text font-small-3 mb-0">{{ __('field.services') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100 clickable-stat-card " data-type="customers">
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            <div class="avatar bg-light-info me-3">
                                <div class="avatar-content">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-users avatar-icon">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="my-auto">
                                <h4 class="fw-bolder mb-0">{{ $statistics['customers_count'] }}</h4>
                                <p class="card-text font-small-3 mb-0">{{ __('field.customers') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="w-100 d-lg-none"></div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100 clickable-stat-card " data-type="bookings">
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            <div class="avatar bg-light-danger me-3">
                                <div class="avatar-content">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-calendar avatar-icon">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </div>
                            </div>
                            <div class="my-auto">
                                <h4 class="fw-bolder mb-0">{{ $statistics['today_bookings_count'] }}</h4>
                                <p class="card-text font-small-3 mb-0">{{ __('field.today_bookings') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100 clickable-stat-card " data-type="revenue">
                    <div class="card-body">
                        <div class="d-flex flex-row">
                            <div class="avatar bg-light-success me-3">
                                <div class="avatar-content">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-dollar-sign avatar-icon">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="my-auto">
                                <h4 class="fw-bolder mb-0">{{ number_format($statistics['today_revenue'], 0) }}</h4>
                                <p class="card-text font-small-3 mb-0">{{ __('field.today_revenue') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics Row -->
    <div class="container mb-4">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100 clickable-stat-card" data-type="coupons">
                    <div class="card-body text-center">
                        <div class="avatar bg-light-warning mx-auto mb-2">
                            <div class="avatar-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-percent avatar-icon">
                                    <line x1="19" y1="5" x2="5" y2="19"></line>
                                    <circle cx="6.5" cy="6.5" r="2.5"></circle>
                                    <circle cx="17.5" cy="17.5" r="2.5"></circle>
                                </svg>
                            </div>
                        </div>
                        <h3 class="fw-bolder mb-1">{{ $statistics['active_coupons_count'] }}%</h3>
                        <p class="text-muted mb-0">{{ __('field.active_coupons') }}</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100 clickable-stat-card" data-type="workers">
                    <div class="card-body text-center">
                        <div class="avatar bg-light-secondary mx-auto mb-2">
                            <div class="avatar-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-briefcase avatar-icon">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="fw-bolder mb-1">{{ $statistics['active_workers_count'] }}</h3>
                        <p class="text-muted mb-0">{{ __('field.active_workers') }}</p>
                    </div>
                </div>
            </div>
            <div class="w-100 d-lg-none"></div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100 clickable-stat-card" data-type="products">
                    <div class="card-body text-center">
                        <div class="avatar bg-light-dark mx-auto mb-2">
                            <div class="avatar-content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="feather feather-package avatar-icon">
                                    <path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                </svg>
                            </div>
                        </div>
                        <h3 class="fw-bolder mb-1">{{ $statistics['available_products_count'] }}</h3>
                        <p class="mb-0">{{ __('field.available_products') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers Row -->
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100">
                    <div class="card-header alter-info text-white">
                        <h5 class="card-title mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-award me-2">
                                <circle cx="12" cy="8" r="6"></circle>
                                <polyline points="9,12 12,15 15,12"></polyline>
                                <path d="M21 12c.552 0 1-.448 1-1V8c0-.552-.448-1-1-1h-3.586a1 1 0 0 0-.707.293l-1.414 1.414a1 1 0 0 1-.707.293h-2.172a1 1 0 0 1-.707-.293L9.293 7.293A1 1 0 0 0 8.586 7H5c-.552 0-1 .448-1 1v3c0 .552.448 1 1 1"></path>
                            </svg>
                            {{ __('field.best_service') }}
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h4 class=" mb-1">{{ $statistics['best_service']['name'] }}</h4>
                        <h3 class="fw-bolder  mb-0">{{ $statistics['best_service']['count'] }}</h3>
                        <small class="  ">{{ __('field.bookings_this_month') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100">
                    <div class="card-header alter-info text-white">
                        <h5 class="card-title mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-user-check me-2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                                <polyline points="17,11 19,13 23,9"></polyline>
                            </svg>
                            {{ __('field.best_worker') }}
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h4 class=" mb-1">{{ $statistics['best_worker']['name'] }}</h4>
                        <h3 class="fw-bolder  mb-0">{{ $statistics['best_worker']['count'] }}</h3>
                        <small class="text-muted">{{ __('field.bookings_this_month') }}</small>
                    </div>
                </div>
            </div>
            <div class="w-100 d-lg-none"></div>
            <div class="col-lg-4 col-md-6 col-sm-6 col-6 mb-3">
                <div class="card h-100">
                    <div class="card-header alter-info text-white">
                        <h5 class="card-title mb-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trending-up me-2">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                            {{ __('field.best_customer') }}
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <h4 class=" mb-1">{{ $statistics['best_customer']['name'] }}</h4>
                        <h3 class="fw-bolder  mb-0">{{ $statistics['best_customer']['count'] }}</h3>
                        <small class="text-muted">{{ __('field.bookings_this_month') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detail Tables Component -->
    @include('CenterUser.Components.detail-tables')
@endsection
