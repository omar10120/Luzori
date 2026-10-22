{{-- Admin: eye opens side details drawer (RTL-aware) --}}
<style>
    #lzAdminRowDrawer.lz-admin-row-drawer {
        position: fixed !important;
        top: 0 !important;
        right: 0 !important;
        left: auto !important;
        width: min(420px, 96vw) !important;
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
    html[dir="rtl"] #lzAdminRowDrawer.lz-admin-row-drawer {
        right: auto !important;
        left: 0 !important;
        transform: translateX(-100%) !important;
        box-shadow: 8px 0 28px rgba(0, 43, 38, 0.18);
    }
    #lzAdminRowDrawer.lz-admin-row-drawer.show,
    html[dir="rtl"] #lzAdminRowDrawer.lz-admin-row-drawer.show {
        transform: translateX(0) !important;
    }
    .lz-admin-row-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 10990 !important;
        background: rgba(0, 20, 18, 0.35) !important;
    }
    #lzAdminRowDrawer .lz-admin-row-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.15rem;
        background: #fdfaf6;
        border-bottom: 1px solid #efe6d8;
        flex: 0 0 auto;
    }
    #lzAdminRowDrawer .lz-admin-row-title {
        margin: 0;
        color: #002b26;
        font-weight: 700;
        font-size: 1.05rem;
    }
    #lzAdminRowDrawer .lz-admin-row-body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 1rem 1.15rem 1.25rem;
        background: #fff;
    }
    #lzAdminRowDrawer .lz-admin-row-footer {
        flex: 0 0 auto;
        padding: 0.85rem 1.15rem 1.1rem;
        border-top: 1px solid #efe6d8;
        background: #fdfaf6;
    }
    #lzAdminRowDrawer .lz-admin-row-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }
    #lzAdminRowDrawer .lz-admin-row-table th {
        width: 38%;
        color: #7a8582;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        border-bottom: 1px solid #efe6d8;
        vertical-align: top;
        text-align: start;
        padding: 0.7rem 0.5rem 0.7rem 0;
    }
    #lzAdminRowDrawer .lz-admin-row-table td {
        color: #1a2a27;
        border-bottom: 1px solid #efe6d8;
        vertical-align: top;
        text-align: start;
        overflow-wrap: anywhere;
        padding: 0.7rem 0;
        font-size: 0.9rem;
    }
    #lzAdminRowDrawer .lz-admin-row-table img {
        max-width: 120px !important;
        max-height: 120px !important;
        width: auto !important;
        height: auto !important;
        object-fit: contain !important;
        border-radius: 12px;
        border: 1px solid #efe6d8;
    }
    html.dark-style #lzAdminRowDrawer.lz-admin-row-drawer {
        background: #1c2233 !important;
    }
    html.dark-style #lzAdminRowDrawer .lz-admin-row-header,
    html.dark-style #lzAdminRowDrawer .lz-admin-row-footer {
        background: #1a2030 !important;
        border-color: #2e364a !important;
    }
    html.dark-style #lzAdminRowDrawer .lz-admin-row-body {
        background: #1c2233 !important;
    }
    html.dark-style #lzAdminRowDrawer .lz-admin-row-title {
        color: #e8d5b5 !important;
    }
    html.dark-style #lzAdminRowDrawer .lz-admin-row-table th {
        color: #a8b0bf !important;
        border-color: #2e364a !important;
    }
    html.dark-style #lzAdminRowDrawer .lz-admin-row-table td {
        color: #eef1f6 !important;
        border-color: #2e364a !important;
    }
</style>

<div class="lz-admin-row-drawer" id="lzAdminRowDrawer" aria-hidden="true">
    <div class="lz-admin-row-header">
        <h5 class="lz-admin-row-title">{{ __('general.view_details') }}</h5>
        <button type="button" class="btn-close" id="lzAdminRowCloseBtn" aria-label="Close"></button>
    </div>
    <div class="lz-admin-row-body" id="lzAdminRowDrawerBody"></div>
    <div class="lz-admin-row-footer">
        <button type="button" class="btn btn-luzori-muted btn-sm w-100" id="lzAdminRowCloseFooter">
            {{ __('general.cancel') }}
        </button>
    </div>
