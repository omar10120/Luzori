@extends('layouts/layoutMaster')

@section('title', __('admin.control_panel'))

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-logistics-dashboard.scss')
@endsection

@section('content')
@push('styles')
<style>
    .luzori-dashboard {
        /* --luzori-teal: #0a4a44; */
        --luzori-peach: #fde8d8;
        --luzori-mint: #d8f5eb;
        --luzori-coral: #f8d9d4;
        --luzori-lavender: #e8e0f5;
        --luzori-chart-mint: #b8e4d4;
        --luzori-muted: #6b7c79;
    }

    .luzori-dashboard .dashboard-title {
        /* color: var(--luzori-teal); */
        font-weight: 700;
        font-size: 1.35rem;
        margin-bottom: 1.25rem;
    }

    .luzori-kpi-card {
        border: none;
        border-radius: 18px;
        padding: 1.35rem 1.5rem;
        height: 100%;
        transition: transform .2s ease, box-shadow .2s ease;
        box-shadow: 0 4px 18px rgba(10, 74, 68, .06);
    }

    .luzori-kpi-card.clickable-stat-card {
        cursor: pointer;
    }

    .luzori-kpi-card.clickable-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(10, 74, 68, .12);
    }

    .luzori-kpi-card--bookings { background: var(--luzori-peach); }
    .luzori-kpi-card--customers { background: var(--luzori-mint); }
    .luzori-kpi-card--services { background: var(--luzori-coral); }
    .luzori-kpi-card--revenue { background: var(--luzori-lavender); }
    .luzori-kpi-card--coupons { background: #fff4dc; }
    .luzori-kpi-card--workers { background: #e8f0ff; }
    .luzori-kpi-card--products { background: #f0f0f0; }

    .luzori-kpi-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: #fff;
    }

    .luzori-kpi-icon--dark { background: #3d4f4c; }
    .luzori-kpi-icon--teal { background: #1a5c55; }
    .luzori-kpi-icon--brown { background: #5c3d35; }
    .luzori-kpi-icon--gold { background: #8a6d2f; }
    .luzori-kpi-icon--blue { background: #3d5a80; }
    .luzori-kpi-icon--slate { background: #4a5568; }

    .luzori-kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1f2d2b;
        line-height: 1.1;
        margin-bottom: .15rem;
    }

    .luzori-kpi-label {
        color: var(--luzori-muted);
        font-size: .9rem;
        margin: 0;
    }

    .luzori-panel {
        border: none;
        border-radius: 18px;
        box-shadow: 0 4px 18px rgba(10, 74, 68, .06);
        height: 100%;
        background: #fff;
    }

    .luzori-panel .card-header {
        background: transparent;
        border-bottom: none;
        padding: 1.25rem 1.5rem .5rem;
    }

    .luzori-panel .card-body {
        padding: .5rem 1.5rem 1.25rem;
    }

    .luzori-panel-title {
        color: var(--luzori-teal);
        
        font-weight: 700;
        font-size: 1rem;
        margin: 0;
    }

    .luzori-mini-stat {
        border-radius: 14px;
        padding: 1rem 1.1rem;
        height: 100%;
    }

    .luzori-sales-table thead th {
        /* background: var(--luzori-teal); */
        color: #fff;
        font-weight: 600;
        font-size: .78rem;
        border: none;
        padding: .85rem .75rem;
        white-space: nowrap;
    }

    .luzori-sales-table tbody td {
        vertical-align: middle;
        font-size: .82rem;
        padding: .75rem;
        border-color: #eef2f1;
    }

    .luzori-sales-table .service-cell {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .luzori-sales-table .service-thumb {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        object-fit: cover;
        background: #f3f5f4;
    }

    .luzori-sales-table .customer-email {
        color: var(--luzori-muted);
        font-size: .72rem;
    }

    .luzori-status-badge {
        border-radius: 999px;
        padding: .28rem .75rem;
        font-size: .72rem;
        font-weight: 600;
    }

    .luzori-status-badge--confirmed {
        background: #d8f5eb;
        color: #0a4a44;
    }

    .luzori-status-badge--pending {
        background: #fff4dc;
        color: #9a6b00;
    }

    .luzori-filter-btn {
        border-radius: 999px;
        padding: .35rem .9rem;
        font-size: .78rem;
        border: 1px solid #dce5e3;
        background: #fff;
        color: var(--luzori-muted);
    }

    .luzori-filter-btn:hover {
        /* color: var(--luzori-teal); */
        /* border-color: var(--luzori-teal); */
    }

    .luzori-filter-btn.active {
        background: #0a4a44 !important;
        border-color: #0a4a44 !important;
        color: #fff !important;
    }

    .luzori-rating-bubbles {
        position: relative;
        min-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .luzori-rating-bubble {
        position: absolute;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #fff;
        font-weight: 700;
        box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
    }

    .luzori-rating-bubble small {
        font-size: .62rem;
        font-weight: 500;
        opacity: .9;
        max-width: 80%;
        line-height: 1.2;
    }

    .luzori-rating-bubble--1 {
        width: 110px;
        height: 110px;
        top: 10px;
        left: 50%;
        transform: translateX(-50%);
    }

    .luzori-rating-bubble--2 {
        width: 95px;
        height: 95px;
        right: 8%;
        top: 42%;
    }

    .luzori-rating-bubble--3 {
        width: 88px;
        height: 88px;
        left: 10%;
        bottom: 8%;
    }

    .luzori-performer-card {
        border: none;
        border-radius: 16px;
        background: #f8fbfa;
        height: 100%;
    }

    .luzori-performer-card .card-header {
        /* background: var(--luzori-teal); */
        color: #fff;
        border-radius: 16px 16px 0 0;
        border: none;
        padding: .85rem 1rem;
    }

    .luzori-performer-card .card-header h5 {
        font-size: .88rem;
        margin: 0;
    }

    .luzori-earnings-legend {
        display: flex;
        flex-direction: column;
        gap: .65rem;
        margin-top: .5rem;
    }

    .luzori-earnings-legend-item {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-size: .8rem;
        color: var(--luzori-muted);
    }

    .luzori-earnings-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>
@endpush

@php
    $popularServices = $chartData['popular_services'] ?? [];
    $revenueTrends = $chartData['revenue_trends'] ?? [];
    $earningsWeek = $chartData['earnings_per_week'] ?? [];
    $latestSales = $chartData['latest_sales'] ?? [];
    $ratingStats = $chartData['rating_stats'] ?? [];
    $currency = trim(get_currency());
    $salesPeriod = $salesPeriod ?? 'day';
@endphp

<div class="luzori-dashboard">
    <h4 class="dashboard-title">{{ __('locale.statistics') }}</h4>

    {{-- Primary KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="luzori-kpi-card luzori-kpi-card--bookings clickable-stat-card" data-type="bookings">
                <div class="d-flex align-items-center gap-3">
                    <div class="luzori-kpi-icon luzori-kpi-icon--dark">
                        <i class="ti ti-calendar-event ti-md"></i>
                    </div>
                    <div>
                        <div class="luzori-kpi-value">{{ number_format($statistics['today_bookings_count']) }}</div>
                        <p class="luzori-kpi-label">{{ __('field.today_bookings') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="luzori-kpi-card luzori-kpi-card--customers clickable-stat-card" data-type="customers">
                <div class="d-flex align-items-center gap-3">
                    <div class="luzori-kpi-icon luzori-kpi-icon--teal">
                        <i class="ti ti-users ti-md"></i>
                    </div>
                    <div>
                        <div class="luzori-kpi-value">{{ number_format($statistics['customers_count']) }}</div>
                        <p class="luzori-kpi-label">{{ __('field.customers') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="luzori-kpi-card luzori-kpi-card--services clickable-stat-card" data-type="services">
                <div class="d-flex align-items-center gap-3">
                    <div class="luzori-kpi-icon luzori-kpi-icon--brown">
                    <img src="{{ asset('assets/icons/profile-2.svg') }}" alt="icon" style="width: 22px; height: 22px; object-fit: contain;">    
                    </div>
                    <div>
                        <div class="luzori-kpi-value">{{ number_format($statistics['services_count']) }}</div>
                        <p class="luzori-kpi-label">{{ __('field.services') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="luzori-kpi-card luzori-kpi-card--revenue clickable-stat-card" data-type="revenue">
                <div class="d-flex align-items-center gap-3">
                    <div class="luzori-kpi-icon luzori-kpi-icon--teal">
                        <!-- <i class="ti ti-trending-up ti-md"></i> -->
                        <img src="{{ asset('assets/icons/profit_1.svg') }}" alt="icon" style="width: 20px; height: 20px; object-fit: contain;">    
                    </div>
                    <div>
                        <div class="luzori-kpi-value">{{ number_format($statistics['today_revenue'], 0) }} {{ $currency }}</div>
                        <p class="luzori-kpi-label">{{ __('field.today_revenue') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary KPIs --}}
    <!-- <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-4">
            <div class="luzori-kpi-card luzori-kpi-card--coupons luzori-mini-stat clickable-stat-card" data-type="coupons">
                <div class="d-flex align-items-center gap-3">
                    <div class="luzori-kpi-icon luzori-kpi-icon--gold">
                        <i class="ti ti-discount-2 ti-md"></i>
                    </div>
                    <div>
                        <div class="luzori-kpi-value">{{ $statistics['active_coupons_count'] }}%</div>
                        <p class="luzori-kpi-label">{{ __('field.active_coupons') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="luzori-kpi-card luzori-kpi-card--workers luzori-mini-stat clickable-stat-card" data-type="workers">
                <div class="d-flex align-items-center gap-3">
                    <div class="luzori-kpi-icon luzori-kpi-icon--blue">
                        <i class="ti ti-briefcase ti-md"></i>
                    </div>
                    <div>
                        <div class="luzori-kpi-value">{{ number_format($statistics['active_workers_count']) }}</div>
                        <p class="luzori-kpi-label">{{ __('field.active_workers') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-4">
            <div class="luzori-kpi-card luzori-kpi-card--products luzori-mini-stat clickable-stat-card" data-type="products">
                <div class="d-flex align-items-center gap-3">
                    <div class="luzori-kpi-icon luzori-kpi-icon--slate">
                        <i class="ti ti-package ti-md"></i>
                    </div>
                    <div>
                        <div class="luzori-kpi-value">{{ number_format($statistics['available_products_count']) }}</div>
                        <p class="luzori-kpi-label">{{ __('field.available_products') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card luzori-panel">
                <div class="card-header">
                    <h5 class="luzori-panel-title">{{ __('field.most_popular_services') }}</h5>
                </div>
                <div class="card-body">
                    <div id="popularServicesChart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-7">
            <div class="card luzori-panel">
                <div class="card-header">
                    <h5 class="luzori-panel-title">{{ __('field.most_revenue_trends') }}</h5>
                </div>
                <div class="card-body">
                    <div id="revenueTrendsChart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-12">
            <div class="card luzori-panel">
                <div class="card-header">
                    <h5 class="luzori-panel-title">{{ __('field.earnings_per_week') }}</h5>
                </div>
                <div class="card-body">
                    <div id="earningsWeekChart"></div>
                    <div class="luzori-earnings-legend">
                        <div class="luzori-earnings-legend-item">
                            <span class="luzori-earnings-dot" style="background:#1e3a5f"></span>
                            {{ __('api.outside_booking') }} ({{ $earningsWeek['outside_pct'] ?? 0 }}%)
                        </div>
                        <div class="luzori-earnings-legend-item">
                            <span class="luzori-earnings-dot" style="background:#6ba3c7"></span>
                            {{ __('api.inside_booking') }} ({{ $earningsWeek['inside_pct'] ?? 0 }}%)
                        </div>
                        <div class="luzori-earnings-legend-item">
                            <span class="luzori-earnings-dot" style="background:#c5d0d8"></span>
                            {{ __('field.total') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Latest Sales + Rating --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8 col-lg-7">
            <div class="card luzori-panel">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="luzori-panel-title">{{ __('field.latest_sales') }}</h5>
                    <form method="POST" action="{{ route('center_user.cp') }}" class="d-flex gap-2 align-items-center mb-0">
                        @csrf
                        <button type="submit" name="period" value="day"
                            class="luzori-filter-btn {{ $salesPeriod === 'day' ? 'active' : '' }}">{{ __('field.day') }}</button>
                        <button type="submit" name="period" value="week"
                            class="luzori-filter-btn {{ $salesPeriod === 'week' ? 'active' : '' }}">{{ __('field.week') }}</button>
                        <button type="submit" name="period" value="month"
                            class="luzori-filter-btn {{ $salesPeriod === 'month' ? 'active' : '' }}">{{ __('field.month') }}</button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table luzori-sales-table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('field.service') }}</th>
                                    <th>{{ __('field.customer') }}</th>
                                    <th>{{ __('field.phone') }}</th>
                                    <th>{{ __('field.worker') }}</th>
                                    <th>{{ __('field.payment_type') }}</th>
                                    <th>{{ __('field.total') }}</th>
                                    <th>{{ __('field.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($latestSales as $sale)
                                    <tr>
                                        <td>
                                            <div class="service-cell">
                                                <img src="{{ $sale['service_image'] }}" alt="" class="service-thumb">
                                                <span>{{ $sale['service_name'] }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $sale['customer_name'] }}</div>
                                            @if (!empty($sale['customer_email']))
                                                <div class="customer-email">{{ $sale['customer_email'] }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $sale['phone'] }}</td>
                                        <td>{{ $sale['worker'] }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $sale['payment_type'])) }}</td>
                                        <td>{{ number_format($sale['total'], 2) }} {{ $currency }}</td>
                                        <td>
                                            <span class="luzori-status-badge luzori-status-badge--{{ $sale['status'] === 'pending' ? 'pending' : 'confirmed' }}">
                                                {{ ucfirst($sale['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">{{ __('general.no_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-5">
            <div class="card luzori-panel">
                <div class="card-header">
                    <h5 class="luzori-panel-title">{{ __('field.top_performers') }}</h5>
                    <p class="text-muted mb-0 mt-1" style="font-size:.78rem">{{ __('field.bookings_this_month') }}</p>
                </div>
                <div class="card-body">
                    <div class="luzori-rating-bubbles">
                        @foreach ($ratingStats as $index => $stat)
                            <div class="luzori-rating-bubble luzori-rating-bubble--{{ $index + 1 }}"
                                style="background: {{ $stat['color'] }}">
                                <div>{{ $stat['pct'] }}%</div>
                                <small>{{ Str::limit($stat['label'], 18) }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Performers Detail Cards --}}
    <div class="row g-3 mb-2">
        <div class="col-lg-4">
            <div class="card luzori-performer-card">
                <div class="card-header">
                    <h5><i class="ti ti-award me-1"></i> {{ __('field.best_service') }}</h5>
                </div>
                <div class="card-body text-center py-4">
                    <h5 class="mb-1">{{ $statistics['best_service']['name'] }}</h5>
                    <h3 class="fw-bold text-primary mb-0">{{ $statistics['best_service']['count'] }}</h3>
                    <small class="text-muted">{{ __('field.bookings_this_month') }}</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card luzori-performer-card">
                <div class="card-header">
                    <h5><i class="ti ti-user-check me-1"></i> {{ __('field.best_worker') }}</h5>
                </div>
                <div class="card-body text-center py-4">
                    <h5 class="mb-1">{{ $statistics['best_worker']['name'] }}</h5>
                    <h3 class="fw-bold text-primary mb-0">{{ $statistics['best_worker']['count'] }}</h3>
                    <small class="text-muted">{{ __('field.bookings_this_month') }}</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card luzori-performer-card">
                <div class="card-header">
                    <h5><i class="ti ti-trending-up me-1"></i> {{ __('field.best_customer') }}</h5>
                </div>
                <div class="card-body text-center py-4">
                    <h5 class="mb-1">{{ $statistics['best_customer']['name'] }}</h5>
                    <h3 class="fw-bold text-primary mb-0">{{ $statistics['best_customer']['count'] }}</h3>
                    <small class="text-muted">{{ __('field.bookings_this_month') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

@include('CenterUser.Components.detail-tables')
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const teal = '#0a4a44';
    const chartMint = '#b8e4d4';
    const peachFill = '#fde8d8';

    const popularServices = @json($popularServices);
    const revenueTrends = @json($revenueTrends);
    const earningsWeek = @json($earningsWeek);
    const currency = @json($currency);

    if (popularServices.length && document.querySelector('#popularServicesChart')) {
        const maxCount = Math.max(...popularServices.map(s => s.count));
        const barColors = popularServices.map(s => s.count === maxCount ? teal : chartMint);

        new ApexCharts(document.querySelector('#popularServicesChart'), {
            chart: { type: 'bar', height: 260, toolbar: { show: false } },
            plotOptions: {
                bar: {
                    borderRadius: 8,
                    columnWidth: '48%',
                    distributed: true
                }
            },
            colors: barColors,
            dataLabels: { enabled: false },
            series: [{ name: '{{ __("field.services") }}', data: popularServices.map(s => s.count) }],
            xaxis: {
                categories: popularServices.map(s => s.name),
                labels: { style: { fontSize: '11px' }, rotate: -35 }
            },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            grid: { borderColor: '#eef2f1', strokeDashArray: 4 },
            legend: { show: false },
            tooltip: { theme: 'light' }
        }).render();
    } else if (document.querySelector('#popularServicesChart')) {
        document.querySelector('#popularServicesChart').innerHTML = '<p class="text-muted text-center py-5">{{ __("general.no_data") }}</p>';
    }

    if (revenueTrends.length && document.querySelector('#revenueTrendsChart')) {
        new ApexCharts(document.querySelector('#revenueTrendsChart'), {
            chart: { type: 'area', height: 260, toolbar: { show: false }, zoom: { enabled: false } },
            colors: [teal],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: .45,
                    opacityTo: .05,
                    stops: [0, 90, 100],
                    colorStops: [
                        { offset: 0, color: peachFill, opacity: .8 },
                        { offset: 100, color: peachFill, opacity: .1 }
                    ]
                }
            },
            stroke: { curve: 'smooth', width: 2.5 },
            dataLabels: { enabled: false },
            series: [{ name: '{{ __("field.today_revenue") }}', data: revenueTrends.map(r => r.revenue) }],
            xaxis: {
                categories: revenueTrends.map(r => r.day),
                labels: { style: { fontSize: '10px' } },
                tickAmount: 10
            },
            yaxis: {
                labels: {
                    style: { fontSize: '10px' },
                    formatter: val => Math.round(val).toLocaleString()
                }
            },
            grid: { borderColor: '#eef2f1', strokeDashArray: 4 },
            tooltip: {
                y: { formatter: val => val.toLocaleString() + ' ' + currency }
            }
        }).render();
    }

    if (document.querySelector('#earningsWeekChart')) {
        const insidePct = earningsWeek.inside_pct || 0;
        const outsidePct = earningsWeek.outside_pct || 0;

        new ApexCharts(document.querySelector('#earningsWeekChart'), {
            chart: { type: 'radialBar', height: 220, toolbar: { show: false } },
            plotOptions: {
                radialBar: {
                    hollow: { size: '30%' },
                    track: { background: '#eef2f1', strokeWidth: '100%' },
                    dataLabels: {
                        name: { fontSize: '11px' },
                        value: { fontSize: '14px', fontWeight: 700 },
                        total: {
                            show: true,
                            label: '{{ __("field.total") }}',
                            formatter: () => (earningsWeek.all || 0).toLocaleString() + ' ' + currency
                        }
                    }
                }
            },
            colors: ['#1e3a5f', '#6ba3c7', '#c5d0d8'],
            labels: ['{{ __("api.outside_booking") }}', '{{ __("api.inside_booking") }}', '{{ __("field.total") }}'],
            series: [outsidePct, insidePct, Math.min(100, outsidePct + insidePct)]
        }).render();
    }
});
</script>
@endpush
