@extends('layouts/layoutMaster')

@section('title', '403 - Forbidden')

@section('content')
<div class="container-xxl container-p-y">
    <div class="misc-wrapper">
        <h2 class="mb-2 mx-2">403 - Forbidden</h2>
        <p class="mb-4 mx-2">You do not have permission to access this resource.</p>
        <a href="{{ url()->previous() }}" class="btn btn-primary">Go Back</a>
    </div>
</div>
@endsection

