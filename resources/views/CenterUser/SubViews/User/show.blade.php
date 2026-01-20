@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <img src="{{ $item->image }}" alt="user image"
                        style="height:150px;width:150px;border-radius:100%;margin:15px auto;" />
                    <div class="card-body text-center">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mt-2">
                                    <h5 class="mb-75" style="font-weight:bold;">{{__('field.name')}}</h5>
                                    <p class="card-text">{{ $item->name }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mt-2">
                                    <h5 class="mb-75" style="font-weight:bold;">{{__('field.email')}}</h5>
                                    <p class="card-text">{{ $item->email }}</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mt-2">
                                    <h5 class="mb-75" style="font-weight:bold;">{{__('field.phone')}}</h5>
                                    <p class="card-text">{{ $item->phone }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mt-2">
                                    <h5 class="mb-75" style="font-weight:bold;">{{__('field.created_at')}}</h5>
                                    <p class="card-text">{{ $item->created_at }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
