@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-payments-table tbody tr td.dtr-control {
            cursor: pointer;
        }
        .lz-payments-table tbody tr.child,
        .lz-payments-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-payments-table tbody tr.dtr-expanded {
            background: #f3ebe0 !important;
        }
        .lz-payments-table .lz-type-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            margin: 0.12rem;
            padding: 0.35rem 0.7rem;
            line-height: 1.2;
        }
        .lz-payments-table .lz-type-badge.is-general {
            background: #e8f0ee;
            color: #0a3a34;
        }
        .lz-payments-table .lz-type-badge.is-wallet {
            background: #efe6d8;
            color: #7a5a2f;
        }
        .lz-payments-table .lz-type-badge.is-booking {
            background: #d8f0e6;
            color: #1a6b5a;
        }
        .lz-payments-table .lz-type-badge.is-product {
            background: #f3ebe0;
            color: #002b26;
        }
        .lz-payments-table .lz-type-badge.is-tips,
        .lz-payments-table .lz-type-badge.is-default {
            background: #f1ebe3;
            color: #5f7a72;
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
    <div class="luzori-page lz-payments-page">
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
                    <i class="ti ti-credit-card"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-payments-actions"></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-payments-table']) }}
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
            const target = document.getElementById('lz-payments-actions');

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
