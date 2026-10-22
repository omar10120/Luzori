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
                        #{{ $ticket->id }} ·
                        <span class="lz-ticket-status {{ $ticket->status }}">{{ __('field.' . $ticket->status) }}</span>
                        · <span class="lz-ticket-priority {{ $ticket->priority }}">{{ __('field.' . $ticket->priority) }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('center_user.support-tickets.index') }}" class="btn btn-luzori-muted">{{ __('general.back') }}</a>
        </div>

        <div class="card lz-ticket-card mb-3">
            <div class="card-body">
                <div class="lz-ticket-thread">
                    @foreach($ticket->replies as $reply)
                        <div class="lz-ticket-reply {{ $reply->sender_type === 'admin' ? 'admin' : '' }}">
                            <div class="lz-ticket-reply-meta">
                                <strong>{{ $reply->sender_name }}
                                    @if($reply->sender_type === 'admin')
                                        <span class="badge bg-label-primary ms-1">{{ __('locale.admins') }}</span>
                                    @endif
                                </strong>
                                <span>{{ optional($reply->created_at)->diffForHumans() }}</span>
                            </div>
                            <div>{!! nl2br(e($reply->body)) !!}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if(!in_array($ticket->status, ['closed'], true))
            <div class="card lz-ticket-card">
                <div class="card-body">
                    <form method="post" action="{{ route('center_user.support-tickets.reply', $ticket->id) }}">
                        @csrf
                        <label class="form-label">{{ __('field.reply') }}</label>
                        <textarea name="body" class="form-control mb-3" rows="4" required></textarea>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-send me-1"></i>{{ __('general.send') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
