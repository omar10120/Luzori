{{--
  Language switcher — use @include('components.lang_switcher', ['variant' => 'header-desktop'])
  Variants: header-desktop | header-mobile | register
--}}
@php
    $variant = $variant ?? 'header-desktop';
    $dropdownId = match ($variant) {
        'header-mobile' => 'mobileLanguageDropdown',
        'register' => 'registerLanguageDropdown',
        default => 'languageDropdown',
    };
    $isAr = app()->getLocale() === 'ar';
    $currentLabel = $isAr ? '🇦🇪 ' . __('website.lang_ar') : '🇺🇸 ' . __('website.lang_en');
@endphp

<div @class([
    'dropdown',
    'me-3' => $variant === 'header-desktop',
    'lang-switcher-register' => $variant === 'register',
])>
    <button
        @class([
            'btn btn-sm dropdown-toggle',
            'btn-outline-warning' => $variant !== 'register',
            'register-lang-btn' => $variant === 'register',
        ])
        type="button"
        id="{{ $dropdownId }}"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        @if ($variant === 'header-mobile')
            style="font-size: 0.7rem; padding: 0.2rem 0.4rem;"
        @endif
    >
        {{ $currentLabel }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="{{ $dropdownId }}">
        <li>
            <a class="dropdown-item" href="{{ route('locale.switch', 'en') }}">
                🇺🇸 {{ __('website.lang_en') }}
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('locale.switch', 'ar') }}">
                🇦🇪 {{ __('website.lang_ar') }}
            </a>
        </li>
    </ul>
</div>
