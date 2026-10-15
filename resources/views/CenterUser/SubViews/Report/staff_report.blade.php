@extends('layouts/layoutMaster')

@section('title', __('locale.staff_report'))

@section('vendor-style')
    @include('CenterUser.Components.report-page-styles')
@endsection

@section('content')
    <div class="luzori-page lz-report-page">
        @include('CenterUser.Components.breadcrumbs', [
            'menu' => __('locale.reports'),
            'menu_link' => null,
            'title' => __('locale.staff_report'),
        ])

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-users"></i>
                </div>
                <h1 class="lz-page-title">{{ __('locale.staff_report') }}</h1>
            </div>
        </div>

        <div class="card lz-form-card">
            <div class="card-body">
                <div class="lz-report-filters">
                    <form method="get" action="{{ route('center_user.reports.staff-report') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="lz-field-label">{{ __('field.year') }}</label>
                                <select class="form-control select" name="year" required>
                                    @if (!empty($years))
                                        @foreach ($years as $year)
                                            @php
                                                $selected = '';
                                                if ($year == $selected_year) {
                                                    $selected = "selected=''";
                                                }
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
                                            $selected = '';
                                            if ($i == $selected_month) {
                                                $selected = "selected=''";
                                            }
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
                                                $selected = '';
                                                if ($branch->id == $selected_branch) {
                                                    $selected = 'selected=""';
                                                }
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
                                    @if (isset($request) && !empty($request->has('year')))
                                        <a target="_blank"
                                            href="{{ route('center_user.reports.staff-report') }}?year={{ $request->get('year') }}&month={{ $request->get('month') }}&branch_id={{ $request->get('branch_id') }}&is_pdf=true"
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
