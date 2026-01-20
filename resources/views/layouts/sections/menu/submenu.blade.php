@php
    if (str_contains(url()->current(), 'admin')) {
        $model = 'Admin';
        $route = 'admins';
        $guard = 'admin';
    } else {
        $model = 'CenterUser';
        $route = 'centers';
        $guard = 'center_api';
    }
@endphp

<ul class="menu-sub">
    @if (isset($menu))
        @foreach ($menu as $submenu)
            @php
                $activeClass = null;
                $active = $configData['layout'] === 'vertical' ? 'active open' : 'active';
                $currentRouteName = Route::currentRouteName();

                if ($currentRouteName === $submenu->slug) {
                    $activeClass = 'active';
                } elseif (isset($submenu->submenu)) {
                    if (gettype($submenu->slug) === 'array') {
                        foreach ($submenu->slug as $slug) {
                            if (str_contains($currentRouteName, $slug) and strpos($currentRouteName, $slug) === 0) {
                                $activeClass = $active;
                            }
                        }
                    } else {
                        if (
                            str_contains($currentRouteName, $submenu->slug) and
                            strpos($currentRouteName, $submenu->slug) === 0
                        ) {
                            $activeClass = $active;
                        }
                    }
                }
            @endphp

            @if (!isset($submenu->permissions))
                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($submenu->url) ? url('/' . ltrim($submenu->url, '/')) : 'javascript:void(0)' }}"
                        class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
                        @if (isset($submenu->icon))
                            <i class="menu-icon tf-icons ti ti-{{ $submenu->icon }}"></i>
                        @endif
                        @isset($submenu->external_icon)
                            @php
                                $isDark = $configData['style'] !== 'light';
                                $subIconName = $submenu->external_icon;
                                if ($isDark) {
                                    if (isset($submenu->external_icon_dark)) {
                                        $subIconName = $submenu->external_icon_dark;
                                    } elseif (!\Illuminate\Support\Str::contains($subIconName, '-dark')) {
                                        $subIconName .= '-dark';
                                    }
                                }
                                $subIconFile = \Illuminate\Support\Str::endsWith($subIconName, '.svg') ? $subIconName : $subIconName . '.svg';
                            @endphp
                            <img src="{{ asset('assets/icons/' . $subIconFile) }}"
                                style="width: 1.375rem;margin: 0 8px 0 0;" alt="">
                        @endisset
                        <div>{{ isset($submenu->name) ? __('locale.' . $submenu->name) : '' }}</div>
                        @isset($submenu->badge)
                            <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}
                            </div>
                        @endisset
                    </a>

                    @if (isset($submenu->submenu))
                        @include('layouts.sections.menu.submenu', ['menu' => $submenu->submenu])
                    @endif
                </li>
            @else
                @canany($submenu->permissions, $guard)
                    <li class="menu-item {{ $activeClass }}">
                        <a href="{{ isset($submenu->url) ? url('/' . ltrim($submenu->url, '/')) : 'javascript:void(0)' }}"
                            class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                            @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
                            @if (isset($submenu->icon))
                                <i class="menu-icon tf-icons ti ti-{{ $submenu->icon }}"></i>
                            @endif
                            @isset($submenu->external_icon)
                                @php
                                    $subIconName2 = $submenu->external_icon;
                                    if ($isDark) {
                                        if (isset($submenu->external_icon_dark)) {
                                            $subIconName2 = $submenu->external_icon_dark;
                                        } elseif (!\Illuminate\Support\Str::contains($subIconName2, '-dark')) {
                                            $subIconName2 .= '-dark';
                                        }
                                    }
                                    $subIconFile2 = \Illuminate\Support\Str::endsWith($subIconName2, '.svg') ? $subIconName2 : $subIconName2 . '.svg';
                                @endphp
                                <img src="{{ asset('assets/icons/' . $subIconFile2) }}"
                                    style="width: 1.375rem;margin: 0 8px 0 0;" alt="">
                            @endisset
                            <div>{{ isset($submenu->name) ? __('locale.' . $submenu->name) : '' }}</div>
                            @isset($submenu->badge)
                                <div class="badge bg-{{ $submenu->badge[0] }} rounded-pill ms-auto">{{ $submenu->badge[1] }}
                                </div>
                            @endisset
                        </a>

                        @if (isset($submenu->submenu))
                            @include('layouts.sections.menu.submenu', ['menu' => $submenu->submenu])
                        @endif
                    </li>
                @endcanany
            @endif
        @endforeach
    @endif
</ul>
