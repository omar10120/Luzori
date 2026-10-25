@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-global-cat-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
        }
        @media (max-width: 992px) {
            .lz-global-cat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 576px) {
            .lz-global-cat-grid {
                grid-template-columns: 1fr;
            }
        }
        .lz-global-cat-item {
            position: relative;
            display: block;
            margin: 0;
            cursor: pointer;
        }
        .lz-global-cat-item input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .lz-global-cat-card {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            min-height: 76px;
            padding: 0.95rem 1rem;
            border-radius: 14px;
            border: 1px solid #efe6d8;
            background: #fdfaf6;
            transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
        }
        .lz-global-cat-item:hover .lz-global-cat-card {
            border-color: #d4b483;
            background: #fff;
        }
        .lz-global-cat-item input:checked + .lz-global-cat-card {
            border-color: #c5a070;
            background: linear-gradient(135deg, #f7f2ea 0%, #fff 60%);
            box-shadow: 0 6px 18px rgba(197, 160, 112, 0.18);
        }
        .lz-global-cat-check {
            width: 22px;
            height: 22px;
            border-radius: 7px;
            border: 1.5px solid #c5a070;
            background: #fff;
            flex-shrink: 0;
            margin-top: 0.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: transparent;
            font-size: 0.85rem;
            transition: background 0.15s ease, color 0.15s ease;
        }
        .lz-global-cat-item input:checked + .lz-global-cat-card .lz-global-cat-check {
            background: #002b26;
            border-color: #002b26;
            color: #e8d5b5;
        }
        .lz-global-cat-name {
            display: block;
            font-weight: 700;
            color: #002b26;
            font-size: 0.95rem;
            line-height: 1.3;
        }
        .lz-global-cat-slug {
            display: block;
            margin-top: 0.2rem;
            color: #7a8582;
            font-size: 0.78rem;
            word-break: break-word;
        }
        .lz-global-cat-hint {
            color: #7a8582;
            font-size: 0.9rem;
            margin: 0 0 1.25rem;
            max-width: 46rem;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-global-categories-page ">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-category-2"></i>
                </div>
                <h1 class="lz-page-title">{{ $title }}</h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <div class="card lz-form-card">
                <div class="card-body">
                    <p class="lz-global-cat-hint">
                        {{ __('general.slug_hint') ?? 'Select the categories that describe the services your center provides.' }}
                    </p>

                    @if($allCategories->isEmpty())
                        <div class="alert alert-warning mb-0">
                            {{ __('general.no_data') ?? 'No global categories have been added by the admin yet.' }}
                        </div>
                    @else
                        <div class="lz-global-cat-grid">
                            @foreach($allCategories as $category)
                                <label class="lz-global-cat-item" for="global_cat_{{ $category->id }}">
                                    <input
                                        type="checkbox"
                                        name="global_category_ids[]"
                                        id="global_cat_{{ $category->id }}"
                                        value="{{ $category->id }}"
                                        {{ in_array($category->id, $selectedIds) ? 'checked' : '' }}
                                    />
                                    <span class="lz-global-cat-card">
                                        <span class="lz-global-cat-check">
                                            <i class="ti ti-check"></i>
                                        </span>
                                        <span>
                                            <span class="lz-global-cat-name">{{ $category->name }}</span>
                                            <span class="lz-global-cat-slug">{{ $category->slug }}</span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="card-footer d-flex justify-content-end">
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
@endsection

@section('page-script')
    @include('Admin.Components.submit-form-ajax')
@endsection
