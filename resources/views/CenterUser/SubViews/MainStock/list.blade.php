@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-mainstock-table tbody tr td.dtr-control {
            cursor: pointer;
        }
        .lz-mainstock-table tbody tr.child,
        .lz-mainstock-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-mainstock-table tbody tr.dtr-expanded {
            background: #f3ebe0 !important;
        }
        .lz-mainstock-table .lz-stock-num {
            font-weight: 700;
            color: #002b26;
            font-variant-numeric: tabular-nums;
        }
        .lz-mainstock-table .lz-stock-muted {
            color: #7a8582;
            font-variant-numeric: tabular-nums;
        }
        .lz-mainstock-table .lz-stock-ok {
            color: #1a6b5a;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
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
    <div class="luzori-page lz-mainstock-page">
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
                    <i class="ti ti-building-warehouse"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-mainstock-actions"></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-mainstock-table']) }}
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
            const target = document.getElementById('lz-mainstock-actions');

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

            $(document).on('dblclick', '#mainstocks-table tbody tr', function (e) {
                const $tr = $(this);
                if ($tr.hasClass('child')) return;
                if ($(e.target).closest('a, button, input, label, .switch, .dropdown, .dropdown-menu').length) {
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
