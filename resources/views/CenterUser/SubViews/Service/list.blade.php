@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-services-table tbody tr {
            cursor: default;
        }
        .lz-services-table tbody tr td.dtr-control {
            cursor: pointer;
        }
        .lz-services-table tbody tr.child,
        .lz-services-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-services-table tbody tr.dtr-expanded {
            background: #f3ebe0 !important;
        }
        .lz-services-table img {
            width: 48px !important;
            height: 48px !important;
            object-fit: cover;
            border-radius: 10px;
        }
        table.dataTable.dtr-column > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-column.collapsed > tbody > tr > td.dtr-control:before,
        table.dataTable.dtr-column.collapsed > tbody > tr > th.dtr-control:before {
            border-radius: 50%;
            border-color: #c5a070 !important;
            box-shadow: none !important;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-services-page">
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
                    <i class="ti ti-cut"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-services-actions"></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-services-table']) }}
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
            const actionsTarget = document.getElementById('lz-services-actions');

            const moveButtons = () => {
                const source = document.querySelector('.dt-action-buttons');
                if (!actionsTarget || !source || !source.children.length) return false;
                while (source.firstChild) {
                    actionsTarget.appendChild(source.firstChild);
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

            // Double-click row to toggle details; single-click arrow still works
        });
    </script>
@endsection
