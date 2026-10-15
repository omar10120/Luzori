@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-staff-table .lz-staff-name {
            font-weight: 700;
            color: #002b26;
        }
        .lz-staff-table .lz-role-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            background: #eef3f2;
            color: #0a3a34;
            font-size: 0.72rem;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-staff-page">
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
                    <i class="ti ti-user-plus"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-staff-actions"><a href="{{ route('center_user.centerusers.create') }}" class="btn btn-luzori-primary btn-sm mx-1 px-3 py-2 lz-btn-add-new">{{ __('general.add_new') }} <i class="ti ti-plus"></i></a></div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-staff-table']) }}
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
            const target = document.getElementById('lz-staff-actions');

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
