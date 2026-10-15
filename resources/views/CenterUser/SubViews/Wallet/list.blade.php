@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-wallets-table .lz-wallet-code {
            font-weight: 700;
            color: #002b26;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .lz-wallets-table .lz-amount {
            font-weight: 700;
            color: #002b26;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .lz-wallets-table .lz-used-badge,
        .lz-row-details-table .lz-used-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .lz-wallets-table .lz-used-yes,
        .lz-row-details-table .lz-used-yes {
            background: #eef3f2;
            color: #0a3a34;
        }
        .lz-wallets-table .lz-used-no,
        .lz-row-details-table .lz-used-no {
            background: #f3ebe0;
            color: #8a6a3d;
        }
        .lz-wallets-table .lz-user-badge,
        .lz-row-details-table .lz-user-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.22rem 0.55rem;
            border-radius: 999px;
            background: #eef3f2;
            color: #0a3a34;
            font-size: 0.68rem;
            font-weight: 600;
            margin: 0.15rem;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-wallets-page">
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
                    <i class="ti ti-wallet"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-wallets-actions"><a href="{{ route('center_user.wallets.create') }}" class="btn btn-luzori-primary btn-sm mx-1 px-3 py-2 lz-btn-add-new">{{ __('general.add_new') }} <i class="ti ti-plus"></i></a></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-wallets-table']) }}
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
            const target = document.getElementById('lz-wallets-actions');

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
