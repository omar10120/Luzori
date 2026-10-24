<!-- BEGIN: Vendor JS-->

@vite(['resources/assets/vendor/libs/jquery/jquery.js', 'resources/assets/vendor/libs/popper/popper.js', 'resources/assets/vendor/js/bootstrap.js', 'resources/assets/vendor/libs/node-waves/node-waves.js', 'resources/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js', 'resources/assets/vendor/libs/hammer/hammer.js', 'resources/assets/vendor/libs/typeahead-js/typeahead.js', 'resources/assets/vendor/js/menu.js'])
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
@vite(['resources/assets/js/main.js'])

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
@stack('scripts')
<!-- END: Page JS-->

<script type="module" src="https://cdn.datatables.net/buttons/2.3.4/js/buttons.colVis.min.js"></script>
<script type="module" src="https://cdn.datatables.net/plug-ins/2.0.3/dataRender/ellipsis.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function fireMessage(confirmButtonText, title = "{{ __('admin.done_delete_successfully') }}", text =
        "{{ __('admin.operation_done_successfully') }}", icon =
        'success') {
        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            confirmButtonText: confirmButtonText,
            timer: 1500,
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
    }

    $(document).on('click', '.read-more-btn', function(e) {
        e.preventDefault();
        const $this = $(this);
        const $parent = $this.parent();
        const $shortText = $parent.find('.short-text');
        const $fullText = $parent.find('.full-text');
        const readMoreText = "{{ __('general.read_more') }}";
        const readLessText = "{{ __('general.read_less') }}";
        
        if ($fullText.is(':visible')) {
            $fullText.hide();
            $shortText.show();
            $this.text(readMoreText);
        } else {
            $fullText.show();
            $shortText.hide();
            $this.text(readLessText);
        }
    });

    // Global AJAX error handler
    $(document).ajaxError(function(event, xhr, settings) {
        // Handle 401 Unauthorized - redirect to login (authentication issue)
        if (xhr.status === 401 && !settings._handled401) {
            var currentUrl = window.location.href;
            var loginUrl = '';
            
            // Determine login URL based on current path
            if (currentUrl.includes('/admin')) {
                loginUrl = '{{ route("admin.login", [], false) }}';
            } else if (currentUrl.includes('/center_user') || currentUrl.includes('/center-user')) {
                loginUrl = '{{ route("center_user.login", [], false) }}';
            } else {
                // Try to get redirect URL from response
                try {
                    var response = xhr.responseJSON;
                    if (response && response.redirect) {
                        loginUrl = response.redirect;
                    }
                } catch(e) {
                    // Fallback to center_user login
                    loginUrl = '{{ route("center_user.login", [], false) }}';
                }
            }
            
            // Show message and redirect
            if (typeof toastr !== 'undefined') {
                toastr.error('{{ __('admin.session_expired') ?? "Your session has expired. Please login again." }}');
            } else if (typeof fireMessage !== 'undefined') {
                fireMessage("{{ __('admin.ok') }}",
                    "{{ __('admin.session_expired') ?? 'Your session has expired. Please login again.' }}",
                    "{{ __('admin.fire_message') }}",
                    'error');
            }
            
            // Redirect after a short delay
            setTimeout(function() {
                if (loginUrl) {
                    window.location.href = loginUrl;
                }
            }, 1500);
        }
        
        // Handle 403 Forbidden - show error message, don't redirect (permission issue)
        if (xhr.status === 403 && !settings._handled403) {
            var message = 'You do not have permission to perform this action.';
            try {
                var response = xhr.responseJSON;
                if (response && response.message) {
                    message = response.message;
                }
            } catch(e) {
                // Use default message
            }
            
            // Show error message but don't redirect
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else if (typeof fireMessage !== 'undefined') {
                fireMessage("{{ __('admin.ok') }}", message, "{{ __('admin.fire_message') }}", 'error');
            } else {
                alert(message);
            }
        }
    });
</script>

@if (!str_contains(url()->current(), 'admin') && (str_contains(url()->current(), 'center_user') || auth('center_user')->check()))
<script>
    (function () {
        function applyLuzoriLoaderDefaults() {
            if (!window.jQuery || !jQuery.blockUI || !jQuery.blockUI.defaults) return;
            jQuery.blockUI.defaults.message = '<div class="lz-loader" aria-hidden="true"></div>';
            jQuery.blockUI.defaults.css = {
                border: '0',
                backgroundColor: 'transparent',
                cursor: 'wait'
            };
            jQuery.blockUI.defaults.overlayCSS = {
                backgroundColor: '#f9f6f1',
                opacity: 0.72,
                cursor: 'wait'
            };
        }

        if (window.jQuery) {
            applyLuzoriLoaderDefaults();
        } else {
            document.addEventListener('DOMContentLoaded', applyLuzoriLoaderDefaults);
        }
    })();
</script>
@endif

@php
    $lzSupportAlertsUrl = null;
    if (str_contains(url()->current(), 'admin') && auth('admin')->check()) {
        $lzSupportAlertsUrl = route('admin.support-alerts.poll');
    } elseif (auth('center_user')->check() && !str_contains(url()->current(), 'admin')) {
        $lzSupportAlertsUrl = route('center_user.support-alerts.poll');
    }
