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
    $lzSupportChatUnreadUrl = null;
    if (str_contains(url()->current(), 'admin') && auth('admin')->check()) {
        $lzSupportChatUnreadUrl = route('admin.support-chat.unread');
    } elseif (auth('center_user')->check() && !str_contains(url()->current(), 'admin')) {
        $lzSupportChatUnreadUrl = route('center_user.support-chat.unread');
    }
@endphp
@if($lzSupportChatUnreadUrl)
<script>
(function () {
    var url = @json($lzSupportChatUnreadUrl);
    function setBadge(n) {
        n = parseInt(n, 10) || 0;
        document.querySelectorAll('.lz-support-chat-badge').forEach(function (el) {
            el.textContent = n > 99 ? '99+' : String(n);
            el.classList.toggle('d-none', n <= 0);
        });
    }
    function tick() {
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (data && typeof data.unread !== 'undefined') setBadge(data.unread);
        }).catch(function () {});
    }
    tick();
    setInterval(tick, 25000);
})();
</script>
@endif