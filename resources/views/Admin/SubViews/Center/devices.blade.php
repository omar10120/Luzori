@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-devices-table td,
        .lz-devices-table th {
            vertical-align: middle;
            white-space: nowrap;
        }
        .lz-devices-table .lz-actions {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            flex-wrap: nowrap;
        }
        .lz-devices-table .lz-actions form {
            margin: 0;
            display: inline;
        }
        .lz-devices-table td.lz-wrap {
            white-space: normal;
            min-width: 160px;
        }
    </style>
@endsection

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
                <table class="table table-hover lz-devices-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('field.device_name') }}</th>
                            <th>{{ __('field.user') }}</th>
                            <th>{{ __('field.device_type') }}</th>
                            <th>{{ __('field.last_used_at') }}</th>
                            <th>{{ __('field.status') }}</th>
                            <th>{{ __('field.location') }}</th>
                            <th>{{ __('general.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($devices as $device)
                            <tr>
                                <td class="lz-wrap">
                                    {{ $device->device_name }}
                                    <br><small class="text-muted">{{ $device->ip_address }}</small>
                                </td>
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
                                <td>
                                    @if ($device->hasLocation() || $device->ip_address)
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-show-location
                                            data-lat="{{ $device->latitude }}"
                                            data-lng="{{ $device->longitude }}"
                                            data-ip="{{ $device->ip_address }}">
                                            {{ __('field.show_location') }}
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="lz-actions">
                                        @if ($device->is_blocked)
                                            <form method="POST" action="{{ route('admin.centers.devices.unblock', [$center->id, $device->id]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success">{{ __('field.unblock') }}</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.centers.devices.block', [$center->id, $device->id]) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning">{{ __('field.block') }}</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.centers.devices.destroy', [$center->id, $device->id]) }}" onsubmit="return confirm('{{ __('general.sure_delete') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">{{ __('general.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ __('general.no_data') ?? '—' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>{{ __('field.active_sessions') }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table lz-devices-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('field.user') }}</th>
                            <th>{{ __('field.device_name') }}</th>
                            <th>{{ __('field.location') }}</th>
                            <th>{{ __('field.started_at') ?? 'Started' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr>
                                <td>#{{ $session->user_id }}</td>
                                <td>{{ $session->device?->device_name ?: '—' }}</td>
                                <td>{{ $session->ip_address ?: '—' }}</td>
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
                <table class="table table-sm lz-devices-table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('field.created_at') }}</th>
                            <th>{{ __('field.action') ?? 'Action' }}</th>
                            <th>{{ __('field.device_name') }}</th>
                            <th>{{ __('field.device_id') }}</th>
                            <th>{{ __('field.location') }}</th>
                            <th>{{ __('field.user') }}</th>
                            <th>{{ __('field.show_location') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $device = ($log->device_uuid && isset($devicesByUuid[$log->device_uuid]))
                                    ? $devicesByUuid[$log->device_uuid]
                                    : null;
                                $lat = $log->latitude ?? $device?->latitude;
                                $lng = $log->longitude ?? $device?->longitude;
                                $canShowMap = ($lat !== null && $lng !== null) || !empty($log->ip_address) || !empty($device?->ip_address);
                            @endphp
                            <tr>
                                <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if ($log->action === 'login_success')
                                        <span class="badge bg-success">{{ __('field.login_success') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ __('field.login_failed') }}</span>
                                    @endif
                                </td>
                                <td class="lz-wrap">{{ $device?->device_name ?: '—' }}</td>
                                <td>
                                    @if ($device)
                                        #{{ $device->id }}
                                    @elseif ($log->device_uuid)
                                        <code>{{ \Illuminate\Support\Str::limit($log->device_uuid, 12) }}</code>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $log->ip_address ?: '—' }}</td>
                                <td>{{ $log->user_id ? '#'.$log->user_id : '—' }}</td>
                                <td>
                                    @if ($canShowMap)
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                            data-show-location
                                            data-lat="{{ $lat }}"
                                            data-lng="{{ $lng }}"
                                            data-ip="{{ $log->ip_address ?: ($device?->ip_address ?: '') }}">
                                            {{ __('field.show_location') }}
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted">{{ __('general.no_data') ?? '—' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('CenterUser.Components.device_map_modal', [
        'resolveLocationUrl' => route('admin.centers.devices.resolve-location'),
    ])
@endsection
