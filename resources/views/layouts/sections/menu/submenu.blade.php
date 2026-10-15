@php
    $fontFamily = App::getLocale() == 'ar' ? 'Cairo' : 'Poppins';
@endphp
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Poppins:wght@400;700&display=swap" rel="stylesheet">

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

<ul class="menu-sub" style="font-size:12px;">
    @if (isset($menu))
        @foreach ($menu as $submenu)
            @php
                $activeClass = null;
                // Keep parent groups open only; gold highlight is for the selected leaf.
                $openClass = $configData['layout'] === 'vertical' ? 'open' : 'active';
                $currentRouteName = Route::currentRouteName();
                $currentSegment = request()->segment(2);
                $currentPath = trim(request()->path(), '/');

                if (!isset($submenu->submenu)) {
                    // Leaf: prefer exact URL, then route-name slug list (avoids siblings sharing segment-2).
                    if (!empty($submenu->url)) {
                        $itemPath = trim($submenu->url, '/');
                        if ($currentPath === $itemPath || str_starts_with($currentPath, $itemPath . '/')) {
                            $activeClass = 'active';
                        }
                    }
                    if (!$activeClass && is_array($submenu->slug ?? null)) {
                        if (in_array($currentRouteName, $submenu->slug, true)) {
                            $activeClass = 'active';
                        } elseif (
                            // Backward-compatible segment match only when slug has no route-style dots
                            !str_contains(implode(',', $submenu->slug), '.')
                            && in_array($currentSegment, $submenu->slug, true)
                        ) {
                            $activeClass = 'active';
                        }
                    } elseif (!$activeClass && is_string($submenu->slug ?? null) && $currentRouteName === $submenu->slug) {
                        $activeClass = 'active';
                    }
                } elseif (isset($submenu->submenu)) {
                    if (gettype($submenu->slug) === 'array') {
                        foreach ($submenu->slug as $slug) {
                            if ($currentSegment === $slug || (is_string($currentRouteName) && str_contains($currentRouteName, $slug) && strpos($currentRouteName, $slug) === 0)) {
                                $activeClass = $openClass;
                            }
                        }
                    } else {
                        if (
                            $currentSegment === $submenu->slug ||
                            (is_string($currentRouteName) &&
                                str_contains($currentRouteName, $submenu->slug) &&
                                strpos($currentRouteName, $submenu->slug) === 0)
                        ) {
                            $activeClass = $openClass;
                        }
                    }
                }
            @endphp

            @if (!isset($submenu->permissions))
                <li class="menu-item {{ $activeClass }}">
                    <a href="{{ isset($submenu->url) ? url('/' . ltrim($submenu->url, '/')) : 'javascript:void(0)' }}" style="font-size:12px;"
                        class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                        @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
                        @if (isset($submenu->icon))
                            <i class="menu-icon tf-icons ti ti-{{ $submenu->icon }}"></i>
                        @endif
                        @isset($submenu->external_icon)
                            @php
                                $isDark = $configData['style'] !== 'light';
                                $isCenterTheme = !str_contains(url()->current(), 'admin');
                                $subIconName = $submenu->external_icon;
                                if ($isCenterTheme) {
                                    $subIconName = $submenu->external_icon;
                                } elseif ($isDark) {
                                    if (isset($submenu->external_icon_dark)) {
                                        $subIconName = $submenu->external_icon_dark;
                                    } elseif (!\Illuminate\Support\Str::contains($subIconName, '-dark')) {
                                        $subIconName .= '-dark';
                                    }
                                }
                                $subIconFile = \Illuminate\Support\Str::endsWith($subIconName, '.svg') ? $subIconName : $subIconName . '.svg';
                            @endphp
                            <div class="lz-menu-icon-wrap">
                                <img src="{{ asset('assets/icons/' . $subIconFile) }}" alt="">
                            </div>
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
                        <a href="{{ isset($submenu->url) ? url('/' . ltrim($submenu->url, '/')) : 'javascript:void(0)' }}" style="font-size:12px;"
                            class="{{ isset($submenu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
                            @if (isset($submenu->target) and !empty($submenu->target)) target="_blank" @endif>
                            @if (isset($submenu->icon))
                                <i class="menu-icon tf-icons ti ti-{{ $submenu->icon }}"></i>
                            @endif
                            @isset($submenu->external_icon)
                                @php
                                    $isDark2 = $configData['style'] !== 'light';
                                    $isCenterTheme2 = !str_contains(url()->current(), 'admin');
                                    $subIconName2 = $submenu->external_icon;
                                    if ($isCenterTheme2) {
                                        $subIconName2 = $submenu->external_icon;
                                    } elseif ($isDark2) {
                                        if (isset($submenu->external_icon_dark)) {
                                            $subIconName2 = $submenu->external_icon_dark;
                                        } elseif (!\Illuminate\Support\Str::contains($subIconName2, '-dark')) {
                                            $subIconName2 .= '-dark';
                                        }
                                    }
                                    $subIconFile2 = \Illuminate\Support\Str::endsWith($subIconName2, '.svg') ? $subIconName2 : $subIconName2 . '.svg';
                                @endphp
                                <div class="lz-menu-icon-wrap">
                                    <img src="{{ asset('assets/icons/' . $subIconFile2) }}" alt="">
                                </div>
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
