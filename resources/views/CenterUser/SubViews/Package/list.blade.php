@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-packages-table .lz-package-name {
            font-weight: 700;
            color: #002b26;
        }
        .lz-packages-table .lz-amount,
        .lz-packages-table .lz-total-value {
            font-weight: 700;
            color: #002b26;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .lz-packages-table .lz-service-badge,
        .lz-row-details-table .lz-service-badge,
        .lz-packages-table .lz-user-badge,
        .lz-row-details-table .lz-user-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.22rem 0.55rem;
            border-radius: 999px;
            font-size: 0.68rem;
            font-weight: 600;
            margin: 0.15rem;
        }
        .lz-packages-table .lz-service-paid,
        .lz-row-details-table .lz-service-paid {
            background: #eef3f2;
            color: #0a3a34;
        }
        .lz-packages-table .lz-service-free,
        .lz-row-details-table .lz-service-free {
            background: #f3ebe0;
            color: #8a6a3d;
        }
        .lz-packages-table .lz-user-badge,
        .lz-row-details-table .lz-user-badge {
            background: #e8efe9;
            color: #002b26;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-packages-page">
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
                    <i class="ti ti-packages"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-packages-actions"></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-packages-table']) }}
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
            const target = document.getElementById('lz-packages-actions');

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