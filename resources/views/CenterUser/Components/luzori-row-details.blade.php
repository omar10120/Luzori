{{-- Luzori: eye in Actions opens dialog with remaining row fields --}}
<div class="modal fade" id="lzRowDetailsModal" tabindex="-1" aria-labelledby="lzRowDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content lz-row-details-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="lzRowDetailsModalLabel">{{ __('general.view_details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="lzRowDetailsModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-luzori-muted btn-sm" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    if (!document.body.classList.contains('luzori-center')) return;

    const detailsTitle = @json(__('general.view_details'));
    const detailsBtnTitle = @json(__('general.view_details'));
    const emptyLabel = '—';
    const actionsLabels = ['actions', 'action', 'الإجراءات', 'اجراءات'];

    function detailsButtonHtml() {
        return '<a href="javascript:void(0)" class="lz-action-btn lz-open-row-details" title="' +
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
        // Scroll/responsive wrappers sometimes nest tables
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
                return rendered;
            }
        } catch (e) {}
        try {
            const data = api.cell(rowIdx, colIdx).data();
            if (data !== null && data !== undefined && String(data).trim() !== '') {
                return data;
            }
        } catch (e) {}
        return emptyLabel;
    }

    function collectHiddenDetails($, api, rowIdx) {
        const rows = [];
        const seen = {};

        api.columns().every(function (colIdx) {
            const $header = $(this.header());
            const title = ($header.text() || '').replace(/\s+/g, ' ').trim();
            if (!title || isActionsHeader(title, $header)) return;

            let hidden = false;
            if ($header.hasClass('none') || $header.hasClass('never') || $header.hasClass('dtr-hidden')) {
                hidden = true;
            }
            try {
                if (typeof this.responsiveHidden === 'function' && this.responsiveHidden()) {
                    hidden = true;
                }
            } catch (e) {}

            const $cell = $(api.cell(rowIdx, colIdx).node());
            if ($cell.length) {
                if ($cell.hasClass('none') || $cell.hasClass('never') || $cell.hasClass('dtr-hidden')) {
                    hidden = true;
                }
                if (!$cell.is(':visible') && (!$header.is(':visible') || $header.hasClass('none') || $header.hasClass('never'))) {
                    hidden = true;
                }
            }

            if (!hidden) return;
            if (seen[title]) return;
            seen[title] = true;
            rows.push({ title: title, value: cellDisplay(api, rowIdx, colIdx) });
        });

        if (!rows.length) {
            api.columns().every(function (colIdx) {
                const $header = $(this.header());
                const title = ($header.text() || '').replace(/\s+/g, ' ').trim();
                if (!title || isActionsHeader(title, $header)) return;
                if (seen[title]) return;
                seen[title] = true;
                rows.push({ title: title, value: cellDisplay(api, rowIdx, colIdx) });
            });
        }

        return rows;
    }

    function ensureBackdrop() {
        let backdrop = document.querySelector('.modal-backdrop.lz-row-details-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show lz-row-details-backdrop';
            document.body.appendChild(backdrop);
        }
        return backdrop;
    }

    function openDetailsModal(items) {
        const body = document.getElementById('lzRowDetailsModalBody');
        const label = document.getElementById('lzRowDetailsModalLabel');
        const modalEl = document.getElementById('lzRowDetailsModal');
        if (!body || !modalEl) return;

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
        }

        // Prefer Bootstrap 5 API
        try {
            if (window.bootstrap && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
                return;
            }
        } catch (e) {}

        // jQuery bootstrap plugin fallback
        try {
            if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
                jQuery(modalEl).modal('show');
                return;
            }
        } catch (e) {}

        // Manual fallback
        modalEl.style.display = 'block';
        modalEl.classList.add('show');
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        ensureBackdrop();
    }

    // Global — called from inline onclick (bypasses stopPropagation on parent)
    window.lzOpenRowDetails = function (el) {
        if (!window.jQuery || !jQuery.fn || !jQuery.fn.dataTable) return false;

        const $ = jQuery;
        const api = findDataTable($, el);
        if (!api) return false;

        let $tr = $(el).closest('tr');
        if ($tr.hasClass('child')) $tr = $tr.prev();

        const row = api.row($tr);
        if (!row.node()) return false;

        openDetailsModal(collectHiddenDetails($, api, row.index()));
        return false;
    };

    function injectDetailsButtons($, api) {
        api.rows({ page: 'current' }).every(function () {
            const $tr = $(this.node());
            if (!$tr.length || $tr.hasClass('child')) return;
            if ($tr.find('.lz-open-row-details').length) return;

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

    // Close manual modal
    document.addEventListener('click', function (e) {
        const modalEl = document.getElementById('lzRowDetailsModal');
        if (!modalEl || !modalEl.classList.contains('show')) return;
        if (e.target.closest('[data-bs-dismiss="modal"]') || e.target === modalEl) {
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            document.querySelectorAll('.lz-row-details-backdrop').forEach(function (b) { b.remove(); });
        }
    });

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

        $(document).on('init.dt draw.dt xhr.dt', function (e, settings) {
            try {
                const api = new $.fn.dataTable.Api(settings);
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
