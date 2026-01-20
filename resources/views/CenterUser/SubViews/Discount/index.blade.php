@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
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
                                    <label for="type" class="form-label">{{ __('field.type') }}</label>
                                    <select class="form-control" id="type" name="type">
                                        <option {{ $item ? ($item->type == 'fixed' ? 'selected' : null) : null }}
                                            value="fixed">
                                            {{ __('field.fixed') }}</option>
                                        <option {{ $item ? ($item->type == 'percentage' ? 'selected' : null) : null }}
                                            value="percentage">
                                            {{ __('field.percentage') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="using_type" class="form-label">{{ __('field.using_type') }} </label>
                                    <select class="form-control" id="using_type" name="using_type">
                                        <option {{ $item ? ($item->using_type == 'single' ? 'selected' : null) : null }}
                                            value="single">{{ __('field.single') }}</option>
                                        <option {{ $item ? ($item->using_type == 'multi' ? 'selected' : null) : null }}
                                            value="multi">{{ __('field.multi') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mb-2" id="benefit_numbers"
                                {{ $item ? ($item->using_type != 'multi' ? 'style=display:none;' : null) : 'style=display:none;' }}>
                                <div class="mb-1">
                                    <label for="benefit_numbers" class="form-label">{{ __('field.benefit_numbers') }}
                                    </label>
                                    <input type="number" id="benefit_numbers" class="form-control" name="benefit_numbers"
                                        placeholder="{{ __('field.benefit_numbers') }}"
                                        value="{{ $item ? $item->benefit_numbers : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12 mb-2">
                                <div class="mb-1">
                                    <label for="amount" class="form-label">{{ __('field.amount') }} </label>
                                    <input type="number" id="amount" class="form-control" name="amount"
                                        placeholder="{{ __('field.amount') }}"
                                        value="{{ $item ? $item->amount : null }}" />
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
                                        placeholder="{{ __('field.start_at') }}"
                                        value="{{ $item ? $item->start_at : null }}" />
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="end_at" class="form-label">{{ __('field.end_at') }} </label>
                                    <input type="date" id="end_at" class="form-control" name="end_at"
                                        placeholder="{{ __('field.end_at') }}"
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
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')

    <script>
        $('#using_type').change(function() {
            if ($(this).val() == 'multi') {
                $('#benefit_numbers').show();
            } else {
                $('#benefit_numbers').hide();
            }
        });
    </script>
@endsection
