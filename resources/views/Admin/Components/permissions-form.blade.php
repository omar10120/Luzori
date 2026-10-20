{{--
  Shared Admin permissions picker.
  Expected:
    $permissionGroups  - keyed groups of permissions (models or arrays)
    $selectedKeys      - array of selected permission names OR ids
    $checkBy           - 'name' | 'id' (default name)
    $valueBy           - attribute used as checkbox value (default 'name')
--}}
@php
    $checkBy = $checkBy ?? 'name';
    $valueBy = $valueBy ?? 'name';
    $selectedKeys = array_map('strval', $selectedKeys ?? []);
    $locale = app()->getLocale();
    $totalCount = 0;
    $selectedCount = 0;
    foreach ($permissionGroups as $groupPerms) {
        foreach ($groupPerms as $perm) {
            $totalCount++;
            $key = is_array($perm) ? ($perm[$checkBy] ?? null) : ($perm->{$checkBy} ?? null);
            if ($key !== null && in_array((string) $key, $selectedKeys, true)) {
                $selectedCount++;
            }
        }
    }
@endphp

<div class="lz-perm-form" id="lzPermForm">
    <div class="lz-perm-toolbar">
        <div class="lz-perm-toolbar-left">
            <span class="lz-perm-toolbar-title">{{ __('general.permissions') }}</span>
            <span class="lz-perm-count-pill" id="lzPermSelectedCount">{{ $selectedCount }} / {{ $totalCount }}</span>
        </div>
        <div class="lz-perm-toolbar-actions">
            <button type="button" class="btn btn-sm btn-luzori-beige" id="lzPermSelectAll">
                {{ __('general.select_all') }}
            </button>
            <button type="button" class="btn btn-sm btn-luzori-muted" id="lzPermClearAll">
                {{ __('general.clear') }}
            </button>
        </div>
    </div>

    <div class="lz-perm-groups">
        @foreach ($permissionGroups as $group => $groupPerms)
            @php
                $groupId = 'lz-perm-g-' . md5((string) $group);
                $groupTotal = 0;
                $groupSelected = 0;
                foreach ($groupPerms as $perm) {
                    $groupTotal++;
                    $key = is_array($perm) ? ($perm[$checkBy] ?? null) : ($perm->{$checkBy} ?? null);
                    if ($key !== null && in_array((string) $key, $selectedKeys, true)) {
                        $groupSelected++;
                    }
                }
            @endphp
            <section class="lz-perm-group" data-perm-group="{{ $groupId }}">
                <div class="lz-perm-group-head">
                    <div class="lz-perm-group-title-wrap">
                        <h3 class="lz-perm-group-title">{{ $group }}</h3>
                        <span class="lz-perm-group-count" data-group-count>{{ $groupSelected }}/{{ $groupTotal }}</span>
                    </div>
                    <label class="lz-perm-group-toggle">
                        <input type="checkbox" class="form-check-input lz-perm-group-all"
                            data-group="{{ $groupId }}"
                            {{ $groupTotal && $groupSelected === $groupTotal ? 'checked' : '' }}>
                        <span>{{ __('general.select_all') }}</span>
                    </label>
                </div>
                <div class="lz-perm-grid">
                    @foreach ($groupPerms as $perm)
                        @php
                            $value = is_array($perm) ? ($perm[$valueBy] ?? '') : ($perm->{$valueBy} ?? '');
                            $checkVal = is_array($perm) ? ($perm[$checkBy] ?? '') : ($perm->{$checkBy} ?? '');
                            $label = is_array($perm)
                                ? (($locale === 'ar') ? ($perm['name_ar'] ?? $perm['name'] ?? '') : ($perm['name'] ?? ''))
                                : (($locale === 'ar') ? ($perm->name_ar ?? $perm->name ?? '') : ($perm->name ?? ''));
                            $inputId = 'perm-' . md5($group . '|' . $value);
                            $isChecked = in_array((string) $checkVal, $selectedKeys, true);
                        @endphp
                        <label class="lz-perm-tile{{ $isChecked ? ' is-checked' : '' }}" for="{{ $inputId }}">
                            <input class="form-check-input lz-perm-input"
                                type="checkbox"
                                id="{{ $inputId }}"
                                name="permissions[]"
                                value="{{ $value }}"
                                data-group="{{ $groupId }}"
                                {{ $isChecked ? 'checked' : '' }}>
                            <span class="lz-perm-tile-label">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>

<script>
(function () {
    function refreshPermUi() {
        var root = document.getElementById('lzPermForm');
        if (!root) return;
        var all = root.querySelectorAll('.lz-perm-input');
        var checked = root.querySelectorAll('.lz-perm-input:checked');
        var countEl = document.getElementById('lzPermSelectedCount');
        if (countEl) countEl.textContent = checked.length + ' / ' + all.length;

        root.querySelectorAll('.lz-perm-group').forEach(function (group) {
            var inputs = group.querySelectorAll('.lz-perm-input');
            var on = group.querySelectorAll('.lz-perm-input:checked');
            var count = group.querySelector('[data-group-count]');
            if (count) count.textContent = on.length + '/' + inputs.length;
            var master = group.querySelector('.lz-perm-group-all');
            if (master) {
                master.checked = inputs.length > 0 && on.length === inputs.length;
                master.indeterminate = on.length > 0 && on.length < inputs.length;
            }
            group.querySelectorAll('.lz-perm-tile').forEach(function (tile) {
                var input = tile.querySelector('.lz-perm-input');
                tile.classList.toggle('is-checked', !!(input && input.checked));
            });
        });
    }

    document.addEventListener('change', function (e) {
        var t = e.target;
        if (!t) return;
        if (t.classList.contains('lz-perm-group-all')) {
            var gid = t.getAttribute('data-group');
            document.querySelectorAll('.lz-perm-input[data-group="' + gid + '"]').forEach(function (input) {
                input.checked = t.checked;
            });
            refreshPermUi();
            return;
        }
        if (t.classList.contains('lz-perm-input')) {
            refreshPermUi();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        var selectAll = document.getElementById('lzPermSelectAll');
        var clearAll = document.getElementById('lzPermClearAll');
        if (selectAll) {
            selectAll.addEventListener('click', function () {
                document.querySelectorAll('#lzPermForm .lz-perm-input').forEach(function (i) { i.checked = true; });
                refreshPermUi();
            });
        }
        if (clearAll) {
            clearAll.addEventListener('click', function () {
                document.querySelectorAll('#lzPermForm .lz-perm-input').forEach(function (i) { i.checked = false; });
                refreshPermUi();
            });
        }
        refreshPermUi();
    });
})();
</script>
