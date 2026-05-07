@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
@endsection

@section('content')
    <div class="container">
        @include('Admin.Components.breadcrumbs')

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
                                <div class="mb-4">
                                    <label class="form-label">
                                        {{ __('field.name') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="name"
                                        id="name"
                                        placeholder="{{ __('field.name') }}"
                                        value="{{ $item ? $item->name : '' }}"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label">
                                        {{ __('field.slug') }}
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        class="form-control"
                                        name="slug"
                                        id="slug"
                                        placeholder="{{ __('field.slug') }}"
                                        value="{{ $item ? $item->slug : '' }}"
                                    />
                                    <small class="text-muted">{{ __('general.slug_hint', [], app()->getLocale()) ?? 'Used for filtering (e.g. hair-salon, spa)' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.global-categories.index') }}" class="btn btn-secondary me-2">
                            <i class="ti ti-arrow-back"></i>
                            <span>{{ __('general.back') }}</span>
                        </a>
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
    <script>
        // Auto-generate slug from name if slug is empty
        document.getElementById('name').addEventListener('input', function () {
            const slugField = document.getElementById('slug');
            if (!slugField.dataset.touched) {
                slugField.value = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[\s_]+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-');
            }
        });

        document.getElementById('slug').addEventListener('input', function () {
            this.dataset.touched = 'true';
        });
    </script>

    @include('Admin.Components.submit-form-ajax')
@endsection
