@php
    $configData = Helper::appClasses();
    $currentSegment = request()->segment(2);
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    @if (!isset($navbarFull))
        <div class="app-brand demo">
            @if (str_contains(url()->current(), 'admin'))
                <a href="{{ route('admin.cp') }}" class="app-brand-link">
                    <span class="app-brand-logo demo">
                            @if ($configData['style'] === 'light')
                            @include('_partials.macros', ['height' => 20])
                        @else
                            @include('_partials.macros_light', ['height' => 20])
                        @endif
                    </span>
                </a>
            @else
                <a href="{{ route('center_user.cp') }}" class="app-brand-link">
                    <span class="app-brand-logo demo">
                            @if ($configData['style'] === 'light')
                            @include('_partials.macros', ['height' => 20])
                        @else
                            @include('_partials.macros_light', ['height' => 20])
                        @endif
                    </span>
                </a>
            @endif

            <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
                <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
            </a>
        </div>
    @endif

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($menuData[0]->menu as $menu)
            {{-- Menu Header --}}
            @if (isset($menu->menuHeader))
                <li class="menu-header small text-uppercase">
                    <span class="menu-header-text">{{ __('locale.' . $menu->menuHeader) }}</span>
                </li>
            @else
                @php
                    $activeClass = '';
                    if (in_array($currentSegment, $menu->slug ?? [])) {
                        $activeClass = 'active';
                    }
                @endphp

                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url('/' . ltrim($menu->url, '/')) : 'javascript:void(0);' }}"
                       class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                       @if(isset($menu->target) && !empty($menu->target)) target="_blank" @endif>

                        @isset($menu->icon)
                            <i class="menu-icon tf-icons ti ti-{{ $menu->icon }}"></i>
                        @endisset
                        @isset($menu->external_icon)
                            @php
                                $isDark = $configData['style'] !== 'light';
                                $iconName = $menu->external_icon;
                                if ($isDark) {
                                    if (isset($menu->external_icon_dark)) {
                                        $iconName = $menu->external_icon_dark;
                                    } elseif (!\Illuminate\Support\Str::contains($iconName, '-dark')) {
                                        $iconName .= '-dark';
                                    }
                                }
                                $iconFile = \Illuminate\Support\Str::endsWith($iconName, '.svg') ? $iconName : $iconName . '.svg';
                            @endphp
                            <img src="{{ asset('assets/icons/' . $iconFile) }}"
                                 style="width: 1.375rem;margin: 0 8px 0 0;" alt="">
                        @endisset

                        <div>{{ __('locale.' . $menu->name) }}</div>

                        @isset($menu->badge)
                            <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
                        @endisset
                    </a>

                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>
            @endif
        @endforeach
    </ul>
</aside>
