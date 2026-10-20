{{-- Luzori: eye in Actions opens a right-side details drawer --}}
<style>
    #lzRowDetailsDrawer.lz-row-drawer {
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        left: auto !important;
        width: min(420px, 94vw) !important;
        height: 100vh !important;
        max-width: 100vw !important;
        border: 0 !important;
        z-index: 11000 !important;
        background: #fff !important;
        box-shadow: -8px 0 28px rgba(0, 43, 38, 0.18);
        transform: translateX(100%) !important;
        visibility: visible !important;
        transition: transform 0.25s ease !important;
        display: flex;
        flex-direction: column;
    }
    html[dir="rtl"] #lzRowDetailsDrawer.lz-row-drawer {
        right: auto !important;
        left: 0 !important;
        transform: translateX(-100%) !important;
        box-shadow: 8px 0 28px rgba(0, 43, 38, 0.18);
    }
    #lzRowDetailsDrawer.lz-row-drawer.show,
    html[dir="rtl"] #lzRowDetailsDrawer.lz-row-drawer.show {
        transform: translateX(0) !important;
    }
    .lz-row-drawer-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 10990 !important;
        background: rgba(0, 20, 18, 0.35) !important;
    }
    #lzRowDetailsDrawer .lz-row-drawer-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.15rem;
        background: #fdfaf6;
        border-bottom: 1px solid #efe6d8;
        flex: 0 0 auto;
    }
    #lzRowDetailsDrawer .lz-row-drawer-title {
        margin: 0;
        color: #002b26;
        font-weight: 700;
        font-size: 1.05rem;
    }
    #lzRowDetailsDrawer .lz-row-drawer-body {
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 1rem 1.15rem 1.25rem;
        background: #fff;
    }
    #lzRowDetailsDrawer .lz-row-drawer-footer {
        flex: 0 0 auto;
        padding: 0.85rem 1.15rem 1.1rem;
        border-top: 1px solid #efe6d8;
        background: #fdfaf6;
    }
    #lzRowDetailsDrawer .lz-row-details-table {
        margin: 0;
    }
    #lzRowDetailsDrawer .lz-row-details-table th {
        width: 34%;
        color: #7a8582;
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-color: #efe6d8;
        vertical-align: top;
        background: transparent;
        padding: 0.75rem 0.5rem 0.75rem 0;
    }
    #lzRowDetailsDrawer .lz-row-details-table td {
        color: #1a2a27;
        border-color: #efe6d8;
        vertical-align: top;
        overflow-wrap: anywhere;
        padding: 0.75rem 0;
    }
    #lzRowDetailsDrawer .lz-row-details-table img,
    #lzRowDetailsDrawer .lz-row-drawer-body img {
        display: block !important;
        max-width: 140px !important;
        max-height: 140px !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        border-radius: 12px;
        border: 1px solid #efe6d8;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0, 43, 38, 0.06);
    }
</style>

<div class="lz-row-drawer" id="lzRowDetailsDrawer" tabindex="-1" aria-labelledby="lzRowDetailsDrawerLabel" aria-hidden="true">
    <div class="lz-row-drawer-header">
        <h5 class="lz-row-drawer-title" id="lzRowDetailsDrawerLabel">{{ __('general.view_details') }}</h5>
        <button type="button" class="btn-close" id="lzRowDetailsCloseBtn" aria-label="Close"></button>
    </div>
    <div class="lz-row-drawer-body" id="lzRowDetailsDrawerBody"></div>
    <div class="lz-row-drawer-footer">
        <button type="button" class="btn btn-luzori-muted btn-sm w-100" id="lzRowDetailsCloseFooter">
            {{ __('general.cancel') }}
        </button>
    </div>
</div>

