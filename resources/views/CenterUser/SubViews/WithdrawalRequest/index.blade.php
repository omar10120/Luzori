@extends('layouts/layoutMaster')
@section('title', $title)
@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
@endsection
@section('vendor-script')
    @include('CenterUser.Components.datatable-js')
@endsection
@section('content')
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title mb-2">{{ __('api.wallet_balance') ?? 'Wallet Balance' }}</h5>
                    <h2 class="text-primary mb-3">{{ number_format($walletBalance, 2) }}</h2>
                    <p class="text-muted mb-4">{{ __('api.available_for_withdrawal') ?? 'Available for withdrawal' }}</p>
                    
                    @if($walletBalance > 0)
                    <button type="button" class="btn btn-primary" onclick="requestWithdrawal()">
                        <i class="ti ti-cash me-1"></i> {{ __('api.request_withdrawal') ?? 'Request Withdrawal' }}
                    </button>
                    @else
                    <button type="button" class="btn btn-secondary" disabled>
                        <i class="ti ti-cash me-1"></i> {{ __('api.request_withdrawal') ?? 'Request Withdrawal' }}
                    </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ __('api.withdrawal_instructions') ?? 'Withdrawal Instructions' }}</h5>
                    <p class="mb-2">1. {{ __('api.withdrawal_step_1') ?? 'When you request a withdrawal, your entire wallet balance will be sent to the administration.' }}</p>
                    <p class="mb-2">2. {{ __('api.withdrawal_step_2') ?? 'Your balance will be temporarily withdrawn to prevent multiple requests.' }}</p>
                    <p class="mb-2">3. {{ __('api.withdrawal_step_3') ?? 'Once the administration confirms the transfer, the request will be marked as Confirmed.' }}</p>
                    <p class="mb-0">4. {{ __('api.withdrawal_step_4') ?? 'If the request is rejected, the amount will be refunded to your wallet.' }}</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-datatable text-nowrap">
                    {!! $dataTable->table() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
        function requestWithdrawal() {
            Swal.fire({
                title: '{{ __("general.are_you_sure") }}',
                text: '{{ __("api.confirm_withdrawal_request") ?? "Are you sure you want to request a withdrawal of your entire balance?" }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
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
                            if(xhr.responseJSON && xhr.responseJSON.message) {
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
@endpush
