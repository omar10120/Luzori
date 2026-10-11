@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-shifts-table tbody tr td.dtr-control {
            cursor: pointer;
        }
        .lz-shifts-table tbody tr.child,
        .lz-shifts-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-shifts-table tbody tr.dtr-expanded {
            background: #f3ebe0 !important;
        }
        .lz-shifts-table .lz-time-cell {
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            color: #1a2a27;
        }
        table.dataTable.dtr-column > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-column.collapsed > tbody > tr > td.dtr-control:before {
            border-radius: 50%;
            border-color: #c5a070 !important;
            box-shadow: none !important;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-shifts-page">
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
                    <i class="ti ti-clock-hour-4"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-shifts-actions"></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-shifts-table']) }}
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
        document.addEventListener('DOMContentLoaded', function () {
            const target = document.getElementById('lz-shifts-actions');

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

            $(document).on('dblclick', '#shifts-table tbody tr', function (e) {
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
    </script>
@endsection
