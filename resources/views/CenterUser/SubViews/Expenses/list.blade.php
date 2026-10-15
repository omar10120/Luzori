@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-expenses-page .lz-filter-card {
            border: 0;
            border-radius: 16px;
            background: #fdfaf6;
            box-shadow: 0 8px 28px rgba(0, 43, 38, 0.06);
            margin-bottom: 1.25rem;
        }
        .lz-expenses-page .lz-filter-card .form-label {
            color: #7a8582;
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        .lz-expenses-page .lz-filter-card .form-select,
        .lz-expenses-page .lz-filter-card .form-control {
            border-color: #e4d8c4;
            background: #fff;
            color: #002b26;
            min-height: 38px;
        }
        .lz-expenses-table .lz-amount {
            font-weight: 700;
            color: #002b26;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }
        .lz-expenses-table .lz-expense-type {
            display: inline-flex;
            padding: 0.25rem 0.65rem;
            border-radius: 999px;
            background: #f3ebe0;
            color: #8a6a3d;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: capitalize;
        }
        .lz-expenses-table .lz-receipt-thumb {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #efe6d8;
            cursor: pointer;
        }
        #receiptModal .modal-content {
            border: 0;
            border-radius: 16px;
            background: #fdfaf6;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-expenses-page">
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
                    <i class="ti ti-receipt-2"></i>
                </div>
                <h1 class="lz-page-title">{{ __('general.manage') }} {{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-expenses-actions"><a href="{{ route('center_user.expenses.create') }}" class="btn btn-luzori-primary btn-sm mx-1 px-3 py-2 lz-btn-add-new">{{ __('general.add_new') }} <i class="ti ti-plus"></i></a></div>
        </div>

        <div class="card lz-filter-card">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="branchFilter">{{ __('field.branch') }}</label>
                        <select id="branchFilter" class="form-select form-select-sm">
                            <option value="">{{ __('general.all_branches') ?: 'All Branches' }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="dateFilter">{{ __('field.date') }}</label>
                        <input type="date" id="dateFilter" class="form-control form-control-sm" placeholder="{{ __('field.select_date') ?: 'Select date' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="nameFilter">{{ __('field.expense_name') }}</label>
                        <select id="nameFilter" class="form-select form-select-sm">
                            <option value="">{{ __('general.all_expenses') ?: 'All Expenses' }}</option>
                            @foreach ($expenseNames as $expenseName)
                                <option value="{{ $expenseName }}">{{ $expenseName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" id="clearFilters" class="btn btn-luzori-muted btn-sm px-3 py-2">
                            <i class="ti ti-x"></i> {{ __('general.clear') ?: 'Clear' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-expenses-table']) }}
            </div>
        </div>

        <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="receiptModalLabel">{{ __('field.receipt') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img id="receiptModalImage" src="" alt="Receipt" class="img-fluid" style="max-height: 75vh; object-fit: contain;">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-luzori-muted btn-sm" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                    </div>
                </div>
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
            const target = document.getElementById('lz-expenses-actions');

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

            const reloadTable = () => {
                const table = $('#expenses_providers-table').DataTable();
                if (table) table.ajax.reload();
            };

            $('#branchFilter, #dateFilter, #nameFilter').on('change', reloadTable);

            $('#clearFilters').on('click', function () {
                $('#branchFilter').val('');
                $('#dateFilter').val('');
                $('#nameFilter').val('');
                reloadTable();
            });

            $(document).on('click', '.receipt-thumbnail', function () {
                const receiptUrl = $(this).data('receipt-url');
                $('#receiptModalImage').attr('src', receiptUrl || '');
            });
        });
    </script>
@endsection
