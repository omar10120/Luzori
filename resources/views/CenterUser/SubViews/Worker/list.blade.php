@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-workers-table tbody tr td.dtr-control {
            cursor: pointer;
        }
        .lz-workers-table tbody tr.child,
        .lz-workers-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-workers-table tbody tr.dtr-expanded {
            background: #f3ebe0 !important;
        }
        .lz-workers-table img {
            width: 48px !important;
            height: 48px !important;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #efe6d8;
        }
        .lz-workers-table .lz-service-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #efe6d8;
            color: #002b26;
            font-size: 0.72rem;
            font-weight: 600;
            margin: 0.15rem;
            padding: 0.35rem 0.65rem;
            line-height: 1.2;
            max-width: 100%;
            white-space: normal;
            text-align: start;
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
    <div class="luzori-page lz-workers-page">
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
                    <i class="ti ti-users"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-workers-actions"></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-workers-table']) }}
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
            const target = document.getElementById('lz-workers-actions');

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

            $(document).on('dblclick', '#workers-table tbody tr', function (e) {
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
