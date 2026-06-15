<div class="dropdown">
    <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        @if(!empty($options['print']))
            <a class="dropdown-item" href="{{ route($route . '.print', ['id' => $id]) }}" target="_blank">
                <i class="ti ti-print"></i>{{ __('general.print') }}
            </a>
        @endif
        @if(!empty($options['vacation']))
            <a class="dropdown-item" href="{{ route('center_user.vacations.create', ['id' => $id]) }}">
                <i class="ti ti-plus"></i>{{ __('field.vacations') }}
            </a>
        @endif
        @if(!empty($options['show-user-to-wallet']))
            <a class="dropdown-item" href="{{ route('center_user.users_wallets.showUsers', ['id' => $id]) }}">
                <i class="ti ti-eye"></i>{{ __('field.show_users') }}
            </a>
        @endif
        @if(!empty($options['add-user-to-wallet']))
            <a class="dropdown-item" href="{{ route('center_user.users_wallets.create', ['id' => $id]) }}">
                <i class="ti ti-plus"></i>{{ __('field.add_user') }}
            </a>
        @endif
        @if(!empty($options['add-user-to-package']))
            <a class="dropdown-item" href="{{ route('center_user.users_packages.create', ['id' => $id]) }}">
                <i class="ti ti-plus"></i>{{ __('field.add_user') }}
            </a>
        @endif
        @if(!empty($options['show']))
            <a class="dropdown-item" href="{{ route($route . '.show', ['id' => $id]) }}">
                <i class="ti ti-eye"></i>{{ __('general.show') }}
            </a>
        @endif
        @if(!empty($options['edit']))
            <a class="dropdown-item" href="{{ route($route . '.create', ['id' => $id]) }}">
                <i class="ti ti-pin"></i>{{ __('general.edit') }}
            </a>
        @endif
        @if(!empty($options['permissions']))
            <a class="dropdown-item" href="{{ route($route . '.permissions', ['id' => $id]) }}">
                <i class="ti ti-shield"></i>{{ __('general.permissions') }}
            </a>
        @endif
        @if(!empty($options['delete']))
            <a class="dropdown-item" data-id="{{ $id }}" href="#"
                onclick="deleteEntity('{{ $model }}', '{{ $id }}', '{{ $options['operation'] }}', '{{ $options['with_trashed'] }}')">
                <i class="ti ti-trash"></i>{{ __('general.delete') }}
            </a>
        @endif
    </div>
</div>

@routes
<script>
    function deleteEntity(model, id, operation, withTrashed) {
        var url = route('admin.delete', {
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

                        if ($('.table').DataTable().ajax.json())
                            $('.table').DataTable().ajax.reload();
                        else
                            window.location.reload();
                    },
                    error: function(response) {
                        if (response.status == 401) {
                            fireMessage("{{ __('admin.ok') }}",
                                "{{ __('admin.not_allowed') }}",
                                "{{ __('admin.fire_message') }}",
                                'error');
                        } else {
                            fireMessage("{{ __('admin.ok') }}",
                                "{{ __('admin.an_error_occurred') }}",
                                "",
                                'error');
                        }
                    }
                });
            }
        });
    }

    function changeStatus(model, id, operation) {
        var url = route('admin.delete', {
            model: model,
            id: id,
            operation: operation,
            withTrashed: 1
        });

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

                if ($('.table').DataTable().ajax.json())
                    $('.table').DataTable().ajax.reload();
                else
                    window.location.reload();
            },
            error: function(response) {
                if (response.status == 401) {
                    fireMessage("{{ __('admin.ok') }}",
                        "{{ __('admin.not_allowed') }}",
                        "{{ __('admin.fire_message') }}",
                        'error');
                } else {
                    fireMessage("{{ __('admin.ok') }}",
                        "{{ __('admin.an_error_occurred') }}",
                        "",
                        'error');
                }
            }
        });
    }

    function changeStatusWeb(id) {
        var url = route('center_user.centerusers.changeStatusWeb');

        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
            },
            success: function(response, textStatus, xhr) {
                Swal.fire({
                    title: "{{ __('general.success') }}",
                    icon: 'success',
                    timer: 3000
                });

                if ($('.table').DataTable().ajax.json())
                    $('.table').DataTable().ajax.reload();
                else
                    window.location.reload();
            },
            error: function(response) {
                if (response.status == 401) {
                    fireMessage("{{ __('admin.ok') }}",
                        "{{ __('admin.not_allowed') }}",
                        "{{ __('admin.fire_message') }}",
                        'error');
                } else {
                    fireMessage("{{ __('admin.ok') }}",
                        "{{ __('admin.an_error_occurred') }}",
                        "",
                        'error');
                }
            }
        });
    }

    function changeWeekDayStatus(id) {
        var url = route('center_user.weeksdays.changeStatus', {
            id: id,
        });

        $.ajax({
            url: url,
            type: 'POST',
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
            },
            error: function(response) {
                if (response.status == 401) {
                    fireMessage("{{ __('admin.ok') }}",
                        "{{ __('admin.not_allowed') }}",
                        "{{ __('admin.fire_message') }}",
                        'error');
                } else {
                    fireMessage("{{ __('admin.ok') }}",
                        "{{ __('admin.an_error_occurred') }}",
                        "",
                        'error');
                }
            }
        });
    }

    /**
     * Register a center as a MyFatoorah supplier (admin manual action).
     * Called from the centers DataTable action column.
     */
    function createCenterSupplier(id, url) {
        Swal.fire({
            title: "Create MyFatoorah Supplier?",
            html: "This will register the center on MyFatoorah.<br><small class='text-muted'>Commission: 1 fixed + 3% &nbsp;|&nbsp; Deposits: Daily</small>",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: "{{ __('general.confirm') ?? 'Create' }}",
            cancelButtonText: "{{ __('general.cancel') }}",
            confirmButtonColor: '#f4a53a',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing…',
                    allowOutsideClick: false,
                    didOpen: function() { Swal.showLoading(); }
                });
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "{{ __('general.success') }}",
                            text: response.message || 'Supplier created successfully.',
                            icon: 'success',
                            timer: 3000
                        });
                        if ($('.table').DataTable().ajax.json())
                            $('.table').DataTable().ajax.reload();
                        else
                            window.location.reload();
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message)
                            ? xhr.responseJSON.message
                            : "{{ __('admin.an_error_occurred') }}";
                        fireMessage("{{ __('admin.ok') }}", msg, "", 'error');
                    }
                });
            }
        });
    }
</script>
