@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit" enctype="multipart/form-data">
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
                            <!-- Left Column: Basic Info -->
                            <div class="col-md-8">
                                <!-- Basic Info Section -->
                                <h5 class="mb-3">Basic info</h5>
                        @include('CenterUser.Components.languages-tabs')
                        
                                <div class="tab-content mb-4">
                            @foreach (Config::get('translatable.locales') as $locale)
                                <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="{{ $locale }}-add"
                                    aria-labelledby="{{ $locale }}-tab-add" role="tabpanel">
                                    <div class="row">
                                                <div class="col-md-12 mb-3">
                                            <div class="mb-1">
                                                        <label for="name_{{ $locale }}" class="form-label">{{ __('field.name') }}  <span class="text-danger">*</span></label>
                                                        <small class="text-muted">{{__('general.enter_the_name_of_the_product')}}</small>
                                                <input type="text" id="name_{{ $locale }}" class="form-control"
                                                    name="{{ $locale }}[name]"
                                                    placeholder="{{ __('field.name') }}"
                                                            value="{{ $item ? $item->translate($locale)->name : '' }}" required />
                                                    </div>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <div class="mb-1">
                                                        <label for="text_{{ $locale }}" class="form-label">Product description  <span class="text-danger">*</span></label>
                                                        <small class="text-muted">{{__('general.enter_the_description_of_the_product')}}</small>
                                                        <textarea id="text_{{ $locale }}" class="form-control" rows="4"
                                                            name="{{ $locale }}[text]"
                                                            placeholder="Product description"
                                                            maxlength="1000">{{ $item ? $item->translate($locale)->text : '' }}</textarea>
                                                        <small class="text-muted"><span id="text_counter_{{ $locale }}">0</span>/1000</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label for="barcode" class="form-label">{{ __('field.barcode') }} (Optional)  <span class="text-danger">*</span></label>
                                            <small class="text-muted">{{__('general.enter_the_barcode_of_the_product')}}</small>
                                            <input type="text" id="barcode" class="form-control" name="barcode"
                                                placeholder="UPC, EAN, GTIN"
                                                value="{{ $item ? $item->barcode : '' }}" />
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                            <label for="brand_id" class="form-label">{{ __('field.brand') }}  <span class="text-danger">*</span></label>
                                            <small class="text-muted">{{__('general.select_a_brand_from_the_list')}}</small>
                                            <select class="form-control select2" name="brand_id" id="brand_id" data-select="true">
                                                <option value="">{{ __('general.choose') }} {{ __('field.brand') }}</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" {{ $item && $item->brand_id == $brand->id ? 'selected' : '' }}>
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-brand-btn" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                                                <i class="ti ti-plus"></i> Add new brand
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label for="measure_unit" class="form-label">Measure  <span class="text-danger">*</span></label>
                                            <small class="text-muted">{{__('general.select_a_measure_unit_from_the_list')}}</small>
                                            <select class="form-control select2" name="measure_unit" id="measure_unit" data-select="true">
                                                <option value="">{{ __('general.choose') }} measure unit</option>
                                                <option value="ml" {{ $item && $item->measure_unit == 'ml' ? 'selected' : '' }}>Milliliters (ml)</option>
                                                <option value="l" {{ $item && $item->measure_unit == 'l' ? 'selected' : '' }}>Liters (l)</option>
                                                <option value="fl_oz" {{ $item && $item->measure_unit == 'fl_oz' ? 'selected' : '' }}>Fluid ounces (Fl. oz.)</option>
                                                <option value="g" {{ $item && $item->measure_unit == 'g' ? 'selected' : '' }}>Grams (g)</option>
                                                <option value="kg" {{ $item && $item->measure_unit == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                                                <option value="gal" {{ $item && $item->measure_unit == 'gal' ? 'selected' : '' }}>Gallons (gal)</option>
                                                <option value="oz" {{ $item && $item->measure_unit == 'oz' ? 'selected' : '' }}>Ounces (oz)</option>
                                                <option value="lb" {{ $item && $item->measure_unit == 'lb' ? 'selected' : '' }}>Pounds (lb)</option>
                                                <option value="cm" {{ $item && $item->measure_unit == 'cm' ? 'selected' : '' }}>Centimeters (cm)</option>
                                                <option value="ft" {{ $item && $item->measure_unit == 'ft' ? 'selected' : '' }}>Feet (ft)</option>
                                                <option value="in" {{ $item && $item->measure_unit == 'in' ? 'selected' : '' }}>Inches (in)</option>
                                                <option value="whole" {{ $item && $item->measure_unit == 'whole' ? 'selected' : '' }}>A whole product</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label for="measure_amount" class="form-label">Amount  <span class="text-danger">*</span></label>
                                            <small class="text-muted">{{__('general.enter_the_amount_of_the_product')}}</small>
                                            <input type="number" min="0.1" type="number"id="measure_amount" class="form-control" name="measure_amount"
                                                placeholder="{{ $item && $item->measure_unit ? $item->measure_unit . ' 0.00' : '0.00' }}" step="0.01"
                                                value="{{ $item ? $item->measure_amount : '' }}" />
                                        </div>
                                    </div>
                                            </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label for="short_description" class="form-label">Short description  <span class="text-danger">*</span></label>
                                            <small class="text-muted">{{__('general.enter_the_short_description_of_the_product')}}</small>
                                            <textarea id="short_description" class="form-control" rows="2"
                                                name="short_description"
                                                placeholder="Short description"
                                                maxlength="100">{{ $item ? $item->short_description : '' }}</textarea>
                                            <small class="text-muted"><span id="short_desc_counter">0</span>/100</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <label for="category_id" class="form-label">{{ __('field.category') }}  <span class="text-danger">*</span></label>
                                            <small class="text-muted">{{__('general.select_a_category_from_the_list')}}</small>
                                            <select class="form-control select2" name="category_id" id="category_id" data-select="true">
                                                <option value="">{{ __('general.choose') }} {{ __('field.category') }}</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ $item && $item->category_id == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                            @endforeach
                                            </select>
                                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-category-btn" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                                <i class="ti ti-plus"></i> Add new category
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Product Photos -->
                            <div class="col-md-4">
                                <!-- Product Photos Section -->
                                <h5 class="mb-3">Product photos</h5>
                                <p class="text-muted mb-3">Drag and drop a photo to change the order.</p>
                                <div class="mb-4">
                                    @include('CenterUser.Components.multi-image', [
                                        'item' => $item,
                                        'name' => 'image',
                                        'model' => 'Product',
                                    ])
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Section -->
                        <h5 class="mb-3 mt-4">Pricing</h5>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="supply_price" class="form-label">{{ __('field.supply_price') }}  <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_supply_price_of_the_product')}}</small>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ trim(get_currency()) }}</span>
                                        <input type="number" id="supply_price" class="form-control" name="supply_price"
                                            placeholder="0.00" step="0.01" required
                                            value="{{ $item ? $item->supply_price : '' }}" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <p class="mb-2">Allow sales of this product at checkout.</p>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allow_retail_sales" name="allow_retail_sales" value="1"
                                            {{ $item && $item->allow_retail_sales ? 'checked' : '' }}>
                                        <label class="form-check-label" for="allow_retail_sales">Enable retail sales</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="retail-pricing-fields" style="{{ $item && $item->allow_retail_sales ? '' : 'display:none;' }}">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label for="retail_price" class="form-label">{{ __('field.retail_price') }}  <span class="text-danger">*</span></label>
                                        <small class="text-muted">{{__('general.enter_the_retail_price_of_the_product')}}</small>
                                        <div class="input-group">
                                            <span class="input-group-text">{{ trim(get_currency()) }}</span>
                                            <input type="number" id="retail_price" class="form-control" name="retail_price"
                                                placeholder="0.00" step="0.01"
                                                value="{{ $item ? $item->retail_price : '' }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="mb-1">
                                        <label for="markup" class="form-label">Markup  <span class="text-danger">*</span></label>
                                        <small class="text-muted">{{__('general.enter_the_markup_of_the_product')}}</small>
                                        <div class="input-group">
                                            <span class="input-group-text">%</span>
                                            <input type="number" id="markup" class="form-control" name="markup"
                                                placeholder="0.00" step="0.01" min="0" max="100"
                                                value="{{ $item ? $item->markup : '' }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventory Section -->
                        <h5 class="mb-3 mt-4">Inventory</h5>
                        <p class="text-muted mb-3">Manage stock levels of this product through Fresha.</p>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.sku') }} (Stock Keeping Unit) (Optional)</label>
                                    <div id="sku-container">
                                        @if($item && $item->skus && $item->skus->count() > 0)
                                            @foreach($item->skus as $index => $sku)
                                                <div class="sku-row mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <a href="#" class="text-primary generate-sku-link" data-sku-index="{{ $index }}">Generate SKU automatically</a>
                                                        @if($index > 0)
                                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-sku-btn" data-sku-index="{{ $index }}">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                    <input type="text" class="form-control sku-input" name="skus[{{ $index }}][sku]"
                                                        placeholder="Enter SKU code" value="{{ $sku->sku }}" required />
                                                    <input type="hidden" name="skus[{{ $index }}][order]" value="{{ $index }}">
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="sku-row mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <a href="#" class="text-primary generate-sku-link" data-sku-index="0">Generate SKU automatically</a>
                                                </div>
                                                <input type="text" class="form-control sku-input" name="skus[0][sku]"
                                                    placeholder="Enter SKU code" />
                                                <input type="hidden" name="skus[0][order]" value="0">
                                            </div>
                                        @endif
                                    </div>
                                    <a href="#" class="text-primary" id="add-sku-link">Add another SKU code</a>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label for="product_supplier_id" class="form-label">Supplier  <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_supplier_from_the_list')}}</small>
                                    <select class="form-control select2" name="product_supplier_ids[]" id="product_supplier_id" data-select="true">
                                        <option value="">{{ __('general.choose') }} supplier</option>
                                        @foreach($productSuppliers as $supplier)
                                            <option value="{{ $supplier->id }}" 
                                                {{ $item && $item->productSuppliers->contains($supplier->id) ? 'selected' : '' }}>
                                                {{ $supplier->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-supplier-btn" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                                        <i class="ti ti-plus"></i> Add new supplier
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Product Branches Section -->
                        <h5 class="mb-3 mt-4">Product Branches & Stock</h5>
                        <p class="text-muted mb-3">Select branches and set stock quantity for each selected branch.</p>
                        
                        <div id="product-branches-container">
                            @if($item && $item->productBranches && $item->productBranches->count() > 0)
                                @foreach($item->productBranches as $index => $productBranch)
                                    <div class="product-branch-row mb-3 border rounded p-3" data-branch-index="{{ $index }}">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Branch  <span class="text-danger">*</span></label>
                                                <small class="text-muted">{{__('general.select_a_branch_from_the_list')}}</small>
                                                <select class="form-control select2 branch-select" 
                                                    name="product_branches[{{ $index }}][branch_id]" 
                                                    data-select="true" required>
                                                    <option value="">{{ __('general.choose') }} {{ __('field.branch') }}</option>
                                                    @foreach($branches as $branch)
                                                        <option value="{{ $branch->id }}" 
                                                            {{ $productBranch->branch_id == $branch->id ? 'selected' : '' }}>
                                                            {{ $branch->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5 mb-3 stock-quantity-wrapper" style="{{ $productBranch->branch_id ? '' : 'display:none;' }}">
                                                <label class="form-label">Stock Quantity  <span class="text-danger">*</span></label>
                                                <small class="text-muted">{{__('general.enter_the_stock_quantity_of_the_product')}}</small>
                                                <input type="number" class="form-control stock-quantity-input" 
                                                    name="product_branches[{{ $index }}][stock_quantity]" 
                                                    placeholder="0" min="0" 
                                                    value="{{ $productBranch->stock_quantity ?? 0 }}" />
                                            </div>
                                            <div class="col-md-1 mb-3">
                                                <label class="form-label">&nbsp;</label>
                                                @if($index > 0)
                                                    <button type="button" class="btn btn-sm btn-danger w-100 remove-branch-btn">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="product-branch-row mb-3 border rounded p-3" data-branch-index="0">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Branch  <span class="text-danger">*</span></label>
                                            <small class="text-muted">{{__('general.select_a_branch_from_the_list')}}</small>
                                            <select class="form-control select2 branch-select" 
                                                name="product_branches[0][branch_id]" 
                                                data-select="true">
                                                <option value="">{{ __('general.choose') }} {{ __('field.branch') }}</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}">
                                                        {{ $branch->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5 mb-3 stock-quantity-wrapper" style="display:none;">
                                            <label class="form-label">Stock Quantity</label>
                                            <input type="number" class="form-control stock-quantity-input" 
                                                name="product_branches[0][stock_quantity]" 
                                                placeholder="0" min="0" 
                                                value="1" />
                                        </div>
                                        <div class="col-md-1 mb-3">
                                            <label class="form-label">&nbsp;</label>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add-branch-btn">
                            <i class="ti ti-plus"></i> Add another branch
                        </button>
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

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBrandModalLabel">Add new brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-brand-form">
                        <div class="mb-3">
                            <label for="brand_name" class="form-label">Brand name</label>
                            <input type="text" class="form-control" id="brand_name" name="name" placeholder="Enter brand name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-brand-btn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add new category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-category-form">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category name</label>
                            <input type="text" class="form-control" id="category_name" name="name" placeholder="Enter category name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-category-btn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Supplier Modal -->
    <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalLabel">Add new supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-supplier-form">
                        <div class="mb-3">
                            <label for="supplier_name" class="form-label">Supplier name</label>
                            <input type="text" class="form-control" id="supplier_name" name="name" placeholder="Enter supplier name" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-supplier-btn">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    @include('CenterUser.Components.multi-image-js')
    @include('CenterUser.Components.submit-form-ajax')
    
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').each(function() {
                $(this).select2({
                    placeholder: $(this).find('option:first').text(),
                    allowClear: true,
                    dropdownParent: $(this).parent()
                });
            });
            
            // Initialize Select2 for branch selects specifically
            $('.branch-select').each(function() {
                if (!$(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2({
                        placeholder: '{{ __('general.choose') }} {{ __('field.branch') }}',
                        allowClear: true,
                        dropdownParent: $(this).parent()
                    });
                }
            });

            // Character counters
            @foreach (Config::get('translatable.locales') as $locale)
                $('#text_{{ $locale }}').on('input', function() {
                    $('#text_counter_{{ $locale }}').text($(this).val().length);
                });
                $('#text_counter_{{ $locale }}').text($('#text_{{ $locale }}').val().length);
            @endforeach

            $('#short_description').on('input', function() {
                $('#short_desc_counter').text($(this).val().length);
            });
            $('#short_desc_counter').text($('#short_description').val().length);

            // Product Branches Management
            let branchIndex = {{ $item && $item->productBranches ? $item->productBranches->count() : 1 }};
            
            // Show/hide stock quantity input when branch is selected
            $(document).on('change', '.branch-select', function() {
                const $row = $(this).closest('.product-branch-row');
                const $stockWrapper = $row.find('.stock-quantity-wrapper');
                const $stockInput = $row.find('.stock-quantity-input');
                
                if ($(this).val() && $(this).val() !== '') {
                    $stockWrapper.show();
                    $stockInput.attr('required', 'required');
                } else {
                    $stockWrapper.hide();
                    $stockInput.removeAttr('required');
                    $stockInput.val('0');
                }
            });
            
            // Initialize stock visibility for existing branches
            $('.branch-select').each(function() {
                if ($(this).val() && $(this).val() !== '') {
                    $(this).closest('.product-branch-row').find('.stock-quantity-wrapper').show();
                }
            });
            
            // Add branch
            $('#add-branch-btn').on('click', function(e) {
                e.preventDefault();
                const branchRow = `
                    <div class="product-branch-row mb-3 border rounded p-3" data-branch-index="${branchIndex}">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Branch</label>
                                <select class="form-control select2 branch-select" 
                                    name="product_branches[${branchIndex}][branch_id]" 
                                    data-select="true">
                                    <option value="">{{ __('general.choose') }} {{ __('field.branch') }}</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 mb-3 stock-quantity-wrapper" style="display:none;">
                                <label class="form-label">Stock Quantity  <span class="text-danger">*</span></label>
                                <small class="text-muted">{{__('general.enter_the_stock_quantity_of_the_product')}}</small>
                                <input type="number" class="form-control stock-quantity-input" 
                                    name="product_branches[${branchIndex}][stock_quantity]" 
                                    placeholder="0" min="0" 
                                    value="0" />
                            </div>
                            <div class="col-md-1 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-sm btn-danger w-100 remove-branch-btn">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                $('#product-branches-container').append(branchRow);
                
                // Initialize Select2 for the new branch select
                $('#product-branches-container .branch-select').last().select2({
                    placeholder: '{{ __('general.choose') }} {{ __('field.branch') }}',
                    allowClear: true,
                    dropdownParent: $('#product-branches-container .branch-select').last().parent()
                });
                
                branchIndex++;
            });
            
            // Remove branch
            $(document).on('click', '.remove-branch-btn', function(e) {
                e.preventDefault();
                $(this).closest('.product-branch-row').remove();
            });

            // Enable retail sales toggle
            function toggleRetailPricingFields() {
                if ($('#allow_retail_sales').is(':checked')) {
                    $('#retail-pricing-fields').show();
                    $('#retail_price, #markup').attr('required', 'required');
                } else {
                    $('#retail-pricing-fields').hide();
                    $('#retail_price, #markup').removeAttr('required');
                    // Clear values when hidden
                    $('#retail_price, #markup').val('');
                }
            }

            $('#allow_retail_sales').on('change', toggleRetailPricingFields);

            // Initialize retail pricing fields visibility on page load
            toggleRetailPricingFields();

            // Update measure amount placeholder based on selected measure unit
            function updateMeasureAmountPlaceholder() {
                const measureUnit = $('#measure_unit').val();
                const measureAmountInput = $('#measure_amount');
                
                // Map measure unit values to display text
                const measureUnitMap = {
                    'ml': 'ml',
                    'l': 'l',
                    'fl_oz': 'Fl. oz.',
                    'g': 'g',
                    'kg': 'kg',
                    'gal': 'gal',
                    'oz': 'oz',
                    'lb': 'lb',
                    'cm': 'cm',
                    'ft': 'ft',
                    'in': 'in',
                    'whole': 'whole product'
                };
                
                if (measureUnit && measureUnitMap[measureUnit]) {
                    measureAmountInput.attr('placeholder', measureUnitMap[measureUnit] + ' 0.00');
                } else {
                    measureAmountInput.attr('placeholder', '0.00');
                }
            }

            // Update placeholder when measure unit changes (works with Select2)
            $('#measure_unit').on('change', function() {
                updateMeasureAmountPlaceholder();
            });
            
            // Also listen for Select2 change event
            $('#measure_unit').on('select2:select', function() {
                updateMeasureAmountPlaceholder();
            });
            
            // Initialize placeholder on page load
            updateMeasureAmountPlaceholder();

            // Generate SKU automatically
            $(document).on('click', '.generate-sku-link', function(e) {
                e.preventDefault();
                const skuRow = $(this).closest('.sku-row');
                const skuInput = skuRow.find('.sku-input');
                const productName = $('#name_ar').val() || $('#name_en').val() || 'PROD';
                const barcode = $('#barcode').val() || '';
                const timestamp = Date.now().toString().slice(-6);
                const skuIndex = $(this).data('sku-index') || skuRow.index();
                
                // Generate SKU: prefer barcode, otherwise use product name + timestamp + index
                let generatedSku = '';
                
                    const namePart = productName.substring(0, 3).replace(/\s+/g, '').toUpperCase();
                    generatedSku = namePart + timestamp + (skuIndex > 0 ? skuIndex : '');
                
                
                skuInput.val(generatedSku);
            });

            // Add another SKU
            let skuIndex = {{ $item && $item->skus ? $item->skus->count() : 1 }};
            $('#add-sku-link').on('click', function(e) {
                e.preventDefault();
                const newSkuRow = `
                    <div class="sku-row mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <a href="#" class="text-primary generate-sku-link" data-sku-index="${skuIndex}">Generate SKU automatically</a>
                            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-sku-btn" data-sku-index="${skuIndex}">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                        <input type="text" class="form-control sku-input" name="skus[${skuIndex}][sku]"
                            placeholder="Enter SKU code" />
                        <input type="hidden" name="skus[${skuIndex}][order]" value="${skuIndex}">
                    </div>
                `;
                $('#sku-container').append(newSkuRow);
                skuIndex++;
            });

            // Remove SKU
            $(document).on('click', '.remove-sku-btn', function(e) {
                e.preventDefault();
                $(this).closest('.sku-row').remove();
            });

            // Auto-calculate markup when supply_price and retail_price change
            $('#supply_price, #retail_price').on('input', function() {
                const supplyPrice = parseFloat($('#supply_price').val()) || 0;
                const retailPrice = parseFloat($('#retail_price').val()) || 0;
                
                if (supplyPrice > 0 && retailPrice > 0) {
                    const markup = ((retailPrice - supplyPrice) / supplyPrice) * 100;
                    $('#markup').val(markup.toFixed(2));
                }
            });

            // Handle supplier field - ensure it submits correctly
            $('#frmSubmit').on('submit', function() {
                const supplierSelect = $('#product_supplier_id');
                // If no supplier selected, remove the name attribute so field isn't sent
                // Backend will handle this by detaching all suppliers
                if (!supplierSelect.val() || supplierSelect.val() === '') {
                    supplierSelect.removeAttr('name');
                }
            });

            // Add Brand functionality
            $('#save-brand-btn').on('click', function() {
                const brandName = $('#brand_name').val().trim();
                const brandInput = $('#brand_name');
                const invalidFeedback = brandInput.next('.invalid-feedback');
                
                if (!brandName) {
                    brandInput.addClass('is-invalid');
                    invalidFeedback.text('Brand name is required');
                    return;
                }

                // Disable button and show loading
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

                $.ajax({
                    url: '{{ route("center_user.products.add-brand") }}',
                    method: 'POST',
                    data: {
                        name: brandName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response, textStatus, xhr) {
                        if (xhr.status === 201 && response.data) {
                            // Add new brand to select dropdown
                            const brandSelect = $('#brand_id');
                            const newOption = new Option(response.data.name, response.data.id, false, true);
                            brandSelect.append(newOption).trigger('change');
                            
                            // Close modal and reset form
                            $('#addBrandModal').modal('hide');
                            $('#add-brand-form')[0].reset();
                            brandInput.removeClass('is-invalid');
                            
                            // Show success message
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'Brand added successfully');
                            } else {
                                alert(response.message || 'Brand added successfully');
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'An error occurred');
                            } else {
                                alert(response.message || 'An error occurred');
                            }
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        const errors = response?.errors;
                        if (errors && errors.name) {
                            brandInput.addClass('is-invalid');
                            invalidFeedback.text(errors.name[0]);
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response?.message || 'An error occurred');
                            } else {
                                alert(response?.message || 'An error occurred');
                            }
                        }
                    },
                    complete: function() {
                        $('#save-brand-btn').prop('disabled', false).html('Save');
                    }
                });
            });

            // Reset form when modal is closed
            $('#addBrandModal').on('hidden.bs.modal', function() {
                $('#add-brand-form')[0].reset();
                $('#brand_name').removeClass('is-invalid');
            });

            // Clear validation on input
            $('#brand_name').on('input', function() {
                $(this).removeClass('is-invalid');
            });

            // Add Category functionality
            $('#save-category-btn').on('click', function() {
                const categoryName = $('#category_name').val().trim();
                const categoryInput = $('#category_name');
                const invalidFeedback = categoryInput.next('.invalid-feedback');
                
                if (!categoryName) {
                    categoryInput.addClass('is-invalid');
                    invalidFeedback.text('Category name is required');
                    return;
                }

                // Disable button and show loading
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

                $.ajax({
                    url: '{{ route("center_user.products.add-category") }}',
                    method: 'POST',
                    data: {
                        name: categoryName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response, textStatus, xhr) {
                        if (xhr.status === 201 && response.data) {
                            // Add new category to select dropdown
                            const categorySelect = $('#category_id');
                            const newOption = new Option(response.data.name, response.data.id, false, true);
                            categorySelect.append(newOption).trigger('change');
                            
                            // Close modal and reset form
                            $('#addCategoryModal').modal('hide');
                            $('#add-category-form')[0].reset();
                            categoryInput.removeClass('is-invalid');
                            
                            // Show success message
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'Category added successfully');
                            } else {
                                alert(response.message || 'Category added successfully');
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'An error occurred');
                            } else {
                                alert(response.message || 'An error occurred');
                            }
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        const errors = response?.errors;
                        if (errors && errors.name) {
                            categoryInput.addClass('is-invalid');
                            invalidFeedback.text(errors.name[0]);
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response?.message || 'An error occurred');
                            } else {
                                alert(response?.message || 'An error occurred');
                            }
                        }
                    },
                    complete: function() {
                        $('#save-category-btn').prop('disabled', false).html('Save');
                    }
                });
            });

            // Reset form when modal is closed
            $('#addCategoryModal').on('hidden.bs.modal', function() {
                $('#add-category-form')[0].reset();
                $('#category_name').removeClass('is-invalid');
            });

            // Clear validation on input
            $('#category_name').on('input', function() {
                $(this).removeClass('is-invalid');
            });

            // Add Supplier functionality
            $('#save-supplier-btn').on('click', function() {
                const supplierName = $('#supplier_name').val().trim();
                const supplierInput = $('#supplier_name');
                const invalidFeedback = supplierInput.next('.invalid-feedback');
                
                if (!supplierName) {
                    supplierInput.addClass('is-invalid');
                    invalidFeedback.text('Supplier name is required');
                    return;
                }

                // Disable button and show loading
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

                $.ajax({
                    url: '{{ route("center_user.products.add-supplier") }}',
                    method: 'POST',
                    data: {
                        name: supplierName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response, textStatus, xhr) {
                        if (xhr.status === 201 && response.data) {
                            // Add new supplier to select dropdown
                            const supplierSelect = $('#product_supplier_id');
                            const newOption = new Option(response.data.name, response.data.id, false, true);
                            supplierSelect.append(newOption).trigger('change');
                            
                            // Close modal and reset form
                            $('#addSupplierModal').modal('hide');
                            $('#add-supplier-form')[0].reset();
                            supplierInput.removeClass('is-invalid');
                            
                            // Show success message
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'Supplier added successfully');
                            } else {
                                alert(response.message || 'Supplier added successfully');
                            }
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response.message || 'An error occurred');
                            } else {
                                alert(response.message || 'An error occurred');
                            }
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        const errors = response?.errors;
                        if (errors && errors.name) {
                            supplierInput.addClass('is-invalid');
                            invalidFeedback.text(errors.name[0]);
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(response?.message || 'An error occurred');
                            } else {
                                alert(response?.message || 'An error occurred');
                            }
                        }
                    },
                    complete: function() {
                        $('#save-supplier-btn').prop('disabled', false).html('Save');
                    }
                });
            });

            // Reset form when modal is closed
            $('#addSupplierModal').on('hidden.bs.modal', function() {
                $('#add-supplier-form')[0].reset();
                $('#supplier_name').removeClass('is-invalid');
            });

            // Clear validation on input
            $('#supplier_name').on('input', function() {
                $(this).removeClass('is-invalid');
            });

            // Auto-translation listeners
            $('#name_en').on('input', function() {
                debouncedTranslate($(this).val(), 'en', 'ar', 'name_ar');
            });
            $('#name_ar').on('input', function() {
                debouncedTranslate($(this).val(), 'ar', 'en', 'name_en');
            });

            $('#text_en').on('input', function() {
                debouncedTranslate($(this).val(), 'en', 'ar', 'text_ar');
            });
            $('#text_ar').on('input', function() {
                debouncedTranslate($(this).val(), 'ar', 'en', 'text_en');
            });
        });
    </script>
@endsection
@include('CenterUser.Components.translation-js')
