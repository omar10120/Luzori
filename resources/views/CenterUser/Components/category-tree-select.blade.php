@php
    $id = $id ?? 'tree_' . uniqid();
    $name = $name ?? 'category_id';
    $label = $label ?? __('field.category');
    $selectedId = $selectedId ?? '';
    $selectedName = $selectedName ?? __('general.choose');
@endphp

<div class="mb-1 position-relative">
    <label class="form-label">{{ $label }}</label>
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center" 
                type="button" id="{{ $id }}_dropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #dbdade;">
            <span id="{{ $id }}_selected_text">{{ $selectedName }}</span>
        </button>
        <div class="dropdown-menu p-3 category-tree-dropdown-menu" aria-labelledby="{{ $id }}_dropdown" style="width: 100%; min-width: 300px; max-height: 400px; overflow-y: auto;">
            <div class="mb-2">
                <input type="text" class="form-control {{ $id }}_search" id="{{ $id }}_search" placeholder="{{ __('general.search') }}..." autocomplete="off">
            </div>
            <div id="{{ $id }}_jstree"></div>
        </div>
    </div>
    <input type="hidden" name="{{ $name }}" id="{{ $id }}_input" value="{{ $selectedId }}">
</div>

@once
    @push('styles')
        @vite('resources/assets/vendor/libs/jstree/jstree.scss')
        <style>
            .jstree-default .jstree-anchor {
                display: inline-block;
                width: 90%;
            }
            .category-tree-dropdown-menu {
                border: 1px solid #dbdade;
                box-shadow: 0 0.25rem 1rem rgba(165, 163, 174, 0.45);
            }
        </style>
    @endpush
    @push('scripts')
        @vite('resources/assets/vendor/libs/jstree/jstree.js')
    @endpush
@endonce

@push('scripts')
<script>
    $(document).ready(function() {
        const treeId = "{{ $id }}";
        const theme = $('html').hasClass('light-style') ? 'default' : 'default-dark';
        const categoriesData = @json($categoriesJson);
        const selectedId = "{{ $selectedId }}";

        $('#' + treeId + '_jstree').jstree({
            core: {
                themes: { name: theme },
                data: categoriesData,
                multiple: false
            },
            plugins: ["search", "types", "wholerow"],
            types: {
                default: { icon: 'ti ti-folder' },
                file: { icon: 'ti ti-file' }
            }
        });

        // Search logic
        let to = false;
        $('#' + treeId + '_search').keyup(function () {
            if(to) { clearTimeout(to); }
            to = setTimeout(function () {
                const v = $('#' + treeId + '_search').val();
                $('#' + treeId + '_jstree').jstree(true).search(v);
            }, 250);
        });

        // Selection logic
        $('#' + treeId + '_jstree').on("select_node.jstree", function (e, data) {
            const node = data.node;
            $('#' + treeId + '_input').val(node.id);
            $('#' + treeId + '_selected_text').text(node.text);
            // Close dropdown manually
            $('#' + treeId + '_dropdown').dropdown('hide');
        });

        // Prevent dropdown from closing when clicking inside
        $('#' + treeId + '_dropdown').parent().find('.dropdown-menu').on('click', function (e) {
            e.stopPropagation();
        });

        // Set initial selection if exists
        if (selectedId) {
            $('#' + treeId + '_jstree').on('ready.jstree', function() {
                $(this).jstree('select_node', selectedId);
            });
        }
    });
</script>
@endpush
