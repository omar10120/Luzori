@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('CenterUser.Components.datatable-css')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

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

        <div class="card table-responsive">
            <div class="card-header d-flex justify-content-between">
                <h2>{{ __('general.manage') . ' ' . $title }}</h2>
            </div>
            <div class="card-body">
                {{ $dataTable->table() }}
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
        // Handle notes modal
        $(document).on('click', '[data-bs-target^="#notesModal"]', function(e) {
            e.preventDefault();
            
            var target = $(this).data('bs-target');
            var modalId = target.replace('#notesModal', '');
            var notesText = $(this).data('notes');
            
            // Create unique modal for this row if it doesn't exist
            if ($(target).length === 0) {
                var modalHtml = `
                    <div class="modal fade" id="notesModal${modalId}" tabindex="-1" aria-labelledby="notesModalLabel${modalId}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="notesModalLabel${modalId}">Notes Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p id="notesContent${modalId}">${notesText}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(modalHtml);
            } else {
                // Update existing modal content
                $('#notesContent' + modalId).text(notesText);
            }
            
            // Show the modal using Bootstrap 5
            var modal = new bootstrap.Modal(document.getElementById('notesModal' + modalId));
            modal.show();
        });

        // Handle description modal
        $(document).on('click', '[data-bs-target^="#descModal"]', function(e) {
            e.preventDefault();
            
            var target = $(this).data('bs-target');
            var modalId = target.replace('#descModal', '');
            var descText = $(this).data('description');
            
            // Create unique modal for this row if it doesn't exist
            if ($(target).length === 0) {
                var modalHtml = `
                    <div class="modal fade" id="descModal${modalId}" tabindex="-1" aria-labelledby="descModalLabel${modalId}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="descModalLabel${modalId}">Worker Description</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p id="descContent${modalId}">${descText}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $('body').append(modalHtml);
            } else {
                // Update existing modal content
                $('#descContent' + modalId).text(descText);
            }
            
            // Show the modal using Bootstrap 5
            var modal = new bootstrap.Modal(document.getElementById('descModal' + modalId));
            modal.show();
        });
    </script>
@endsection
