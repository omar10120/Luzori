@php
    $customizerHidden = 'customizer-hide';
    $configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', __('admin.login_page'))

@section('content')
    <div class="authentication-wrapper authentication-cover authentication-bg d-flex align-items-center justify-content-center min-vh-100">
        <div class="authentication-inner row w-100">
            <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4 mx-auto">
                <div class="w-px-400 mx-auto">
                    <div class="app-brand mb-4 text-center justify-content-center">
                        <a href="#" class="app-brand-link gap-2 d-inline-flex justify-content-center ">
                            <span class="app-brand-logo demo">@include('_partials.macros', ['height' => 20, 'withbg' => 'fill: #fff;'])</span>
                        </a>
                    </div>

                    <h3 class="mb-1 text-center">{{ __('admin.signin_prompt') }}</h3>
                    <p class="mb-4 text-center">{{ __('admin.enter_verification_code') ?? 'يرجى إدخال رمز المصادقة الثنائية' }}</p>

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('center_user.verify') }}" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label for="code" class="form-label">{{ __('admin.verification_code') ?? 'رمز التحقق' }}</label>
                            <input type="text" class="form-control" id="code" name="code" placeholder="123456" autofocus>
                        </div>
                        <button type="submit" class="btn btn-verify d-grid w-100">
                            <span class="fw-semibold">{{ __('admin.verify') ?? 'تحقق' }}</span>
                        </button>
                    </form>

                    <div class="text-center">
                        <button type="button" id="resend-code" class="btn btn-resend p-0">{{ __('admin.resend_code') ?? 'إعادة إرسال الرمز' }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 1080;">
        <div id="resendToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    {{ __('admin.code_resent') ?? 'تم إرسال رمز التحقق مرة أخرى' }}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var btn = document.getElementById('resend-code');
            if (!btn) return;
            btn.addEventListener('click', function() {
                if (btn.disabled) return;
                btn.disabled = true;
                var tokenEl = document.querySelector('meta[name="csrf-token"]');
                var token = tokenEl ? tokenEl.getAttribute('content') : '';
                fetch("{{ route('center_user.verify.resend') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({})
                }).then(function(res) { return res.json().catch(function(){ return {}; }); })
                  .then(function() {
                      try {
                          if (window.bootstrap && document.getElementById('resendToast')) {
                              var toast = new bootstrap.Toast(document.getElementById('resendToast'));
                              toast.show();
                          } else {
                              alert("{{ __('admin.code_resent') ?? 'تم إرسال رمز التحقق مرة أخرى' }}");
                          }
                      } catch (e) {
                          // fallback
                          alert("{{ __('admin.code_resent') ?? 'تم إرسال رمز التحقق مرة أخرى' }}");
                      }
                  })
                  .finally(function() { btn.disabled = false; });
            });
        })();
    </script>
    <style>
        .btn-verify {
            background: linear-gradient(135deg, #6a6ff5 0%, #7c3aed 100%);
            color: #fff;
            border: none;
            border-radius: 0.75rem;
            padding: 0.85rem 1.25rem;
            box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }
        .btn-verify:hover {
            filter: brightness(1.05);
            box-shadow: 0 10px 24px rgba(124, 58, 237, 0.32);
            transform: translateY(-1px);
        }
        .btn-verify:active {
            transform: translateY(0);
        }
        .btn-resend {
            background: transparent;
            color: #9aa4b2;
            text-decoration: underline;
        }
        .btn-resend:hover {
            color: #cbd5e1;
            text-decoration: none;
        }
    </style>
@endsection


