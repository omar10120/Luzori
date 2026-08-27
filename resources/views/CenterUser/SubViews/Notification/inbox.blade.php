@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $title }}</h4>
                <button type="button" class="btn btn-sm btn-outline-primary" id="markAllRead">
                    {{ __('field.mark_as_read') }}
                </button>
            </div>
            <div class="card-body">
                @forelse ($notifications as $notification)
                    @php $isRead = (bool) ($notification->pivot->is_read ?? false); @endphp
                    <div class="d-flex align-items-start border-bottom py-3 {{ $isRead ? 'opacity-75' : '' }}">
                        @if ($notification->image_url)
                            <img src="{{ $notification->image_url }}" alt=""
                                style="width:56px;height:56px;object-fit:cover;border-radius:8px;" class="me-3" />
                        @endif
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-1">{{ $notification->title }}</h5>
                                <small class="text-muted">{{ $notification->created_at }}</small>
                            </div>
                            <p class="mb-2">{{ $notification->text }}</p>
                            @unless ($isRead)
                                <button type="button" class="btn btn-xs btn-label-primary mark-one-read"
                                    data-id="{{ $notification->id }}">
                                    {{ __('field.mark_as_read') }}
                                </button>
                            @endunless
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('api.noDataFound') }}</p>
                @endforelse

                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
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
