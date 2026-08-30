@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="mb-1">{{ $title }}</h2>
                    <p class="text-muted mb-0">
                        {{ __('field.barcode') }}: {{ $product->barcode ?: '-' }}
                    </p>
                </div>
                <a href="{{ route('center_user.inventorymovements.index') }}" class="btn btn-outline-secondary">
                    {{ __('general.back') }}
                </a>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-4 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">{{ __('field.branch') }}</label>
                        <select name="branch_id" class="form-select" onchange="this.form.submit()">
                            <option value="">{{ __('general.all') ?? 'All' }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected((int) $branchId === (int) $branch->id)>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('field.created_at') }}</th>
                                <th>{{ __('field.branch') }}</th>
                                <th>{{ __('field.movement_type') }}</th>
                                <th>{{ __('field.quantity') }}</th>
                                <th>{{ __('field.reference') }}</th>
                                <th>{{ __('field.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $movement)
                                <tr>
                                    <td>{{ $movement->id }}</td>
                                    <td>{{ $movement->created_at }}</td>
                                    <td>{{ $movement->branch->name ?? '-' }}</td>
                                    <td>
                                        <span class="badge {{ $movement->quantity >= 0 ? 'bg-label-success' : 'bg-label-danger' }}">
                                            {{ __('field.movement_' . $movement->movement_type) }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold {{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                    </td>
                                    <td>
                                        @if($movement->reference_type && $movement->reference_id)
                                            {{ $movement->reference_type }} #{{ $movement->reference_id }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $movement->notes ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">{{ __('general.no_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $movements->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
