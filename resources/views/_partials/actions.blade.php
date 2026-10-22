<div class="lz-action-icons" onclick="event.stopPropagation()">
    @if(!empty($options['print']))
        <a class="lz-action-btn" href="{{ route($route . '.print', ['id' => $id]) }}" target="_blank"
            title="{{ __('general.print') }}">
            <i class="ti ti-printer"></i>
        </a>
    @endif
    @if(!empty($options['vacation']))
        <a class="lz-action-btn" href="{{ route('center_user.vacations.create', ['id' => $id]) }}"
            title="{{ __('field.vacations') }}">
            <i class="ti ti-beach"></i>
        </a>
    @endif
    @if(!empty($options['wallet-sales-history']))
        <a class="lz-action-btn" href="{{ route('center_user.users_wallets.sales', ['wallet_id' => $id]) }}"
            title="{{ __('general.all_users_sales_history') }}">
            <i class="ti ti-receipt"></i>
        </a>
    @endif
    @if(!empty($options['show-user-to-wallet']))
        <a class="lz-action-btn" href="{{ route('center_user.users_wallets.showUsers', ['id' => $id]) }}"
            title="{{ __('field.show_users') }}">
            <i class="ti ti-users"></i>
        </a>
    @endif
    @if(!empty($options['add-user-to-package']))
        <a class="lz-action-btn" href="{{ route('center_user.users_packages.create', ['id' => $id]) }}"
            title="{{ __('field.add_user') }}">
            <i class="ti ti-user-plus"></i>
        </a>
    @endif
    {{-- Side drawer details (all Admin tables) — RTL handled by drawer CSS --}}
    @if(empty($options['hide_view_details']))
        <a href="javascript:void(0)"
            class="lz-action-btn lz-open-admin-row-details lz-admin-view-details"
            title="{{ __('general.view_details') }}"
            onclick="event.preventDefault();event.stopPropagation();if(window.lzOpenAdminRowDetails){window.lzOpenAdminRowDetails(this);}return false;">
            <i class="ti ti-eye"></i>
        </a>
    @endif
    @if(!empty($options['show_full_page']))
        <a class="lz-action-btn" href="{{ route($route . '.show', ['id' => $id]) }}"
            title="{{ __('general.show') }}">
            <i class="ti ti-file-info"></i>
        </a>
    @endif
    @if(!empty($options['edit']))
        <a class="lz-action-btn" href="{{ route($route . '.create', ['id' => $id]) }}"
            title="{{ __('general.edit') }}">
            <i class="ti ti-pencil"></i>
        </a>
    @endif
    @if(!empty($options['permissions']))
        <a class="lz-action-btn" href="{{ route($route . '.permissions', ['id' => $id]) }}"
            title="{{ __('general.permissions') }}">
            <i class="ti ti-shield"></i>
        </a>
    @endif
    @if(!empty($options['create_supplier_url']))
        <a href="javascript:void(0)" class="lz-action-btn"
            title="Create Supplier"
            onclick="event.preventDefault(); event.stopPropagation(); createCenterSupplier({{ (int) $id }}, '{{ $options['create_supplier_url'] }}')">
            <i class="ti ti-building-store"></i>
        </a>
    @endif
    @if(!empty($options['delete']))
        <a class="lz-action-btn lz-action-danger" href="javascript:void(0)"
            title="{{ __('general.delete') }}"
            onclick="event.preventDefault(); event.stopPropagation(); deleteEntity('{{ $model }}', '{{ $id }}', '{{ $options['operation'] }}', '{{ $options['with_trashed'] }}')">
            <i class="ti ti-trash"></i>
        </a>
    @endif
</div>

@once
    @routes
    <script>
        if (typeof deleteEntity !== 'function') {
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
                            success: function() {
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
        }

        if (typeof changeStatus !== 'function') {
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
                    success: function() {
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
        }

        if (typeof changeStatusWeb !== 'function') {
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
                    success: function() {
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
        }

        if (typeof changeWeekDayStatus !== 'function') {
            function changeWeekDayStatus(id) {
                var url = route('center_user.weeksdays.changeStatus', { id: id });
                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'json',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
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
        }

        if (typeof createCenterSupplier !== 'function') {
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
                            data: { _token: '{{ csrf_token() }}' },
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
        }
    </script>
@endonce
