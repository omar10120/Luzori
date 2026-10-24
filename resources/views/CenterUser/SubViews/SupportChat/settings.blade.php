@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/css/luzori-support.css') }}?v=2">
@endsection

@section('content')
    <div class="luzori-page container-fluid px-1 px-sm-2 px-md-4">
        <div class="lz-page-toolbar mb-3">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon"><i class="ti ti-settings"></i></div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        @if(!$centerChatEnabled || !$centerTicketsEnabled)
            <div class="alert alert-warning">
                {{ __('field.support_disabled_by_admin') }}
                @if(!$centerChatEnabled) <div>• {{ __('locale.support_chat') }}</div> @endif
                @if(!$centerTicketsEnabled) <div>• {{ __('locale.support_tickets') }}</div> @endif
            </div>
        @endif

        <div class="card lz-ticket-card">
            <div class="card-body">
                <p class="text-muted mb-3">{{ __('field.support_branch_settings_hint') }}</p>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('field.branch') }}</th>
                                <th>{{ __('locale.support_chat') }}</th>
                                <th>{{ __('locale.support_tickets') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $branch)
                                <tr data-branch-id="{{ $branch->id }}">
                                    <td class="fw-semibold">{{ $branch->name ?? ('#' . $branch->id) }}</td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input lz-branch-toggle"
                                                data-field="support_chat_enabled"
                                                @checked($branch->support_chat_enabled ?? true)
                                                @disabled(!$centerChatEnabled)>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input lz-branch-toggle"
                                                data-field="support_tickets_enabled"
                                                @checked($branch->support_tickets_enabled ?? true)
                                                @disabled(!$centerTicketsEnabled)>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted text-center py-4">{{ __('field.no_data_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
<script>
(function () {
    var csrf = @json(csrf_token());
    var urlTpl = @json(url('center_user/support-chat/branches/__ID__/toggle'));
    document.querySelectorAll('.lz-branch-toggle').forEach(function (el) {
        el.addEventListener('change', function () {
            var tr = el.closest('tr');
            var id = tr && tr.getAttribute('data-branch-id');
            if (!id) return;
            var payload = {};
            payload[el.getAttribute('data-field')] = el.checked ? 1 : 0;
            fetch(urlTpl.replace('__ID__', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (!data || !data.success) {
                    el.checked = !el.checked;
                    if (window.toastr) toastr.error((data && data.message) || 'Error');
                } else if (window.toastr) {
                    toastr.success(@json(__('general.success')));
                }
            }).catch(function () { el.checked = !el.checked; });
        });
    });
})();
</script>
@endsection
