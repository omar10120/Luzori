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

{{-- Move DT buttons (Add/Column/Export) into toolbar; card-header is CSS-hidden --}}
<script>
(function () {
    if (!document.body.classList.contains('luzori-center')) return;

    function moveDtActionButtons() {
        document.querySelectorAll('.luzori-page').forEach(function (page) {
            var target = page.querySelector('.lz-page-actions');
            if (!target) return;
            page.querySelectorAll('.dt-action-buttons').forEach(function (source) {
                if (!source.children.length) return;
                while (source.firstChild) {
                    target.appendChild(source.firstChild);
                }
                var header = source.closest('.card-header');
                if (header) header.style.display = 'none';
            });
        });
    }

    function boot() {
        moveDtActionButtons();
        [50, 150, 400, 800, 1500, 2500].forEach(function (ms) {
            setTimeout(moveDtActionButtons, ms);
        });
        if (window.jQuery) {
            jQuery(document).on('init.dt draw.dt', function () {
                setTimeout(moveDtActionButtons, 0);
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
})();
</script>

@include('CenterUser.Components.luzori-row-details')
