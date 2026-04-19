<!-- Products Tab - Match BuyProduct Structure -->
<div class="tab-pane fade" id="products" role="tabpanel">
    <form class="pt-0" id="productForm">
        <div class="row">
            <div class="col-md-12 mb-2">
                <div class="mb-1">
                    <label for="product-products" class="form-label">{{ __('locale.products') }}</label>
                    <select class="select2 form-control" name="products[]" id="product-products" multiple>
                        <option value="">{{ __('field.select_products') }}</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-12 mb-2">
                <div class="mb-1">
                    <label for="product-discount" class="form-label">{{ __('field.discount_codes') }}</label>
                    <select name="discount" id="product-discount" class="form-control">
                        <option value="">{{ __('field.select_discount') }}</option>
                        @for ($i = 1; $i <= 15; $i++)
                            <option value="{{ $i }}">{{ $i . '%' }}</option>
                        @endfor
                    </select>
                </div>
            </div>
         
           
            <div class="col-md-12 mb-2">
                <div class="mb-1">
                    <label for="product-worker" class="form-label">{{ __('field.worker') }}</label>
                    <select class="select2 form-control" name="worker_id" id="product-worker">
                        <option value="">{{ __('field.select_worker') }}</option>
                        @foreach ($workers as $worker)
                            <option value="{{ $worker->id }}">{{ $worker->name }} - {{ $worker->phone }} {{ $worker->is_center_user ? '('. ($centerUser->name ?? '') .' - reception)' : '' }} </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-12 mb-2" style="display: none;" id="product-commission-div">
                <div class="mb-1">
                    <label for="product-commission" class="form-label">{{ __('field.commission') }} <span class="text-danger">*</span></label>
                    <select class="form-control" name="commission" id="product-commission" required>
                        <option value="">{{ __('field.select_commission') }}</option>
                        @for ($i = 1; $i <= 100; $i++)
                            <option value="{{ $i }}">{{ $i }}%</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-12 mb-2">
                <div class="mb-1">
                    <label for="product-payment_type" class="form-label">{{ __('field.payment_method') }} <span class="text-danger">*</span></label>
                    <select name="payment_type" id="product-payment_type" class="form-control" required>
                        <option value="">{{ __('field.select_payment_method') }}</option>
                        @foreach($productPaymentMethods as $paymentMethod)
                            <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
            </div>
            <div class="col-md-12">
                <button type="button" class="btn btn-primary" id="addProductBtn">
                    <i class="ti ti-shopping-cart me-1"></i>
                    {{ __('field.add_to_cart') }}
                </button>
            </div>
        </div>
    </form>
</div>
