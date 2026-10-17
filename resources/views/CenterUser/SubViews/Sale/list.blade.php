@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-sales-table tbody tr td.dtr-control { cursor: pointer; }
        .lz-sales-table tbody tr.child,
        .lz-sales-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-sales-table tbody tr.dtr-expanded { background: #f3ebe0 !important; }
        .lz-sales-table tbody tr.lz-sale-row-active > td {
            background: #e8f2ef !important;
            box-shadow: inset 3px 0 0 #0a3a34;
        }
        .lz-sales-page .lz-branch-filter {
            min-width: 160px;
            max-width: 220px;
            border-color: #e4d8c4;
            background: #fff;
            color: #002b26;
            font-size: 0.875rem;
            height: 38px;
        }
        .lz-sales-table .lz-sale-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .lz-sales-table .lz-sale-badge.is-booking { background: #f7e4d4; color: #b06a2b; }
        .lz-sales-table .lz-sale-badge.is-product { background: #e6f3ee; color: #1a6b5a; }
        .lz-sales-table .lz-sale-badge.is-package { background: #eef3f2; color: #0a3a34; }
        .lz-sales-table .lz-sale-badge.is-coupon { background: #f3ebe0; color: #8a6a3d; }
        .lz-sales-table .lz-sale-detail-btn {
            border: 1px solid #c5a070;
            color: #002b26;
            background: #fff;
            border-radius: 999px;
            padding: 0.2rem 0.65rem;
            font-size: 0.72rem;
            font-weight: 600;
        }
        .lz-sales-table .lz-sale-detail-btn:hover {
            background: #f3ebe0;
            border-color: #9a6923;
            color: #002b26;
        }
        .lz-sales-table .badge.bg-label-success,
        .lz-sales-table .badge.bg-label-warning,
        .lz-sales-table .badge.bg-label-danger,
        .lz-sales-table .badge.bg-label-info,
        .lz-sales-table .badge.bg-label-secondary,
        .lz-sales-table .badge.bg-label-primary {
            border-radius: 999px;
            font-weight: 700;
        }
        table.dataTable.dtr-column > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-column.collapsed > tbody > tr > td.dtr-control:before {
            border-radius: 50%;
            border-color: #c5a070 !important;
            box-shadow: none !important;
        }

        /* Sale details offcanvas */
        #saleDetailsOffcanvas {
            width: min(400px, 92vw);
            border: 0;
            box-shadow: -8px 0 28px rgba(0, 43, 38, 0.12);
        }
        #saleDetailsOffcanvas .offcanvas-header {
            border-bottom: 1px solid #efe6d8;
            padding: 1rem 1.15rem;
            background: #fdfaf6;
        }
        #saleDetailsOffcanvas .offcanvas-title {
            color: #002b26;
            font-weight: 700;
            font-size: 1.05rem;
        }
        #saleDetailsOffcanvas .offcanvas-body {
            padding: 0;
            display: flex;
            flex-direction: column;
            background: #fff;
        }
        .lz-sale-drawer-scroll {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 1.1rem 1.15rem 0.5rem;
        }
        .lz-sale-drawer-footer {
            flex: 0 0 auto;
            padding: 0.85rem 1.15rem 1.1rem;
            border-top: 1px solid #efe6d8;
            background: #fdfaf6;
        }
        .lz-sale-drawer-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .lz-sale-drawer-ref {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            color: #002b26;
            font-weight: 700;
            font-size: 0.98rem;
            line-height: 1.3;
        }
        .lz-sale-drawer-ref i {
            color: #9a6923;
            font-size: 1.15rem;
        }
        .lz-sale-drawer-status {
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.72rem;
            padding: 0.28rem 0.7rem;
            white-space: nowrap;
        }
        .lz-sale-drawer-status.is-success { background: #e6f3ee; color: #1a6b5a; }
        .lz-sale-drawer-status.is-warning { background: #fff4df; color: #9a6923; }
        .lz-sale-drawer-status.is-danger { background: #fde8e8; color: #b42318; }
        .lz-sale-drawer-status.is-secondary { background: #eef3f2; color: #4a5c58; }
        .lz-sale-drawer-meta {
            list-style: none;
            margin: 0 0 1.15rem;
            padding: 0;
        }
        .lz-sale-drawer-meta li {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            padding: 0.45rem 0;
            border-bottom: 1px solid #f3ebe0;
            font-size: 0.86rem;
            color: #002b26;
        }
        .lz-sale-drawer-meta li:last-child { border-bottom: 0; }
        .lz-sale-drawer-meta i {
            color: #9a6923;
            font-size: 1.05rem;
            margin-top: 0.1rem;
            flex: 0 0 auto;
        }
        .lz-sale-drawer-meta .lz-meta-label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            color: #7a6a55;
            margin-bottom: 0.1rem;
        }
        .lz-sale-drawer-meta .lz-meta-value {
            font-weight: 600;
            word-break: break-word;
        }
        .lz-sale-drawer-section {
            padding: 0.85rem 0;
            border-top: 1px solid #efe6d8;
        }
        .lz-sale-drawer-section-title {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.84rem;
            font-weight: 700;
            color: #002b26;
            margin-bottom: 0.55rem;
        }
        .lz-sale-drawer-section-title i { color: #9a6923; }
        .lz-sale-drawer-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.35rem 0;
            font-size: 0.84rem;
            color: #2a3d39;
        }
        .lz-sale-drawer-item .lz-item-name { font-weight: 600; }
        .lz-sale-drawer-item .lz-item-sub {
            display: block;
            font-size: 0.74rem;
            color: #7a6a55;
            font-weight: 500;
            margin-top: 0.1rem;
        }
        .lz-sale-drawer-item .lz-item-price {
            white-space: nowrap;
            font-weight: 600;
            color: #0a3a34;
            font-size: 0.8rem;
        }
        .lz-sale-drawer-empty {
            font-size: 0.82rem;
            color: #9a8a75;
        }
        .lz-sale-drawer-notes {
            font-size: 0.84rem;
            color: #2a3d39;
            line-height: 1.45;
            white-space: pre-wrap;
        }
        .lz-sale-drawer-full-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            width: 100%;
            border: 1px solid #c5a070;
            background: #fff;
            color: #002b26;
            border-radius: 10px;
            padding: 0.65rem 1rem;
            font-weight: 700;
            font-size: 0.88rem;
            text-decoration: none;
            transition: background 0.15s ease, border-color 0.15s ease;
        }
        .lz-sale-drawer-full-btn:hover {
            background: #f3ebe0;
            border-color: #9a6923;
            color: #002b26;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-sales-page">
        @if (\Session::has('success'))
            <div class="alert alert-success">
                <div>{!! \Session::get('success') !!}</div>
            </div>
        @endif
        @if (\Session::has('error'))
            <div class="alert alert-danger">
                <div>{!! \Session::get('error') !!}</div>
            </div>
        @endif

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-receipt"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-sales-actions"><a href="{{ route('center_user.sales.cart') }}" class="btn btn-luzori-primary btn-sm mx-1 px-3 py-2 lz-btn-add-new">{{ __('general.add_new') }} <i class="ti ti-plus"></i></a>
                
                <select id="booking_source_filter" class="form-select form-select-sm lz-branch-filter" aria-label="{{ __('api.booking_source') }}">
                    <option value="">{{ __('general.all') }}</option>
                    <option value="inside_booking">{{ __('api.inside_booking') ?? 'Inside Booking' }}</option>
                    <option value="outside_booking">{{ __('api.outside_booking') ?? 'Outside Booking' }}</option>
                </select>
            </div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-sales-table']) }}
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="saleDetailsOffcanvas" aria-labelledby="saleDetailsOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="saleDetailsOffcanvasLabel">{{ __('field.booking_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="lz-sale-drawer-scroll" id="saleDetailsOffcanvasBody"></div>
                <div class="lz-sale-drawer-footer">
                    <a href="#" class="lz-sale-drawer-full-btn" id="saleDetailsFullLink" target="_blank" rel="noopener">
                        <i class="ti ti-eye"></i>
                        <span>{{ __('general.view_details') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @include('CenterUser.Components.datatable-js')
@endsection

@section('page-script')
    @include('_partials.sale_otp_modals')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const target = document.getElementById('lz-sales-actions');

            const moveButtons = () => {
                const source = document.querySelector('.dt-action-buttons');
                if (!target || !source || !source.children.length) return false;
                while (source.firstChild) {
                    target.appendChild(source.firstChild);
                }
                const header = source.closest('.card-header');
                if (header) header.style.display = 'none';
                return true;
            };

            if (!moveButtons()) {
                const timer = setInterval(() => {
                    if (moveButtons()) clearInterval(timer);
                }, 120);
                setTimeout(() => clearInterval(timer), 4000);
            }
        });

        (function() {
            var offcanvasEl = document.getElementById('saleDetailsOffcanvas');
            if (!offcanvasEl) return;

            var bodyEl = document.getElementById('saleDetailsOffcanvasBody');
            var fullLink = document.getElementById('saleDetailsFullLink');
            var labels = {
                branch: @json(__('field.branch')),
                client: @json(__('field.client')),
                phone: @json(__('field.phone')),
                payment: @json(__('field.payment_method')),
                createdAt: @json(__('field.created_at')),
                services: @json(__('field.services') . ' (' . __('locale.bookings') . ')'),
                packages: @json(__('field.packages')),
                products: @json(__('locale.products')),
                coupons: @json(__('field.coupons')),
                notes: @json(__('field.notes')),
                booking: @json(__('locale.bookings')),
                empty: '—'
            };

            function esc(str) {
                return String(str == null ? '' : str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function parsePanel(btn) {
                var raw = btn.getAttribute('data-sale-panel');
                if (!raw) return null;
                try {
                    return typeof raw === 'string' ? JSON.parse(raw) : raw;
                } catch (e) {
                    return null;
                }
            }

            function metaRow(icon, label, value) {
                return '<li>' +
                    '<i class="ti ' + icon + '"></i>' +
                    '<div><span class="lz-meta-label">' + esc(label) + '</span>' +
                    '<span class="lz-meta-value">' + esc(value || labels.empty) + '</span></div>' +
                    '</li>';
            }

            function section(icon, title, innerHtml) {
                return '<div class="lz-sale-drawer-section">' +
                    '<div class="lz-sale-drawer-section-title"><i class="ti ' + icon + '"></i><span>' + esc(title) + '</span></div>' +
                    innerHtml +
                    '</div>';
            }

            function renderBookings(items) {
                if (!items || !items.length) {
                    return '<div class="lz-sale-drawer-empty">' + labels.empty + '</div>';
                }
                return items.map(function(d) {
                    var sub = [d.worker, d.date, d.time].filter(Boolean).join(' · ');
                    return '<div class="lz-sale-drawer-item">' +
                        '<div><span class="lz-item-name">' + esc(d.service) + '</span>' +
                        (sub ? '<span class="lz-item-sub">' + esc(sub) + '</span>' : '') +
                        '</div>' +
                        '<span class="lz-item-price">' + esc(d.price || '') + '</span>' +
                        '</div>';
                }).join('');
            }

            function renderSimple(items, nameKey) {
                if (!items || !items.length) {
                    return '<div class="lz-sale-drawer-empty">' + labels.empty + '</div>';
                }
                return items.map(function(d) {
                    return '<div class="lz-sale-drawer-item">' +
                        '<div><span class="lz-item-name">' + esc(d[nameKey]) + '</span></div>' +
                        '<span class="lz-item-price">' + esc(d.price || d.amount || '') + '</span>' +
                        '</div>';
                }).join('');
            }

            function renderCoupons(items) {
                if (!items || !items.length) {
                    return '<div class="lz-sale-drawer-empty">' + labels.empty + '</div>';
                }
                return items.map(function(d) {
                    var sub = [d.type, d.user].filter(function(v) { return v && v !== '-'; }).join(' · ');
                    return '<div class="lz-sale-drawer-item">' +
                        '<div><span class="lz-item-name">' + esc(d.code) + '</span>' +
                        (sub ? '<span class="lz-item-sub">' + esc(sub) + '</span>' : '') +
                        '</div>' +
                        '<span class="lz-item-price">' + esc(d.amount || '') + '</span>' +
                        '</div>';
                }).join('');
            }

            function renderPanel(data, focus) {
                var tone = data.status_tone || 'secondary';
                var html = '';

                html += '<div class="lz-sale-drawer-head">';
                html += '<div class="lz-sale-drawer-ref"><i class="ti ti-calendar-event"></i><span>' + esc(data.ref || ('#' + data.id)) + '</span></div>';
                if (data.status_label && data.status_label !== '-') {
                    html += '<span class="lz-sale-drawer-status is-' + esc(tone) + '">' + esc(data.status_label) + '</span>';
                }
                html += '</div>';

                html += '<ul class="lz-sale-drawer-meta">';
                html += metaRow('ti-building-store', labels.branch, data.branch);
                html += metaRow('ti-user', labels.client, data.client);
                html += metaRow('ti-phone', labels.phone, data.phone);
                html += metaRow('ti-credit-card', labels.payment, data.payment);
                html += metaRow('ti-clock', labels.createdAt, data.created_at);
                html += '</ul>';

                html += section('ti-scissors', labels.services, renderBookings(data.bookings));
                html += section('ti-package', labels.packages, renderSimple(data.packages, 'package'));
                html += section('ti-shopping-bag', labels.products, renderSimple(data.products, 'product'));
                html += section('ti-ticket', labels.coupons, renderCoupons(data.coupons));

                var notesHtml = data.notes
                    ? '<div class="lz-sale-drawer-notes">' + esc(data.notes) + '</div>'
                    : '<div class="lz-sale-drawer-empty">' + labels.empty + '</div>';
                html += section('ti-notes', labels.notes, notesHtml);

                bodyEl.innerHTML = html;

                if (fullLink) {
                    fullLink.href = data.show_url || '#';
                    fullLink.style.display = data.show_url ? '' : 'none';
                }

                if (focus) {
                    // Soft scroll toward focused section after paint
                    setTimeout(function() {
                        var titles = bodyEl.querySelectorAll('.lz-sale-drawer-section-title span');
                        var map = {
                            bookings: labels.services,
                            packages: labels.packages,
                            products: labels.products,
                            coupons: labels.coupons
                        };
                        var want = map[focus];
                        if (!want) return;
                        titles.forEach(function(el) {
                            if (el.textContent === want) {
                                el.closest('.lz-sale-drawer-section')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            }
                        });
                    }, 180);
                }
            }

            function setActiveRow(btn) {
                document.querySelectorAll('.lz-sales-table tbody tr.lz-sale-row-active')
                    .forEach(function(tr) { tr.classList.remove('lz-sale-row-active'); });
                var tr = btn.closest('tr');
                if (tr && !tr.classList.contains('child')) {
                    tr.classList.add('lz-sale-row-active');
                }
            }

            $(document).on('click', '.view-sale-side-details', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var btn = this;
                var data = parsePanel(btn);
                if (!data) return;

                setActiveRow(btn);
                renderPanel(data, btn.getAttribute('data-focus'));

                if (window.bootstrap && bootstrap.Offcanvas) {
                    bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
                }
            });

            offcanvasEl.addEventListener('hidden.bs.offcanvas', function() {
                document.querySelectorAll('.lz-sales-table tbody tr.lz-sale-row-active')
                    .forEach(function(tr) { tr.classList.remove('lz-sale-row-active'); });
            });
        })();
    </script>
@endsection
