@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.scss', 'resources/assets/vendor/libs/tagify/tagify.scss', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.scss'])
@endsection

@section('content')
    <div class="container-fluid">
        @include('CenterUser.Components.breadcrumbs')
        

        @include('CenterUser.SubViews.Sale.partials.customer_selection')

    

        <div class="row">
            <!-- Left Panel: Add Items -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="booking-tab" data-bs-toggle="tab" href="#booking" role="tab">
                                    <i class="ti ti-calendar me-1"></i>
                                    {{ __('locale.bookings') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="products-tab" data-bs-toggle="tab" href="#products" role="tab">
                                    <i class="ti ti-package me-1"></i>
                                    {{ __('locale.products') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="wallet-tab" data-bs-toggle="tab" href="#wallet" role="tab">
                                    <i class="ti ti-wallet me-1"></i>
                                        {{ __('field.coupons') }} ({{ __('locale.wallets') }})
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            @include('CenterUser.SubViews.Sale.partials.booking_wizard')

                            @include('CenterUser.SubViews.Sale.partials.products_tab')

                            @include('CenterUser.SubViews.Sale.partials.wallet_tab')
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @include('CenterUser.SubViews.Sale.partials.cart_summary')
            </div>  
        </div>
    </div>

    @include('CenterUser.SubViews.Sale.partials.modals')
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js', 'resources/assets/vendor/libs/bootstrap-select/bootstrap-select.js', 'resources/assets/vendor/libs/jquery-repeater/jquery-repeater.js', 'resources/assets/vendor/libs/tagify/tagify.js', 'resources/assets/vendor/libs/bs-stepper/bs-stepper.js'])
@endsection

@section('page-script')
    @vite(['resources/assets/js/forms-selects.js', 'resources/assets/js/app-ecommerce-product-add.js', 'resources/assets/js/form-wizard-icons.js'])
    @include('CenterUser.Components.translation-js')

    @include('CenterUser.SubViews.Sale.partials.pos_js')
@endsection