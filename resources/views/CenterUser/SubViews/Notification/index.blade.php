@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/select2/select2.scss'])
@endsection

@section('content')
    <div class="luzori-page lz-notification-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-bell-plus"></i>
                </div>
                <h1 class="lz-page-title">
                    @if (is_null($item))
                        {{ __('general.add_new') }} {{ __('locale.notifications') }}
                    @else
                        {{ __('general.edit') }} {{ __('locale.notifications') }}
                    @endif
                </h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="lz-field-label">{{ __('field.users') }}</label>
                            <small class="lz-field-help">{{ __('general.select_a_user_from_the_list') }}</small>
                            <select class="select2 form-select" name="users[]" multiple>
                                <option value="all">{{ __('field.all_users') }}</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <ul class="nav nav-tabs lz-lang-tabs" role="tablist">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <li class="nav-item" role="presentation">
                                <a class="nav-link {{ $loop->first ? 'active' : null }}"
                                    id="{{ $locale }}-tab-add" data-bs-toggle="tab"
                                    href="#{{ $locale }}-add" aria-controls="{{ $locale }}-add"
                                    role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    {{ Str::upper($locale) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content pt-3">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label">{{ __('field.title') }}</label>
                                        <input type="text" class="form-control"
                                            name="{{ $locale }}[title]"
                                            placeholder="{{ __('field.title') }}"
                                            value="{{ $item ? ($item->translate($locale)->title ?? '') : '' }}" />
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label class="lz-field-label">{{ __('field.text') }}</label>
                                        <textarea name="{{ $locale }}[text]" class="form-control" cols="30" rows="8"
                                            placeholder="{{ __('field.text') }}">{{ $item ? ($item->translate($locale)->text ?? '') : '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.notifications.index') }}" class="btn btn-luzori-beige">
                        {{ __('general.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-luzori-primary submitFrom">
                        <i class="ti ti-check me-1"></i>
                        <span>{{ __('general.save') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('vendor-script')
    @vite(['resources/assets/vendor/libs/select2/select2.js'])
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')
    @include('CenterUser.Components.submit-form-ajax')
@endsection
