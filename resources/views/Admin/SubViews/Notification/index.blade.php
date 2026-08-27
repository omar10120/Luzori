@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @include('Admin.Components.datatable-css')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('content')
    <div class="container">
        @include('Admin.Components.show-response-messages')

        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-notifications" role="tab">
                    {{ __('field.push_notifications') }}
                </button>
            </li>
            <li class="nav-item">
                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-firebase" role="tab">
                    {{ __('field.firebase_settings') }}
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-notifications" role="tabpanel">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">{{ __('field.new_notification') }}</h4>
                    </div>
                    <div class="card-body">
                        <form id="frmSendNotification" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.target_type') }}</label>
                                    <select class="form-select" name="target_type" id="target_type">
                                        <option value="users">{{ __('field.users') }}</option>
                                        <option value="centers">{{ __('field.centers') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.users') }} / {{ __('field.centers') }}</label>
                                    <select class="select2 form-control" name="recipients[]" id="recipients" multiple>
                                        <option value="all" selected>{{ __('field.all_users') }}</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" data-type="users">{{ $user->name }} ({{ $user->email ?? $user->phone }})</option>
                                        @endforeach
                                        @foreach ($centers as $center)
                                            <option value="{{ $center->id }}" data-type="centers" style="display:none;">{{ $center->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @include('Admin.Components.languages-tabs')
                            <div class="tab-content mb-3">
                                @foreach (Config::get('translatable.locales') as $locale)
                                    <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                        aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('field.title') }} ({{ strtoupper($locale) }})</label>
                                            <input type="text" class="form-control" name="{{ $locale }}[title]"
                                                placeholder="{{ __('field.new_notification') }}" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">{{ __('field.description') }} ({{ strtoupper($locale) }})</label>
                                            <textarea class="form-control" name="{{ $locale }}[text]" rows="4"
                                                placeholder="{{ __('field.description') }}"></textarea>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mb-3">
                                <label class="form-label">{{ __('field.image') }} (1:1 — JPEG, PNG, JPG, WEBP — max 50MB)</label>
                                <input type="file" class="form-control" name="image" accept=".jpeg,.jpg,.png,.webp,image/*" />
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-label-secondary">{{ __('general.reset') }}</button>
                                <button type="submit" class="btn btn-primary" id="btnSendNotification">
                                    <i class="ti ti-send me-1"></i>{{ __('field.send_notification') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ __('locale.notifications') }}</h4>
                    </div>
                    <div class="card-body">
                        {{ $dataTable->table() }}
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-firebase" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">{{ __('field.firebase_settings') }}</h4>
                        <p class="text-muted mb-0 mt-1">
                            Here fill up the following data &amp; setup the firebase to work properly the notifications of your system.
                        </p>
                    </div>
                    <div class="card-body">
                        <form id="frmFirebaseSettings">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">{{ __('field.service_account_content') }}</label>
                                <textarea class="form-control" name="service_account_json" rows="10"
                                    placeholder='{"type":"service_account","project_id":"..."}'>{{ old('service_account_json', $firebase->service_account_json) }}</textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.api_key') }}</label>
                                    <input type="text" class="form-control" name="api_key" value="{{ old('api_key', $firebase->api_key) }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.auth_domain') }}</label>
                                    <input type="text" class="form-control" name="auth_domain" value="{{ old('auth_domain', $firebase->auth_domain) }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.project_id') }}</label>
                                    <input type="text" class="form-control" name="project_id" value="{{ old('project_id', $firebase->project_id) }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.storage_bucket') }}</label>
                                    <input type="text" class="form-control" name="storage_bucket" value="{{ old('storage_bucket', $firebase->storage_bucket) }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.messaging_sender_id') }}</label>
                                    <input type="text" class="form-control" name="messaging_sender_id" value="{{ old('messaging_sender_id', $firebase->messaging_sender_id) }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.app_id') }}</label>
                                    <input type="text" class="form-control" name="app_id" value="{{ old('app_id', $firebase->app_id) }}" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">{{ __('field.measurement_id') }}</label>
                                    <input type="text" class="form-control" name="measurement_id" value="{{ old('measurement_id', $firebase->measurement_id) }}" />
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2">
                                <button type="reset" class="btn btn-label-secondary">{{ __('general.reset') }}</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i>{{ __('general.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @include('Admin.Components.datatable-js')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(function () {
            $('.select2').select2();

            const $recipients = $('#recipients');
            const allUsersLabel = @json(__('field.all_users'));
            const allCentersLabel = @json(__('field.all_centers'));

            function refreshRecipients() {
                const type = $('#target_type').val();
                $recipients.find('option').each(function () {
                    const $opt = $(this);
                    if ($opt.val() === 'all') {
                        $opt.text(type === 'centers' ? allCentersLabel : allUsersLabel);
                        return;
                    }
                    const optType = $opt.data('type');
                    if (optType === type) {
                        $opt.prop('disabled', false).show();
                    } else {
                        $opt.prop('disabled', true).prop('selected', false).hide();
                    }
                });
                $recipients.val(['all']).trigger('change');
            }

            $('#target_type').on('change', refreshRecipients);
            refreshRecipients();

            $('#frmSendNotification').on('submit', function (e) {
                e.preventDefault();
                const $btn = $('#btnSendNotification');
                $btn.prop('disabled', true);
                $.ajax({
                    url: @json($sendUrl),
                    type: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (typeof toastr !== 'undefined') toastr.success(res.message);
                        else alert(res.message);
                        window.LaravelDataTables['notifications-table'].ajax.reload();
                        $('#frmSendNotification')[0].reset();
                        refreshRecipients();
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || Object.values(xhr.responseJSON?.errors || {}).flat().join('\n') || 'Error';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                        else alert(msg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                    }
                });
            });

            $('#frmFirebaseSettings').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: @json($firebaseUrl),
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        if (typeof toastr !== 'undefined') toastr.success(res.message);
                        else alert(res.message);
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Error';
                        if (typeof toastr !== 'undefined') toastr.error(msg);
                        else alert(msg);
                    }
                });
            });

            $(document).on('click', '.resend-notification', function () {
                const id = $(this).data('id');
                $.post(@json(route('admin.notifications.resend')), { _token: @json(csrf_token()), id: id })
                    .done(function (res) {
                        if (typeof toastr !== 'undefined') toastr.success(res.message);
                    })
                    .fail(function (xhr) {
                        if (typeof toastr !== 'undefined') toastr.error(xhr.responseJSON?.message || 'Error');
                    });
            });

            $(document).on('change', '.toggle-notification-status', function () {
                const id = $(this).data('id');
                $.post(@json(route('admin.notifications.toggleStatus')), { _token: @json(csrf_token()), id: id })
                    .fail(function (xhr) {
                        if (typeof toastr !== 'undefined') toastr.error(xhr.responseJSON?.message || 'Error');
                    });
            });
        });
    </script>
@endsection
