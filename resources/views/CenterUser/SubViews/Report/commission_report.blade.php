@extends('layouts/layoutMaster')

@section('title', __('locale.commission_report'))

@section('vendor-style')
    @include('CenterUser.Components.report-page-styles')
@endsection

@section('content')
    <div class="luzori-page lz-report-page">
        @include('CenterUser.Components.breadcrumbs', [
            'menu' => __('locale.reports'),
            'menu_link' => null,
            'title' => __('locale.commission_report'),
        ])

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-percentage"></i>
                </div>
                <h1 class="lz-page-title">{{ __('locale.commission_report') }}</h1>
            </div>
        </div>

        <div class="card lz-form-card">
            <div class="card-body">
                <div class="lz-report-filters">
                    <form method="get" action="{{ route('center_user.reports.commission-report') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="lz-field-label">{{ __('field.year') }}</label>
                                <select class="form-control select" name="year" required>
                                    @if (!empty($years))
                                        @foreach ($years as $year)
                                            @php
                                                // $year is a Booking model — compare its attribute, not the object
                                                $selected = ((int) $year->year === (int) $selected_year) ? 'selected' : '';
                                            @endphp
                                            <option {{ $selected }} value="{{ $year->year }}">{{ $year->year }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="lz-field-label">{{ __('field.month') }}</label>
                                <select class="form-control select" name="month" required>
                                    @for ($i = 1; $i <= 12; $i++)
                                        @php
                                            $selected = ((int) $i === (int) $selected_month) ? 'selected' : '';
                                        @endphp
                                        <option {{ $selected }} value="{{ $i }}">
                                            {{ date('F', mktime(0, 0, 0, $i, 10)) }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="lz-field-label">{{ __('field.branch') }}</label>
                                <select class="form-control" name="branch_id">
                                    @if (get_user_role() == 1 || get_user_role() == 3)
                                        <option value="">{{ __('field.all_branches') }}</option>
                                    @endif
                                    @if (!empty($branches))
                                        @foreach ($branches as $branch)
                                            @php
                                                $selected = ((int) $branch->id === (int) $selected_branch) ? 'selected' : '';
                                            @endphp
                                            <option {{ $selected }} value="{{ $branch->id }}">{{ $branch->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="lz-report-actions">
                                    <button class="btn btn-luzori-primary">
                                        <i class="ti ti-eye me-1"></i>{{ __('field.view') }}
                                    </button>
                                    @if (!empty($selected_year))
                                        <a target="_blank"
                                            href="{{ route('center_user.reports.commission-report') }}?year={{ $selected_year }}&month={{ $selected_month }}&branch_id={{ $selected_branch }}&is_pdf=true"
                                            class="btn btn-luzori-gold">
                                            <i class="ti ti-file-type-pdf me-1"></i>PDF
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="lz-report-result">
                    {!! $template !!}
                </div>
            </div>
        </div>
    </div>
@endsection