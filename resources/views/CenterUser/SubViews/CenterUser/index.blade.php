@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                @if($item)
                    <input type="hidden" name="id" value="{{ $item->id }}">
                @endif
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="{{ __('field.name') }}" value="{{ $item ? $item->name : '' }}" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.email') }}@if(!$item) <span class="text-danger">*</span>@endif</label>
                                    <input type="text" name="email" class="form-control dt-email"
                                        placeholder="{{ __('field.email') }}" value="{{ $item ? $item->email : '' }}" {{ !$item ? 'required' : '' }} />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label for="branch_id" class="form-label">{{ __('field.branch') }} <span class="text-danger">*</span></label>
                                    <select disabled class="select2 form-control" name="branch_id" required>
                                        @foreach ($branches as $branch)
                                            <option {{ $item ? ($item->branch_id == $branch->id ? 'selected' : null) : null }}
                                                value="{{ $branch->id }}">{{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($item)
                                        <input type="hidden" name="branch_id" value="{{ $item->branch_id }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2">
                                @include('Admin.Components.country_code', ['item' => $item])
                            </div>
                            <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.phone') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="phone" class="form-control"
                                        placeholder="{{ __('field.phone') }}" value="{{ $item ? $item->phone : '' }}" required />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.password') }}@if(!$item) <span class="text-danger">*</span>@endif</label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" class="form-control" name="password"
                                            placeholder="{{ __('field.password') }}" {{ !$item ? 'required' : '' }} />
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.confirm_password') }}@if(!$item) <span class="text-danger">*</span>@endif</label>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input type="password" class="form-control" name="password_confirmation"
                                            placeholder="{{ __('field.confirm_password') }}" {{ !$item ? 'required' : '' }} />
                                        <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.role') }} <span class="text-danger">*</span></label>
                                    <select disabled class="select2 form-control" name="role" required>
                                        @foreach ($roles as $role)
                                            <option
                                                {{ $item ? ($item->roles()?->first()?->name == $role->name ? 'selected' : null) : null }}
                                                value="{{ $role->id }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($item)
                                        <input type="hidden" name="role" value="{{ $item->roles()?->first()?->id }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.image') }}@if(!$item) <span class="text-danger">*</span>@endif</label>
                                    <input type="file" class="form-control" id="image" name="image" {{ !$item ? 'required' : '' }} />
                                </div>
                                <img id="show_image" src="{{ $item ? $item->image : '' }}"
                                    style="{{ $item ? '' : 'display:none;' }} width:200px;height:200px;margin:20px;"
                                    alt="center user image" />
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

@section('vendor-script')
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')

    <script>
        image.onchange = evt => {
            const [file] = image.files
            if (file) {
                document.getElementById("show_image").style.display = "block";
                show_image.src = URL.createObjectURL(file)
            }
        }
    </script>

    @include('CenterUser.Components.submit-form-ajax')
@endsection
