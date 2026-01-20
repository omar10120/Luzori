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

    // Global AJAX error handler for 401 Unauthorized - redirect to login
    $(document).ajaxError(function(event, xhr, settings) {
        // Only handle if it's a 401 error and not already handled by specific error callbacks
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
    });
</script>