</div>

<script>
(function () {
    var detailsTitle = @json(__('general.view_details'));

    function ensureOnBody() {
        var drawer = document.getElementById('lzAdminRowDrawer');
        if (drawer && drawer.parentElement !== document.body) {
            document.body.appendChild(drawer);
        }
        return drawer;
    }

    function closeAdminRowDrawer() {
        var drawer = document.getElementById('lzAdminRowDrawer');
        if (!drawer) return;
        drawer.classList.remove('show');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        document.querySelectorAll('.lz-admin-row-backdrop').forEach(function (b) { b.remove(); });
    }

    function detailsButtonHtml() {
        return '<a href="javascript:void(0)" class="lz-action-btn lz-open-admin-row-details lz-admin-view-details" title="' +
            String(detailsTitle).replace(/"/g, '&quot;') +
            '" onclick="event.preventDefault();event.stopPropagation();if(window.lzOpenAdminRowDetails){window.lzOpenAdminRowDetails(this);}return false;">' +
            '<i class="ti ti-eye"></i></a>';
    }

    function collectRowDetails(btn) {
        var $ = window.jQuery;
        if (!$) return [];
        var $btn = $(btn);
        if (!$btn.length) return [];
        var $tr = $btn.closest('tr');
        if (!$tr.length) return [];
        if ($tr.hasClass('child')) {
            $tr = $tr.prev('tr');
        }
        var $table = $tr.closest('table');
        var $headers = $table.find('thead th');
        var rows = [];
        var api = null;
        try {
            if ($.fn.dataTable && $.fn.dataTable.isDataTable($table)) {
                api = $table.DataTable();
            }
        } catch (e) {}

        // Prefer DataTables API so ColVis-hidden columns still appear in the drawer
        if (api) {
            var rowIdx = -1;
            try { rowIdx = api.row($tr).index(); } catch (e) {}
            if (rowIdx >= 0) {
                api.columns().every(function (colIdx) {
                    var $th = $(this.header());
                    if ($th.hasClass('lz-actions-col') || $th.hasClass('lz-details-skip')) return;
                    var label = ($th.text() || '').replace(/\s+/g, ' ').trim();
                    if (!label || /^(actions|action|العمليات|عمليات|الإجراءات|اجراءات)$/i.test(label)) return;
                    var html = '';
                    try {
                        var rendered = this.render
                            ? api.cell(rowIdx, colIdx).render('display')
                            : api.cell(rowIdx, colIdx).data();
                        html = rendered == null ? '' : String(rendered);
                    } catch (e) {
                        try { html = String(api.cell(rowIdx, colIdx).data() || ''); } catch (e2) {}
                    }
                    if (!html || !html.replace(/\s|&nbsp;/g, '').length) return;
                    if (/lz-action-icons|lz-open-admin-row-details/.test(html) && !/badge|img|switch|form-check/.test(html)) return;
                    rows.push({ label: label, html: html });
                });
                if (rows.length) return rows;
            }
        }

        $tr.children('td').each(function (idx) {
            var $td = $(this);
            if ($td.hasClass('lz-actions-col')) return;
            if ($td.find('.lz-action-icons').length && !$td.find('.lz-perm-count-wrap, img, .badge, .form-check, input').length) return;
            var $th = $headers.eq(idx);
            if ($th.hasClass('lz-actions-col') || $th.hasClass('lz-details-skip')) return;
            var label = ($th.text() || '').replace(/\s+/g, ' ').trim();
            if (!label || /^(actions|action|العمليات|عمليات|الإجراءات|اجراءات)$/i.test(label)) return;
            var html = $td.html();
            if (!html || !String(html).replace(/\s|&nbsp;/g, '').length) return;
            // Strip injected eye from cell clone shown in drawer
            var $clone = $('<div>').html(html);
            $clone.find('.lz-open-admin-row-details, .lz-inline-row-details').remove();
            html = $clone.html();
            if (!html || !String(html).replace(/\s|&nbsp;/g, '').length) return;
            rows.push({ label: label, html: html });
        });
        return rows;
    }

    function openAdminRowDrawer(btn) {
        var drawer = ensureOnBody();
        var body = document.getElementById('lzAdminRowDrawerBody');
        if (!drawer || !body) return;

        var items = collectRowDetails(btn);
        if (!items.length) {
            body.innerHTML = '<p class="text-muted mb-0">—</p>';
        } else {
            var html = '<table class="lz-admin-row-table"><tbody>';
            items.forEach(function (item) {
                html += '<tr><th>' + String(item.label)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    + '</th><td>' + item.html + '</td></tr>';
            });
            html += '</tbody></table>';
            body.innerHTML = html;
        }

        drawer.classList.add('show');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        if (!document.querySelector('.lz-admin-row-backdrop')) {
            var backdrop = document.createElement('div');
            backdrop.className = 'lz-admin-row-backdrop';
            backdrop.addEventListener('click', closeAdminRowDrawer);
            document.body.appendChild(backdrop);
        }
    }

    function injectDetailsButtons($, api) {
        if (!api) return;
        $(api.rows({ page: 'current' }).nodes()).each(function () {
            var $tr = $(this);
            if ($tr.hasClass('child')) return;
            if ($tr.find('.lz-open-admin-row-details, .lz-admin-view-details').length) return;

            var btn = detailsButtonHtml();
            var $icons = $tr.find('.lz-action-icons').first();
            if ($icons.length) {
                $icons.prepend(btn);
                return;
            }
            var $actionsTd = $tr.find('td.lz-actions-col').first();
            if ($actionsTd.length) {
                var inner = $actionsTd.html();
                if (!inner || !String(inner).replace(/\s|&nbsp;|—|-/g, '').length) {
                    $actionsTd.html('<div class="lz-action-icons">' + btn + '</div>');
                } else {
                    $actionsTd.html('<div class="lz-action-icons">' + btn + inner + '</div>');
                }
                return;
            }
            // Tables without an actions column (e.g. bookings): append eye to last cell
            var $last = $tr.children('td').last();
            if ($last.length) {
                $last.append('<span class="lz-inline-row-details ms-1">' + btn + '</span>');
            }
        });
    }

    function injectAllTables($) {
        $('table.dataTable').each(function () {
            try {
                if ($.fn.dataTable.isDataTable(this)) {
                    injectDetailsButtons($, $(this).DataTable());
                }
            } catch (e) {}
        });
    }

    window.lzOpenAdminRowDetails = function (el) {
        if (!el) return false;
        openAdminRowDrawer(el);
        return false;
    };

    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest
            ? e.target.closest('.lz-open-admin-row-details, .lz-admin-view-details')
            : null;
        if (!btn) return;
        // Don't steal permission-eye clicks
        if (btn.classList.contains('lz-perm-eye-btn') || btn.classList.contains('lz-view-permissions')) return;
        e.preventDefault();
        e.stopPropagation();
        openAdminRowDrawer(btn);
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        ensureOnBody();
        var closeBtn = document.getElementById('lzAdminRowCloseBtn');
        var closeFooter = document.getElementById('lzAdminRowCloseFooter');
        if (closeBtn) closeBtn.addEventListener('click', closeAdminRowDrawer);
        if (closeFooter) closeFooter.addEventListener('click', closeAdminRowDrawer);
    });

    function ensureReady(cb) {
        if (window.jQuery && jQuery.fn && jQuery.fn.dataTable) {
            cb(jQuery);
            return;
        }
        var timer = setInterval(function () {
            if (window.jQuery && jQuery.fn && jQuery.fn.dataTable) {
                clearInterval(timer);
                cb(jQuery);
            }
        }, 40);
        setTimeout(function () { clearInterval(timer); }, 12000);
    }

    ensureReady(function ($) {
        $(document).on('init.dt draw.dt xhr.dt', function (e, settings) {
            try {
                injectDetailsButtons($, new $.fn.dataTable.Api(settings));
            } catch (err) {}
        });
        injectAllTables($);
        setTimeout(function () { injectAllTables($); }, 300);
        setTimeout(function () { injectAllTables($); }, 1200);
    });
})();
</script>
