@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/css/luzori-support.css') }}?v=1">
@endsection

@section('content')
    <div class="luzori-page container-fluid px-1 px-sm-2 px-md-4">
        <div class="lz-page-toolbar mb-3">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon"><i class="ti ti-ticket"></i></div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        <div class="lz-ticket-filters">
            @php
                $filters = [
                    '' => __('general.all') . ' (' . $counts['all'] . ')',
                    'open' => __('field.open') . ' (' . $counts['open'] . ')',
                    'in_progress' => __('field.in_progress') . ' (' . $counts['in_progress'] . ')',
                    'resolved' => __('field.resolved') . ' (' . $counts['resolved'] . ')',
                    'closed' => __('field.closed') . ' (' . $counts['closed'] . ')',
                ];
            @endphp
            @foreach($filters as $key => $label)
                <a class="lz-ticket-pill {{ ($status ?? '') === $key ? 'active' : '' }}"
                   href="{{ route('admin.support-tickets.index', array_filter(['status' => $key ?: null])) }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="card lz-ticket-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('locale.centers') }}</th>
                            <th>{{ __('field.subject') }}</th>
                            <th>{{ __('field.priority') }}</th>
                            <th>{{ __('field.status') }}</th>
                            <th>{{ __('field.opened_by') }}</th>
                            <th>{{ __('field.updated_at') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td>{{ $ticket->center->name ?? '—' }}</td>
                                <td class="fw-semibold">{{ $ticket->subject }}</td>
                                <td><span class="lz-ticket-priority {{ $ticket->priority }}">{{ __('field.' . $ticket->priority) }}</span></td>
                                <td><span class="lz-ticket-status {{ $ticket->status }}">{{ __('field.' . $ticket->status) }}</span></td>
                                <td>{{ $ticket->opened_by_name }}</td>
                                <td>{{ optional($ticket->updated_at)->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.support-tickets.show', $ticket->id) }}" class="btn btn-sm btn-primary">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">{{ __('field.no_data_found') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($tickets->hasPages())
                <div class="p-3">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>
@endsection
