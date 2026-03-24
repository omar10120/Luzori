@php
    $fontFamily = App::getLocale() == 'ar' ? 'Cairo' : 'Poppins';
@endphp

@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('Admin.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="id" value="{{ $item ? $item->id : '' }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.first_name') ?? 'First Name' }}</label>
                                    <input type="text" class="form-control" name="first_name"
                                        placeholder="First Name" value="{{ $item ? $item->first_name : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.last_name') ?? 'Last Name' }}</label>
                                    <input type="text" class="form-control" name="last_name"
                                        placeholder="Last Name" value="{{ $item ? $item->last_name : '' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.email') }}</label>
                                    <input type="email" name="email" class="form-control"
                                        placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-1">
                                    <label class="form-label">Country Code</label>
                                    <input type="text" class="form-control" name="country_code"
                                        placeholder="+971" value="{{ $item ? $item->country_code : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.mobile_number') }}</label>
                                    <input type="text" name="phone" id="phone" class="form-control"
                                        placeholder="{{ __('field.mobile_number') }}"
                                        value="{{ $item ? $item->phone : '' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.password') }}</label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" class="form-control" name="password" id="password"
                                            placeholder="{{ __('field.password') }}" />
                                        <span class="input-group-text cursor-pointer">
                                            <i class="ti ti-eye-off" id="togglePassword"></i>
                                        </span>
                                    </div>
                                    @if ($item)
                                        <small class="text-muted">Leave blank if you don't want to change it</small>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.confirm_password') }}</label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" id="confirmPassword" class="form-control"
                                            name="password_confirmation"
                                            placeholder="{{ __('field.confirm_password') }}" />
                                        <span class="input-group-text cursor-pointer">
                                            <i class="ti ti-eye-off" id="toggleConfirmPassword"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.wallet') }} Balance</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="wallet"
                                        placeholder="0.00" value="{{ $item ? $item->wallet : '0.00' }}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.status') }} (Active)</label>
                                    <select class="form-control" name="is_active">
                                        <option value="1" {{ $item && $item->is_active ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{ $item && !$item->is_active ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.image') }} (Avatar)</label>
                                    <input type="file" class="form-control" id="image" name="image" />
                                </div>
                                <img id="show_image" src="{{ $item ? $item->getFirstMediaUrl('PrimaryImage') : '' }}"
                                    style="{{ $item && $item->getFirstMediaUrl('PrimaryImage') ? '' : 'display:none;' }} width:100px;height:100px;margin-top:10px;border-radius:50%;object-fit:cover;"
                                    alt="user image" />
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary submitFrom">
                            <i class="menu-icon tf-icons ti ti-check"></i>
                            <span>{{ __('general.save') }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.getElementById('image').onchange = evt => {
            const [file] = document.getElementById('image').files
            if (file) {
                document.getElementById("show_image").style.display = "block";
                document.getElementById("show_image").src = URL.createObjectURL(file)
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const togglePasswordList = ['togglePassword', 'toggleConfirmPassword'];
            togglePasswordList.forEach(id => {
                const toggler = document.getElementById(id);
                if(toggler) {
                    toggler.addEventListener('click', function() {
                        const input = this.parentElement.previousElementSibling;
                        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                        input.setAttribute('type', type);
                        this.classList.toggle('ti-eye');
                        this.classList.toggle('ti-eye-off');
                    });
                }
            });
        });
    </script>
    @include('Admin.Components.submit-form-ajax')
@endsection
