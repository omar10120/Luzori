@extends('layouts/layoutMaster')
@section('title', $title)
@section('vendor-style')
    @include('Admin.Components.datatable-css')
@endsection
@section('vendor-script')
    @include('Admin.Components.datatable-js')
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-datatable text-nowrap">
                    {!! $dataTable->table() !!}
                </div>
            </div>
        </div> 
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rejectModalTitle">{{ __('general.reject') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectForm">
                        <input type="hidden" id="reject_request_id">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="admin_notes" class="form-label">{{ __('field.admin_notes') ?? 'Admin Notes (Reason for Rejection)' }}</label>
                                <textarea id="admin_notes" class="form-control" placeholder="Please provide a reason..." required></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('general.close') }}</button>
                    <button type="button" class="btn btn-danger" onclick="submitReject()">{{ __('general.reject') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}
    <script>
        function approveRequest(url) {
            Swal.fire({
                title: '{{ __("general.are_you_sure") }}',
                text: '{{ __("api.confirm_withdrawal_approval") ?? "Are you sure you want to approve this request? This means you have sent the money." }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28c76f',
                cancelButtonColor: '#d33',
                confirmButtonText: '{{ __("general.yes") }}',
                cancelButtonText: '{{ __("general.cancel") }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
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
                                window.LaravelDataTables["withdrawal_requests-table"].ajax.reload();
                            });
                        },
                        error: function(xhr) {
                            let msg = '{{ __("admin.an_error_occurred") }}';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire('Error', msg, 'error');
                        }
                    });
                }
            });
        }

        function rejectRequest(id) {
            $('#reject_request_id').val(id);
            $('#admin_notes').val('');
            $('#rejectModal').modal('show');
        }

        function submitReject() {
            let id = $('#reject_request_id').val();
            let notes = $('#admin_notes').val();
            
            if (!notes) {
                Swal.fire('Error', 'Please provide a reason for rejection', 'error');
                return;
            }

            $.ajax({
                url: '{{ url("admin/withdrawal_requests") }}/' + id + '/reject',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    admin_notes: notes
                },
                success: function(response) {
                    $('#rejectModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.LaravelDataTables["withdrawal_requests-table"].ajax.reload();
                    });
                },
                error: function(xhr) {
                    let msg = '{{ __("admin.an_error_occurred") }}';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            });
        }
    </script>
@endpush
