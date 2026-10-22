{{-- Admin: permission list side drawer (count + eye in roles tables) --}}
<style>
    #lzAdminPermDrawer.lz-admin-perm-drawer {
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
    html[dir="rtl"] #lzAdminPermDrawer.lz-admin-perm-drawer,
    html[dir=rtl] #lzAdminPermDrawer.lz-admin-perm-drawer {
        right: auto !important;
        left: 0 !important;
        transform: translateX(-100%) !important;
        box-shadow: 8px 0 28px rgba(0, 43, 38, 0.18);
    }
    #lzAdminPermDrawer.lz-admin-perm-drawer.show,
    html[dir="rtl"] #lzAdminPermDrawer.lz-admin-perm-drawer.show,
    html[dir=rtl] #lzAdminPermDrawer.lz-admin-perm-drawer.show {
        transform: translateX(0) !important;
    }
    html.dark-style #lzAdminPermDrawer.lz-admin-perm-drawer {
        background: #1c2233 !important;
    }
    html.dark-style #lzAdminPermDrawer .lz-admin-perm-header,
    html.dark-style #lzAdminPermDrawer .lz-admin-perm-footer {
        background: #1a2030 !important;
        border-color: #2e364a !important;
    }
    html.dark-style #lzAdminPermDrawer .lz-admin-perm-body {
        background: #1c2233 !important;
    }
    html.dark-style #lzAdminPermDrawer .lz-admin-perm-title {
        color: #e8d5b5 !important;
    }
    html.dark-style #lzAdminPermDrawer .lz-admin-perm-list li {
        background: #252b3d !important;
        border-color: #3d465c !important;
        color: #eef1f6 !important;
    }
    .lz-admin-perm-backdrop {
        position: fixed !important;
        inset: 0 !important;
        z-index: 10990 !important;
        background: rgba(0, 20, 18, 0.35) !important;
    }
    #lzAdminPermDrawer .lz-admin-perm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        padding: 1rem 1.15rem;
        background: #fdfaf6;
        border-bottom: 1px solid #efe6d8;
        flex: 0 0 auto;
    }
    #lzAdminPermDrawer .lz-admin-perm-title {
        margin: 0;
        color: #002b26;
        font-weight: 700;
        font-size: 1.05rem;
    }
    #lzAdminPermDrawer .lz-admin-perm-sub {
        display: block;
        margin-top: 0.2rem;
        color: #7a8582;
        font-size: 0.8rem;
        font-weight: 500;
    }
    #lzAdminPermDrawer .lz-admin-perm-body {
        flex: 1 1 auto;
        overflow-y: auto;
        padding: 1rem 1.15rem 1.25rem;
        background: #fff;
    }
    #lzAdminPermDrawer .lz-admin-perm-footer {
        flex: 0 0 auto;
        padding: 0.85rem 1.15rem 1.1rem;
        border-top: 1px solid #efe6d8;
        background: #fdfaf6;
    }
    #lzAdminPermDrawer .lz-admin-perm-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }
    #lzAdminPermDrawer .lz-admin-perm-list li {
        display: inline-flex;
        align-items: center;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
        background: rgba(0, 43, 38, 0.08);
        border: 1px solid rgba(197, 160, 112, 0.35);
        color: #002b26;
        font-size: 0.78rem;
        font-weight: 600;
    }
    #lzAdminPermDrawer .lz-admin-perm-empty {
        color: #7a8582;
        margin: 0;
    }
    .lz-perm-count-wrap {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        white-space: nowrap;
    }
    .lz-perm-count-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        padding: 0.3rem 0.65rem;
        border-radius: 999px;
        background: rgba(0, 43, 38, 0.08);
        border: 1px solid rgba(197, 160, 112, 0.35);
        color: #002b26;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .lz-perm-eye-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 10px;
        border: 1px solid #efe6d8;
        background: #fdfaf6;
        color: #002b26;
        cursor: pointer;
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .lz-perm-eye-btn:hover {
        background: #f3ebe0;
        border-color: #c5a070;
        color: #002b26;
    }
