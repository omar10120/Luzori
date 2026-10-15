@php
    $crumbTitle = $title ?? '';
    $crumbMenu = $menu ?? null;
    $crumbMenuLink = $menu_link ?? null;
    $hasMenuLink = !empty($crumbMenu) && !empty($crumbMenuLink);
@endphp
<div class="row breadcrumbs-top mb-2 lz-breadcrumbs">
    <div class="col-12">
        <div class="breadcrumb-wrapper">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('center_user.cp') }}">{{ __('general.home') }}</a>
                </li>
                @if ($hasMenuLink)
                    <li class="breadcrumb-item">
                        <a href="{{ $crumbMenuLink }}">{{ $crumbMenu }}</a>
                    </li>
                @elseif (!empty($crumbMenu))
                    <li class="breadcrumb-item">
                        <span class="lz-crumb-plain">{{ $crumbMenu }}</span>
                    </li>
                @endif
                @if ($crumbTitle !== '')
                    <li class="breadcrumb-item active">{{ $crumbTitle }}</li>
                @endif
            </ol>
        </div>
    </div>
</div>

@include('CenterUser.Components.show-response-messages')
