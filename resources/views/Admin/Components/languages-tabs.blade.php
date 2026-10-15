<ul class="nav nav-tabs lz-lang-tabs" role="tablist">
    @foreach (Config::get('translatable.locales') as $locale)
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $loop->first ? 'active' : null }}"
                id="{{ $locale }}-tab-add" data-bs-toggle="tab"
                href="#{{ $locale }}-add" aria-controls="{{ $locale }}-add"
                role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                <span class="lz-lang-flag lz-lang-flag-{{ $locale }}" aria-hidden="true"></span>
                {{ Str::upper($locale) }}
            </a>
        </li>
    @endforeach
</ul>
