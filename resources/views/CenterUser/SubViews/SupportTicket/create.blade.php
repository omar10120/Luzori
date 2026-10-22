@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/css/luzori-support.css') }}?v=1">
@endsection

@section('content')
    <div class="luzori-page container-fluid px-1 px-sm-2 px-md-4">
        <div class="lz-page-toolbar mb-3">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon"><i class="ti ti-ticket"></i></div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
            <a href="{{ route('center_user.support-tickets.index') }}" class="btn btn-luzori-muted">{{ __('general.back') }}</a>
        </div>

        <div class="card lz-ticket-card">
            <div class="card-body">
                <form method="post" action="{{ route('center_user.support-tickets.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">{{ __('field.subject') }} <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required maxlength="200">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('field.priority') }}</label>
                            <select name="priority" class="form-select">
                                @foreach(['low','normal','high','urgent'] as $p)
                                    <option value="{{ $p }}" @selected(old('priority', 'normal') === $p)>{{ __('field.' . $p) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('field.description') }} <span class="text-danger">*</span></label>
                            <textarea name="body" class="form-control" rows="6" required>{{ old('body') }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('center_user.support-tickets.index') }}" class="btn btn-luzori-muted">{{ __('general.cancel') }}</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>{{ __('general.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
