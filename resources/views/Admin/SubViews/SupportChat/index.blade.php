@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/css/luzori-support.css') }}?v=10">
@endsection

@section('content')
    <div class="luzori-page container-fluid px-1 px-sm-2 px-md-4 lz-chat-page">
        <div class="lz-page-toolbar mb-3">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon"><i class="ti ti-messages"></i></div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        <div class="lz-chat-shell" id="lzChatShell">
            <aside class="lz-chat-aside">
                <div class="lz-chat-aside-head">
                    <h2>{{ __('locale.centers') }}</h2>
                    <p>{{ __('field.support_chat_admin_hint') }}</p>
                </div>
                <div class="lz-chat-search">
                    <input type="search" id="lzChatSearch" placeholder="{{ __('general.search') }}..." autocomplete="off">
                </div>
                <div class="lz-chat-list" id="lzChatContacts">
                    @forelse($conversations as $row)
                        <button type="button"
                            class="lz-chat-item"
                            data-center-id="{{ $row['center_id'] }}"
                            data-name="{{ $row['center_name'] }}"
                            data-domain="{{ $row['center_domain'] }}"
                            data-search="{{ $row['center_name'] }} {{ $row['center_domain'] }}">
                            <span class="lz-chat-avatar">{{ mb_strtoupper(mb_substr($row['center_name'] ?: 'C', 0, 1)) }}</span>
                            <span class="lz-chat-item-body">
                                <span class="lz-chat-item-top">
                                    <span class="lz-chat-item-name">{{ $row['center_name'] }}</span>
                                    <span class="lz-chat-item-time">{{ $row['last_message_human'] }}</span>
                                </span>
                                <span class="d-flex justify-content-between gap-2 align-items-center">
                                    <span class="lz-chat-item-preview">{{ $row['last_message'] ?: '—' }}</span>
                                    @if($row['unread'] > 0)
                                        <span class="lz-chat-badge">{{ $row['unread'] }}</span>
                                    @endif
                                </span>
                                <span class="lz-chat-item-typing">{{ __('field.typing_now') }}</span>
                            </span>
                        </button>
                    @empty
                        <div class="p-3 text-muted">{{ __('field.no_data_found') }}</div>
                    @endforelse
                </div>
            </aside>

            <section class="lz-chat-main">
                <div class="lz-chat-main-head">
                    <button type="button" class="lz-chat-back" id="lzChatBack" aria-label="{{ __('general.back') }}">
                        <i class="ti ti-arrow-left"></i>
                    </button>
                    <span class="lz-chat-avatar" id="lzChatHeadAvatar"><i class="ti ti-building-store"></i></span>
                    <div class="lz-chat-head-text">
                        <h3 id="lzChatTitle">{{ __('field.select_conversation') }}</h3>
                        <div class="lz-chat-typing d-none" id="lzChatTyping" aria-live="polite">
                            <span class="lz-typing-dot"></span>
                            <span class="lz-typing-text"></span>
                        </div>
                        <span id="lzChatSubtitle" class="lz-chat-subtitle">{{ __('field.support_chat_pick_center') }}</span>
                    </div>
                </div>
                <div class="lz-chat-messages" id="lzChatMessages"></div>
                <div class="lz-chat-empty" id="lzChatEmpty">
                    <i class="ti ti-message-circle"></i>
                    {{ __('field.support_chat_empty') }}
                </div>
                <form class="lz-chat-composer" id="lzChatComposer" autocomplete="off">
                    <div class="lz-chat-file-preview d-none" id="lzChatFilePreview">
                        <span class="lz-file-name"></span>
                        <button type="button" class="btn btn-sm btn-link" id="lzChatFileClear">{{ __('general.cancel') }}</button>
                    </div>
                    <div class="lz-chat-composer-row">
                        <label class="lz-chat-attach" title="{{ __('field.attach_file') }}">
                            <i class="ti ti-paperclip"></i>
                            <input type="file" id="lzChatFile" class="d-none"
                                accept="image/*,video/*">
                        </label>
                        <textarea id="lzChatInput" rows="1" placeholder="{{ __('field.type_a_message') }}" disabled></textarea>
                        <button type="submit" class="btn-send" disabled title="{{ __('general.send') }}">
                            <i class="ti ti-send"></i>
                        </button>
                    </div>
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
    <script src="{{ asset('assets/js/luzori-support-chat.js') }}?v=10"></script>
    <script>
    window.__lzSupportSoundUrl = @json(asset('sumsung_galaxy_s4.mp3'));
    (function () {
        var chat = new LuzoriSupportChat({
            mode: 'admin',
            viewer: @json($viewer),
            csrf: @json(csrf_token()),
            firebaseConfig: @json($firebaseConfig),
            firebaseToken: @json($firebaseToken),
            limits: @json($limits),
            i18n: {
                typing: @json(__('field.is_typing')),
                typingNow: @json(__('field.typing_now')),
                unsupported: @json(__('field.unsupported_attachment')),
                tooLarge: @json(__('field.attachment_too_large')),
            },
            routes: {
                messages: @json(url('admin/support-chat/__CENTER__/messages')),
                send: @json(url('admin/support-chat/__CENTER__/send')),
                typing: @json(url('admin/support-chat/__CENTER__/typing')),
                unread: @json(route('admin.support-chat.unread')),
            }
        });
        chat.init();
    })();
    </script>
@endsection
