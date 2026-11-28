@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="luzori-page container-fluid px-1 px-sm-2 px-md-4">
        @include('Admin.Components.breadcrumbs')

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="lz-page-toolbar mb-3">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon"><i class="ti ti-devices"></i></div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
            <a href="{{ route('admin.centers.create', ['id' => $center->id]) }}" class="btn btn-luzori-beige btn-sm">{{ __('general.back') }}</a>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>{{ __('field.center_device_limit') }}</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.centers.devices.limit', $center->id) }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    <div class="col-md-4">
                        <label class="form-label">{{ __('field.center_device_limit') }}</label>
                        <input type="number" min="0" class="form-control" name="center_device_limit" value="{{ $center->center_device_limit }}">
                        <small class="text-muted">{{ __('field.center_device_limit_hint') }}</small>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary">{{ __('general.save') }}</button>
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
                                <td>{{ $device->device_name }}<br><small class="text-muted">{{ $device->ip_address }}</small></td>
                                <td>#{{ $device->user_id }}</td>
                                <td>{{ $device->device_type }} / {{ $device->platform }}</td>
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
                                        <form method="POST" action="{{ route('admin.centers.devices.block', [$center->id, $device->id]) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-warning">{{ __('field.block') }}</button>
                                        </form>
                                    @endunless
                                    <form method="POST" action="{{ route('admin.centers.devices.destroy', [$center->id, $device->id]) }}" onsubmit="return confirm('{{ __('general.sure_delete') }}')">
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
                                <td>#{{ $session->user_id }}</td>
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
                                <td>{{ $log->user_id ? '#'.$log->user_id : '—' }}</td>
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
