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
        #salesDetailModal .modal-content {
            border: 0;
            border-radius: 16px;
            background: #fdfaf6;
        }
        #salesDetailModal .modal-header {
            border-bottom-color: #efe6d8;
        }
        #salesDetailModal .modal-title {
            color: #002b26;
            font-weight: 700;
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
            <div class="lz-page-actions" id="lz-sales-actions">
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

        <div class="modal fade" id="salesDetailModal" tabindex="-1" aria-labelledby="salesDetailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="salesDetailModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="salesDetailModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-luzori-muted" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    </div>
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

            $(document).on('dblclick', '#sales-table tbody tr', function (e) {
                const $tr = $(this);
                if ($tr.hasClass('child')) return;
                if ($(e.target).closest('.lz-action-icons, a, button, input, label, .switch, .dropdown, .dropdown-menu').length) {
                    return;
                }
                const $control = $tr.children('td.dtr-control').first();
                if ($control.length) {
                    $control.trigger('click');
                }
            });
        });

        (function() {
            var modalEl = document.getElementById('salesDetailModal');
            if (!modalEl) return;
            var modalTitle = document.getElementById('salesDetailModalLabel');
            var modalBody = document.getElementById('salesDetailModalBody');

            function parseDetails(btn) {
                var raw = btn.attr('data-details');
                if (Array.isArray(raw)) return raw;
                if (typeof raw !== 'string') return [];
                try {
                    var parsed = JSON.parse(raw);
                    return Array.isArray(parsed) ? parsed : [];
                } catch (e) {
                    return [];
                }
            }

            function tableHtml(headers, rows) {
                var h = '<table class="table table-sm"><thead><tr>';
                headers.forEach(function(th) { h += '<th>' + th + '</th>'; });
                h += '</tr></thead><tbody>';
                rows.forEach(function(row) {
                    h += '<tr>';
                    row.forEach(function(cell) { h += '<td>' + (cell || '—') + '</td>'; });
                    h += '</tr>';
                });
                h += '</tbody></table>';
                return h;
            }

            $(document).on('click', '.view-booking-details', function() {
                var btn = $(this);
                var details = parseDetails(btn);
                var title = btn.attr('data-modal-title') || '{{ __("locale.bookings") }} {{ __("general.show") }}';
                var headers = ['{{ __("field.service") }}', '{{ __("field.employee") }}', '{{ __("field.date") }}', '{{ __("field.time") }}', '{{ __("field.price") }}'];
                var rows = details.map(function(d) { return [d.service, d.worker, d.date, d.time, d.price]; });
                if (modalTitle) modalTitle.textContent = title;
                if (modalBody) modalBody.innerHTML = rows.length ? tableHtml(headers, rows) : '<p class="text-muted">—</p>';
                (new bootstrap.Modal(modalEl)).show();
            });

            $(document).on('click', '.view-product-details', function() {
                var btn = $(this);
                var details = parseDetails(btn);
                var title = btn.attr('data-modal-title') || '{{ __("locale.products") }} {{ __("general.show") }}';
                var headers = ['{{ __("field.product") }}', '{{ __("field.price") }}'];
                var rows = details.map(function(d) { return [d.product, d.price]; });
                if (modalTitle) modalTitle.textContent = title;
                if (modalBody) modalBody.innerHTML = rows.length ? tableHtml(headers, rows) : '<p class="text-muted">—</p>';
                (new bootstrap.Modal(modalEl)).show();
            });

            $(document).on('click', '.view-package-details', function() {
                var btn = $(this);
                var details = parseDetails(btn);
                var title = btn.attr('data-modal-title') || '{{ __("locale.packages") }} {{ __("general.show") }}';
                var headers = ['{{ __("field.package") }}', '{{ __("field.price") }}'];
                var rows = details.map(function(d) { return [d.package, d.price]; });
                if (modalTitle) modalTitle.textContent = title;
                if (modalBody) modalBody.innerHTML = rows.length ? tableHtml(headers, rows) : '<p class="text-muted">—</p>';
                (new bootstrap.Modal(modalEl)).show();
            });

            $(document).on('click', '.view-coupon-details', function() {
                var btn = $(this);
                var details = parseDetails(btn);
                var title = btn.attr('data-modal-title') || '{{ __("field.coupons") }} {{ __("general.show") }}';
                var headers = ['{{ __("field.code") }}', '{{ __("field.amount") }}', '{{ __("field.type") }}', '{{ __("field.client") }}'];
                var rows = details.map(function(d) { return [d.code, d.amount, d.type, d.user]; });
                if (modalTitle) modalTitle.textContent = title;
                if (modalBody) modalBody.innerHTML = rows.length ? tableHtml(headers, rows) : '<p class="text-muted">—</p>';
                (new bootstrap.Modal(modalEl)).show();
            });
        })();
    </script>
@endsection