<script>
(function () {
    if (!document.body.classList.contains('luzori-center')) return;

    const detailsTitle = @json(__('general.view_details'));
    const detailsBtnTitle = @json(__('general.view_details'));
    const emptyLabel = '—';
    const actionsLabels = [
        'actions', 'action',
        @json(__('general.actions')),
        'الإجراءات', 'اجراءات', 'العمليات', 'عمليات'
    ].map(function (s) { return String(s || '').toLowerCase().trim(); });

    function detailsButtonHtml() {
        return '<a href="javascript:void(0)" class="lz-action-btn lz-open-row-details lz-view-side-details" title="' +
            detailsBtnTitle.replace(/"/g, '&quot;') +
            '" onclick="event.preventDefault();event.stopPropagation();window.lzOpenRowDetails(this);return false;">' +
            '<i class="ti ti-eye"></i></a>';
    }

    function isActionsHeader(title, $header) {
        if ($header && ($header.hasClass('lz-actions-col') || $header.hasClass('lz-dtr-control'))) return true;
        const t = (title || '').toLowerCase().trim();
        return actionsLabels.indexOf(t) !== -1;
    }

    function findDataTable($, el) {
        const $btn = $(el);
        let $table = $btn.closest('table');
        if ($table.length && $.fn.dataTable.isDataTable($table)) {
            return $table.DataTable();
        }
        const $wrap = $btn.closest('.dataTables_wrapper, .dt-container');
        if ($wrap.length) {
            const $t = $wrap.find('table.dataTable').first();
            if ($t.length && $.fn.dataTable.isDataTable($t)) {
                return $t.DataTable();
            }
        }
        return null;
    }

    function cellDisplay(api, rowIdx, colIdx) {
        try {
            const rendered = api.cell(rowIdx, colIdx).render('display');
            if (rendered !== null && rendered !== undefined && String(rendered).trim() !== '') {
                return constrainImages(String(rendered));
            }
        } catch (e) {}
        try {
            const data = api.cell(rowIdx, colIdx).data();
            if (data !== null && data !== undefined && String(data).trim() !== '') {
                return constrainImages(String(data));
            }
        } catch (e) {}
        return emptyLabel;
    }

    function constrainImages(html) {
        try {
            const $wrap = jQuery('<div>').html(html);
            $wrap.find('img').each(function () {
                this.setAttribute('style', 'max-width:140px;max-height:140px;width:auto;height:auto;object-fit:contain;display:block;');
                this.removeAttribute('width');
                this.removeAttribute('height');
                this.classList.add('lz-details-thumb');
            });
            return $wrap.html();
        } catch (e) {
            return html;
        }
    }

    function isDetailsSkip($header, $cell) {
        if ($header && $header.hasClass('lz-details-skip')) return true;
        if ($cell && $cell.hasClass('lz-details-skip')) return true;
        return false;
    }

    function isNoneColumn($header, $cell, colApi) {
        if ($header && ($header.hasClass('none') || $header.hasClass('never') || $header.hasClass('dtr-hidden'))) {
            return true;
        }
        if ($cell && ($cell.hasClass('none') || $cell.hasClass('never') || $cell.hasClass('dtr-hidden'))) {
            return true;
        }
        try {
            if (colApi && typeof colApi.visible === 'function' && colApi.visible() === false) {
                return true;
            }
        } catch (e) {}
        try {
            if (colApi && typeof colApi.responsiveHidden === 'function' && colApi.responsiveHidden()) {
                return true;
            }
        } catch (e) {}
        return false;
    }

    function upsertDetail(rows, indexByTitle, title, value, prefer) {
        if (Object.prototype.hasOwnProperty.call(indexByTitle, title)) {
            if (!prefer) return;
            rows[indexByTitle[title]].value = value;
            rows[indexByTitle[title]].prefer = true;
            return;
        }
        indexByTitle[title] = rows.length;
        rows.push({ title: title, value: value, prefer: !!prefer });
    }

    function collectHiddenDetails($, api, rowIdx) {
        const rows = [];
        const indexByTitle = {};

        api.columns().every(function (colIdx) {
            const $header = $(this.header());
            const $cell = $(api.cell(rowIdx, colIdx).node());
            if (isDetailsSkip($header, $cell)) return;

            const title = ($header.text() || '').replace(/\s+/g, ' ').trim();
            if (!title || isActionsHeader(title, $header)) return;

            const prefer = isNoneColumn($header, $cell, this);
            let hidden = prefer;

            if ($cell.length && !$cell.is(':visible') && (!$header.is(':visible') || prefer)) {
                hidden = true;
            }

            if (!hidden) return;
            upsertDetail(rows, indexByTitle, title, cellDisplay(api, rowIdx, colIdx), prefer);
        });

        if (!rows.length) {
            api.columns().every(function (colIdx) {
                const $header = $(this.header());
                const $cell = $(api.cell(rowIdx, colIdx).node());
                if (isDetailsSkip($header, $cell)) return;

                const title = ($header.text() || '').replace(/\s+/g, ' ').trim();
                if (!title || isActionsHeader(title, $header)) return;

                const prefer = isNoneColumn($header, $cell, this);
                upsertDetail(rows, indexByTitle, title, cellDisplay(api, rowIdx, colIdx), prefer);
            });
        }

        return rows.map(function (row) {
            return { title: row.title, value: row.value };
        });
    }

    function ensureDrawerOnBody() {
        const drawer = document.getElementById('lzRowDetailsDrawer');
        if (drawer && drawer.parentElement !== document.body) {
            document.body.appendChild(drawer);
        }
        return drawer;
    }

    function closeDetailsDrawer() {
        const drawer = document.getElementById('lzRowDetailsDrawer');
        if (!drawer) return;
        drawer.classList.remove('show');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        document.querySelectorAll('.lz-row-drawer-backdrop').forEach(function (b) { b.remove(); });
        document.querySelectorAll('table.dataTable tbody tr.lz-sale-row-active, table.dataTable tbody tr.lz-row-details-active')
            .forEach(function (tr) { tr.classList.remove('lz-sale-row-active', 'lz-row-details-active'); });
    }

    function openDetailsDrawer(items) {
        const drawer = ensureDrawerOnBody();
        const body = document.getElementById('lzRowDetailsDrawerBody');
        const label = document.getElementById('lzRowDetailsDrawerLabel');
        if (!drawer || !body) return;

        if (label) label.textContent = detailsTitle;

        if (!items.length) {
            body.innerHTML = '<p class="text-muted mb-0">' + emptyLabel + '</p>';
        } else {
            let html = '<table class="table lz-row-details-table mb-0"><tbody>';
            items.forEach(function (item) {
                html += '<tr><th scope="row">' + item.title + '</th><td>' + item.value + '</td></tr>';
            });
            html += '</tbody></table>';
            body.innerHTML = html;
            // Extra pass: constrain any images after inject
            body.querySelectorAll('img').forEach(function (img) {
                img.style.maxWidth = '140px';
                img.style.maxHeight = '140px';
                img.style.width = 'auto';
                img.style.height = 'auto';
                img.style.objectFit = 'contain';
                img.style.display = 'block';
                img.removeAttribute('width');
                img.removeAttribute('height');
            });
        }

        drawer.classList.add('show');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        if (!document.querySelector('.lz-row-drawer-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'lz-row-drawer-backdrop';
            backdrop.addEventListener('click', closeDetailsDrawer);
            document.body.appendChild(backdrop);
        }
    }

    // Global — called from inline onclick (bypasses stopPropagation on parent)
    window.lzOpenRowDetails = function (el) {
        // Sales page keeps its own side drawer
        try {
            if (el && el.closest && el.closest('.lz-sales-page') && window.lzOpenSaleSideDetails) {
                return window.lzOpenSaleSideDetails(el);
            }
        } catch (e) {}

        if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) return false;

        const $ = jQuery;
        const api = findDataTable($, el);
        if (!api) return false;

        let $tr = $(el).closest('tr');
        if ($tr.hasClass('child')) $tr = $tr.prev();

        const row = api.row($tr);
        if (!row.node()) return false;

        document.querySelectorAll('table.dataTable tbody tr.lz-row-details-active')
            .forEach(function (tr) { tr.classList.remove('lz-row-details-active'); });
        if ($tr[0]) $tr[0].classList.add('lz-row-details-active');

        openDetailsDrawer(collectHiddenDetails($, api, row.index()));
        return false;
    };

    function injectDetailsButtons($, api) {
        try {
            if (api.table().node() && $(api.table().node()).closest('.lz-sales-page').length) {
                return;
            }
        } catch (e) {}

        api.rows({ page: 'current' }).every(function () {
            const $tr = $(this.node());
            if (!$tr.length || $tr.hasClass('child')) return;
            if ($tr.find('.lz-open-row-details, .view-sale-side-details, .lz-view-side-details').length) return;

            const btn = detailsButtonHtml();
            const $icons = $tr.find('.lz-action-icons').first();
            if ($icons.length) {
                $icons.prepend(btn);
                return;
            }

            const $actionsTd = $tr.find('td.lz-actions-col').first();
            if ($actionsTd.length) {
                $actionsTd.html(
                    '<div class="lz-action-icons">' + btn + $actionsTd.html() + '</div>'
                );
                return;
            }

            const $last = $tr.children('td').last();
            if ($last.length) {
                $last.append('<span class="lz-inline-row-details">' + btn + '</span>');
            }
        });
    }

    function injectAllTables($) {
        $('table.dataTable').each(function () {
            if (!$.fn.dataTable.isDataTable(this)) return;
            try {
                injectDetailsButtons($, $(this).DataTable());
            } catch (e) {}
        });
    }

    function hideProcessing(api) {
        try {
            $(api.table().container()).find('.dataTables_processing, .dt-processing')
                .css({ display: 'none', visibility: 'hidden', opacity: 0 })
                .attr('aria-hidden', 'true');
        } catch (e) {}
    }

    function wireExistingEyes() {
        document.querySelectorAll('.lz-open-row-details').forEach(function (el) {
            if (el.getAttribute('data-lz-bound') === '1') return;
            el.setAttribute('data-lz-bound', '1');
            el.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                window.lzOpenRowDetails(el);
            });
        });
    }

    function ensureReady(cb) {
        if (window.jQuery && jQuery.fn && jQuery.fn.dataTable) {
            cb(jQuery);
            return;
        }
        const timer = setInterval(function () {
            if (window.jQuery && jQuery.fn && jQuery.fn.dataTable) {
                clearInterval(timer);
                cb(jQuery);
            }
        }, 40);
        setTimeout(function () { clearInterval(timer); }, 12000);
    }

    document.addEventListener('DOMContentLoaded', function () {
        ensureDrawerOnBody();
        const closeBtn = document.getElementById('lzRowDetailsCloseBtn');
        const closeFooter = document.getElementById('lzRowDetailsCloseFooter');
        if (closeBtn) closeBtn.addEventListener('click', closeDetailsDrawer);
        if (closeFooter) closeFooter.addEventListener('click', closeDetailsDrawer);
    });

    function isViewDetailsControl(el) {
        if (!el || !el.closest) return null;
        // Preferred markers (always side drawer)
        let btn = el.closest('.lz-open-row-details, .lz-view-side-details, .view-sale-side-details');
        if (btn) return btn;

        // Fallback: action eye whose title looks like view details
        btn = el.closest('a.lz-action-btn, a.dropdown-item, button');
        if (!btn) return null;

        // Sale section buttons have their own nested side drawer
        if (btn.classList.contains('view-sale-section-dialog') || btn.classList.contains('lz-sale-detail-btn')) {
            return null;
        }

        const href = (btn.getAttribute('href') || '').toLowerCase();
        // Let dedicated full-page show links navigate normally
        if (href && href !== '#' && href.indexOf('javascript:') !== 0 && href.indexOf('/show/') !== -1) {
            return null;
        }

        const title = ((btn.getAttribute('title') || btn.textContent || '') + '').toLowerCase().replace(/\s+/g, ' ').trim();
        const looksLikeDetails =
            title.indexOf('view details') !== -1 ||
            title.indexOf('عرض التفاصيل') !== -1 ||
            (title.indexOf('تفاصيل') !== -1 && title.indexOf('حذف') === -1);
        if (!looksLikeDetails) return null;
        // Never hijack edit/delete/print/etc.
        if (btn.classList.contains('lz-action-danger')) return null;
        if (btn.querySelector('.ti-pencil, .ti-trash, .ti-printer, .ti-plus, .ti-coin, .ti-file-info')) return null;
        return btn;
    }

    // Capture-phase: any view-details control opens the side drawer
    document.addEventListener('click', function (e) {
        const sideBtn = isViewDetailsControl(e.target);
        if (!sideBtn) return;

        // Sales dedicated drawer
        if (sideBtn.classList.contains('view-sale-side-details')) {
            if (window.lzOpenSaleSideDetails) {
                e.preventDefault();
                e.stopPropagation();
                window.lzOpenSaleSideDetails(sideBtn);
            }
            return;
        }

        e.preventDefault();
        e.stopPropagation();
        if (window.lzOpenRowDetails) {
            window.lzOpenRowDetails(sideBtn);
        }
    }, true);

    ensureReady(function ($) {
        $.extend(true, $.fn.dataTable.defaults, {
            responsive: { details: false }
        });

        $(document).on('preInit.dt', function (e, settings) {
            const current = settings.oInit.responsive;
            if (current === true || current === undefined || current === null) {
                settings.oInit.responsive = { details: false };
            } else if (typeof current === 'object') {
                current.details = false;
            }
        });

        function forceActionsColumnVisible(api) {
            api.columns().every(function (idx) {
                const $header = $(this.header());
                const title = ($header.text() || '').replace(/\s+/g, ' ').trim();
                if (!isActionsHeader(title, $header)) return;

                $header.addClass('all lz-actions-col');
                $(api.cells(null, idx).nodes()).addClass('all lz-actions-col');
            });

            try {
                if (api.responsive && typeof api.responsive.recalc === 'function') {
                    api.responsive.recalc();
                }
            } catch (e) {}
        }

        $(document).on('init.dt draw.dt xhr.dt', function (e, settings) {
            try {
                const api = new $.fn.dataTable.Api(settings);
                if (e.type === 'init' || e.type === 'draw') {
                    forceActionsColumnVisible(api);
                }
                injectDetailsButtons($, api);
                wireExistingEyes();
                if (e.type === 'draw' || e.type === 'xhr') hideProcessing(api);
            } catch (err) {}
        });

        $(document).on('processing.dt', function (e, settings, processing) {
            if (processing) return;
            try { hideProcessing(new $.fn.dataTable.Api(settings)); } catch (err) {}
        });

        injectAllTables($);
        wireExistingEyes();
        setTimeout(function () { injectAllTables($); wireExistingEyes(); }, 300);
        setTimeout(function () { injectAllTables($); wireExistingEyes(); }, 1200);
    });
})();
</script>
