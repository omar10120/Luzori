<script>
    $(document).ready(function() {
        // Real-time validation feedback
        $("#frmSubmit").find("input, textarea, select").on("input change", function() {
            if (this.checkValidity()) {
                $(this).addClass("is-valid").removeClass("is-invalid");
            } else {
                $(this).addClass("is-invalid").removeClass("is-valid");
            }
        });

        $("#frmSubmit").on("submit", function(event) {
            event.preventDefault();


            if (!this.checkValidity()) {
                $(this).find(":input:invalid").addClass("is-invalid");
                this.reportValidity();
                return;
            }

            $.ajax({
                url: '{{ $requestUrl }}',
                type: "POST",
                data: new FormData(this),
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#listError').empty();
                    $("#alertError").hide();
                    $("#alertSuccess").hide();
                    $("#successMessage").hide();
                    $(".submitFrom span").html("{{ __('admin.sending') }}");
                    $('.submitFrom').prop('disabled', true);
                },
                success: function(response, textStatus, xhr) {
                    if (response['message'] == 'redirect_to_home') {
                        window.location.href = response['data'];
                    } else if (xhr.status == 201 || xhr.status == 200) {
                        $("#alertError").hide();
                        $("#alertSuccess").show();
                        $("#successMessage").show();
                        $('#successMessage').html(response["message"]);
                    } else {
                        $("#alertSuccess").hide();
                        $("#alertError").show();
                        $('#errorMessage').html(response["message"]);
                    }

                    $("html, body").animate({
                        scrollTop: 0
                    }, {
                        duration: 1500,
                    });
                    $(".submitFrom span").html("{{ __('general.save') }}");
                    $('.submitFrom').prop('disabled', false);
                },
                error: function(response) {
                    $("#alertError").show();

                    if (response.status == 401) {
                        // Redirect to login page when token expires
                        var loginUrl = response.responseJSON?.redirect || '{{ route("center_user.login") }}';
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.error('{{ __('admin.session_expired') ?? "Your session has expired. Please login again." }}');
                        } else {
                            fireMessage("{{ __('admin.ok') }}",
                                    "{{ __('admin.session_expired') ?? 'Your session has expired. Please login again.' }}",
                                    "{{ __('admin.fire_message') }}",
                                    'error');
                        }
                        
                        // Redirect after a short delay to show the message
                        setTimeout(function() {
                            window.location.href = loginUrl;
                        }, 1500);
                        return;
                    } else {
                        if (response.status == 500) {
                            var ul = document.getElementById("listError");
                            var li = document.createElement("li");
                            li.appendChild(document.createTextNode(response.responseJSON
                                .message));
                            ul.appendChild(li);
                        }

                        var errors = response.responseJSON.errors;
                        for (var error in errors) {
                            var ul = document.getElementById("listError");
                            var li = document.createElement("li");
                            li.appendChild(document.createTextNode(errors[error]));
                            ul.appendChild(li);
                        }
                    }
                    $("html, body").animate({
                        scrollTop: 0
                    }, {
                        duration: 1500,
                    });
                    $(".submitFrom span").html("{{ __('general.save') }}");
                    $('.submitFrom').prop('disabled', false);
                }
            });
        });
    });
</script>
