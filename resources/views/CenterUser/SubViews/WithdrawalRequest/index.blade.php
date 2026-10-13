@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
    <style>
        .lz-withdrawal-page .lz-wallet-card {
            border: 0;
            border-radius: 18px;
            background: linear-gradient(145deg, #fdfaf6 0%, #f3ebe0 100%);
            box-shadow: 0 8px 28px rgba(0, 43, 38, 0.07);
            overflow: hidden;
        }
        .lz-withdrawal-page .lz-wallet-card .card-body {
            padding: 1.5rem 1.35rem;
        }
        .lz-withdrawal-page .lz-wallet-label {
            color: #7a8582;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }
        .lz-withdrawal-page .lz-wallet-amount {
            color: #002b26;
            font-size: 2rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            margin-bottom: 0.35rem;
            line-height: 1.1;
        }
        .lz-withdrawal-page .lz-wallet-hint {
            color: #7a8582;
            font-size: 0.82rem;
            margin-bottom: 1.1rem;
        }
        .lz-withdrawals-table .lz-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: capitalize;
        }
        .lz-withdrawals-table .lz-status-badge.is-pending {
            background: #f3ebe0;
            color: #8a6a3d;
        }
        .lz-withdrawals-table .lz-status-badge.is-confirmed {
            background: #e6f3ee;
            color: #1a6b5a;
        }
        .lz-withdrawals-table .lz-status-badge.is-rejected {
            background: #f7e4d4;
            color: #b06a2b;
        }
        .lz-withdrawals-table .lz-amount {
            font-weight: 700;
            color: #002b26;
            font-variant-numeric: tabular-nums;
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
    <div class="luzori-page lz-withdrawal-page">
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
                    <i class="ti ti-cash-banknote"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
            <div class="lz-page-actions" id="lz-withdrawal-actions"></div>
        </div>

        <div class="row mb-4">
            <div class="col-md-5 col-lg-4">
                <div class="card lz-wallet-card h-100">
                    <div class="card-body">
                        <div class="lz-wallet-label">{{ __('api.wallet_balance') ?? 'Wallet Balance' }}</div>
                        <div class="lz-wallet-amount">{{ number_format($walletBalance, 2) }}</div>
                        <p class="lz-wallet-hint">{{ __('api.available_for_withdrawal') ?? 'Available for withdrawal' }}</p>

                        @if($walletBalance > 0)
                            <button type="button" class="btn btn-luzori-primary btn-sm px-3 py-2" onclick="requestWithdrawal()">
                                <i class="ti ti-cash me-1"></i> {{ __('api.request_withdrawal') ?? 'Request Withdrawal' }}
                            </button>
                        @else
                            <button type="button" class="btn btn-luzori-muted btn-sm px-3 py-2" disabled>
                                <i class="ti ti-cash me-1"></i> {{ __('api.request_withdrawal') ?? 'Request Withdrawal' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                {{ $dataTable->table(['class' => 'table lz-withdrawals-table']) }}
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
            const target = document.getElementById('lz-withdrawal-actions');

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

            $(document).on('dblclick', '#withdrawal_requests-table tbody tr', function (e) {
                const $tr = $(this);
                if ($tr.hasClass('child')) return;
                if ($(e.target).closest('a, button, input, label, .switch, .dropdown, .dropdown-menu').length) {
                    return;
                }
                const $control = $tr.children('td.dtr-control').first();
                if ($control.length) {
                    $control.trigger('click');
                }
            });
        });

        function requestWithdrawal() {
            Swal.fire({
                title: '{{ __("general.are_you_sure") }}',
                text: '{{ __("api.confirm_withdrawal_request") ?? "Are you sure you want to request a withdrawal of your entire balance?" }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#002b26',
                cancelButtonColor: '#7a8582',
                confirmButtonText: '{{ __("general.yes") }}',
                cancelButtonText: '{{ __("general.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("center_user.withdrawal_requests.store") }}',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            let msg = '{{ __("admin.an_error_occurred") }}';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: msg,
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
