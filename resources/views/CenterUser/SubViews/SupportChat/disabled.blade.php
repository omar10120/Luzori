@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="luzori-page container-fluid px-1 px-sm-2 px-md-4">
        <div class="card lz-ticket-card">
            <div class="card-body text-center py-5">
                <i class="ti ti-message-off" style="font-size:2.5rem;color:#c9a46a;"></i>
                <h3 class="mt-3 mb-2">{{ __('field.support_chat_disabled') }}</h3>
                <p class="text-muted mb-0">{{ __('field.support_chat_disabled_hint') }}</p>
            </div>
        </div>
    </div>
@endsection
