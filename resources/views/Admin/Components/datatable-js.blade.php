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

@include('Admin.Components.luzori-permissions-drawer')

<script>
(function () {
    function relocateAdminDtButtons() {
        var slot = document.getElementById('lzAdminDtActions');
        if (!slot) return;
        var source = document.querySelector('.lz-table-card .dt-action-buttons, .lz-table-card .dt-buttons, .dataTables_wrapper .dt-action-buttons');
        if (!source || slot.contains(source)) return;
        slot.appendChild(source);
        document.querySelectorAll('.dataTables_wrapper > .card-header').forEach(function (el) {
            el.classList.add('lz-dt-header-moved');
        });
        // Normalize leftover warning classes from theme
        slot.querySelectorAll('.btn-warning').forEach(function (btn) {
            btn.classList.remove('btn-warning');
            btn.classList.add('btn-luzori-beige');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        relocateAdminDtButtons();
        [100, 300, 800, 1600].forEach(function (ms) {
            setTimeout(relocateAdminDtButtons, ms);
        });
        if (window.jQuery) {
            jQuery(document).on('init.dt draw.dt', function () {
                setTimeout(relocateAdminDtButtons, 0);
            });
            // ColVis: keep layout in sync when toggling visible(false) columns
            jQuery(document).on('column-visibility.dt', function (e, settings) {
                try {
                    var api = new jQuery.fn.dataTable.Api(settings);
                    setTimeout(function () {
                        api.columns.adjust();
                        api.draw(false);
                    }, 0);
                } catch (err) {}
            });
        }
    });
})();
</script>