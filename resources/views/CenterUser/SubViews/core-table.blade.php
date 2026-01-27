@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
@endsection

@section('content')
    <div class="container">

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

        <div class="card table-responsive">
            <div class="card-header d-flex justify-content-between">
                <h2>{{ __('general.manage') . ' ' . $title }}</h2>
            </div>
            <div class="card-body">
                {{ $dataTable->table() }}
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
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
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
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
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
            var h = '<table class="table table-sm table-bordered"><thead><tr>';
            headers.forEach(function(th) { h += '<th>' + th + '</th>'; });
            h += '</tr></thead><tbody>';
            rows.forEach(function(row) {
                h += '<tr>';
                row.forEach(function(cell) { h += '<td>' + (cell || '-') + '</td>'; });
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
            if (modalBody) modalBody.innerHTML = rows.length ? tableHtml(headers, rows) : '<p class="text-muted">-</p>';
            (new bootstrap.Modal(modalEl)).show();
        });

        $(document).on('click', '.view-product-details', function() {
            var btn = $(this);
            var details = parseDetails(btn);
            var title = btn.attr('data-modal-title') || '{{ __("locale.products") }} {{ __("general.show") }}';
            var headers = ['{{ __("field.product") }}', '{{ __("field.price") }}'];
            var rows = details.map(function(d) { return [d.product, d.price]; });
            if (modalTitle) modalTitle.textContent = title;
            if (modalBody) modalBody.innerHTML = rows.length ? tableHtml(headers, rows) : '<p class="text-muted">-</p>';
            (new bootstrap.Modal(modalEl)).show();
        });

        $(document).on('click', '.view-coupon-details', function() {
            var btn = $(this);
            var details = parseDetails(btn);
            var title = btn.attr('data-modal-title') || '{{ __("field.coupons") }} {{ __("general.show") }}';
            var headers = ['{{ __("field.code") }}', '{{ __("field.amount") }}', '{{ __("field.type") }}', '{{ __("field.client") }}'];
            var rows = details.map(function(d) { return [d.code, d.amount, d.type, d.user]; });
            if (modalTitle) modalTitle.textContent = title;
            if (modalBody) modalBody.innerHTML = rows.length ? tableHtml(headers, rows) : '<p class="text-muted">-</p>';
            (new bootstrap.Modal(modalEl)).show();
        });
    })();
    </script>
@endsection
