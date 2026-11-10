@extends('layouts/layoutMaster')

@section('title', __('admin.control_panel'))

@section('page-style')
    @vite('resources/assets/vendor/scss/pages/app-logistics-dashboard.scss')
@endsection

@section('content')
@push('styles')
<style>
    .luzori-dashboard {
        --lz-forest: #002b26;
        --lz-cream: #f9f6f1;
        --lz-cream-card: #fffdf9;
        --lz-gold: #c5a070;
        --lz-gold-soft: #e8d5b5;
        --lz-mint: #d8f0e6;
        --lz-mint-text: #1a6b5a;
        --lz-peach: #f8e6d4;
        --lz-peach-text: #b06a2b;
        --lz-muted: #7a8582;
        --lz-text: #1a2a27;
        --lz-radius: 18px;
        --lz-shadow: 0 10px 30px rgba(0, 43, 38, 0.07);
        --lz-font-display: 'Cormorant Garamond', Georgia, serif;
        --lz-font-body: 'DM Sans', 'Cairo', sans-serif;
        font-family: var(--lz-font-body);
        color: var(--lz-text);
    }

    html[dir='rtl'] .luzori-dashboard {
        --lz-font-body: 'Cairo', 'DM Sans', sans-serif;
    }

    .luzori-dashboard .lz-greeting {
        font-family: var(--lz-font-display);
        font-size: clamp(1.85rem, 2.6vw, 2.4rem);
        font-weight: 600;
        color: var(--lz-forest);
        margin: 0 0 .35rem;
        letter-spacing: -0.01em;
        line-height: 1.15;
    }

    .luzori-dashboard .lz-greeting-sub {
        color: var(--lz-muted);
        font-size: .92rem;
        margin: 0;
    }

    .luzori-dashboard .lz-date-chip {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        background: #fff;
        border: 1px solid rgba(0, 43, 38, 0.08);
        border-radius: 999px;
        padding: .55rem 1rem;
        color: var(--lz-text);
        font-size: .85rem;
        font-weight: 500;
        box-shadow: 0 4px 14px rgba(0, 43, 38, 0.04);
        white-space: nowrap;
    }

    .luzori-dashboard .lz-date-chip i {
        color: var(--lz-gold-deep, #9a6923);
    }

    .luzori-dashboard .lz-kpi {
        position: relative;
        border: none;
        border-radius: var(--lz-radius);
        padding: 1.35rem 1.4rem 1.2rem;
        height: 100%;
        min-height: 148px;
        overflow: hidden;
        box-shadow: var(--lz-shadow);
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .luzori-dashboard .lz-kpi.clickable-stat-card {
        cursor: pointer;
    }

    .luzori-dashboard .lz-kpi.clickable-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 34px rgba(0, 43, 38, 0.12);
    }

    .luzori-dashboard .lz-kpi--dark {
        background: linear-gradient(145deg, #003830 0%, #002b26 55%, #001f1b 100%);
        color: #fff;
    }

    .luzori-dashboard .lz-kpi--dark::after {
        content: '';
        position: absolute;
        right: -20px;
        bottom: -28px;
        width: 150px;
        height: 90px;
        background: radial-gradient(ellipse at center, rgba(255,255,255,.12) 0%, transparent 70%);
        pointer-events: none;
    }

    .luzori-dashboard .lz-kpi--light {
        background: var(--lz-cream-card);
        color: var(--lz-text);
    }

    .luzori-dashboard .lz-kpi-top {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: .85rem;
    }

    .luzori-dashboard .lz-kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.35rem;
    }

    .luzori-dashboard .lz-kpi--dark .lz-kpi-icon {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        backdrop-filter: blur(6px);
        border: 1px solid rgba(255, 255, 255, 0.14);
    }

    .luzori-dashboard .lz-kpi--light .lz-kpi-icon {
        background: #efe2d0;
        color: #7a5a2f;
    }

    .luzori-dashboard .lz-kpi-label {
        font-size: .88rem;
        margin: 0 0 .35rem;
        opacity: .85;
        font-weight: 500;
    }

    .luzori-dashboard .lz-kpi--dark .lz-kpi-label {
        color: rgba(255, 255, 255, 0.78);
    }

    .luzori-dashboard .lz-kpi--light .lz-kpi-label {
        color: var(--lz-muted);
    }

    .luzori-dashboard .lz-kpi-value {
        font-size: 1.85rem;
        font-weight: 700;
        line-height: 1.1;
        margin: 0;
        letter-spacing: -0.02em;
    }

    .luzori-dashboard .lz-kpi--dark .lz-kpi-value {
        color: #fff;
    }

    .luzori-dashboard .lz-kpi-arrow {
        position: absolute;
        inset-inline-end: 1rem;
        bottom: 1rem;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .95rem;
        z-index: 1;
    }

    .luzori-dashboard .lz-kpi--dark .lz-kpi-arrow {
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
    }

    .luzori-dashboard .lz-kpi--light .lz-kpi-arrow {
        background: #f1ebe3;
        color: var(--lz-forest);
    }

    .luzori-dashboard .lz-panel {
        border: none;
        border-radius: var(--lz-radius);
        box-shadow: var(--lz-shadow);
        height: 100%;
        background: #fff;
    }

    .luzori-dashboard .lz-panel .card-header {
        background: transparent;
        border-bottom: none;
        padding: 1.25rem 1.35rem .35rem;
    }

    .luzori-dashboard .lz-panel .card-body {
        padding: .4rem 1.35rem 1.25rem;
    }

    .luzori-dashboard .lz-panel-title {
        color: var(--lz-forest);
        font-weight: 700;
        font-size: 1rem;
        margin: 0;
    }

    .luzori-dashboard .lz-filter-btn {
        border-radius: 999px;
        padding: .35rem .9rem;
        font-size: .78rem;
        border: 1px solid #e5ddd2;
        background: #fff;
        color: var(--lz-muted);
        font-weight: 500;
    }

    .luzori-dashboard .lz-filter-btn:hover {
        color: var(--lz-forest);
        border-color: var(--lz-gold);
    }

    .luzori-dashboard .lz-filter-btn.active {
        background: var(--lz-forest) !important;
        border-color: var(--lz-forest) !important;
        color: #fff !important;
    }

    .luzori-dashboard .lz-sales-table thead th {
        background: transparent;
        color: var(--lz-muted);
        font-weight: 600;
        font-size: .75rem;
        border: none;
        border-bottom: 1px solid #efe9e0;
        padding: .85rem .75rem;
        white-space: nowrap;
        text-transform: none;
    }

    .luzori-dashboard .lz-sales-table tbody td {
        vertical-align: middle;
        font-size: .82rem;
        padding: .8rem .75rem;
        border-color: #f3eee7;
        color: var(--lz-text);
    }

    .luzori-dashboard .lz-sales-table .service-cell {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .luzori-dashboard .lz-sales-table .service-thumb {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        object-fit: cover;
        background: #f3f5f4;
    }

    .luzori-dashboard .lz-sales-table .customer-email {
        color: var(--lz-muted);
        font-size: .72rem;
    }

    .luzori-dashboard .lz-status-badge {
        border-radius: 999px;
        padding: .28rem .75rem;
        font-size: .72rem;
        font-weight: 600;
    }

    .luzori-dashboard .lz-status-badge--confirmed {
        background: var(--lz-mint);
        color: var(--lz-mint-text);
    }

    .luzori-dashboard .lz-status-badge--pending {
        background: var(--lz-peach);
        color: var(--lz-peach-text);
    }

    .luzori-dashboard .lz-performer-row {
        display: flex;
        align-items: center;
        gap: .85rem;
        padding: .7rem 0;
    }

    .luzori-dashboard .lz-performer-row + .lz-performer-row {
        border-top: 1px solid #f1ebe3;
    }

    .luzori-dashboard .lz-rank {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f3ebe0;
        color: var(--lz-forest);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .78rem;
        font-weight: 700;
        flex-shrink: 0;
    }

    .luzori-dashboard .lz-performer-avatar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4b483, #efe2d0);
        color: var(--lz-forest);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .85rem;
        flex-shrink: 0;
        overflow: hidden;
    }

    .luzori-dashboard .lz-performer-meta {
        flex: 1;
        min-width: 0;
    }

    .luzori-dashboard .lz-performer-name {
        font-weight: 600;
        font-size: .88rem;
        margin: 0 0 .15rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .luzori-dashboard .lz-performer-sub {
        font-size: .72rem;
        color: var(--lz-muted);
        margin: 0 0 .4rem;
    }

    .luzori-dashboard .lz-progress {
        height: 5px;
        background: #f1ebe3;
        border-radius: 999px;
        overflow: hidden;
    }

    .luzori-dashboard .lz-progress > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: linear-gradient(90deg, #0a3a34, #c5a070);
    }

    .luzori-dashboard .lz-earnings-legend {
        display: flex;
        flex-direction: column;
        gap: .75rem;
        margin-top: .75rem;
    }

    .luzori-dashboard .lz-earnings-legend-item {
        font-size: .8rem;
        color: var(--lz-muted);
    }

    .luzori-dashboard .lz-earnings-legend-item .d-flex {
        margin-bottom: .3rem;
    }

    .luzori-dashboard .lz-earnings-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .luzori-dashboard .lz-earnings-bar {
        height: 6px;
        background: #f1ebe3;
        border-radius: 999px;
        overflow: hidden;
    }

    .luzori-dashboard .lz-earnings-bar > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: var(--lz-forest);
    }

    .luzori-dashboard .lz-promo {
        position: relative;
        border-radius: var(--lz-radius);
        overflow: hidden;
        min-height: 220px;
        height: calc(100% - 0px);
        background:
            linear-gradient(115deg, rgba(0, 30, 26, 0.88) 0%, rgba(0, 43, 38, 0.55) 48%, rgba(20, 30, 28, 0.35) 100%),
            url('https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80') center/cover no-repeat;
        color: #fff;
        box-shadow: var(--lz-shadow);
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .luzori-dashboard .lz-promo::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160' viewBox='0 0 160 160' fill='none'%3E%3Cpath d='M30 120c25-40 50-55 70-58 10 22 6 48-8 70' stroke='%23c5a070' stroke-opacity='.25'/%3E%3Cpath d='M70 130c28-42 52-55 75-58' stroke='%23c5a070' stroke-opacity='.18'/%3E%3Ccircle cx='95' cy='45' r='4' fill='%23c5a070' fill-opacity='.3'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right -10px top -10px;
        opacity: .55;
        pointer-events: none;
    }

    .luzori-dashboard .lz-promo-eyebrow {
        position: relative;
        font-size: .72rem;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--lz-gold-soft);
        font-weight: 600;
        margin-bottom: .35rem;
    }

    .luzori-dashboard .lz-promo-title {
        position: relative;
        font-family: var(--lz-font-display);
        font-size: 1.55rem;
        font-weight: 600;
        line-height: 1.2;
        margin: 0 0 1rem;
        max-width: 12ch;
    }

    .luzori-dashboard .lz-promo-btn {
        position: relative;
        align-self: flex-start;
        background: linear-gradient(90deg, #d4b483, #e8d5b5);
        color: var(--lz-forest);
        border: none;
        border-radius: 999px;
        padding: .55rem 1.1rem;
        font-size: .82rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        transition: transform .15s ease, box-shadow .15s ease;
    }

    .luzori-dashboard .lz-promo-btn:hover {
        color: var(--lz-forest);
        transform: translateY(-1px);
        box-shadow: 0 8px 18px rgba(197, 160, 112, 0.35);
    }

    .luzori-dashboard .lz-shimmer,
    .luzori-dashboard .lz-shimmer-block,
    .luzori-dashboard .lz-shimmer-line {
        background: linear-gradient(90deg, #efe8de 25%, #fffdf9 37%, #efe8de 63%);
        background-size: 400% 100%;
        animation: lz-shimmer 1.15s ease infinite;
    }

    .luzori-dashboard .lz-kpi--dark .lz-shimmer {
        background: linear-gradient(90deg, rgba(255,255,255,.14) 25%, rgba(255,255,255,.32) 37%, rgba(255,255,255,.14) 63%);
        background-size: 400% 100%;
    }

    .luzori-dashboard .lz-shimmer {
        display: inline-block;
        width: 5.2rem;
        height: .85em;
        border-radius: 8px;
        vertical-align: middle;
    }

    .luzori-dashboard .lz-shimmer--short {
        width: 2.4rem;
    }

    .luzori-dashboard .lz-shimmer-block {
        height: 280px;
        border-radius: 14px;
    }

    .luzori-dashboard .lz-shimmer-block--donut {
        height: 220px;
    }

    .luzori-dashboard .lz-shimmer-line {
        height: 12px;
        border-radius: 999px;
        margin-bottom: .7rem;
    }

    .luzori-dashboard .lz-shimmer-line:last-child {
        width: 70%;
        margin-bottom: 0;
    }

    @keyframes lz-shimmer {
        0% { background-position: 100% 0; }
        100% { background-position: 0 0; }
    }
</style>
@endpush

@php
    $salesPeriod = $salesPeriod ?? 'day';
    $userName = auth('center_user')->user()->name ?? '';
    $hour = (int) now()->format('H');
    if ($hour < 12) {
        $greeting = __('field.good_morning');
    } elseif ($hour < 18) {
        $greeting = __('field.good_afternoon');
    } else {
        $greeting = __('field.good_evening');
    }
@endphp

<div class="luzori-dashboard" data-stats-url="{{ route('center_user.cp.stats') }}" data-period="{{ $salesPeriod }}">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <h1 class="lz-greeting">{{ $greeting }}, {{ $userName }}</h1>
            <p class="lz-greeting-sub">{{ __('field.dashboard_subtitle') }}</p>
        </div>
        <div class="lz-date-chip">
            <i class="ti ti-calendar-event"></i>
            <span>{{ __('field.today') }}, {{ now()->translatedFormat('d M Y') }}</span>
        </div>
    </div>

    {{-- Primary KPIs --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="lz-kpi lz-kpi--dark clickable-stat-card" data-type="bookings">
                <div class="lz-kpi-top">
                    <div class="lz-kpi-icon"><i class="ti ti-calendar-event"></i></div>
                    <div>
                        <p class="lz-kpi-label">{{ __('field.today_bookings') }}</p>
                        <p class="lz-kpi-value" id="lz-kpi-bookings"><span class="lz-shimmer"></span></p>
                    </div>
                </div>
                <span class="lz-kpi-arrow"><i class="ti ti-arrow-up-right"></i></span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="lz-kpi lz-kpi--light clickable-stat-card" data-type="customers">
                <div class="lz-kpi-top">
                    <div class="lz-kpi-icon"><i class="ti ti-users"></i></div>
                    <div>
                        <p class="lz-kpi-label">{{ __('field.customers') }}</p>
                        <p class="lz-kpi-value" id="lz-kpi-customers"><span class="lz-shimmer"></span></p>
                    </div>
                </div>
                <span class="lz-kpi-arrow"><i class="ti ti-arrow-up-right"></i></span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="lz-kpi lz-kpi--light clickable-stat-card" data-type="services">
                <div class="lz-kpi-top">
                    <div class="lz-kpi-icon"><i class="ti ti-scissors"></i></div>
                    <div>
                        <p class="lz-kpi-label">{{ __('field.services') }}</p>
                        <p class="lz-kpi-value" id="lz-kpi-services"><span class="lz-shimmer"></span></p>
                    </div>
                </div>
                <span class="lz-kpi-arrow"><i class="ti ti-arrow-up-right"></i></span>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="lz-kpi lz-kpi--dark clickable-stat-card" data-type="revenue">
                <div class="lz-kpi-top">
                    <div class="lz-kpi-icon"><i class="ti ti-coin"></i></div>
                    <div>
                        <p class="lz-kpi-label">{{ __('field.today_revenue') }}</p>
                        <p class="lz-kpi-value" id="lz-kpi-revenue"><span class="lz-shimmer"></span></p>
                    </div>
                </div>
                <span class="lz-kpi-arrow"><i class="ti ti-arrow-up-right"></i></span>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-lg-5">
            <div class="card lz-panel">
                <div class="card-header">
                    <h5 class="lz-panel-title">{{ __('field.most_popular_services') }}</h5>
                </div>
                <div class="card-body">
                    <div id="popularServicesChart" class="lz-shimmer-block"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-5 col-lg-7">
            <div class="card lz-panel">
                <div class="card-header">
                    <h5 class="lz-panel-title">{{ __('field.most_revenue_trends') }}</h5>
                </div>
                <div class="card-body">
                    <div id="revenueTrendsChart" class="lz-shimmer-block"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-12">
            <div class="card lz-panel">
                <div class="card-header">
                    <h5 class="lz-panel-title">{{ __('field.earnings_per_week') }}</h5>
                </div>
                <div class="card-body">
                    <div id="earningsWeekChart" class="lz-shimmer-block lz-shimmer-block--donut"></div>
                    <div class="lz-earnings-legend">
                        <div class="lz-earnings-legend-item">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <span class="d-flex align-items-center gap-2">
                                    <span class="lz-earnings-dot" style="background:#8a9a96"></span>
                                    {{ __('api.outside_booking') }}
                                </span>
                                <span id="lz-earnings-outside-pct"><span class="lz-shimmer lz-shimmer--short"></span></span>
                            </div>
                            <div class="lz-earnings-bar"><span id="lz-earnings-outside-bar" style="width: 0%; background:#8a9a96"></span></div>
                        </div>
                        <div class="lz-earnings-legend-item">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <span class="d-flex align-items-center gap-2">
                                    <span class="lz-earnings-dot" style="background:#002b26"></span>
                                    {{ __('api.inside_booking') }}
                                </span>
                                <span id="lz-earnings-inside-pct"><span class="lz-shimmer lz-shimmer--short"></span></span>
                            </div>
                            <div class="lz-earnings-bar"><span id="lz-earnings-inside-bar" style="width: 0%"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Latest Sales + Top Performers + Promo --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-8">
            <div class="card lz-panel">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="lz-panel-title">{{ __('field.latest_sales') }}</h5>
                    <div class="d-flex gap-2 align-items-center mb-0" id="lz-sales-filters">
                        <button type="button" data-period="day"
                            class="lz-filter-btn {{ $salesPeriod === 'day' ? 'active' : '' }}">{{ __('field.day') }}</button>
                        <button type="button" data-period="week"
                            class="lz-filter-btn {{ $salesPeriod === 'week' ? 'active' : '' }}">{{ __('field.week') }}</button>
                        <button type="button" data-period="month"
                            class="lz-filter-btn {{ $salesPeriod === 'month' ? 'active' : '' }}">{{ __('field.month') }}</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table lz-sales-table mb-0">
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
                            <tbody id="lz-sales-body">
                                @for ($i = 0; $i < 4; $i++)
                                    <tr>
                                        <td colspan="7"><span class="lz-shimmer-line"></span></td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card lz-panel mb-3">
                <div class="card-header">
                    <h5 class="lz-panel-title">{{ __('field.top_performers') }}</h5>
                    <p class="text-muted mb-0 mt-1" style="font-size:.78rem">{{ __('field.bookings_this_month') }}</p>
                </div>
                <div class="card-body" id="lz-performers">
                    @for ($i = 0; $i < 3; $i++)
                        <div class="lz-shimmer-line"></div>
                        <div class="lz-shimmer-line"></div>
                    @endfor
                </div>
            </div>

            <div class="lz-promo">
                <div class="lz-promo-eyebrow">{{ __('field.luxury_care') }}</div>
                <h3 class="lz-promo-title">{{ __('field.more_bookings_more_growth') }}</h3>
                <a href="{{ route('center_user.settings.index') }}" class="lz-promo-btn">
                    {{ __('field.explore_features') }}
                    <i class="ti ti-arrow-right"></i>
                </a>
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
    const forest = '#002b26';
    const gold = '#c5a070';
    const sage = '#7d9b8f';
    const tan = '#d4b483';
    const cream = '#e8d5b5';
    const isDarkStyle = document.documentElement.classList.contains('dark-style');
    const chartLabelColor = isDarkStyle ? '#e4e6e8' : '#3d4f4c';

    const root = document.querySelector('.luzori-dashboard');
    const labels = {
        noData: @json(__('general.no_data')),
        bookingsCount: @json(__('field.bookings_count')),
        loadError: @json(__('general.error')),
        services: @json(__('field.services')),
        revenue: @json(__('field.today_revenue')),
        total: @json(__('field.total')),
        outside: @json(__('api.outside_booking')),
        inside: @json(__('api.inside_booking'))
    };

    function clearChart(selector) {
        const el = document.querySelector(selector);
        if (!el) return null;
        el.classList.remove('lz-shimmer-block', 'lz-shimmer-block--donut');
        el.replaceChildren();
        return el;
    }

    function salesShimmer() {
        const body = document.querySelector('#lz-sales-body');
        if (!body) return;
        body.replaceChildren();
        for (let i = 0; i < 4; i++) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 7;
            const line = document.createElement('span');
            line.className = 'lz-shimmer-line';
            td.appendChild(line);
            tr.appendChild(td);
            body.appendChild(tr);
        }
    }

    function fillSales(rows) {
        const body = document.querySelector('#lz-sales-body');
        if (!body) return;
        body.replaceChildren();
        if (!rows.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 7;
            td.className = 'text-center text-muted py-4';
            td.textContent = labels.noData;
            tr.appendChild(td);
            body.appendChild(tr);
            return;
        }

        rows.forEach(function (sale) {
            const tr = document.createElement('tr');
            const service = document.createElement('td');
            const cell = document.createElement('div');
            cell.className = 'service-cell';
            const img = document.createElement('img');
            img.src = sale.service_image || '';
            img.alt = '';
            img.className = 'service-thumb';
            const name = document.createElement('span');
            name.textContent = sale.service_name || '';
            cell.append(img, name);
            service.appendChild(cell);

            const customer = document.createElement('td');
            const customerName = document.createElement('div');
            customerName.textContent = sale.customer_name || '';
            customer.appendChild(customerName);
            if (sale.customer_email) {
                const email = document.createElement('div');
                email.className = 'customer-email';
                email.textContent = sale.customer_email;
                customer.appendChild(email);
            }

            const phone = document.createElement('td');
            phone.textContent = sale.phone || '';
            const worker = document.createElement('td');
            worker.textContent = sale.worker || '';
            const payment = document.createElement('td');
            payment.textContent = sale.payment_label || '';
            const total = document.createElement('td');
            total.textContent = sale.total_display || '';
            const status = document.createElement('td');
            const badge = document.createElement('span');
            badge.className = 'lz-status-badge lz-status-badge--' + (sale.status_class || 'confirmed');
            badge.textContent = sale.status_label || '';
            status.appendChild(badge);

            tr.append(service, customer, phone, worker, payment, total, status);
            body.appendChild(tr);
        });
    }

    function fillPerformers(stats) {
        const box = document.querySelector('#lz-performers');
        if (!box) return;
        box.replaceChildren();
        if (!stats.length) {
            const empty = document.createElement('p');
            empty.className = 'text-muted text-center py-4 mb-0';
            empty.textContent = labels.noData;
            box.appendChild(empty);
            return;
        }

        stats.forEach(function (stat, index) {
            const row = document.createElement('div');
            row.className = 'lz-performer-row';
            const rank = document.createElement('div');
            rank.className = 'lz-rank';
            rank.textContent = String(index + 1);
            const avatar = document.createElement('div');
            avatar.className = 'lz-performer-avatar';
            avatar.textContent = String(stat.label || 'N').charAt(0).toLocaleUpperCase();
            const meta = document.createElement('div');
            meta.className = 'lz-performer-meta';
            const title = document.createElement('p');
            title.className = 'lz-performer-name';
            title.textContent = stat.label || '';
            const sub = document.createElement('p');
            sub.className = 'lz-performer-sub';
            sub.textContent = (stat.value || 0) + ' ' + labels.bookingsCount + ' · ' + (stat.subtitle || '');
            const progress = document.createElement('div');
            progress.className = 'lz-progress';
            const bar = document.createElement('span');
            bar.style.width = (stat.pct || 0) + '%';
            progress.appendChild(bar);
            meta.append(title, sub, progress);
            row.append(rank, avatar, meta);
            box.appendChild(row);
        });
    }

    function renderCharts(data) {
        const popularServices = data.popular_services || [];
        const revenueTrends = data.revenue_trends || [];
        const earningsWeek = data.earnings_per_week || {};
        const currency = data.currency || '';
        const popularEl = clearChart('#popularServicesChart');
        const revenueEl = clearChart('#revenueTrendsChart');
        const earningsEl = clearChart('#earningsWeekChart');

        if (popularServices.length && popularEl) {
            const palette = [forest, sage, tan, cream, '#5f7a72', '#b8956a'];
            new ApexCharts(popularEl, {
                chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'DM Sans, Cairo, sans-serif' },
                plotOptions: { bar: { borderRadius: 10, columnWidth: '45%', distributed: true } },
                colors: popularServices.map((_, i) => palette[i % palette.length]),
                dataLabels: { enabled: false },
                series: [{ name: labels.services, data: popularServices.map(s => s.count) }],
                xaxis: {
                    categories: popularServices.map(s => s.name),
                    labels: { style: { fontSize: '11px', colors: chartLabelColor }, rotate: -30, trim: true },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: { labels: { style: { fontSize: '11px', colors: chartLabelColor } } },
                grid: { borderColor: '#f1ebe3', strokeDashArray: 4 },
                legend: { show: false },
                tooltip: { theme: 'light' }
            }).render();
        } else if (popularEl) {
            const empty = document.createElement('p');
            empty.className = 'text-muted text-center py-5';
            empty.textContent = labels.noData;
            popularEl.appendChild(empty);
        }

        if (revenueTrends.length && revenueEl) {
            new ApexCharts(revenueEl, {
                chart: { type: 'area', height: 280, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'DM Sans, Cairo, sans-serif' },
                colors: [forest],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: .4,
                        opacityTo: .05,
                        stops: [0, 90, 100],
                        colorStops: [
                            { offset: 0, color: '#7d9b8f', opacity: .45 },
                            { offset: 100, color: '#f9f6f1', opacity: .05 }
                        ]
                    }
                },
                stroke: { curve: 'smooth', width: 2.5 },
                dataLabels: { enabled: false },
                series: [{ name: labels.revenue, data: revenueTrends.map(r => r.revenue) }],
                xaxis: {
                    categories: revenueTrends.map(r => r.day),
                    labels: { style: { fontSize: '10px', colors: chartLabelColor } },
                    tickAmount: 10,
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        style: { fontSize: '10px', colors: chartLabelColor },
                        formatter: val => Math.round(val).toLocaleString()
                    }
                },
                grid: { borderColor: '#f1ebe3', strokeDashArray: 4 },
                tooltip: { y: { formatter: val => val.toLocaleString() + ' ' + currency } }
            }).render();
        }

        if (earningsEl) {
            const insidePct = earningsWeek.inside_pct || 0;
            const outsidePct = earningsWeek.outside_pct || 0;
            document.querySelector('#lz-earnings-outside-pct').textContent = outsidePct + '%';
            document.querySelector('#lz-earnings-inside-pct').textContent = insidePct + '%';
            document.querySelector('#lz-earnings-outside-bar').style.width = outsidePct + '%';
            document.querySelector('#lz-earnings-inside-bar').style.width = insidePct + '%';

            new ApexCharts(earningsEl, {
                chart: { type: 'donut', height: 220, fontFamily: 'DM Sans, Cairo, sans-serif' },
                series: [outsidePct || 0.001, insidePct || 0.001],
                labels: [labels.outside, labels.inside],
                colors: ['#8a9a96', forest],
                stroke: { width: 0 },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '12px', color: chartLabelColor, offsetY: 18 },
                                value: {
                                    show: true,
                                    fontSize: '15px',
                                    fontWeight: 700,
                                    color: forest,
                                    offsetY: -12,
                                    formatter: () => (earningsWeek.all || 0).toLocaleString()
                                },
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: labels.total,
                                    fontSize: '12px',
                                    color: chartLabelColor,
                                    formatter: () => (earningsWeek.all || 0).toLocaleString() + ' ' + currency
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { show: false },
                tooltip: { y: { formatter: val => val + '%' } }
            }).render();
        }
    }

    function applyStats(data) {
        const stats = data.statistics || {};
        document.querySelector('#lz-kpi-bookings').textContent = stats.today_bookings_count || '0';
        document.querySelector('#lz-kpi-customers').textContent = stats.customers_count || '0';
        document.querySelector('#lz-kpi-services').textContent = stats.services_count || '0';
        document.querySelector('#lz-kpi-revenue').textContent = stats.today_revenue || '0';
        fillSales(data.latest_sales || []);
        fillPerformers(data.rating_stats || []);
        renderCharts(data);
    }

    function showLoadError() {
        ['#lz-kpi-bookings', '#lz-kpi-customers', '#lz-kpi-services', '#lz-kpi-revenue'].forEach(function (selector) {
            const el = document.querySelector(selector);
            if (el) el.textContent = labels.loadError;
        });
        clearChart('#popularServicesChart');
        clearChart('#revenueTrendsChart');
        clearChart('#earningsWeekChart');
        fillSales([]);
        fillPerformers([]);
    }

    function loadStats(onlySales) {
        if (!root) return;
        const url = new URL(root.dataset.statsUrl, window.location.origin);
        url.searchParams.set('period', root.dataset.period || 'day');
        if (onlySales) {
            url.searchParams.set('only', 'sales');
            salesShimmer();
        }

        fetch(url.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (response) {
            if (!response.ok) throw new Error('stats');
            return response.json();
        }).then(function (data) {
            if (onlySales) fillSales(data.latest_sales || []);
            else applyStats(data);
        }).catch(function () {
            if (onlySales) fillSales([]);
            else showLoadError();
        });
    }

    document.querySelectorAll('#lz-sales-filters [data-period]').forEach(function (button) {
        button.addEventListener('click', function () {
            root.dataset.period = button.dataset.period;
            document.querySelectorAll('#lz-sales-filters [data-period]').forEach(function (item) {
                item.classList.toggle('active', item === button);
            });
            loadStats(true);
        });
    });

    loadStats(false);
});
</script>
@endpush
