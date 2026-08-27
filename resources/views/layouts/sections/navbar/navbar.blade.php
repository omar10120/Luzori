@php
    $containerNav =
        isset($configData['contentLayout']) && $configData['contentLayout'] === 'compact'
            ? 'container-xxl'
            : 'container-fluid';
    $navbarDetached = $navbarDetached ?? '';
@endphp

@if (str_contains(url()->current(), 'admin'))
    @php
        $model = 'Admin';
        $route = 'admins';
        $guard = 'admin';
    @endphp
@else
    @php
        $model = 'CenterUser';
        $route = 'centers';
        $guard = 'center_user';
    @endphp
@endif

<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
    <nav class="layout-navbar {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} align-items-center bg-navbar-theme"
        id="layout-navbar">
@endif
@if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
@endif

<!--  Brand demo (display only for navbar-full and hide on below xl) -->
@if (isset($navbarFull))
    <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{ route($guard . '.cp') }}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">
                    @if ($configData['style'] === 'light')
                            @include('_partials.macros', ['height' => 20])
                        @else
                            @include('_partials.macros_light', ['height' => 20])
                        @endif
            </span>
            {{-- <span class="app-brand-text demo menu-text fw-bold">{{ $brand }}</span> --}}
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
            <i class="ti ti-x ti-sm align-middle"></i>
        </a>
    </div>
@endif


@if (!isset($navbarHideToggle))
    <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>
@endif

