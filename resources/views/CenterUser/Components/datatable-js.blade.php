@vite([
  'resources/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
  'resources/assets/vendor/libs/moment/moment.js',
  'resources/assets/vendor/libs/flatpickr/flatpickr.js',
  'resources/assets/vendor/libs/@form-validation/popular.js',
  'resources/assets/vendor/libs/@form-validation/bootstrap5.js',
  'resources/assets/vendor/libs/@form-validation/auto-focus.js',
  'resources/assets/vendor/libs/select2/select2.js'
])

<script type="module" src="https://cdn.datatables.net/buttons/2.3.4/js/buttons.colVis.min.js"></script>
<script type="module" src="https://cdn.datatables.net/plug-ins/2.0.3/dataRender/ellipsis.js"></script>

{{-- Inject Add button + move Column/Export into toolbar (card-header is CSS-hidden) --}}
<script>
(function () {
    if (!document.body.classList.contains('luzori-center')) return;

    function ensureAddButtons() {
        document.querySelectorAll('.lz-page-actions[data-lz-add-url]').forEach(function (target) {
            if (target.querySelector('.lz-btn-add-new')) return;
            var url = target.getAttribute('data-lz-add-url');
            if (!url) return;
            var label = target.getAttribute('data-lz-add-label') || 'Add New';
            var a = document.createElement('a');
            a.href = url;
            a.className = 'btn btn-luzori-primary btn-sm mx-1 px-3 py-2 lz-btn-add-new';
            a.innerHTML = label + ' <i class="ti ti-plus"></i>';
            target.insertBefore(a, target.firstChild);
        });
    }

    function moveDtActionButtons() {
        document.querySelectorAll('.luzori-page').forEach(function (page) {
            var target = page.querySelector('.lz-page-actions');
            if (!target) return;
            page.querySelectorAll('.dt-action-buttons').forEach(function (source) {
                if (!source.children.length) return;
                while (source.firstChild) {
                    // Skip duplicate Add if somehow still injected by DataTables
                    var node = source.firstChild;
                    if (node.nodeType === 1 && node.classList && node.classList.contains('lz-btn-add-new') && target.querySelector('.lz-btn-add-new')) {
                        source.removeChild(node);
                        continue;
                    }
                    target.appendChild(node);
                }
                var header = source.closest('.card-header');
                if (header) header.style.display = 'none';
            });
        });
        ensureAddButtons();
    }

    function boot() {
        ensureAddButtons();
        moveDtActionButtons();
        [50, 150, 400, 800, 1500, 2500].forEach(function (ms) {
            setTimeout(function () {
                ensureAddButtons();
                moveDtActionButtons();
            }, ms);
        });
        if (window.jQuery) {
            jQuery(document).on('init.dt draw.dt', function () {
                setTimeout(function () {
                    ensureAddButtons();
                    moveDtActionButtons();
                }, 0);
                setTimeout(moveDtActionButtons, 120);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
    window.lzMoveDtActionButtons = moveDtActionButtons;
    window.lzEnsureAddButtons = ensureAddButtons;
})();
</script>

@include('CenterUser.Components.luzori-row-details')