</style>

<div class="lz-admin-perm-drawer" id="lzAdminPermDrawer" aria-hidden="true">
    <div class="lz-admin-perm-header">
        <div>
            <h5 class="lz-admin-perm-title" id="lzAdminPermDrawerTitle">{{ __('general.permissions') }}</h5>
            <span class="lz-admin-perm-sub" id="lzAdminPermDrawerSub"></span>
        </div>
        <button type="button" class="btn-close" id="lzAdminPermCloseBtn" aria-label="Close"></button>
    </div>
    <div class="lz-admin-perm-body" id="lzAdminPermDrawerBody"></div>
    <div class="lz-admin-perm-footer">
        <button type="button" class="btn btn-luzori-muted btn-sm w-100" id="lzAdminPermCloseFooter">
            {{ __('general.cancel') }}
        </button>
    </div>
</div>

<script>
(function () {
    var labels = {
        permissions: @json(__('general.permissions')),
        empty: '—',
        count: @json(__('general.permissions'))
    };

    function ensureOnBody() {
        var drawer = document.getElementById('lzAdminPermDrawer');
        if (drawer && drawer.parentElement !== document.body) {
            document.body.appendChild(drawer);
        }
        return drawer;
    }

    function closePermDrawer() {
        var drawer = document.getElementById('lzAdminPermDrawer');
        if (!drawer) return;
        drawer.classList.remove('show');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        document.querySelectorAll('.lz-admin-perm-backdrop').forEach(function (b) { b.remove(); });
    }

    function openPermDrawer(btn) {
        var drawer = ensureOnBody();
        var body = document.getElementById('lzAdminPermDrawerBody');
        var titleEl = document.getElementById('lzAdminPermDrawerTitle');
        var subEl = document.getElementById('lzAdminPermDrawerSub');
        if (!drawer || !body) return;

        var roleName = btn.getAttribute('data-role-name') || '';
        var count = btn.getAttribute('data-perm-count') || '0';
        var items = [];
        try {
            var b64 = btn.getAttribute('data-perms-b64') || '';
            items = b64 ? JSON.parse(atob(b64)) : [];
        } catch (e) {
            items = [];
        }

        if (titleEl) titleEl.textContent = labels.permissions;
        if (subEl) {
            subEl.textContent = (roleName ? roleName + ' · ' : '') + count + ' ' + labels.count;
        }

        if (!items.length) {
            body.innerHTML = '<p class="lz-admin-perm-empty">' + labels.empty + '</p>';
        } else {
            var html = '<ul class="lz-admin-perm-list">';
            items.forEach(function (name) {
                html += '<li>' + String(name == null ? '' : name)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;') + '</li>';
            });
            html += '</ul>';
            body.innerHTML = html;
        }

        drawer.classList.add('show');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        if (!document.querySelector('.lz-admin-perm-backdrop')) {
            var backdrop = document.createElement('div');
            backdrop.className = 'lz-admin-perm-backdrop';
            backdrop.addEventListener('click', closePermDrawer);
            document.body.appendChild(backdrop);
        }
    }

    window.lzOpenAdminPermissions = function (el) {
        if (!el) return false;
        openPermDrawer(el);
        return false;
    };

    document.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest ? e.target.closest('.lz-perm-eye-btn, .lz-view-permissions') : null;
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();
        openPermDrawer(btn);
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        ensureOnBody();
        var closeBtn = document.getElementById('lzAdminPermCloseBtn');
        var closeFooter = document.getElementById('lzAdminPermCloseFooter');
        if (closeBtn) closeBtn.addEventListener('click', closePermDrawer);
        if (closeFooter) closeFooter.addEventListener('click', closePermDrawer);
    });
})();
</script>
