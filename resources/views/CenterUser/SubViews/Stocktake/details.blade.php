@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container-fluid">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2>Stocktake details</h2>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Stocktake details:</h5>
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Started on:</strong></td>
                                        <td>{{ $stocktake->started_at ? $stocktake->started_at->format('d F Y H:i') : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Counted by:</strong></td>
                                        <td>{{ $stocktake->startedBy->name ?? '-' }}</td>
                                    </tr>
                                    @if($stocktake->description)
                                    <tr>
                                        <td><strong>Stocktake note:</strong></td>
                                        <td>{{ $stocktake->description }}</td>
                                    </tr>
                                    @endif
                                    @if($stocktake->completed_at)
                                    <tr>
                                        <td><strong>Completed on:</strong></td>
                                        <td>{{ $stocktake->completed_at->format('d F Y H:i') }}</td>
                                    </tr>
                                    @endif
                                    @if($stocktake->reviewedBy)
                                    <tr>
                                        <td><strong>Reviewed by:</strong></td>
                                        <td>{{ $stocktake->reviewedBy->name }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <h5 class="mb-3">Product list summary</h5>

                        <div class="mb-3">
                            <input type="text" class="form-control" id="product-search" 
                                placeholder="Search by name or barcode" style="max-width: 300px;" />
                        </div>

                        <div class="btn-group mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary active filter-btn" data-filter="counted">
                                Counted <span class="badge bg-primary">{{ $stocktake->stocktakeProducts->whereNotNull('counted_qty')->count() }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="unmatched">
                                Unmatched <span class="badge bg-primary">{{ $stocktake->stocktakeProducts->whereNotNull('counted_qty')->where('difference', '!=', 0)->count() }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="matched">
                                Matched <span class="badge bg-primary">{{ $stocktake->stocktakeProducts->whereNotNull('counted_qty')->where('difference', 0)->count() }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="excluded">
                                Excluded <span class="badge bg-primary">0</span>
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="products-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Counted by</th>
                                        <th>Expected</th>
                                        <th>Counted</th>
                                        <th>Difference</th>
                                        <th>Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalExpected = 0;
                                        $totalCounted = 0;
                                        $totalDifference = 0;
                                        $totalCost = 0;
                                    @endphp
                                    @foreach($stocktake->stocktakeProducts as $stocktakeProduct)
                                        @php
                                            $totalExpected += $stocktakeProduct->expected_qty;
                                            if ($stocktakeProduct->counted_qty !== null) {
                                                $totalCounted += $stocktakeProduct->counted_qty;
                                            }
                                            $totalDifference += $stocktakeProduct->difference;
                                            $totalCost += $stocktakeProduct->cost;
                                        @endphp
                                        <tr class="product-row" 
                                            data-counted="{{ $stocktakeProduct->counted_qty !== null ? '1' : '0' }}"
                                            data-difference="{{ $stocktakeProduct->difference }}"
                                            data-barcode="{{ $stocktakeProduct->product->barcode ?? '' }}">
                                            <td>
                                                <strong>{{ $stocktakeProduct->product->name }}</strong>
                                                @if($stocktakeProduct->product->barcode)
                                                    <br><small class="text-muted">Barcode: {{ $stocktakeProduct->product->barcode }}</small>
                                                @endif
                                                @if($stocktakeProduct->product->primarySku)
                                                    <br><small class="text-muted">SKU: {{ $stocktakeProduct->product->primarySku->sku }}</small>
                                                @endif
                                                <br><a href="{{ route('center_user.products.create', ['id' => $stocktakeProduct->product_id]) }}" target="_blank" class="text-primary small">View product</a>
                                            </td>
                                            <td>
                                                {{ $stocktakeProduct->countedBy->name ?? '-' }}
                                            </td>
                                            <td>{{ $stocktakeProduct->expected_qty }}</td>
                                            <td>{{ $stocktakeProduct->counted_qty ?? '-' }}</td>
                                            <td>
                                                @if($stocktakeProduct->counted_qty !== null)
                                                    @if($stocktakeProduct->difference > 0)
                                                        <span class="text-success">+{{ $stocktakeProduct->difference }}</span>
                                                    @elseif($stocktakeProduct->difference < 0)
                                                        <span class="text-danger">{{ $stocktakeProduct->difference }}</span>
                                                    @else
                                                        <span>{{ $stocktakeProduct->difference }}</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($stocktakeProduct->counted_qty !== null && $stocktakeProduct->cost != 0)
                                                    @if($stocktakeProduct->cost > 0)
                                                        <span class="text-success">+{{ get_currency() }} {{ number_format($stocktakeProduct->cost, 2) }}</span>
                                                    @else
                                                        <span class="text-danger">{{ get_currency() }} {{ number_format($stocktakeProduct->cost, 2) }}</span>
                                                    @endif
                                                @else
                                                    {{ get_currency() }} 0.00
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-info fw-bold">
                                        <td>Total</td>
                                        <td>-</td>
                                        <td>{{ $totalExpected }}</td>
                                        <td>{{ $totalCounted }}</td>
                                        <td>
                                            @if($totalDifference > 0)
                                                <span class="text-success">+{{ $totalDifference }}</span>
                                            @elseif($totalDifference < 0)
                                                <span class="text-danger">{{ $totalDifference }}</span>
                                            @else
                                                <span>{{ $totalDifference }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($totalCost > 0)
                                                <span class="text-success">+{{ get_currency() }} {{ number_format($totalCost, 2) }}</span>
                                            @elseif($totalCost < 0)
                                                <span class="text-danger">{{ get_currency() }} {{ number_format($totalCost, 2) }}</span>
                                            @else
                                                <span>{{ get_currency() }} {{ number_format($totalCost, 2) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        @if($stocktake->status == 'completed')
                            <div class="mt-4">
                                <h5>Review Stocktake</h5>
                                <form id="review-form" action="{{ route('center_user.stocktakes.review', $stocktake->id) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Notes (Optional)</label>
                                        <textarea class="form-control" name="notes" rows="3" 
                                            placeholder="Add any notes about this stocktake">{{ $stocktake->notes }}</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Review and Update Stock</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        $(document).ready(function() {
            let currentFilter = 'counted';

            // Filter buttons
            $('.filter-btn').on('click', function() {
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                currentFilter = $(this).data('filter');
                filterProducts();
            });

            function filterProducts() {
                $('.product-row').each(function() {
                    const isCounted = $(this).data('counted') == '1';
                    const difference = parseInt($(this).data('difference')) || 0;
                    let show = false;

                    if (currentFilter == 'counted' && isCounted) {
                        show = true;
                    } else if (currentFilter == 'unmatched' && isCounted && difference != 0) {
                        show = true;
                    } else if (currentFilter == 'matched' && isCounted && difference == 0) {
                        show = true;
                    } else if (currentFilter == 'excluded') {
                        show = false; // Excluded logic can be added later
                    }

                    $(this).toggle(show);
                });
            }

            // Search functionality
            $('#product-search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                $('.product-row').each(function() {
                    const productName = $(this).find('td:first').text().toLowerCase();
                    const barcode = $(this).data('barcode').toLowerCase();
                    
                    const matches = productName.includes(searchTerm) || barcode.includes(searchTerm);
                    
                    if (matches) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Review form submission
            $('#review-form').on('submit', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to review this stocktake and update stock quantities?')) {
                    this.submit();
                }
            });
        });
    </script>
@endsection

