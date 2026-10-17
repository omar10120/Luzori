<div class="lz-action-icons" onclick="event.stopPropagation()">
    @isset($options['sale_panel'])
        <a href="javascript:void(0)" class="lz-action-btn view-sale-side-details"
            title="{{ __('general.view_details') }}"
            data-sale-panel="{!! $options['sale_panel'] !!}"
            onclick="event.preventDefault(); event.stopPropagation();">
            <i class="ti ti-eye"></i>
        </a>
    @elseif(!isset($options['row_details']) || $options['row_details'])
        <a href="javascript:void(0)" class="lz-action-btn lz-open-row-details"
            title="{{ __('general.view_details') }}"
            onclick="event.preventDefault(); event.stopPropagation(); if (window.lzOpenRowDetails) { window.lzOpenRowDetails(this); } return false;">
            <i class="ti ti-eye"></i>
        </a>
    @endif
    @isset($options['vacation'])
        <a class="lz-action-btn" href="{{ route('center_user.vacations.create', ['id' => $id]) }}"
            title="{{ __('field.vacations') }}">
            <i class="ti ti-beach"></i>
        </a>
    @endisset
    @isset($options['print'])
        <a class="lz-action-btn" href="{{ route($route . '.print', ['id' => $id]) }}" target="_blank"
            title="{{ __('general.print') }}">
            <i class="ti ti-printer"></i>
        </a>
    @endisset
    @isset($options['wallet-sales-history'])
        <a class="lz-action-btn" href="{{ route('center_user.users_wallets.sales', ['wallet_id' => $id]) }}"
            title="{{ __('general.all_users_sales_history') }}">
            <i class="ti ti-receipt"></i>
        </a>
    @endisset
    @isset($options['show-user-to-wallet'])
        <a class="lz-action-btn" href="{{ route('center_user.users_wallets.showUsers', ['id' => $id]) }}"
            title="{{ __('field.show_users') }}">
            <i class="ti ti-users"></i>
        </a>
    @endisset
    @isset($options['add-user-to-wallet'])
        <a class="lz-action-btn" href="{{ route('center_user.users_wallets.create', ['id' => $id]) }}"
            title="{{ __('field.add_user') }}">
            <i class="ti ti-user-plus"></i>
        </a>
    @endisset
    @isset($options['add-user-to-package'])
        <a class="lz-action-btn" href="{{ route('center_user.users_packages.create', ['id' => $id]) }}"
            title="{{ __('field.add_user') }}">
            <i class="ti ti-user-plus"></i>
        </a>
    @endisset
    @isset($options['edit'])
        <a class="lz-action-btn" href="{{ route($route . '.create', ['id' => $id]) }}"
            title="{{ __('general.edit') }}">
            <i class="ti ti-pencil"></i>
        </a>
    @endisset
    @isset($options['receive_url'])
        <a class="lz-action-btn" href="{{ $options['receive_url'] }}"
            title="{{ __('general.receive') }}">
            <i class="ti ti-package-import"></i>
        </a>
    @endisset
    @isset($options['show'])
        <a class="lz-action-btn" href="{{ route($route . '.show', ['id' => $id]) }}"
            title="{{ __('general.show') }}">
            <i class="ti ti-file-info"></i>
        </a>
    @endisset
    @isset($options['add_tip'])
        <a class="lz-action-btn" href="{{ route($route . '.tip', ['id' => $id]) }}"
            title="{{ __('field.add_tip') }}">
            <i class="ti ti-coin"></i>
        </a>
    @endisset
    @isset($options['sale_otp_delete'])
        <a class="lz-action-btn lz-action-danger" href="javascript:void(0)"
            title="{{ __('general.delete') }}"
            onclick="event.preventDefault(); event.stopPropagation(); saleOtpStart('delete', {{ $id }})">
            <i class="ti ti-trash"></i>
        </a>
    @endisset
    @isset($options['delete'])
        <a class="lz-action-btn lz-action-danger" href="javascript:void(0)"
            title="{{ __('general.delete') }}"
            onclick="event.preventDefault(); event.stopPropagation(); deleteEntity('{{ $model }}', '{{ $id }}', '{{ $options['operation'] }}', '{{ $options['with_trashed'] }}')">
            <i class="ti ti-trash"></i>
        </a>
    @endisset
</div>

@once
    @routes
    <script>
        if (typeof deleteEntity !== 'function') {
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
                    data: { _token: '{{ csrf_token() }}' },
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
    </script>
@endonce