<div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
    
    @if (!isset($menuHorizontal))
        <!-- Search -->
        {{-- <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <a class="nav-item nav-link search-toggler d-flex align-items-center px-0" href="javascript:void(0);">
                    <i class="ti ti-search ti-md me-2"></i>
                    <span class="d-none d-md-inline-block text-muted">Search (Ctrl+/)</span>
                </a>
            </div>
        </div> --}}
        <!-- /Search -->
    @endif
    <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Language -->
        <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <i class='ti ti-language rounded-circle ti-md'></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                @if (str_contains(url()->current(), 'admin'))
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.swap', ['locale' => 'en']) }}"
                            data-language="en">
                            <i class="fi fi-us fis rounded-circle me-1 fs-3"></i>
                            <span class="align-middle">{{ __('English') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.swap', ['locale' => 'ar']) }}"
                            data-language="ar">
                            <i class="fi fi-sa fis rounded-circle me-1 fs-3"></i>
                            <span class="align-middle">العربية</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a class="dropdown-item" href="{{ route('center_user.swap', ['locale' => 'en']) }}"
                            data-language="en">
                            <i class="fi fi-us fis rounded-circle me-1 fs-3"></i>
                            <span class="align-middle">{{ __('English') }}</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('center_user.swap', ['locale' => 'ar']) }}"
                            data-language="ar">
                            <i class="fi fi-sa fis rounded-circle me-1 fs-3"></i>
                            <span class="align-middle">العربية</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
        <!--/ Language -->

        @if (auth('center_user')->check() && !str_contains(url()->current(), 'admin'))
            <!-- Payment portal -->
            <li class="nav-item me-2 me-xl-0">
                <a class="nav-link hide-arrow" href="{{ route('center_user.subscription.plans') }}"
                    title="{{ __('field.payment') }}">
                    <i class="ti ti-credit-card ti-md"></i>
                </a>
            </li>
            <!--/ Payment portal -->
        @endif

        @if (isset($menuHorizontal))
            <!-- Search -->
            <li class="nav-item navbar-search-wrapper me-2 me-xl-0">
                <a class="nav-link search-toggler" href="javascript:void(0);">
                    <i class="ti ti-search ti-md"></i>
                </a>
            </li>
            <!-- /Search -->
        @endif
        @if ($configData['hasCustomizer'] == true)
            <!-- Style Switcher -->
            <li class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class='ti ti-md'></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-styles">
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                            <span class="align-middle"><i class='ti ti-sun me-2'></i>Light</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                            <span class="align-middle"><i class="ti ti-moon me-2"></i>Dark</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                            <span class="align-middle"><i class="ti ti-device-desktop me-2"></i>System</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ Style Switcher -->
        @endif

        <!-- Quick links  -->
        <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                data-bs-auto-close="outside" aria-expanded="false">
                <i class='ti ti-layout-grid-add ti-md'></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end py-0">
                <div class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h5 class="text-body mb-0 me-auto">Shortcuts</h5>
                        <a href="javascript:void(0)" class="dropdown-shortcuts-add text-body" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="Add shortcuts"><i class="ti ti-sm ti-apps"></i></a>
                    </div>
                </div>
                <div class="dropdown-shortcuts-list scrollable-container">
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                <i class="ti ti-calendar fs-4"></i>
                            </span>
                            <a href="{{ url('app/calendar') }}" class="stretched-link">Calendar</a>
                            <small class="text-muted mb-0">Appointments</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                <i class="ti ti-file-invoice fs-4"></i>
                            </span>
                            <a href="{{ url('app/invoice/list') }}" class="stretched-link">Invoice App</a>
                            <small class="text-muted mb-0">Manage Accounts</small>
                        </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                <i class="ti ti-users fs-4"></i>
                            </span>
                            <a href="{{ url('app/user/list') }}" class="stretched-link">User App</a>
                            <small class="text-muted mb-0">Manage Users</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                <i class="ti ti-lock fs-4"></i>
                            </span>
                            <a href="{{ url('app/access-roles') }}" class="stretched-link">Role Management</a>
                            <small class="text-muted mb-0">Permission</small>
                        </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                <i class="ti ti-chart-bar fs-4"></i>
                            </span>
                            <a href="{{ url('/') }}" class="stretched-link">Dashboard</a>
                            <small class="text-muted mb-0">User Profile</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                <i class="ti ti-settings fs-4"></i>
                            </span>
                            <a href="{{ url('pages/account-settings-account') }}" class="stretched-link">Setting</a>
                            <small class="text-muted mb-0">Account Settings</small>
                        </div>
                    </div>
                    <div class="row row-bordered overflow-visible g-0">
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                <i class="ti ti-help fs-4"></i>
                            </span>
                            <a href="{{ url('pages/faq') }}" class="stretched-link">FAQs</a>
                            <small class="text-muted mb-0">FAQs & Articles</small>
                        </div>
                        <div class="dropdown-shortcuts-item col">
                            <span class="dropdown-shortcuts-icon rounded-circle mb-2">
                                <i class="ti ti-square fs-4"></i>
                            </span>
                            <a href="{{ url('modal-examples') }}" class="stretched-link">Modals</a>
                            <small class="text-muted mb-0">Useful Popups</small>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <!-- Quick links -->

        <!-- Notification -->
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                data-bs-auto-close="outside" aria-expanded="false">
                <i class="ti ti-bell ti-md"></i>
                @if (($number_notifications ?? 0) > 0)
                    <span class="badge bg-danger rounded-pill badge-notifications">{{ $number_notifications }}</span>
                @endif
            </a>
            <ul class="dropdown-menu dropdown-menu-end py-0" style="min-width: 350px;">
                <li class="dropdown-menu-header border-bottom">
                    <div class="dropdown-header d-flex align-items-center py-3">
                        <h5 class="text-body mb-0 me-auto">{{ __('locale.notifications') }}</h5>
                        @if (!empty($notifications_mark_all_url))
                            <a href="javascript:void(0)" class="dropdown-notifications-all text-body"
                                data-mark-all-url="{{ $notifications_mark_all_url }}"
                                data-bs-toggle="tooltip" data-bs-placement="top"
                                title="{{ __('field.mark_as_read') }}">
                                <i class="ti ti-mail-opened fs-4"></i>
                            </a>
                        @endif
                    </div>
                </li>
                <li class="dropdown-notifications-list scrollable-container">
                    <ul class="list-group list-group-flush">
                        @forelse (($notis ?? collect()) as $noti)
                            @php
                                $isRead = isset($noti->pivot) ? (bool) $noti->pivot->is_read : true;
                                $title = $noti->title ?? optional($noti->translation)->title ?? '-';
                                $text = $noti->text ?? optional($noti->translation)->text ?? '';
                                $image = $noti->image_url ?? null;
                            @endphp
                            <li class="list-group-item list-group-item-action dropdown-notifications-item {{ $isRead ? 'marked-as-read' : '' }}"
                                data-notification-id="{{ $noti->id }}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar">
                                            @if ($image)
                                                <img src="{{ $image }}" alt class="h-auto rounded-circle" style="width:38px;height:38px;object-fit:cover;">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    <i class="ti ti-bell"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ \Illuminate\Support\Str::limit($title, 40) }}</h6>
                                        <p class="mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($text), 60) }}</p>
                                        <small class="text-muted">{{ $noti->created_at }}</small>
                                    </div>
                                    @if (!empty($notifications_mark_one_url) && !$isRead)
                                        <div class="flex-shrink-0 dropdown-notifications-actions">
                                            <a href="javascript:void(0)"
                                                class="dropdown-notifications-read"
                                                data-mark-url="{{ $notifications_mark_one_url }}"
                                                data-id="{{ $noti->id }}">
                                                <span class="badge badge-dot"></span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item">
                                <div class="text-center text-muted py-3">{{ __('api.noDataFound') }}</div>
                            </li>
                        @endforelse
                    </ul>
                </li>
                <li class="dropdown-menu-footer border-top">
                    <a href="{{ $notifications_view_all_url ?? 'javascript:void(0)' }}"
                        class="dropdown-item d-flex justify-content-center text-primary p-2 h-px-40 mb-1 align-items-center">
                        {{ __('field.view_all_notifications') }}
                    </a>
                </li>
            </ul>
        </li>
        <!--/ Notification -->

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
            <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                <div class="avatar avatar-online">
                    <img src="{{ auth($guard)->user()->image }}" alt class="rounded-circle">
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="#">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <img src="{{ auth($guard)->user()->image }}" alt class="rounded-circle">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <span class="fw-medium d-block">
                                    {{ auth($guard)->user()->name }}
                                </span>
                                <small
                                    class="text-muted">{{ ucfirst(auth($guard)->user()->getRoleNames()->first()) }}</small>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider"></div>
                </li>
                @if (str_contains(url()->current(), 'admin'))
                    <li>
                        <a class="dropdown-item"
                            href="{{ route('admin.admins.create', ['id' => auth($guard)->user()->id]) }}">
                            <i class="ti ti-user-check me-2 ti-sm"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a class="dropdown-item"
                            href="{{ route('center_user.centerusers.create', ['id' => auth($guard)->user()->id]) }}">
                            <i class="ti ti-user-check me-2 ti-sm"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                    </li>
                @endif
                <li>
                    <div class="dropdown-divider"></div>
                </li>
                @if (auth('admin')->check())
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.logout') }}">
                            <i class='ti ti-logout me-2'></i>
                            <span>{{ __('Log out') }}</span>
                        </a>
                    </li>
                @elseif (auth('center_user')->check())
                    <li>
                        <a class="dropdown-item" href="{{ route('center_user.logout') }}">
                            <i class='ti ti-logout me-2'></i>
                            <span>{{ __('Log out') }}</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a class="dropdown-item"
                            href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                            <i class='ti ti-login me-2'></i>
                            <span class="align-middle">{{ __('Login') }}</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
        <!--/ User -->
    </ul>
