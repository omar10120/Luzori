@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-inbox-item {
            display: flex;
            gap: 1rem;
            padding: 1.1rem 0;
            border-bottom: 1px solid #efe6d8;
        }
        .lz-inbox-item:last-child {
            border-bottom: 0;
        }
        .lz-inbox-item.is-read {
            opacity: 0.72;
        }
        .lz-inbox-thumb {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #efe6d8;
            background: #fff;
            flex-shrink: 0;
        }
        .lz-inbox-title {
            font-weight: 700;
            color: #002b26;
            margin-bottom: 0.25rem;
        }
        .lz-inbox-meta {
            color: #7a8582;
            font-size: 0.78rem;
            white-space: nowrap;
        }
        .lz-inbox-text {
            color: #3a4542;
            margin-bottom: 0.65rem;
        }
        .lz-inbox-empty {
            text-align: center;
            color: #7a8582;
            padding: 2.5rem 1rem;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-inbox-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-mail-opened"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
            <div class="lz-page-actions">
                <button type="button" class="btn btn-luzori-beige btn-sm px-3 py-2" id="markAllRead">
                    <i class="ti ti-checks me-1"></i>{{ __('field.mark_as_read') }}
                </button>
            </div>
        </div>

        <div class="card lz-table-card">
            <div class="card-body">
                @forelse ($notifications as $notification)
                    @php $isRead = (bool) ($notification->pivot->is_read ?? false); @endphp
                    <div class="lz-inbox-item {{ $isRead ? 'is-read' : '' }}">
                        @if ($notification->image_url)
                            <img src="{{ $notification->image_url }}" alt="" class="lz-inbox-thumb" />
                        @else
                            <div class="lz-inbox-thumb d-flex align-items-center justify-content-center">
                                <i class="ti ti-bell" style="color:#8a6a3d;"></i>
                            </div>
                        @endif
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between gap-2 flex-wrap">
                                <h5 class="lz-inbox-title mb-0">{{ $notification->title }}</h5>
                                <small class="lz-inbox-meta">{{ $notification->created_at }}</small>
                            </div>
                            <p class="lz-inbox-text mb-2">{{ $notification->text }}</p>
                            @unless ($isRead)
                                <button type="button" class="btn btn-luzori-primary btn-sm mark-one-read"
                                    data-id="{{ $notification->id }}">
                                    {{ __('field.mark_as_read') }}
                                </button>
                            @endunless
                        </div>
                    </div>
                @empty
                    <div class="lz-inbox-empty">
                        <i class="ti ti-bell-off mb-2" style="font-size:1.75rem;display:block;"></i>
                        {{ __('api.noDataFound') }}
                    </div>
                @endforelse

                @if ($notifications->hasPages())
                    <div class="mt-3">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        $(function () {
            function markSeen(id) {
                $.post(@json(route('center_user.notifications.markSeen')), {
                    _token: @json(csrf_token()),
                    id: id || null
                }).done(function () {
                    location.reload();
                });
            }

            $('#markAllRead').on('click', function () {
                markSeen(null);
            });

            $('.mark-one-read').on('click', function () {
                markSeen($(this).data('id'));
            });
        });
    </script>
@endsection
