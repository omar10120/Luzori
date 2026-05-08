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
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            {{ __('general.slug_hint') ?? 'Select the categories that describe the services your center provides.' }}
                        </p>

                        @if($allCategories->isEmpty())
                            <div class="alert alert-warning">
                                {{ __('general.no_data') ?? 'No global categories have been added by the admin yet.' }}
                            </div>
                        @else
                            <div class="row">
                                @foreach($allCategories as $category)
                                    <div class="col-md-4 col-sm-6 mb-3">
                                        <div class="form-check form-check-custom form-check-solid">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="global_category_ids[]"
                                                id="global_cat_{{ $category->id }}"
                                                value="{{ $category->id }}"
                                                {{ in_array($category->id, $selectedIds) ? 'checked' : '' }}
                                            />
                                            <label class="form-check-label" for="global_cat_{{ $category->id }}">
                                                {{ $category->name }}
                                                <small class="text-muted d-block">{{ $category->slug }}</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
    @include('Admin.Components.submit-form-ajax')
@endsection
