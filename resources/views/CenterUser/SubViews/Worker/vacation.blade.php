@extends('layouts/layoutMaster')

@section('title', $title)



@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <input type="hidden" name="worker_id" value="{{ $item->id }}">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }} ({{ $item->name }})</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="day" class="form-labe">{{ __('field.date') }} </label>
                                    <input type="date" id="day" class="form-control" name="day"
                                        placeholder="{{ __('field.date') }}" />
                                </div>
                            </div>
                            <div class="col-md-12 mb-4">
                                <div class="mb-1">
                                    <label for="describe" class="form-label">{{ __('field.description') }}
                                    </label>
                                    <textarea id="describe" class="form-control" name="describe" cols="30" rows="5"
                                        placeholder="{{ __('field.description') }}"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <h1>{{ __('field.all_vacations') }}</h1>
                                <div class="table-responsive text-center">
                                    <table class="table table-striped table-head-custom table-checkable" id="dtTable">
                                        <thead>
                                            <tr>
                                                <th>{{ __('field.date') }}</th>
                                                <th>{{ __('field.description') }} </th>
                                                <td>{{ __('field.action') }}</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($item->vacations as $vacation)
                                                <tr>
                                                    <td>{{ $vacation->day }}</td>
                                                    <td>{{ $vacation->describe }}</td>
                                                    <td>
                                                        <a class="btn btn-danger" data-id="{{ $vacation->id }}"
                                                            style="color: white;"
                                                            onclick="deleteEntity('Vacation', '{{ $vacation->id }}', 'FORCE_DELETE', 0)">
                                                            <i class="fa fa-trash me-2"></i>{{ __('general.delete') }}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary submitFrom">
                            <i class="menu-icon tf-icons ti ti-check"></i>
                            <span>{{ __('general.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('vendor-script')
    @include('CenterUser.Components.datatable-js')
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')

    @routes
    <script>
        function deleteEntity(model, id, operation, withTrashed) {
            var url = route('center_user.delete', {
                model: model,
                id: id,
                operation: operation,
                withTrashed: withTrashed
            });
            Swal.fire({
                title: "{{ __('admin.sure_delete') }}",
                icon: 'warning',
                html: "{{ __('admin.not_be_able_to_back') }}",
                showDenyButton: true,
                confirmButtonText: "{{ __('general.delete') }}",
                denyButtonText: "{{ __('general.cancel') }}",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        dataType: 'json',
                        data: {
                            _token: '{{ csrf_token() }}',
                        },
                        success: function(response, textStatus, xhr) {
                            Swal.fire({
                                title: "{{ __('general.success') }}",
                                icon: 'success',
                                timer: 3000
                            });

                            window.location.reload();
                        },
                        error: function(response) {
                            Swal.fire({
                                title: "{{ __('general.error') }}",
                                icon: 'error',
                                timer: 3000
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
