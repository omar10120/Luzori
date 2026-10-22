@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/css/luzori-support.css') }}?v=1">
@endsection

@section('content')
    <div class="luzori-page container-fluid px-1 px-sm-2 px-md-4 lz-chat-page">
        <div class="lz-page-toolbar mb-3">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon"><i class="ti ti-headset"></i></div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        <div class="lz-chat-shell lz-chat-single">
            <section class="lz-chat-main">
                <div class="lz-chat-main-head">
                    <span class="lz-chat-avatar"><i class="ti ti-lifebuoy"></i></span>
                    <div>
                        <h3 id="lzChatTitle">{{ __('locale.luzori_support') }}</h3>
                        <span id="lzChatSubtitle">{{ __('field.support_chat_center_hint') }}</span>
                    </div>
                </div>
                <div class="lz-chat-messages" id="lzChatMessages"></div>
                <div class="lz-chat-empty d-none" id="lzChatEmpty">
                    <i class="ti ti-message-circle"></i>
                    {{ __('field.support_chat_empty') }}
                </div>
                <form class="lz-chat-composer" id="lzChatComposer" autocomplete="off">
                    <textarea id="lzChatInput" rows="1" placeholder="{{ __('field.type_a_message') }}"></textarea>
                    <button type="submit" class="btn-send" title="{{ __('general.send') }}">
                        <i class="ti ti-send"></i>
                    </button>
                </form>
            </section>
        </div>
    </div>
@endsection

@section('page-script')
    @if(!empty($firebaseConfig))
        <script src="https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/10.14.1/firebase-auth-compat.js"></script>
        <script src="https://www.gstatic.com/firebasejs/10.14.1/firebase-database-compat.js"></script>
    @endif
    <script src="{{ asset('assets/js/luzori-support-chat.js') }}?v=1"></script>
    <script>
    (function () {
        var chat = new LuzoriSupportChat({
            mode: 'center',
            viewer: @json($viewer),
            csrf: @json(csrf_token()),
            firebaseConfig: @json($firebaseConfig),
            firebaseToken: @json($firebaseToken),
            activeCenterId: {{ (int) $center->id }},
            centerName: @json($center->name),
            centerDomain: @json($center->domain),
            initialMessages: @json($messages),
            routes: {
                messages: @json(route('center_user.support-chat.messages')),
                send: @json(route('center_user.support-chat.send')),
                unread: @json(route('center_user.support-chat.unread')),
            }
        });
        chat.init();
    })();
    </script>
@endsection
