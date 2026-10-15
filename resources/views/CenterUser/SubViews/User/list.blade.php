@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-users-table .lz-user-name {
            font-weight: 700;
            color: #002b26;
        }
        .lz-users-table .lz-pkg-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            background: #eef3f2;
            color: #0a3a34;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .lz-users-table .lz-yes-badge,
        .lz-users-table .lz-no-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .lz-users-table .lz-yes-badge {
            background: #e6f3ee;
            color: #1a6b5a;
        }
        .lz-users-table .lz-no-badge {
            background: #f7e4d4;
            color: #b06a2b;
        }
        .lz-users-table .lz-wallet-num {
            font-weight: 700;
            color: #002b26;
            font-variant-numeric: tabular-nums;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-users-page">
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
                    <i class="ti ti-heart-handshake"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-users-actions"><a href="{{ route('center_user.users.create') }}" class="btn btn-luzori-primary btn-sm mx-1 px-3 py-2 lz-btn-add-new">{{ __('general.add_new') }} <i class="ti ti-plus"></i></a></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-users-table']) }}
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
            const target = document.getElementById('lz-users-actions');

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