@endphp
@if($lzSupportAlertsUrl)
<style>
.lz-local-toasts {
  position: fixed;
  top: 1rem;
  inset-inline-end: 1rem;
  z-index: 20050;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  width: min(360px, calc(100vw - 1.5rem));
  pointer-events: none;
}
.lz-local-toast {
  pointer-events: auto;
  background: #002b26;
  color: #f8f1e6;
  border-radius: 14px;
  box-shadow: 0 12px 32px rgba(0, 43, 38, 0.28);
  padding: 0.85rem 1rem;
  display: flex;
  gap: 0.75rem;
  align-items: flex-start;
  animation: lzToastIn 0.28s ease;
  border: 1px solid rgba(201, 164, 106, 0.35);
  cursor: pointer;
}
.lz-local-toast .lz-lt-icon {
  width: 36px; height: 36px; border-radius: 10px;
  background: rgba(201, 164, 106, 0.2);
  color: #e8d5b5;
  display: inline-flex; align-items: center; justify-content: center;
  flex: 0 0 auto;
}
.lz-local-toast .lz-lt-title {
  font-weight: 700; font-size: 0.88rem; margin: 0 0 0.15rem;
  color: #e8d5b5;
}
.lz-local-toast .lz-lt-body {
  font-size: 0.8rem; margin: 0; opacity: 0.92;
  overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.lz-local-toast .lz-lt-close {
  margin-inline-start: auto;
  background: transparent; border: 0; color: #e8d5b5; opacity: 0.7;
  font-size: 1.1rem; line-height: 1; padding: 0;
}
@keyframes lzToastIn {
  from { transform: translateY(-10px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}
html[dir="rtl"] .lz-local-toasts { inset-inline-end: auto; inset-inline-start: 1rem; }
</style>
<div class="lz-local-toasts" id="lzLocalToasts" aria-live="polite"></div>
<script>
(function () {
    var url = @json($lzSupportAlertsUrl);
    var lastChatFp = null;
    var lastTicketFp = null;
    var lastChatUnread = null;
    var bootstrapped = false;

    window.__lzSupportSoundUrl = @json(asset('sumsung_galaxy_s4.mp3'));
    function playBeep() {
        try {
            if (window.lzPlaySupportSound) { window.lzPlaySupportSound(); return; }
            var src = window.__lzSupportSoundUrl || @json(asset('sumsung_galaxy_s4.mp3'));
            if (!window.__lzSupportAudio) {
                window.__lzSupportAudio = new Audio(src);
                window.__lzSupportAudio.preload = 'auto';
                window.__lzSupportAudio.volume = 0.9;
            }
            var audio = window.__lzSupportAudio;
            audio.pause();
            audio.currentTime = 0;
            var p = audio.play();
            if (p && typeof p.catch === 'function') p.catch(function () {});
        } catch (e) {}
    }

    function setChatBadge(n) {
        n = parseInt(n, 10) || 0;
        document.querySelectorAll('.lz-support-chat-badge').forEach(function (el) {
            el.textContent = n > 99 ? '99+' : String(n);
            el.classList.toggle('d-none', n <= 0);
        });
    }

    function showLocalToast(item, kind) {
        if (!item) return;
        var host = document.getElementById('lzLocalToasts');
        if (!host) return;
        var el = document.createElement('div');
        el.className = 'lz-local-toast';
        el.innerHTML =
            '<span class="lz-lt-icon"><i class="ti ' + (kind === 'ticket' ? 'ti-ticket' : 'ti-messages') + '"></i></span>' +
            '<div style="min-width:0;flex:1">' +
              '<p class="lz-lt-title"></p>' +
              '<p class="lz-lt-body"></p>' +
            '</div>' +
            '<button type="button" class="lz-lt-close" aria-label="Close">&times;</button>';
        el.querySelector('.lz-lt-title').textContent = item.title || '';
        el.querySelector('.lz-lt-body').textContent = item.body || '';
        el.addEventListener('click', function (e) {
            if (e.target.closest('.lz-lt-close')) {
                el.remove();
                return;
            }
            if (item.url) window.location.href = item.url;
        });
        host.prepend(el);
        setTimeout(function () { if (el.parentNode) el.remove(); }, 8000);

        // Also toastr if available
        try {
            if (window.toastr) {
                toastr.info(item.body || '', item.title || '', { timeOut: 6000, progressClose: true, onclick: function () {
                    if (item.url) window.location.href = item.url;
                }});
            }
        } catch (e) {}
    }

    function tick() {
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (!data || !data.success) return;
            setChatBadge(data.chat_unread || 0);

            if (!bootstrapped) {
                lastChatFp = data.chat_fp;
                lastTicketFp = data.ticket_fp;
                lastChatUnread = data.chat_unread || 0;
                bootstrapped = true;
                return;
            }

            var chatChanged = data.chat_fp && data.chat_fp !== lastChatFp;
            var ticketChanged = data.ticket_fp && data.ticket_fp !== lastTicketFp;
            var unreadUp = (data.chat_unread || 0) > (lastChatUnread || 0);

            if (chatChanged || unreadUp) {
                playBeep();
                showLocalToast(data.latest_chat, 'chat');
            }
            if (ticketChanged) {
                playBeep();
                showLocalToast(data.latest_ticket, 'ticket');
            }

            lastChatFp = data.chat_fp;
            lastTicketFp = data.ticket_fp;
            lastChatUnread = data.chat_unread || 0;
        }).catch(function () {});
    }

    // Unlock audio after first tap
    document.addEventListener('click', function once() {
        playBeep();
        document.removeEventListener('click', once);
    }, { once: true });

    tick();
    setInterval(tick, 10000);
})();
</script>
@endif