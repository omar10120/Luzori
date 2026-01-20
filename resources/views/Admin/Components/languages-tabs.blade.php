<ul class="nav nav-tabs" role="tablist">
    @foreach (Config::get('translatable.locales') as $locale)
        <li class="nav-item">
            <a class="nav-link {{ $loop->first ? 'active' : null }}"
                id="{{ $locale }}-tab-add" data-bs-toggle="tab"
                href="#{{ $locale }}-add" aria-controls="{{ $locale }}-add"
                role="tab" aria-selected="true">
                <i class="menu-icon tf-icons ti ti-flag"></i>
                {{ Str::upper($locale) }}</a>
        </li>
    @endforeach
</ul>
