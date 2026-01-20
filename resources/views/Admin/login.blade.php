@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('admin.login_page'))

@section('vendor-style')
    <!-- Vendor -->
    @vite('resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css')
@endsection

@section('page-style')
    <!-- Page -->
    @vite('resources/assets/vendor/scss/pages/page-auth.scss')
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    @vite([
    'resources/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js',
    'resources/assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js',
    'resources/assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js'
    ])
@endsection

@section('page-script')
    @vite('resources/assets/js/pages-auth.js')

    <script>
        $(document).ready(function() {
            $("#frmLogin").on("submit", function(event) {
                event.preventDefault();

                $.ajax({
                    url: "{{ route('admin.login') }}",
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val(),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    beforeSend: function() {
                        $('#listError').empty();
                        $("#alertError").hide();
                        $(".submitFrom span").html('{{ __('admin.logining') }}');
                        $('.submitFrom').prop('disabled', true);
                    },
                    success: function(response, textStatus, xhr) {
                        if (xhr.status == 200) {
                            window.location.href = "{{ route('admin.cp') }}";
                        } else {
                            $("#alertError").show();
                            $('#listError').html(data.message);
                        }

                        $("html, body").animate({
                            scrollTop: 0
                        }, {
                            duration: 1500,
                        });
                        $(".submitFrom span").html('{{ __('admin.login') }}');
                        $('.submitFrom').prop('disabled', false);
                    },
                    error: function(response) {
                        $("#alertError").show();
                        var errors = response.responseJSON.errors;
                        if (errors) {
                            for (var error in errors) {
                                var ul = document.getElementById("listError");
                                var li = document.createElement("li");
                                li.appendChild(document.createTextNode(errors[error]));
                                ul.appendChild(li);
                            }
                        } else {
                            $('#listError').html(response.responseJSON.message);
                        }

                        $("html, body").animate({
                            scrollTop: 0
                        }, {
                            duration: 1500,
                        });
                        $(".submitFrom span").html('{{ __('admin.login') }}');
                        $('.submitFrom').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg">
        <div class="authentication-inner row">
            
            <!-- /Left Text -->
            <div class="d-none d-lg-flex col-lg-7 p-0">
                <div class="auth-cover-bg auth-cover-bg-color d-flex justify-content-center align-items-center">
                    <img src="{{ asset('assets/img/illustrations/auth-login-illustration-' . $configData['style'] . '.png') }}"
                        alt="auth-login-cover" class="img-fluid my-5 auth-illustration"
                        data-app-light-img="illustrations/auth-login-illustration-light.png"
                        data-app-dark-img="illustrations/auth-login-illustration-dark.png">

                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-' . $configData['style'] . '.png') }}"
                        alt="auth-login-cover" class="platform-bg"
                        data-app-light-img="illustrations/bg-shape-image-light.png"
                        data-app-dark-img="illustrations/bg-shape-image-dark.png">
                </div>
            </div>
            <!-- /Left Text -->

            <!-- Login -->
            <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
                
                <div class="w-px-400 mx-auto">
                    <!-- Logo -->
                    <div class="app-brand mb-4 text-center text-lg-start">
                        <a href="#" class="app-brand-link gap-2 d-inline-flex justify-content-center justify-content-lg-start">
                            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h3 class=" mb-1">{{ __('admin.welcome_brand', ['brand' => $brand]) }}</h3>
                    <p class="mb-4">{{ __('admin.signin_prompt') }}</p>

                    <div id="alertError" class="alert alert-danger mt-4" role="alert" style="display:none;">
                        <h4 class="alert-heading">{{ __('general.fail') }}</h4>
                        <div class="alert-body">
                            <div class="alert-text font-weight-bold">
                                <div id="listError"></div>
                            </div>
                        </div>
                    </div>
                    
                    <form id="frmLogin" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('field.email') }}</label>
                            <input type="text" class="form-control" id="email" name="email"
                                placeholder="{{ __('general.enter') . ' ' . __('field.email') }}" autofocus>
                        </div>
                        <div class="mb-3 form-password-toggle">
                            <div class="d-flex justify-content-between">
                                <label class="form-label" for="password">{{ __('field.password') }}</label>
                                {{-- <a href="{{ url('auth/forgot-password-cover') }}">
                                    <small>Forgot Password?</small>
                                </a> --}}
                            </div>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password"
                                    placeholder="{{ __('general.enter') . ' ' . __('field.password') }}"
                                    aria-describedby="password" />
                                <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember-me">
                                <label class="form-check-label" for="remember-me">
                                    {{ __('admin.remember_me') }}
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100 submitFrom">
                            <span>{{ __('admin.login') }}</span>
                        </button>
                    </form>
                </div>
            </div>
            <!-- /Login -->
        </div>
    </div>
@endsection
