@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-start">
                            <!-- Logo Section - Left Side -->
                            <div class="col-12 col-md-4 col-lg-3 text-center text-md-start mb-4 mb-md-0">
                                <div class="d-flex justify-content-center justify-content-md-start">
                                    <img src="{{ $item->image }}" alt="user image"
                                        class="img-fluid rounded-circle"
                                        style="width: 180px; height: 180px; object-fit: cover; border: 3px solid #e0e0e0;" />
                                </div>
                            </div>

                            <!-- Details Section - Right Side -->
                            <div class="col-12 col-md-8 col-lg-9">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 fw-semibold text-muted">{{__('field.name')}}</h6>
                                            <p class="mb-0 fs-5 fw-medium">{{ $item->name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 fw-semibold text-muted">{{__('field.email')}}</h6>
                                            <p class="mb-0 fs-5">
                                                {{ $item->email ?: __('general.not_available') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 fw-semibold text-muted">{{__('field.phone')}}</h6>
                                            <p class="mb-0 fs-5">
                                                @if($item->country_code)
                                                    <span class="text-muted">{{ $item->country_code }}</span>
                                                @endif
                                                {{ $item->phone }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 fw-semibold text-muted">{{__('field.created_at')}}</h6>
                                            <p class="mb-0 fs-5">
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
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 0.75rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .rounded-circle {
            border-radius: 50% !important;
        }
        @media (max-width: 768px) {
            .card-body .row > div:first-child {
                margin-bottom: 1.5rem;
            }
        }
    </style>
@endsection
