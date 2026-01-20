@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container-fluid">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ $stocktake->id }}</h2>
                            <small class="text-muted">{{ $stocktake->name ?? 'Stocktake #' . $stocktake->id }}</small>
                            <div class="mt-2">
                                <i class="ti ti-calendar"></i> Started {{ $stocktake->started_at ? $stocktake->started_at->diffForHumans() : 'just now' }}
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-secondary me-2" id="pause-btn">Pause</button>
                            <a href="{{ route('center_user.stocktakes.index') }}" class="btn btn-outline-secondary me-2">Close</a>
                            <button type="button" class="btn btn-primary" id="review-stock-btn">Review stock</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="quick-scan" />
                                <label class="form-check-label" for="quick-scan">
                                    Quick-scan counting
                                    <i class="ti ti-help-circle" data-bs-toggle="tooltip" title="Enable quick scanning mode"></i>
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <input type="text" class="form-control" id="product-search" 
                                placeholder="Scan a product or start typing" />
                        </div>

                        <div class="btn-group mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary active filter-btn" data-filter="all">
                                All <span class="badge bg-primary">{{ $stocktake->stocktakeProducts->count() }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="uncounted">
                                Uncounted <span class="badge bg-primary">{{ $stocktake->stocktakeProducts->whereNull('counted_qty')->count() }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="counted">
                                Counted <span class="badge bg-primary">{{ $stocktake->stocktakeProducts->whereNotNull('counted_qty')->count() }}</span>
                            </button>
                        </div>

                        <form id="count-form">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-bordered" id="products-table">
                                    <thead>
                                        <tr>
                                            <th>Product name</th>
                                            <th>Expected</th>
                                            <th>Counted</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stocktake->stocktakeProducts as $stocktakeProduct)
                                            <tr class="product-row" 
                                                data-product-id="{{ $stocktakeProduct->product_id }}"
                                                data-counted="{{ $stocktakeProduct->counted_qty !== null ? '1' : '0' }}"
                                                data-barcode="{{ $stocktakeProduct->product->barcode ?? '' }}"
                                                data-sku="{{ $stocktakeProduct->product->primarySku->sku ?? '' }}">
                                                <td>
                                                    <strong>{{ $stocktakeProduct->product->name }}</strong>
                                                    @if($stocktake->branches->count() > 1)
                                                        <br><small class="text-info">Branch: {{ $stocktakeProduct->branch->name }}</small>
                                                    @endif
                                                    @if($stocktakeProduct->product->barcode)
                                                        <br><small class="text-muted">Barcode: {{ $stocktakeProduct->product->barcode }}</small>
                                                    @endif
                                                    @if($stocktakeProduct->product->primarySku)
                                                        <br><small class="text-muted">SKU: {{ $stocktakeProduct->product->primarySku->sku }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>{{ $stocktakeProduct->expected_qty }}</strong>
                                                </td>
                                                <td>
                                                    <input type="number" 
                                                        class="form-control counted-input" 
                                                        name="products[{{ $loop->index }}][counted_qty]"
                                                        data-stocktake-product-id="{{ $stocktakeProduct->id }}"
                                                        value="{{ $stocktakeProduct->counted_qty ?? '' }}"
                                                        min="0"
                                                        placeholder="0" />
                                                    <input type="hidden" 
                                                        name="products[{{ $loop->index }}][stocktake_product_id]"
                                                        value="{{ $stocktakeProduct->id }}" />
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Your last counted products</h5>
                    </div>
                    <div class="card-body" id="last-counted-products">
                        @foreach($stocktake->stocktakeProducts->whereNotNull('counted_qty')->sortByDesc('updated_at')->take(5) as $stocktakeProduct)
                            <div class="mb-3 pb-3 border-bottom">
                                <strong>{{ $stocktakeProduct->product->name }}</strong>
                                @if($stocktakeProduct->product->barcode)
                                    <br><small class="text-muted">Barcode: {{ $stocktakeProduct->product->barcode }}</small>
                                @endif
                                @if($stocktakeProduct->product->primarySku)
                                    <br><small class="text-muted">SKU: {{ $stocktakeProduct->product->primarySku->sku }}</small>
                                @endif
                            </div>
                        @endforeach
                        @if($stocktake->stocktakeProducts->whereNotNull('counted_qty')->count() == 0)
                            <p class="text-muted">No products counted yet</p>
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
            let currentFilter = 'all';

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
                    let show = false;

                    if (currentFilter == 'all') {
                        show = true;
                    } else if (currentFilter == 'uncounted' && !isCounted) {
                        show = true;
                    } else if (currentFilter == 'counted' && isCounted) {
                        show = true;
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
                    const sku = $(this).data('sku').toLowerCase();
                    
                    const matches = productName.includes(searchTerm) || 
                                   barcode.includes(searchTerm) || 
                                   sku.includes(searchTerm);
                    
                    if (matches && (currentFilter == 'all' || 
                        (currentFilter == 'uncounted' && $(this).data('counted') == '0') ||
                        (currentFilter == 'counted' && $(this).data('counted') == '1'))) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Auto-save on input change
            let saveTimeout;
            $('.counted-input').on('input', function() {
                clearTimeout(saveTimeout);
                const $row = $(this).closest('tr');
                saveTimeout = setTimeout(function() {
                    saveCount($row);
                }, 1000);
            });

            function saveCount($row) {
                const $input = $row.find('.counted-input');
                const stocktakeProductId = $input.data('stocktake-product-id');
                const countedQty = $input.val();

                $.ajax({
                    url: '{{ route("center_user.stocktakes.update-count", $stocktake->id) }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        products: [{
                            stocktake_product_id: stocktakeProductId,
                            counted_qty: countedQty || null
                        }]
                    },
                    success: function(response) {
                        $row.data('counted', countedQty ? '1' : '0');
                        updateCounts();
                    }
                });
            }

            function updateCounts() {
                const total = $('.product-row:visible').length;
                const uncounted = $('.product-row:visible').filter(function() {
                    return $(this).data('counted') == '0';
                }).length;
                const counted = $('.product-row:visible').filter(function() {
                    return $(this).data('counted') == '1';
                }).length;

                $('.filter-btn[data-filter="all"] .badge').text(total);
                $('.filter-btn[data-filter="uncounted"] .badge').text(uncounted);
                $('.filter-btn[data-filter="counted"] .badge').text(counted);
            }

            // Review stock button
            $('#review-stock-btn').on('click', function() {
                if (confirm('Are you sure you want to complete this stocktake? You will be able to review and update stock quantities next.')) {
                    window.location.href = '{{ route("center_user.stocktakes.complete", $stocktake->id) }}';
                }
            });
        });
    </script>
@endsection

