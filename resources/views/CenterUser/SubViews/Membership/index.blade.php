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
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="user_id" class="form-label">{{ __('field.user') }}</label>
                                    <select class="select2 form-control" name="user_id">
                                        @foreach ($users as $user)
                                            <option {{ $item ? ($item->user_id == $user->id ? 'selected' : null) : null }}
                                                value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="membership_no" class="form-label">{{ __('field.membership_no') }} </label>
                                    <input type="text" id="membership_no" class="form-control" name="membership_no"
                                        placeholder="{{ __('field.membership_no') }}"
                                        value="{{ $item ? $item->membership_no : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="percent" class="form-label">{{ __('field.discount_percentage') }} </label>
                                    <input type="number" id="percent" class="form-control" name="percent"
                                        placeholder="{{ __('field.discount_percentage') }}"
                                        value="{{ $item ? $item->percent : null }}" />
                                </div>
                            </div>
                            @php
                                if($item) {
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
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="start_at" class="form-label">{{ __('field.start_at') }} </label>
                                    <input type="date" id="start_at" class="form-control" name="start_at"
                                        value="{{ $item ? $item->start_at : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="end_at" class="form-label">{{ __('field.end_at') }} </label>
                                    <input type="date" id="end_at" class="form-control" name="end_at"
                                        value="{{ $item ? $item->end_at : null }}" />
                                </div>
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

    @include('CenterUser.Components.submit-form-ajax')
@endsection
