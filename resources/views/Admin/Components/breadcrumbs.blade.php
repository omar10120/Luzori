<div class="row breadcrumbs-top mb-2">
    <div class="col-12">
        <div class="breadcrumb-wrapper">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.cp') }}">{{ __('general.home') }}</a>
                </li>
                @if (isset($menu))
                    <li class="breadcrumb-item"><a href="{{ $menu_link }}">{{ $menu }}</a>
                    </li>
                @endif
                <li class="breadcrumb-item active">{{ $title }}
                </li>
            </ol>
        </div>
    </div>
</div>

@include('Admin.Components.show-response-messages')