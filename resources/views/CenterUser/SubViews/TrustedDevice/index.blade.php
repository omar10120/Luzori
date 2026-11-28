@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="luzori-page container-fluid px-1 px-sm-2 px-md-4">
        @include('CenterUser.Components.breadcrumbs')

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="lz-page-toolbar mb-3">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon"><i class="ti ti-devices"></i></div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>{{ __('field.device_otp_extra_emails') }}</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('center_user.trusted-devices.extra-emails') }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-9">
                        <input type="text" class="form-control" name="device_otp_extra_emails"
                            value="{{ $center->device_otp_extra_emails }}"
                            placeholder="admin2@example.com, security@example.com">
                        <small class="text-muted">{{ __('field.device_otp_extra_emails_hint') }}</small>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100">{{ __('general.save') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>{{ __('field.trusted_devices') }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ __('field.device_name') }}</th>
                            <th>{{ __('field.user') }}</th>
                            <th>{{ __('field.device_type') }}</th>
                            <th>{{ __('field.last_used_at') }}</th>
                            <th>{{ __('field.status') }}</th>
                            <th>{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($devices as $device)
                            <tr>
                                <td>{{ $device->device_name }}<br><small class="text-muted">{{ $device->browser }} / {{ $device->platform }}</small></td>
                                <td>{{ $userNames[$device->user_id] ?? ('#'.$device->user_id) }}</td>
                                <td>{{ $device->device_type }}</td>
                                <td>{{ optional($device->last_used_at)->format('Y-m-d H:i') ?: '—' }}</td>
                                <td>
                                    @if ($device->is_blocked)
                                        <span class="badge bg-danger">{{ __('field.blocked') }}</span>
                                    @else
                                        <span class="badge bg-success">{{ __('field.trusted') }}</span>
                                    @endif
                                </td>
                                <td class="d-flex gap-1">
                                    @unless ($device->is_blocked)
                                        <form method="POST" action="{{ route('center_user.trusted-devices.block', $device->id) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-warning">{{ __('field.block') }}</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('center_user.trusted-devices.destroy', $device->id) }}" onsubmit="return confirm('{{ __('general.sure_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">{{ __('general.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">{{ __('general.no_data') ?? '—' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>{{ __('field.pending_verification') }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('field.user') }}</th>
                            <th>Device UUID</th>
                            <th>{{ __('field.expires_at') ?? 'Expires' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pendingOtps as $otp)
                            <tr>
                                <td>{{ $userNames[$otp->user_id] ?? ('#'.$otp->user_id) }}</td>
                                <td><code>{{ $otp->device_uuid }}</code></td>
                                <td>{{ optional($otp->expires_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted">{{ __('general.no_data') ?? '—' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>{{ __('field.active_sessions') }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>{{ __('field.user') }}</th>
                            <th>{{ __('field.device_name') }}</th>
                            <th>IP</th>
                            <th>{{ __('field.started_at') ?? 'Started' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr>
                                <td>{{ $userNames[$session->user_id] ?? ('#'.$session->user_id) }}</td>
                                <td>{{ $session->device?->device_name ?: '—' }}</td>
                                <td>{{ $session->ip_address }}</td>
                                <td>{{ optional($session->started_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">{{ __('general.no_data') ?? '—' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>{{ __('field.auth_logs') }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('field.created_at') }}</th>
                            <th>{{ __('field.user') }}</th>
                            <th>{{ __('field.action') ?? 'Action' }}</th>
                            <th>IP</th>
                            <th>{{ __('field.notes') ?? 'Notes' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ $userNames[$log->user_id] ?? ($log->user_id ? '#'.$log->user_id : '—') }}</td>
                                <td>{{ $log->action }}</td>
                                <td>{{ $log->ip_address }}</td>
                                <td>{{ $log->notes }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted">{{ __('general.no_data') ?? '—' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
