@php
    $configData = Helper::appClasses();
@endphp

<aside id="layout-menu" class="layout-menu-horizontal menu-horizontal  menu bg-menu-theme flex-grow-0">
    <div class="{{ $containerNav }} d-flex h-100">
        <ul class="menu-inner">
            @foreach ($menuData[1]->menu as $menu)
                @php
                    $activeClass = null;
                    $currentRouteName = Route::currentRouteName();

                    if ($currentRouteName === $menu->slug) {
                        $activeClass = 'active';
                    } elseif (isset($menu->submenu)) {
                        if (gettype($menu->slug) === 'array') {
                            foreach ($menu->slug as $slug) {
                                if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
                                    $activeClass = 'active';
                                }
                            }
                        } else {
                            if (
                                str_contains($currentRouteName, $menu->slug) and
                                strpos($currentRouteName, $menu->slug) === 0
                            ) {
                                $activeClass = 'active';
                            }
                        }
                    }
                @endphp

                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
                        class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($menu->target) and !empty($menu->target)) target="_blank" @endif>
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
                        <div>{{ isset($menu->name) ? __('locale.' . $menu->name) : '' }}</div>
                    </a>

                    @isset($menu->submenu)
                        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
                    @endisset
                </li>
            @endforeach
        </ul>
    </div>
</aside>
