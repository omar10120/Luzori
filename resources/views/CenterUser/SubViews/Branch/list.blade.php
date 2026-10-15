@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-branches-table tbody tr td.dtr-control {
            cursor: pointer;
        }
        .lz-branches-table tbody tr.child,
        .lz-branches-table tbody tr.child:hover {
            cursor: default;
            background: #faf7f2 !important;
        }
        .lz-branches-table tbody tr.dtr-expanded {
            background: #f3ebe0 !important;
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
    <div class="luzori-page lz-branches-page">
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
                    <i class="ti ti-building-store"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-branches-actions"><a href="{{ route('center_user.branches.create') }}" class="btn btn-luzori-primary btn-sm mx-1 px-3 py-2 lz-btn-add-new">{{ __('general.add_new') }} <i class="ti ti-plus"></i></a></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-branches-table']) }}
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
            const target = document.getElementById('lz-branches-actions');

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

            // Double-click row to toggle details; arrow still works on single click
        });
    </script>
@endsection
