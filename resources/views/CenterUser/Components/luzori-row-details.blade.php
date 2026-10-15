{{-- Luzori: eye in Actions opens dialog with hidden/remaining row fields (replaces expand arrow) --}}
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

    function detailsButtonHtml() {
        return '<a href="javascript:void(0)" class="lz-action-btn lz-open-row-details" title="' +
            detailsBtnTitle.replace(/"/g, '&quot;') + '"><i class="ti ti-eye"></i></a>';
    }

    function isActionsHeader(title, $header) {
        if ($header && ($header.hasClass('lz-actions-col') || $header.hasClass('lz-dtr-control'))) return true;
        const t = (title || '').toLowerCase().trim();
        return actionsLabels.indexOf(t) !== -1;
    }

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
                    '<div class="lz-action-icons" onclick="event.stopPropagation()">' +
                    btn + $actionsTd.html() + '</div>'
                );
                return;
            }

            // No actions column: append a dedicated last-cell eye
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

            // Columns marked for child/details row (old expand content)
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
                // Responsive often hides with display:none
                if ($cell.css('display') === 'none' || !$cell.is(':visible')) {
                    // Only treat as hidden detail if header also not visible / none-class
                    if (!$header.is(':visible') || $header.hasClass('none') || $header.hasClass('never')) {
                        hidden = true;
                    }
                }
            }

            if (!hidden) return;
            if (seen[title]) return;
            seen[title] = true;

            rows.push({
                title: title,
                value: cellDisplay(api, rowIdx, colIdx)
            });
        });

        // Fallback: show every non-action field so the eye always has content
        if (!rows.length) {
            api.columns().every(function (colIdx) {
                const $header = $(this.header());
                const title = ($header.text() || '').replace(/\s+/g, ' ').trim();
                if (!title || isActionsHeader(title, $header)) return;
                if (seen[title]) return;
                seen[title] = true;
                rows.push({
                    title: title,
                    value: cellDisplay(api, rowIdx, colIdx)
                });
            });
        }

        return rows;
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

        if (window.bootstrap && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (window.jQuery) {
            jQuery(modalEl).modal('show');
        }
    }

    ensureReady(function ($) {
        // Disable inline expand child rows (arrow) — keep Responsive column hiding
        $.extend(true, $.fn.dataTable.defaults, {
            responsive: { details: false }
        });

        $(document).on('preInit.dt', function (e, settings) {
            if (!document.body.classList.contains('luzori-center')) return;
            const current = settings.oInit.responsive;
            if (current === true || current === undefined || current === null) {
                settings.oInit.responsive = { details: false };
            } else if (typeof current === 'object') {
                current.details = false;
            }
        });

        $(document).on('init.dt draw.dt', function (e, settings) {
            if (!document.body.classList.contains('luzori-center')) return;
            try {
                injectDetailsButtons($, new $.fn.dataTable.Api(settings));
            } catch (err) {}
        });

        // Catch tables already initialized before our listeners bound
        injectAllTables($);
        setTimeout(function () { injectAllTables($); }, 300);
        setTimeout(function () { injectAllTables($); }, 1000);

        $(document).on('click', '.lz-open-row-details', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            const $table = $btn.closest('table');
            if (!$table.length || !$.fn.dataTable.isDataTable($table)) return;

            const api = $table.DataTable();
            let $tr = $btn.closest('tr');
            if ($tr.hasClass('child')) $tr = $tr.prev();

            const row = api.row($tr);
            if (!row.node()) return;

            openDetailsModal(collectHiddenDetails($, api, row.index()));
        });

        // Kill leftover expand-arrow clicks
        $(document).on('click', 'table.dataTable tbody td.dtr-control', function (e) {
            if (!document.body.classList.contains('luzori-center')) return;
            if ($(e.target).closest('.lz-open-row-details, .lz-action-icons a, button, input, label').length) {
                return;
            }
            e.preventDefault();
            e.stopImmediatePropagation();
        });
    });
})();
</script>
