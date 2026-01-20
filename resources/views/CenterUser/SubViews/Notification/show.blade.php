@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="row">
                            @foreach (Config::get('translatable.locales') as $locale)
                                <div class="col-md-6">
                                    <div class="mt-2">
                                        <h5 class="mb-75" style="font-weight:bold;">{{__('field.address')}}
                                            {{ Str::upper($locale) }}</h5>
                                        <p class="card-text">{{ $item->translate($locale)->title }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="row">
                            @foreach (Config::get('translatable.locales') as $locale)
                                <div class="col-md-6">
                                    <div class="mt-2">
                                        <h5 class="mb-75" style="font-weight:bold;">{{__('field.description')}} {{ Str::upper($locale) }}
                                        </h5>
                                        <p class="card-text">{{ $item->translate($locale)->text }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if (count($item->users) > 0)
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="table-responsive text-center">
                                <table class="table table-striped table-head-custom table-checkable" id="dtTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{__('field.name')}}</th>
                                            <th>{{__('field.email')}}</th>
                                            <th>{{__('field.phone')}}</th>
                                            <th>{{__('field.created_at')}}</th>
                                            <th>{{__('field.action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($item->users as $user)
                                            <tr>
                                                <td>{{ $user->id }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone }}</td>
                                                <td>{{ $user->created_at }}</td>
                                                <td>
                                                    <a href="{{ route('center_user.users.show', ['id' => $user->id]) }}"
                                                        data-bs-toggle="tooltip" title="" data-bs-animation="false"
                                                        data-bs-original-title="عرض">
                                                        <svg style="margin:12px;" xmlns="http://www.w3.org/2000/svg"
                                                            width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" class="feather feather-eye">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z">
                                                            </path>
                                                            <circle cx="12" cy="12" r="3">
                                                            </circle>
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
