<div class="dropdown">
    <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        @isset($options['print'])
            <a class="dropdown-item" href="{{ route($route . '.print', ['id' => $id]) }}" target="_blank">
                <i class="fa fa-print"></i>{{ __('general.print') }}
            </a>
        @endisset
        @isset($options['vacation'])
            <a class="dropdown-item" href="{{ route('center_user.vacations.create', ['id' => $id]) }}">
                <i class="fa fa-plus"></i>{{ __('field.vacations') }}
            </a>
        @endisset
        @isset($options['show-user-to-wallet'])
            <a class="dropdown-item" href="{{ route('center_user.users_wallets.showUsers', ['id' => $id]) }}">
                <i class="ti ti-eye"></i>{{ __('field.show_users') }}
            </a>
        @endisset
        @isset($options['add-user-to-wallet'])
            <a class="dropdown-item" href="{{ route('center_user.users_wallets.create', ['id' => $id]) }}">
                <i class="ti ti-plus"></i>{{ __('field.add_user') }}
            </a>
        @endisset
        @isset($options['show'])
            <a class="dropdown-item" href="{{ route($route . '.show', ['id' => $id]) }}">
                <i class="ti ti-eye"></i>{{ __('general.show') }}
            </a>
        @endisset
        @isset($options['edit'])
            <a class="dropdown-item" href="{{ route($route . '.create', ['id' => $id]) }}">
                <i class="ti ti-pin"></i>{{ __('general.edit') }}
            </a>
        @endisset
        @isset($options['delete'])
            <a class="dropdown-item" data-id="{{ $id }}" href="#"
                onclick="deleteEntity('{{ $model }}', '{{ $id }}', '{{ $options['operation'] }}', '{{ $options['with_trashed'] }}')">
                <i class="ti ti-trash"></i>{{ __('general.delete') }}
            </a>
        @endisset
    </div>
</div>

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
        var url = route('center_user.delete', {
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
</script>
