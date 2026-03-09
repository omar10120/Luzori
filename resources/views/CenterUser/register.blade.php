@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Register - Become Business')

@section('vendor-style')
    @vite('resources/assets/vendor/libs/@form-validation/umd/styles/index.min.css')
@endsection

@section('page-style')
    <style>
        .auth-custom-bg {
            background-color: #0d1226 !important; /* Starry dark blue background */
            background-image: radial-gradient(circle at center, rgba(255, 255, 255, 0.05) 0%, transparent 100%),
                              url("data:image/svg+xml,%3Csvg width='100' height='100' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='10' cy='10' r='1' fill='rgba(255,255,255,0.3)'/%3E%3Ccircle cx='50' cy='60' r='1' fill='rgba(255,255,255,0.2)'/%3E%3Ccircle cx='80' cy='20' r='1.5' fill='rgba(255,255,255,0.4)'/%3E%3C/svg%3E");
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            
        }

        .register-card {
            background-color: #2b3040; /* Card dark color */
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            padding: 2.5rem;
            width: 100%;
            max-width: 800px;
            margin: 2rem 1rem;
        }

        .auth-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .auth-logo img {
            height: 60px;
        }

        .register-title {
            text-align: center;
            
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 2rem;
        }

        .form-label {
            
            font-size: 0.85rem;
            font-weight: 500;
            color:white;
        }

        .form-control, .form-select {
            
            border: none !important;
            border-radius: 0.375rem;
            
            padding: 0.6rem 1rem;
        }
        
        .form-control[type="password"] {
            
        }

        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 2px rgba(241, 223, 197, 0.5);
        }
        
        .input-group-text {
            
            
            
        }

        /* Phone input group styling */
        .phone-input-group {
            display: flex;
        }
        .phone-input-group .form-select {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            width: 35%;
            
        }
        .phone-input-group .form-control {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            width: 65%;
            
            
        }

        .file-upload-box {
            border: 1px dashed #6b7280;
            border-radius: 0.375rem;
            background-color: transparent;
            text-align: center;
            padding: 1.5rem;
            cursor: pointer;
            
            transition: all 0.2s ease;
            position: relative;
        }

        .file-upload-box:hover {
            border-color: #f1dfc5;
            
        }

        .file-upload-box input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .file-upload-text {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-register {
            background-color: #276749; /* Dark green */
            
            
            
            
            padding: 0.6rem 2.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-register:hover {
            background-color: #22543d;
            
            transform: translateY(-1px);
        }

        .btn-register:disabled {
            background-color: #4a5568;
            cursor: not-allowed;
        }
        
        #alertError {
            display: none;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection

@section('vendor-script')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            // Password toggle
            $('.toggle-password').click(function() {
                let input = $(this).closest('.input-group').find('input');
                let icon = $(this).find('i');
                if (input.attr('type') == 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                }
            });

            // File selection preview text
            $('input[type="file"]').change(function(e) {
                let fileName = e.target.files[0]?.name;
                if (fileName) {
                    $(this).siblings('.file-upload-text').text(fileName);
                } else {
                    let defaultText = $(this).attr('id') === 'image' ? 'SELECT LOGO' : 'PRIMARY IMAGE';
                    $(this).siblings('.file-upload-text').text(defaultText);
                }
            });

            // Form submission
            $("#frmRegister").on("submit", function(event) {
                event.preventDefault();

                let formData = new FormData(this);
                // The API expects 'password_confirmation' which usually matches password
                formData.append('password_confirmation', formData.get('password'));

                $.ajax({
                    url: "{{ url('/center_api/auth/register') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('#listError').empty();
                        $("#alertError").hide();
                        $(".btn-register span").text('Processing...');
                        $('.btn-register').prop('disabled', true);
                    },
                    success: function(response, textStatus, xhr) {
                        if (xhr.status == 201 || xhr.status == 200) {
                            // Success
                            alert('Registration successful! Your request under review.');
                            window.location.href = "{{ route('center_user.login') }}";
                        } else {
                            $("#alertError").show();
                            $('#listError').html('Registration failed.');
                            $(".btn-register span").text('Register');
                            $('.btn-register').prop('disabled', false);
                        }
                    },
                    error: function(response) {
                        $("#alertError").show();
                        var errors = response.responseJSON && response.responseJSON.errors;
                        var ul = document.getElementById("listError");
                        ul.innerHTML = '';
                        
                        if (errors) {
                            for (var error in errors) {
                                var li = document.createElement("li");
                                li.appendChild(document.createTextNode(errors[error][0])); // Display first error of each field
                                ul.appendChild(li);
                            }
                        } else if (response.responseJSON && response.responseJSON.message) {
                            $('#listError').html('<li>' + response.responseJSON.message + '</li>');
                        } else {
                            $('#listError').html('<li>Registration failed with status ' + response.status + '</li>');
                        }

                        $("html, body").animate({ scrollTop: 0 }, 500);
                        $(".btn-register span").text('Register');
                        $('.btn-register').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="auth-custom-bg">
        
        <div class="container d-flex flex-column align-items-center">
            <!-- Top Logo -->
            <div class="auth-logo w-100">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('logo.svg') }}" alt="Luzori Logo">
                </a>
            </div>

            <div class="register-card">
                <h2 class="register-title">Become Business</h2>
                
                <div id="alertError" class="alert alert-danger" role="alert">
                    <ul id="listError" class="mb-0 ps-3"></ul>
                </div>

                <form id="frmRegister" enctype="multipart/form-data">
                    <div class="row g-4">
                        <!-- Column 1 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name Center</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="name of center" required  style="background-color:#DFC3A5 ; color:black">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Center</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="example@email.com"  required style="background-color:#DFC3A5 ; color:black">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" id="password" class="form-control" name="password" placeholder="••••••••" required  style="background-color:#DFC3A5 ; color:black">
                                    <span class="input-group-text cursor-pointer toggle-password"  ><i class="ti ti-eye-off"></i></span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Logo Center</label>
                                <div class="file-upload-box">
                                    <div class="file-upload-icon"><i class="ti ti-camera"></i></div>
                                    <div class="file-upload-text">SELECT LOGO</div>
                                    <input type="file" id="image" name="image" accept="image/*"  style="background-color:#DFC3A5 ; color:black">
                                </div>
                            </div>
                        </div>

                        <!-- Column 2 -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="domain" class="form-label">Domain Center</label>
                                <input type="text" class="form-control" id="domain" name="domain" placeholder="center-name" required  style="background-color:#DFC3A5 ; color:black">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Center</label>
                                <div class="phone-input-group">
                                    <select class="form-select" id="country_code" name="country_code"  style="background-color:#DFC3A5 ; color:black">
                                        <option value="971">🇦🇪 +971</option>
                                        <option value="966">🇸🇦 +966</option>
                                        <option value="974">🇶🇦 +974</option>
                                        <option value="965">🇰🇼 +965</option>
                                        <option value="968">🇴🇲 +968</option>
                                        <option value="973">🇧🇭 +973</option>
                                    </select>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="503140232" required  style="background-color:#DFC3A5 ; color:black">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="currency" class="form-label">Currency</label>
                                <select class="form-select" id="currency" name="currency"  style="background-color:#DFC3A5 ; color:black">
                                    <option value="AED">AED</option>
                                    <option value="USD">USD</option>
                                    <option value="SAR">SAR</option>
                                    <option value="EUR">EUR</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Primary Image</label>
                                <div class="file-upload-box">
                                    <div class="file-upload-icon"><i class="ti ti-upload"></i></div>
                                    <div class="file-upload-text">PRIMARY IMAGE</div>
                                    <input type="file" id="primary_image" name="primary_image" accept="image/*"  style="background-color:#DFC3A5 ; color:black">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <button type="submit" class="btn btn-register" style="background-color:#276749 ; color:white; border-radius:5rem">
                            <span>Register</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