</div>

<!-- Search Small Screens -->
<div class="navbar-search-wrapper search-input-wrapper {{ isset($menuHorizontal) ? $containerNav : '' }} d-none">
    <input type="text"
        class="form-control search-input {{ isset($menuHorizontal) ? '' : $containerNav }} border-0"
        placeholder="Search..." aria-label="Search...">
    <i class="ti ti-x ti-sm search-toggler cursor-pointer"></i>
</div>
@if (isset($navbarDetached) && $navbarDetached == '')
    </div>
@endif
</nav>
<!-- / Navbar -->

@if (!empty($notifications_mark_all_url) || !empty($notifications_mark_one_url))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = @json(csrf_token());

    function markSeen(url, id) {
        if (!url) return Promise.resolve();
        const body = new FormData();
        body.append('_token', csrf);
        if (id) body.append('id', id);
        return fetch(url, { method: 'POST', body: body, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function () { window.location.reload(); })
            .catch(function () {});
    }

    document.querySelectorAll('.dropdown-notifications-all').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            markSeen(el.getAttribute('data-mark-all-url'), null);
        });
    });

    document.querySelectorAll('.dropdown-notifications-read').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            markSeen(el.getAttribute('data-mark-url'), el.getAttribute('data-id'));
        });
    });
});
</script>
@endif
