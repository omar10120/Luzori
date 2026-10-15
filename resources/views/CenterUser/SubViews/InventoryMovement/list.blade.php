@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-inventory-table tbody tr td.dtr-control {
            cursor: pointer;
        }
        .lz-inventory-table tbody tr.child,
        .lz-inventory-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-inventory-table tbody tr.dtr-expanded {
            background: #f3ebe0 !important;
        }
        .lz-inventory-table .lz-stock-num {
            font-weight: 700;
            color: #002b26;
            font-variant-numeric: tabular-nums;
        }
        .lz-inventory-table .lz-stock-muted {
            color: #7a8582;
            font-variant-numeric: tabular-nums;
        }
        .lz-inventory-page .lz-branch-filter {
            min-width: 180px;
            max-width: 240px;
            border-color: #e4d8c4;
            background: #fff;
            color: #002b26;
            font-size: 0.875rem;
            height: 38px;
        }
        .lz-inventory-table .form-check-input {
            border-color: #c5a070;
            cursor: pointer;
        }
        .lz-inventory-table .form-check-input:checked {
            background-color: #002b26;
            border-color: #002b26;
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
    <div class="luzori-page lz-inventory-page">
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
                    <i class="ti ti-arrows-exchange"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-inventory-actions">
                <select id="inventoryBranchFilter" class="form-select form-select-sm lz-branch-filter" aria-label="{{ __('field.branch') }}">
                    <option value="">{{ __('general.all') }} {{ __('field.branch') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-inventory-table']) }}
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
            const target = document.getElementById('lz-inventory-actions');

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
    </script>
@endsection
