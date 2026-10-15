@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="luzori-page lz-membership-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-award"></i>
                </div>
                <h1 class="lz-page-title">
                    @if (is_null($item))
                        {{ __('general.add_new') }} {{ __('locale.memberships') }}
                    @else
                        {{ __('general.edit') }} {{ __('locale.memberships') }}
                    @endif
                </h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="user_id" class="lz-field-label">
                                {{ __('field.user') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.select_a_user_from_the_list') }}</small>
                            <select class="select2 form-select" name="user_id" id="user_id">
                                @foreach ($users as $user)
                                    <option {{ $item ? ($item->user_id == $user->id ? 'selected' : null) : null }}
                                        value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="membership_no" class="lz-field-label">
                                {{ __('field.membership_no') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_membership_number') }}</small>
                            <input type="text" id="membership_no" class="form-control" name="membership_no"
                                placeholder="{{ __('field.membership_no') }}"
                                value="{{ $item ? $item->membership_no : null }}" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="percent" class="lz-field-label">
                                {{ __('field.discount_percentage') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.enter_the_discount_percentage') }}</small>
                            <input type="number" id="percent" class="form-control" name="percent"
                                placeholder="{{ __('field.discount_percentage') }}"
                                value="{{ $item ? $item->percent : null }}" />
                        </div>
                        @php
                            if ($item) {
                                if (str_contains($item->start_at, '/')) {
                                    $start_at = DateTime::createFromFormat('d/m/Y', $item->start_at);
                                    $item->start_at = $start_at->format('Y-m-d');
                                }
                                if (str_contains($item->end_at, '/')) {
                                    $end_at = DateTime::createFromFormat('d/m/Y', $item->end_at);
                                    $item->end_at = $end_at->format('Y-m-d');
                                }
                            }
                        @endphp
                        <div class="col-md-6 mb-3">
                            <label for="start_at" class="lz-field-label">
                                {{ __('field.start_at') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.select_a_start_date_of_the_membership') }}</small>
                            <input type="date" id="start_at" class="form-control" name="start_at"
                                value="{{ $item ? $item->start_at : null }}" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_at" class="lz-field-label">
                                {{ __('field.end_at') }} <span class="text-danger">*</span>
                            </label>
                            <small class="lz-field-help">{{ __('general.select_a_end_date_of_the_membership') }}</small>
                            <input type="date" id="end_at" class="form-control" name="end_at"
                                value="{{ $item ? $item->end_at : null }}" />
                        </div>
                        <div class="col-md-12">
                            @include('CenterUser.Components.image', [
                                'item' => $item,
                                'name' => 'image',
                                'model' => 'membership',
                            ])
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.memberships.index') }}" class="btn btn-luzori-beige">
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
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @vite('resources/assets/js/forms-selects.js')

    @include('CenterUser.Components.submit-form-ajax')
    @include('CenterUser.Components.image-js')
@endsection
