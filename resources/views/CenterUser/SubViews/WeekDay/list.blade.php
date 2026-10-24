@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-weeksdays-table tbody tr td.dtr-control {
            cursor: pointer;
        }
        .lz-weeksdays-table tbody tr.child,
        .lz-weeksdays-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-weeksdays-table tbody tr.dtr-expanded {
            background: #f3ebe0 !important;
        }
        .lz-weeksdays-table .lz-day-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            font-weight: 600;
            color: #002b26;
            white-space: nowrap;
        }
        .lz-weeksdays-table .lz-day-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #c5a070;
            flex-shrink: 0;
        }
        table.dataTable.dtr-column > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-column.collapsed > tbody > tr > td.dtr-control:before {
            border-radius: 50%;
            border-color: #c5a070 !important;
            box-shadow: none !important;
        }

        /* Mobile: keep table layout + enable horizontal scroll */
        @media (max-width: 991.98px) {
            .lz-weeksdays-page {
                overflow-x: visible !important;
                max-width: 100%;
            }
            .lz-weeksdays-page .lz-table-card,
            .lz-weeksdays-page .lz-table-card .card-body,
            .lz-weeksdays-page .dataTables_wrapper {
                overflow: visible;
                max-width: 100%;
            }
            .lz-weeksdays-page .table-responsive {
                display: block;
                width: 100%;
                max-width: 100%;
                overflow-x: auto !important;
                overflow-y: hidden;
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-x: contain;
                touch-action: pan-x pan-y;
                margin: 0 -0.25rem;
                padding-bottom: 0.35rem;
            }
            .lz-weeksdays-page #weeksdays-table,
            .lz-weeksdays-page .lz-weeksdays-table {
                min-width: 520px;
                width: 100%;
            }
            .lz-weeksdays-page .lz-weeksdays-table thead th,
            .lz-weeksdays-page .lz-weeksdays-table tbody td {
                white-space: nowrap;
                vertical-align: middle !important;
            }
            .lz-weeksdays-page .lz-weeksdays-table .switch {
                margin: 0;
            }
            .lz-weeksdays-page .dataTables_wrapper .row {
                margin-left: 0;
                margin-right: 0;
            }
            .lz-weeksdays-page .dataTables_wrapper .dataTables_filter,
            .lz-weeksdays-page .dataTables_wrapper .dataTables_length {
                float: none;
                text-align: start;
            }
            .lz-weeksdays-page .dataTables_wrapper .dataTables_filter input {
                width: 100%;
                max-width: 100%;
                margin-left: 0 !important;
                margin-top: 0.35rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-weeksdays-page">
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
                    <i class="ti ti-calendar-week"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-weeksdays-actions"></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-weeksdays-table']) }}
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @include('CenterUser.Components.datatable-js')
@endsection

@section('page-script')
    @routes
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const target = document.getElementById('lz-weeksdays-actions');

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

        function changeWeekDayStatus(id) {
            var url = route('center_user.weeksdays.changeStatus', { id: id });
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: { _token: '{{ csrf_token() }}' },
                success: function () {
                    Swal.fire({
                        title: "{{ __('general.success') }}",
                        icon: 'success',
                        timer: 3000
                    });
                },
                error: function (response) {
                    if (response.status == 401) {
                        fireMessage("{{ __('admin.ok') }}",
                            "{{ __('admin.not_allowed') }}",
                            "{{ __('admin.fire_message') }}",
                            'error');
                    } else {
                        fireMessage("{{ __('admin.ok') }}",
                            "{{ __('admin.an_error_occurred') }}",
                            "",
                            'error');
                    }
                }
            });
        }
    </script>
@endsection
