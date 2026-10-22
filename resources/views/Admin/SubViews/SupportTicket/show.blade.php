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
                <div>
                    <h1 class="lz-page-title mb-0">{{ $ticket->subject }}</h1>
                    <div class="text-muted small">
                        #{{ $ticket->id }} · {{ $ticket->center->name ?? '—' }} ·
                        <span class="lz-ticket-status {{ $ticket->status }}">{{ __('field.' . $ticket->status) }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.support-tickets.index') }}" class="btn btn-luzori-muted">{{ __('general.back') }}</a>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card lz-ticket-card mb-3">
                    <div class="card-body">
                        <div class="lz-ticket-thread">
                            @foreach($ticket->replies as $reply)
                                <div class="lz-ticket-reply {{ $reply->sender_type === 'admin' ? 'admin' : '' }}">
                                    <div class="lz-ticket-reply-meta">
                                        <strong>{{ $reply->sender_name }}</strong>
                                        <span>{{ optional($reply->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <div>{!! nl2br(e($reply->body)) !!}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card lz-ticket-card">
                    <div class="card-body">
                        <form method="post" action="{{ route('admin.support-tickets.reply', $ticket->id) }}">
                            @csrf
                            <label class="form-label">{{ __('field.reply') }}</label>
                            <textarea name="body" class="form-control mb-3" rows="4" required placeholder="{{ __('field.type_a_message') }}"></textarea>
                            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                <select name="status" class="form-select w-auto">
                                    @foreach(['open','in_progress','resolved','closed'] as $st)
                                        <option value="{{ $st }}" @selected($ticket->status === $st)>{{ __('field.' . $st) }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-send me-1"></i>{{ __('general.send') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card lz-ticket-card">
                    <div class="card-body">
                        <h5 class="mb-3">{{ __('field.details') }}</h5>
                        <dl class="mb-0">
                            <dt>{{ __('locale.centers') }}</dt>
                            <dd>{{ $ticket->center->name ?? '—' }} ({{ $ticket->center->domain ?? '—' }})</dd>
                            <dt>{{ __('field.opened_by') }}</dt>
                            <dd>{{ $ticket->opened_by_name }}</dd>
                            <dt>{{ __('field.priority') }}</dt>
                            <dd class="lz-ticket-priority {{ $ticket->priority }}">{{ __('field.' . $ticket->priority) }}</dd>
                            <dt>{{ __('field.created_at') }}</dt>
                            <dd>{{ optional($ticket->created_at)->format('Y-m-d H:i') }}</dd>
                        </dl>
                        <form method="post" action="{{ route('admin.support-tickets.status', $ticket->id) }}" class="mt-3">
                            @csrf
                            <label class="form-label">{{ __('field.status') }}</label>
                            <div class="input-group">
                                <select name="status" class="form-select">
                                    @foreach(['open','in_progress','resolved','closed'] as $st)
                                        <option value="{{ $st }}" @selected($ticket->status === $st)>{{ __('field.' . $st) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary" type="submit">{{ __('general.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
