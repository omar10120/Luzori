@extends('layouts/layoutMaster')

@section('title', __('admin.control_panel'))

@section('vendor-style')
    <style>
        .lz-admin-dash {
            --lz-forest: #002b26;
            --lz-forest-soft: #0a3a34;
            --lz-cream: #f9f6f1;
            --lz-cream-card: #fdfaf6;
            --lz-gold: #c5a070;
            --lz-gold-deep: #9a6923;
            --lz-mint: #d8f0e6;
            --lz-mint-text: #1a6b5a;
            --lz-peach: #f7e4d4;
            --lz-peach-text: #b06a2b;
            --lz-muted: #7a8582;
            --lz-text: #1a2a27;
            --lz-radius: 18px;
            --lz-shadow: 0 10px 28px rgba(0, 43, 38, 0.07);
        }

        .lz-admin-dash .lz-hero {
            border: 0;
            border-radius: var(--lz-radius);
            background:
                linear-gradient(135deg, rgba(0, 43, 38, 0.97) 0%, rgba(10, 58, 52, 0.94) 58%, rgba(154, 105, 35, 0.55) 100%);
            color: #fff;
            box-shadow: var(--lz-shadow);
            overflow: hidden;
            position: relative;
        }
        .lz-admin-dash .lz-hero::after {
            content: '';
            position: absolute;
            inset: auto -12% -45% auto;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(197, 160, 112, 0.35) 0%, transparent 70%);
            pointer-events: none;
        }
        .lz-admin-dash .lz-hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .lz-admin-dash .lz-hero-title {
            margin: 0.4rem 0 0.25rem;
            font-size: clamp(1.55rem, 2.4vw, 2rem);
            font-weight: 800;
            line-height: 1.2;
        }
        .lz-admin-dash .lz-hero-sub {
            margin: 0;
            opacity: 0.88;
            font-size: 0.92rem;
        }

        .lz-admin-dash .lz-stat-card {
            border: 0;
            border-radius: var(--lz-radius);
            background: var(--lz-cream-card);
            box-shadow: var(--lz-shadow);
            height: 100%;
            overflow: hidden;
            position: relative;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .lz-admin-dash .lz-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 32px rgba(0, 43, 38, 0.12);
        }
        .lz-admin-dash .lz-stat-card::before {
            content: '';
            position: absolute;
            inset-inline-start: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--lz-gold);
        }
        .lz-admin-dash .lz-stat-card.is-products::before { background: #c5a070; }
        .lz-admin-dash .lz-stat-card.is-services::before { background: #1a6b5a; }
        .lz-admin-dash .lz-stat-card.is-users::before { background: #0a3a34; }
        .lz-admin-dash .lz-stat-card.is-centers::before { background: #9a6923; }

        .lz-admin-dash .lz-stat-body {
            padding: 1.35rem 1.4rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .lz-admin-dash .lz-stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1.35rem;
        }
        .lz-admin-dash .lz-stat-card.is-products .lz-stat-icon {
            background: var(--lz-peach);
            color: var(--lz-peach-text);
        }
        .lz-admin-dash .lz-stat-card.is-services .lz-stat-icon {
            background: var(--lz-mint);
            color: var(--lz-mint-text);
        }
        .lz-admin-dash .lz-stat-card.is-users .lz-stat-icon {
            background: #eef3f2;
            color: var(--lz-forest);
        }
        .lz-admin-dash .lz-stat-card.is-centers .lz-stat-icon {
            background: #f3ebe0;
            color: var(--lz-gold-deep);
        }
        .lz-admin-dash .lz-stat-label {
            margin: 0;
            color: var(--lz-muted);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }
        .lz-admin-dash .lz-stat-value {
            margin: 0.15rem 0 0;
            color: var(--lz-forest);
            font-size: 1.85rem;
            font-weight: 800;
            line-height: 1.1;
        }

        .lz-admin-dash .lz-panel {
            border: 0;
            border-radius: var(--lz-radius);
            background: var(--lz-cream-card);
            box-shadow: var(--lz-shadow);
        }
        .lz-admin-dash .lz-panel-head {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 1.15rem 1.35rem 0.35rem;
            color: var(--lz-forest);
            font-weight: 700;
            font-size: 1rem;
        }
        .lz-admin-dash .lz-panel-head i { color: var(--lz-gold-deep); }
        .lz-admin-dash .lz-panel-body { padding: 0.65rem 1.35rem 1.35rem; }

        .lz-admin-dash .lz-chart-box {
            min-height: 300px;
        }

        html.dark-style .lz-admin-dash .lz-stat-value,
        html.dark-style .lz-admin-dash .lz-panel-head {
            color: #e8d5b5 !important;
        }
        html.dark-style .lz-admin-dash .lz-stat-label {
            color: #a8b0bf !important;
        }
        html.dark-style .lz-admin-dash .lz-stat-card.is-users .lz-stat-icon {
            background: #252b3d !important;
            color: #e8d5b5 !important;
        }
        html.dark-style .lz-admin-dash .lz-stat-card.is-services .lz-stat-icon {
            background: #1e3d36 !important;
            color: #8fd4c0 !important;
        }
        html.dark-style .lz-admin-dash .lz-stat-card.is-centers .lz-stat-icon {
            background: #2a3144 !important;
            color: #d4b483 !important;
        }
        html.dark-style .lz-admin-dash .lz-panel,
        html.dark-style .lz-admin-dash .lz-stat-card {
            background: #1c2233 !important;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page luzori-dashboard lz-admin-dash">
        <div class="card lz-hero mb-3">
            <div class="card-body p-4 position-relative" style="z-index:1;">
                <div class="lz-hero-eyebrow">
                    <i class="ti ti-layout-dashboard"></i>
                    <span>{{ __('locale.statistics') }}</span>
                </div>
                <h1 class="lz-hero-title">{{ __('admin.control_panel') }}</h1>
                <p class="lz-hero-sub">{{ __('general.home') }} · Luzori Admin</p>
            </div>
        </div>

        <div class="card lz-panel mb-3">
            <div class="lz-panel-head">
                <i class="ti ti-chart-bar"></i>
                <span>{{ __('locale.statistics') }}</span>
            </div>
            <div class="lz-panel-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card lz-stat-card is-products">
                            <div class="lz-stat-body">
                                <div class="lz-stat-icon">
                                    <i class="ti ti-package"></i>
                                </div>
                                <div>
                                    <p class="lz-stat-label">{{ __('locale.products') }}</p>
                                    <p class="lz-stat-value">{{ number_format($productsCount) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card lz-stat-card is-services">
                            <div class="lz-stat-body">
                                <div class="lz-stat-icon">
                                    <i class="ti ti-scissors"></i>
                                </div>
                                <div>
                                    <p class="lz-stat-label">{{ __('locale.services') }}</p>
                                    <p class="lz-stat-value">{{ number_format($servicesCount) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card lz-stat-card is-users">
                            <div class="lz-stat-body">
                                <div class="lz-stat-icon">
                                    <i class="ti ti-users"></i>
                                </div>
                                <div>
                                    <p class="lz-stat-label">{{ __('locale.users') }}</p>
                                    <p class="lz-stat-value">{{ number_format($usersCount) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="card lz-stat-card is-centers">
                            <div class="lz-stat-body">
                                <div class="lz-stat-icon">
                                    <i class="ti ti-building-store"></i>
                                </div>
                                <div>
                                    <p class="lz-stat-label">{{ __('locale.centers') }}</p>
                                    <p class="lz-stat-value">{{ number_format($centersCount) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card lz-panel h-100">
                    <div class="lz-panel-head">
                        <i class="ti ti-chart-donut-3"></i>
                        <span>{{ __('locale.statistics') }} — {{ __('general.overview') }}</span>
                    </div>
                    <div class="lz-panel-body">
                        <div id="lzAdminOverviewDonut" class="lz-chart-box"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card lz-panel h-100">
                    <div class="lz-panel-head">
                        <i class="ti ti-chart-area-line"></i>
                        <span>{{ __('locale.users') }} / {{ __('locale.centers') }}</span>
                    </div>
                    <div class="lz-panel-body">
                        <div id="lzAdminGrowthChart" class="lz-chart-box"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        (function () {
            const isDark = document.documentElement.classList.contains('dark-style');
            const labelColor = isDark ? '#e8d5b5' : '#002b26';
            const mutedColor = isDark ? '#a8b0bf' : '#7a8582';
            const gridColor = isDark ? '#2e364a' : '#efe6d8';

            const labels = {
                products: @json(__('locale.products')),
                services: @json(__('locale.services')),
                users: @json(__('locale.users')),
                centers: @json(__('locale.centers')),
            };

            const counts = {
                products: {{ (int) $productsCount }},
                services: {{ (int) $servicesCount }},
                users: {{ (int) $usersCount }},
                centers: {{ (int) $centersCount }},
            };

            const months = @json($chartMonths);
            const usersTrend = @json($usersTrend);
            const centersTrend = @json($centersTrend);

            const donutEl = document.querySelector('#lzAdminOverviewDonut');
            if (donutEl && typeof ApexCharts !== 'undefined') {
                new ApexCharts(donutEl, {
                    chart: {
                        type: 'donut',
                        height: 300,
                        fontFamily: 'inherit',
                        toolbar: { show: false },
                    },
                    labels: [labels.products, labels.services, labels.users, labels.centers],
                    series: [counts.products, counts.services, counts.users, counts.centers],
                    colors: ['#c5a070', '#1a6b5a', '#0a3a34', '#9a6923'],
                    legend: {
                        position: 'bottom',
                        labels: { colors: mutedColor },
                    },
                    dataLabels: {
                        enabled: true,
                        style: { colors: ['#fff'] },
                    },
                    stroke: { width: 2, colors: [isDark ? '#1c2233' : '#fdfaf6'] },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '62%',
                                labels: {
                                    show: true,
                                    name: { color: mutedColor },
                                    value: {
                                        color: labelColor,
                                        fontWeight: 700,
                                    },
                                    total: {
                                        show: true,
                                        label: @json(__('locale.statistics')),
                                        color: mutedColor,
                                        formatter: function (w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        },
                                    },
                                },
                            },
                        },
                    },
                }).render();
            }

            const growthEl = document.querySelector('#lzAdminGrowthChart');
            if (growthEl && typeof ApexCharts !== 'undefined') {
                new ApexCharts(growthEl, {
                    chart: {
                        type: 'area',
                        height: 300,
                        fontFamily: 'inherit',
                        toolbar: { show: false },
                        zoom: { enabled: false },
                    },
                    series: [
                        { name: labels.users, data: usersTrend },
                        { name: labels.centers, data: centersTrend },
                    ],
                    colors: ['#c5a070', '#1a6b5a'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.35,
                            opacityTo: 0.05,
                            stops: [0, 90, 100],
                        },
                    },
                    xaxis: {
                        categories: months,
                        labels: { style: { colors: mutedColor } },
                        axisBorder: { color: gridColor },
                        axisTicks: { color: gridColor },
                    },
                    yaxis: {
                        labels: { style: { colors: mutedColor } },
                        min: 0,
                        forceNiceScale: true,
                    },
                    grid: {
                        borderColor: gridColor,
                        strokeDashArray: 4,
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'end',
                        labels: { colors: mutedColor },
                    },
                    tooltip: { theme: isDark ? 'dark' : 'light' },
                }).render();
            }
        })();
    </script>
@endsection
