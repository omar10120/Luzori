@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-sale-show {
            --lz-forest: #002b26;
            --lz-forest-mid: #0a3a34;
            --lz-cream: #fdfaf6;
            --lz-cream-deep: #f3ebe0;
            --lz-gold: #c5a070;
            --lz-gold-deep: #9a6923;
            --lz-muted: #7a8582;
        }

        .lz-sale-show .lz-sale-hero {
            border: 0;
            border-radius: 18px;
            background:
                linear-gradient(135deg, rgba(0, 43, 38, 0.96) 0%, rgba(10, 58, 52, 0.92) 55%, rgba(154, 105, 35, 0.55) 100%);
            color: #fff;
            box-shadow: 0 10px 28px rgba(0, 43, 38, 0.14);
            overflow: hidden;
            position: relative;
        }
        .lz-sale-show .lz-sale-hero::after {
            content: '';
            position: absolute;
            inset: auto -20% -40% auto;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(197, 160, 112, 0.35) 0%, transparent 70%);
            pointer-events: none;
        }
        .lz-sale-show .lz-sale-hero-ref {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            opacity: 0.9;
        }
        .lz-sale-show .lz-sale-hero-total {
            font-size: clamp(1.6rem, 2.5vw, 2.1rem);
            font-weight: 800;
            line-height: 1.15;
            margin: 0.35rem 0 0;
        }
        .lz-sale-show .lz-sale-hero-meta {
            margin: 0.35rem 0 0;
            font-size: 0.92rem;
            opacity: 0.88;
        }

        .lz-sale-show .lz-panel {
            border: 0;
            border-radius: 18px;
            background: var(--lz-cream);
            box-shadow: 0 8px 28px rgba(0, 43, 38, 0.07);
            height: 100%;
        }
        .lz-sale-show .lz-panel-head {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 1.1rem 1.35rem 0.35rem;
            color: var(--lz-forest);
            font-weight: 700;
            font-size: 1rem;
        }
        .lz-sale-show .lz-panel-head i {
            color: var(--lz-gold-deep);
            font-size: 1.15rem;
        }
        .lz-sale-show .lz-panel-body {
            padding: 0.75rem 1.35rem 1.35rem;
        }

        .lz-sale-show .lz-kv {
            display: grid;
            gap: 0.9rem;
        }
        .lz-sale-show .lz-kv-row {
            display: grid;
            grid-template-columns: minmax(110px, 34%) 1fr;
            gap: 0.75rem;
            align-items: start;
            padding-bottom: 0.85rem;
            border-bottom: 1px solid rgba(197, 160, 112, 0.28);
        }
        .lz-sale-show .lz-kv-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }
        .lz-sale-show .lz-kv-label {
            color: var(--lz-muted);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            padding-top: 0.2rem;
        }
        .lz-sale-show .lz-kv-value {
            color: var(--lz-forest);
            font-size: 0.98rem;
            font-weight: 600;
            word-break: break-word;
        }
        .lz-sale-show .lz-kv-value.is-muted {
            color: #9aa3a0;
            font-weight: 500;
        }

        .lz-sale-show .lz-date-edit {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.55rem;
        }
        .lz-sale-show .lz-date-edit .form-control {
            max-width: 160px;
            border: 1px solid var(--lz-gold);
            border-radius: 10px;
            background: #fff;
            color: var(--lz-forest);
            height: 36px;
            font-size: 0.875rem;
        }
        .lz-sale-show .lz-date-edit .form-control:focus {
            border-color: var(--lz-gold-deep);
            box-shadow: 0 0 0 0.2rem rgba(197, 160, 112, 0.2);
        }
        .lz-sale-show .btn-lz-date {
            border: 1px solid var(--lz-forest-mid);
            color: var(--lz-forest-mid);
            background: #fff;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.82rem;
            padding: 0.35rem 0.85rem;
            height: 36px;
        }
        .lz-sale-show .btn-lz-date:hover {
            background: var(--lz-cream-deep);
            border-color: var(--lz-gold-deep);
            color: var(--lz-forest);
        }

        .lz-sale-show .lz-finance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 0.55rem 0;
            color: var(--lz-forest);
            font-size: 0.95rem;
        }
        .lz-sale-show .lz-finance-row span:first-child {
            color: var(--lz-muted);
            font-weight: 600;
        }
        .lz-sale-show .lz-finance-row span:last-child {
            font-weight: 700;
        }
        .lz-sale-show .lz-finance-tip span:last-child {
            color: var(--lz-gold-deep);
        }
        .lz-sale-show .lz-finance-total {
            margin-top: 0.55rem;
            padding-top: 0.9rem;
            border-top: 1px solid rgba(197, 160, 112, 0.35);
        }
        .lz-sale-show .lz-finance-total span:first-child {
            color: var(--lz-forest);
            font-size: 1.05rem;
            font-weight: 800;
        }
        .lz-sale-show .lz-finance-total span:last-child {
            color: var(--lz-forest-mid);
            font-size: 1.25rem;
            font-weight: 800;
        }

        .lz-sale-show .lz-items-card {
            border: 0;
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 8px 28px rgba(0, 43, 38, 0.07);
            overflow: hidden;
        }
        .lz-sale-show .lz-items-card .lz-panel-head {
            padding: 1.15rem 1.35rem 0.85rem;
            border-bottom: 1px solid rgba(197, 160, 112, 0.22);
            background: var(--lz-cream);
            margin: 0;
        }
        .lz-sale-show .lz-items-table {
            margin: 0;
        }
        .lz-sale-show .lz-items-table thead th {
            background: #faf7f2;
            color: var(--lz-muted);
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(197, 160, 112, 0.28);
            white-space: nowrap;
            padding: 0.85rem 1rem;
        }
        .lz-sale-show .lz-items-table tbody td {
            border-color: rgba(197, 160, 112, 0.18);
            color: var(--lz-forest);
            vertical-align: middle;
            padding: 0.95rem 1rem;
        }
        .lz-sale-show .lz-items-table tbody tr:hover {
            background: #faf7f2;
        }
        .lz-sale-show .lz-item-name {
            font-weight: 650;
            color: var(--lz-forest);
        }
        .lz-sale-show .lz-item-sub {
            display: block;
            margin-top: 0.2rem;
            color: var(--lz-muted);
            font-size: 0.82rem;
        }
        .lz-sale-show .lz-item-free {
            display: inline-block;
            margin-top: 0.25rem;
            color: #1a6b5a;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .lz-sale-show .lz-type-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .lz-sale-show .lz-type-badge.is-booking { background: #f7e4d4; color: #b06a2b; }
        .lz-sale-show .lz-type-badge.is-package { background: #eef3f2; color: #0a3a34; }
        .lz-sale-show .lz-type-badge.is-product { background: #e6f3ee; color: #1a6b5a; }

        .lz-sale-show .btn-lz-danger {
            background: #c0392b;
            border-color: #c0392b;
            color: #fff;
            border-radius: 10px;
            font-weight: 600;
        }
        .lz-sale-show .btn-lz-danger:hover {
            background: #a93226;
            border-color: #a93226;
            color: #fff;
        }

        @media (max-width: 575.98px) {
            .lz-sale-show .lz-kv-row {
                grid-template-columns: 1fr;
                gap: 0.25rem;
            }
        }
    </style>
@endsection

@section('content')
    @php
        $saleDateDisplay = is_string($sale->created_at)
            ? substr($sale->created_at, 0, 16)
            : ($sale->created_at ? $sale->created_at->format('Y-m-d H:i') : '-');
        $saleDateInput = is_string($sale->created_at)
            ? substr($sale->created_at, 0, 10)
            : ($sale->created_at ? $sale->created_at->format('Y-m-d') : '');
        $saleDisplay = $saleDisplay ?? [];
        $currency = $saleDisplay['currency'] ?? trim((string) get_currency());
        $canUpdateSale = auth('center_user')->user()->can('UPDATE_SALES', 'center_api')
            || auth('center_user')->user()->can('SHOW_SALES', 'center_api');
        $canDeleteSale = auth('center_user')->user()->can('DELETE_SALES', 'center_api');
        $displayWorker = $saleDisplay['worker'] ?? ($sale->worker?->name ?? '-');
        $displayPayment = $saleDisplay['payment'] ?? '-';
        $displayBranch = $saleDisplay['branch'] ?? '-';
        $displayPhone = $saleDisplay['phone'] ?? '-';
        $displayStatus = $saleDisplay['status_label'] ?? '-';
        $displayTipTotal = (float) ($saleDisplay['tip_total'] ?? $sale->tip ?? 0);
        $displayTipLabel = $saleDisplay['tip_label'] ?? __('field.tip');
        $bookingRows = $saleDisplay['booking_rows'] ?? [];
        $bookingEmployees = $saleDisplay['booking_employees'] ?? [];
        $displayNotes = $saleDisplay['notes'] ?? '';
    @endphp

    <div class="luzori-page lz-sale-show">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-receipt"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
            <div class="lz-page-actions">
                <a href="{{ route('center_user.sales.index') }}" class="btn btn-luzori-muted btn-sm px-3 py-2">
                    <i class="ti ti-arrow-left me-1"></i>{{ __('general.back') }}
                </a>
                <a href="{{ route('center_user.sales.print', $sale->id) }}" class="btn btn-luzori-primary btn-sm px-3 py-2">
                    <i class="ti ti-printer me-1"></i>
                    {{ __('general.print') }}
                </a>
                @if($canDeleteSale)
                    <button type="button" class="btn btn-lz-danger btn-sm px-3 py-2"
                        onclick="saleOtpStart('delete', {{ $sale->id }})">
                        <i class="ti ti-trash me-1"></i>
                        {{ __('general.delete') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="card lz-sale-hero mb-3">
            <div class="card-body p-4 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 position-relative" style="z-index:1;">
                    <div>
                        <div class="lz-sale-hero-ref">
                            <i class="ti ti-hash"></i>
                            <span>{{ __('field.sale_id') }} #{{ $sale->id }}</span>
                        </div>
                        <p class="lz-sale-hero-meta mb-0">
                            <i class="ti ti-calendar-event me-1"></i>{{ $saleDateDisplay }}
                            <span class="mx-2 opacity-50">·</span>
                            <i class="ti ti-user me-1"></i>{{ $sale->client?->name ?? __('general.walk_in') }}
                        </p>
                    </div>
                    <div class="text-md-end">
                        <div class="lz-sale-hero-ref justify-content-md-end">{{ __('field.total') }}</div>
                        <p class="lz-sale-hero-total">{{ number_format($sale->total, 2) }} {{ $currency }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-lg-6">
                <div class="card lz-panel">
                    <div class="lz-panel-head">
                        <i class="ti ti-info-circle"></i>
                        <span>{{ __('field.sale_information') }}</span>
                    </div>
                    <div class="lz-panel-body">
                        <div class="lz-kv">
                            <div class="lz-kv-row">
                                <div class="lz-kv-label">{{ __('field.sale_id') }}</div>
                                <div class="lz-kv-value">#{{ $sale->id }}</div>
                            </div>
                            <div class="lz-kv-row">
                                <div class="lz-kv-label">{{ __('field.date') }}</div>
                                <div class="lz-kv-value">
                                    <span id="saleShowDateDisplay">{{ $saleDateDisplay }}</span>
                                    @if($canUpdateSale)
                                        <div class="lz-date-edit">
                                            <input type="date" class="form-control form-control-sm" id="saleShowDateInput"
                                                value="{{ $saleDateInput }}">
                                            <button type="button" class="btn btn-sm btn-lz-date"
                                                onclick="saleOtpStart('edit', {{ $sale->id }}, document.getElementById('saleShowDateInput').value)">
                                                <i class="ti ti-calendar-check me-1"></i>{{ __('general.update_date') }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="lz-kv-row">
                                <div class="lz-kv-label">{{ __('field.branch') }}</div>
                                <div class="lz-kv-value {{ $displayBranch !== '-' ? '' : 'is-muted' }}">{{ $displayBranch }}</div>
                            </div>
                            <div class="lz-kv-row">
                                <div class="lz-kv-label">{{ __('field.worker') }}</div>
                                <div class="lz-kv-value {{ $displayWorker !== '-' ? '' : 'is-muted' }}">{{ $displayWorker }}</div>
                            </div>
                            <div class="lz-kv-row">
                                <div class="lz-kv-label">{{ __('field.client') }}</div>
                                <div class="lz-kv-value">{{ $sale->client?->name ?? __('general.walk_in') }}</div>
                            </div>
                            <div class="lz-kv-row">
                                <div class="lz-kv-label">{{ __('field.phone') }}</div>
                                <div class="lz-kv-value {{ $displayPhone !== '-' ? '' : 'is-muted' }}">{{ $displayPhone }}</div>
                            </div>
                            <div class="lz-kv-row">
                                <div class="lz-kv-label">{{ __('field.payment_method') }}</div>
                                <div class="lz-kv-value {{ $displayPayment !== '-' ? '' : 'is-muted' }}">{{ $displayPayment }}</div>
                            </div>
                            <div class="lz-kv-row">
                                <div class="lz-kv-label">{{ __('field.status') }}</div>
                                <div class="lz-kv-value {{ $displayStatus !== '-' ? '' : 'is-muted' }}">{{ $displayStatus }}</div>
                            </div>
                            @if(!empty($bookingEmployees))
                                <div class="lz-kv-row">
                                    <div class="lz-kv-label">{{ __('field.employee') }} ({{ __('locale.bookings') }})</div>
                                    <div class="lz-kv-value">{{ implode(', ', $bookingEmployees) }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card lz-panel">
                    <div class="lz-panel-head">
                        <i class="ti ti-report-money"></i>
                        <span>{{ __('field.financial_summary') }}</span>
                    </div>
                    <div class="lz-panel-body">
                        <div class="lz-finance-row">
                            <span>{{ __('field.subtotal') }}</span>
                            <span>{{ number_format($sale->subtotal, 2) }} {{ $currency }}</span>
                        </div>
                        <div class="lz-finance-row">
                            <span>{{ __('field.tax') }}</span>
                            <span>{{ number_format($sale->tax, 2) }} {{ $currency }}</span>
                        </div>
                        @if($displayTipTotal > 0)
                            <div class="lz-finance-row lz-finance-tip">
                                <span>{{ $displayTipLabel }}</span>
                                <span>{{ number_format($displayTipTotal, 2) }} {{ $currency }}</span>
                            </div>
                        @else
                            <div class="lz-finance-row">
                                <span>{{ __('field.tip') }}</span>
                                <span class="is-muted">-</span>
                            </div>
                        @endif
                        <div class="lz-finance-row lz-finance-total">
                            <span>{{ __('field.total') }}</span>
                            <span>{{ number_format($sale->total, 2) }} {{ $currency }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($bookingRows))
            <div class="card lz-items-card mb-3">
                <div class="lz-panel-head">
                    <i class="ti ti-calendar-event"></i>
                    <span>{{ __('field.booking_details') }}</span>
                </div>
                <div class="table-responsive">
                    <table class="table lz-items-table align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('field.service') }}</th>
                                <th>{{ __('field.employee') }}</th>
                                <th>{{ __('field.date') }}</th>
                                <th>{{ __('field.time') }}</th>
                                <th>{{ __('field.status') }}</th>
                                <th class="text-end">{{ __('field.price') }}</th>
                                <th class="text-end">{{ __('field.tip') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookingRows as $row)
                                <tr>
                                    <td>
                                        <span class="lz-item-name">{{ $row['service'] }}</span>
                                        @if(!empty($row['is_free']))
                                            <span class="lz-item-free">({{ __('field.covered_by_package') ?? 'Covered by Package' }})</span>
                                        @endif
                                        @if(!empty($row['payment']) && $row['payment'] !== '-')
                                            <span class="lz-item-sub">{{ __('field.payment_method') }}: {{ $row['payment'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $row['worker'] }}</td>
                                    <td>{{ $row['date'] }}</td>
                                    <td>{{ $row['time'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td class="text-end fw-semibold">{{ $row['price'] }}</td>
                                    <td class="text-end">{{ $row['tip'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="card lz-items-card">
            <div class="lz-panel-head">
                <i class="ti ti-list-details"></i>
                <span>{{ __('field.items') }}</span>
            </div>
            <div class="table-responsive">
                <table class="table lz-items-table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('field.type') }}</th>
                            <th>{{ __('field.item') }}</th>
                            <th>{{ __('field.employee') }}</th>
                            <th>{{ __('field.quantity') }}</th>
                            <th class="text-end">{{ __('field.price') }}</th>
                            <th class="text-end">{{ __('field.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleItems as $saleItem)
                            @php
                                $typeClass = $saleItem->item_type === 'booking'
                                    ? 'is-booking'
                                    : ($saleItem->item_type === 'user_package' ? 'is-package' : 'is-product');
                                $typeLabel = $saleItem->item_type === 'booking'
                                    ? __('locale.bookings')
                                    : ($saleItem->item_type === 'user_package' ? __('locale.packages') : __('locale.products'));
                                $itemEmployee = '-';
                            @endphp
                            <tr>
                                <td>
                                    <span class="lz-type-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                </td>
                                <td>
                                    @if($saleItem->item_type === 'booking')
                                        @php
                                            $booking = $saleItem->itemable;
                                            $details = $booking?->details ?? collect();
                                        @endphp
                                        @forelse($details as $detail)
                                            @php
                                                $service = $detail->service ?? null;
                                                $svcName = $service?->translation?->name ?? $service?->name ?? '-';
                                                if ($itemEmployee === '-' && $detail->worker?->name) {
                                                    $itemEmployee = $detail->worker->name;
                                                }
                                            @endphp
                                            <div class="{{ !$loop->first ? 'mt-2' : '' }}">
                                                <span class="lz-item-name">{{ $svcName }}</span>
                                                <span class="lz-item-sub">
                                                    {{ $detail->_date ?? '-' }} •
                                                    {{ $detail->from_time ?? '' }} - {{ $detail->to_time ?? '' }}
                                                    @if($detail->worker?->name)
                                                        • {{ $detail->worker->name }}
                                                    @endif
                                                </span>
                                                @if($detail->is_free)
                                                    <span class="lz-item-free">({{ __('field.covered_by_package') ?? 'Covered by Package' }})</span>
                                                @endif
                                                @if((float) ($detail->tip ?? 0) > 0)
                                                    <span class="lz-item-sub">{{ __('field.tip') }}: {{ number_format((float) $detail->tip, 2) }} {{ $currency }}</span>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="lz-item-name">-</span>
                                        @endforelse
                                    @elseif($saleItem->item_type === 'buy_product')
                                        @php
                                            $buyProduct = $saleItem->itemable;
                                            $products = $buyProduct?->details?->map(function($detail) {
                                                return $detail->product?->translation?->name ?? $detail->product?->name;
                                            })->filter()->implode(', ');
                                            $itemEmployee = $buyProduct?->sales_worker?->name
                                                ?? $buyProduct?->worker?->name
                                                ?? '-';
                                        @endphp
                                        <span class="lz-item-name">{{ $products ?: '-' }}</span>
                                    @elseif($saleItem->item_type === 'user_package')
                                        @php
                                            $userPackage = $saleItem->itemable;
                                            $package = $userPackage->package ?? null;
                                        @endphp
                                        <span class="lz-item-name">{{ $package?->name ?? '-' }}</span>
                                        <span class="lz-item-sub">{{ __('general.new_purchase') ?? 'New Purchase' }}</span>
                                    @endif
                                </td>
                                <td>{{ $itemEmployee }}</td>
                                <td>{{ $saleItem->quantity }}</td>
                                <td class="text-end">{{ number_format($saleItem->price, 2) }} {{ $currency }}</td>
                                <td class="text-end fw-semibold">{{ number_format($saleItem->subtotal, 2) }} {{ $currency }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($displayNotes !== '')
            <div class="card lz-panel mt-3">
                <div class="lz-panel-head">
                    <i class="ti ti-notes"></i>
                    <span>{{ __('field.notes') }}</span>
                </div>
                <div class="lz-panel-body">
                    <div class="lz-kv-value" style="white-space: pre-wrap;">{{ $displayNotes }}</div>
                </div>
            </div>
        @endif
    </div>
@endsection

@section('page-script')
    @include('_partials.sale_otp_modals')
@endsection
