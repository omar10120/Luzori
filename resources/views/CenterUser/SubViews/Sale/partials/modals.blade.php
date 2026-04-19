<!-- Select Customer Modal -->
<div class="modal fade" id="selectCustomerModal" tabindex="-1" aria-labelledby="selectCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="selectCustomerModalLabel">{{ __('field.select_customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                 
                <label for="select-customer-dropdown" class="form-label">{{ __('field.search_customer') }}</label>
                    <select class="select2 form-control" id="select-customer-dropdown" style="width: 100%;">
                        <option value="">{{ __('field.search_by_name_phone_or_email') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" 
                                data-name="{{ $user->name }}"
                                data-email="{{ $user->email ?? '' }}"
                                data-phone="{{ $user->phone ?? $user->full_phone ?? '' }}"
                                data-image="{{ $user->image ?? '' }}"
                                data-branch-id="{{ $user->branch_id ?? '' }}">
                                {{ $user->name }} 
                                @if($user->phone || $user->full_phone)
                                    - {{ $user->phone ?? $user->full_phone }}
                                @endif
                                @if($user->email)
                                    - {{ $user->email }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div id="selected-customer-info" style="display: none;" class="mt-3 p-3 border rounded bg-light">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" id="selected-customer-avatar">
                            <img id="selected-customer-img" src="" alt="" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0" id="selected-customer-name"></h6>
                            <small class="text-muted d-block" id="selected-customer-email"></small>
                            <small class="text-muted d-block" id="selected-customer-phone"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="confirm-select-customer" disabled>
                    <i class="ti ti-check me-1"></i>
                    {{ __('field.select') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Quick Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">{{ __('general.add') }} {{ __('field.customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quick-add-customer-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_customer_first_name" class="form-label">
                                {{ __('field.first_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="quick_customer_first_name" class="form-control" name="first_name" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_customer_last_name" class="form-label">
                                {{ __('field.last_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="quick_customer_last_name" class="form-control" name="last_name" required />
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_customer_email" class="form-label">
                                {{ __('field.email') }}
                            </label>
                            <input type="email" id="quick_customer_email" class="form-control" name="email" />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-2 mb-3">
                            @include('Admin.Components.country_code', ['item' => null])
                        </div>
                        <div class="col-md-2 mb-3" id="quick_customer_phone_prefix_container" style="display: none;">
                            <label for="quick_customer_phone_prefix" class="form-label">
                                Prefix
                            </label>
                            <select class="form-control" name="phone_prefix" id="quick_customer_phone_prefix">
                                @php
                                    $prefixes = ['50', '52', '54', '55', '56', '58'];
                                @endphp
                                @foreach ($prefixes as $prefix)
                                    <option value="{{ $prefix }}">{{ $prefix }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3" id="quick_customer_phone_input_container">
                            <label for="quick_customer_phone" class="form-label">
                                {{ __('field.mobile_number') }} <span class="text-danger">*</span>
                            </label>
                            <input type="tel" maxlength="7" id="quick_customer_phone" class="form-control" name="phone" required pattern="[0-9]{7}" title="{{ __('field.phone_must_be_7_digits') }}" />
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="quick_customer_image" class="form-label">
                            {{ __('field.image') }}
                        </label>
                        <input type="file" id="quick_customer_image" class="form-control" name="image" accept="image/*" />
                        <div class="invalid-feedback"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-quick-customer-btn">
                    <i class="ti ti-check me-1"></i>
                    {{ __('general.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCustomerModalLabel">{{ __('general.edit') }} {{ __('field.customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-customer-form">
                    @csrf
                    <input type="hidden" id="edit_customer_id" name="id" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_customer_first_name" class="form-label">
                                {{ __('field.first_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="edit_customer_first_name" class="form-control" name="first_name" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_customer_last_name" class="form-label">
                                {{ __('field.last_name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="edit_customer_last_name" class="form-control" name="last_name" required />
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_customer_email" class="form-label">
                                {{ __('field.email') }} <span class="text-danger">*</span>
                            </label>
                            <input type="email" id="edit_customer_email" class="form-control" name="email" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-2 mb-3">
                            @include('Admin.Components.country_code', ['item' => null, 'id_prefix' => 'edit_customer_'])
                        </div>
                        <div class="col-md-2 mb-3" id="edit_customer_phone_prefix_container" style="display: none;">
                            <label for="edit_customer_phone_prefix" class="form-label">
                                Prefix
                            </label>
                            <select class="form-control" name="phone_prefix" id="edit_customer_phone_prefix">
                                @php
                                    $prefixes = ['50', '52', '54', '55', '56', '58'];
                                @endphp
                                @foreach ($prefixes as $prefix)
                                    <option value="{{ $prefix }}">{{ $prefix }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4 mb-3" id="edit_customer_phone_input_container">
                            <label for="edit_customer_phone" class="form-label">
                                {{ __('field.mobile_number') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" maxlength="7" id="edit_customer_phone" class="form-control" name="phone" required />
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_customer_image" class="form-label">
                            {{ __('field.image') }}
                        </label>
                        <input type="file" id="edit_customer_image" class="form-control" name="image" accept="image/*" />
                        <div class="invalid-feedback"></div>
                        <div id="edit_customer_current_image" class="mt-2" style="display:none;">
                            <img id="edit_customer_image_preview" src="" alt="Current image" style="max-width: 150px; max-height: 150px; border-radius: 8px;">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-edit-customer-btn">
                    <i class="ti ti-check me-1"></i>
                    {{ __('general.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Quick Modal -->
<div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addServiceModalLabel">{{ __('general.add') }} {{ __('locale.services') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quick-add-service-form">
                    @csrf
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <li class="nav-item">
                                <a class="nav-link {{ $loop->first ? 'active' : null }}"
                                    id="quick-service-{{ $locale }}-tab-link" data-bs-toggle="tab"
                                    href="#quick-service-{{ $locale }}-add" aria-controls="quick-service-{{ $locale }}-add"
                                    role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    <i class="menu-icon tf-icons ti ti-flag"></i>
                                    {{ Str::upper($locale) }}</a>
                            </li>
                        @endforeach
                    </ul>
                    
                    <div class="tab-content mb-3">
                        @foreach (Config::get('translatable.locales') as $locale)
                            <div class="tab-pane {{ $loop->first ? 'active' : null }}" id="quick-service-{{ $locale }}-add"
                                aria-labelledby="quick-service-{{ $locale }}-tab-link" role="tabpanel">
                                <div class="mb-3">
                                    <label for="quick_service_name_{{ $locale }}" class="form-label">
                                        {{ __('field.name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="quick_service_name_{{ $locale }}" class="form-control"
                                        name="{{ $locale }}[name]"
                                        placeholder="{{ __('field.name') }}" required />
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="mb-3">
                                    <label for="quick_service_description_{{ $locale }}" class="form-label">
                                        {{ __('field.description') }}
                                    </label>
                                    <textarea id="quick_service_description_{{ $locale }}" class="form-control"
                                        name="{{ $locale }}[description]"
                                        placeholder="{{ __('field.description') }}" rows="3"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_service_rooms_no" class="form-label">
                                {{ __('field.rooms_no') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="quick_service_rooms_no" class="form-control" name="rooms_no"
                                placeholder="{{ __('field.rooms_no') }}" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_service_free_book" class="form-label">
                                {{ __('field.free_book') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="quick_service_free_book" class="form-control" name="free_book"
                                placeholder="{{ __('field.free_book') }}" required />
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quick_service_price" class="form-label">
                                {{ __('field.price') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="quick_service_price" class="form-control" name="price"
                                placeholder="{{ __('field.price') }}" step="0.01" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex gap-4 mt-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="quick_service_is_top" name="is_top" />
                                    <label class="form-check-label" for="quick_service_is_top">{{ __('field.is_top') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="quick_service_has_commission" name="has_commission" />
                                    <label class="form-check-label" for="quick_service_has_commission">{{ __('field.commission') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            @include('CenterUser.Components.category-tree-select', [
                                'categoriesJson' => $categoriesJson,
                                'selectedId' => null,
                                'selectedName' => null,
                                'name' => 'category_id',
                                'label' => __('field.category'),
                                'id' => 'quick_service_category_tree'
                            ])
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            {{ __('field.image') }} <span class="text-danger">*</span>
                        </label>
                        <input type="file" id="quick_service_image" class="form-control" name="image" accept="image/*" required />
                        <div class="invalid-feedback" id="quick-service-image-error"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">  
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-quick-service-btn">
                    <i class="ti ti-check me-1"></i>
                    {{ __('general.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Coupon Quick Modal -->
<div class="modal fade" id="addCouponModal" tabindex="-1" aria-labelledby="addCouponModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCouponModalLabel">{{ __('general.add') }} {{ __('field.new_coupon') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quick-add-coupon-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="quick_coupon_amount" class="form-label">
                                {{ __('field.amount') }} <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="quick_coupon_amount" class="form-control" name="amount" 
                                placeholder="{{ __('field.amount') }}" step="0.01" required />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="quick_coupon_invoiced_amount" class="form-label">
                                {{ __('field.invoiced_amount') }}
                            </label>
                            <input type="number" id="quick_coupon_invoiced_amount" class="form-control" name="invoiced_amount" 
                                placeholder="{{ __('field.invoiced_amount') }}" step="0.01" />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_coupon_start_at" class="form-label">
                                {{ __('field.start_at') }}
                            </label>
                            <input type="date" id="quick_coupon_start_at" class="form-control" name="start_at" />
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="quick_coupon_end_at" class="form-label">
                                {{ __('field.end_at') }}
                            </label>
                            <input type="date" id="quick_coupon_end_at" class="form-control" name="end_at" />
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-quick-coupon-btn">
                    <i class="ti ti-check me-1"></i>
                    {{ __('general.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Wallet User Modal -->
<div class="modal fade" id="addWalletUserModal" tabindex="-1" aria-labelledby="addWalletUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWalletUserModalLabel">{{ __('locale.add_users_to') }} {{ __('locale.wallets') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="add-wallet-user-form">
                    @csrf
                    <input type="hidden" name="wallet_id" id="modal-wallet-id">
                    <div class="row">
                        <div class="col-md-9 mb-3">
                            <div class="mb-1">
                                <label for="modal-wallet-user" class="form-label">{{ __('field.users') }} <span class="text-danger">*</span></label>
                                <select class="select2 form-control" name="user_id" id="modal-wallet-user" required>
                                    <option value="">{{ __('field.select_user') }}</option>
                                    @if($users && $users->count() > 0)
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->phone ?? $user->full_phone ?? '' }})
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="" disabled>{{ __('field.no_users_available') }}</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="mb-1">
                                <label for="modal-wallet-type" class="form-label">{{ __('field.type') }} <span class="text-danger">*</span></label>
                                <select class="form-control" name="wallet_type" id="modal-wallet-type" required>
                                    <option value="">{{ __('field.select_type') }}</option>
                                    @foreach($walletPaymentMethods as $paymentMethod)
                                        <option value="{{ $paymentMethod->name }}">{{ $paymentMethod->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-9 mb-3">
                            <div class="mb-1">
                                <label for="modal-wallet-worker" class="form-label">{{ __('locale.workers') }}</label>
                                <select class="form-control" name="worker_id" id="modal-wallet-worker">
                                    <option value="">{{ __('field.select_worker') }}</option>
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}">{{ $worker->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3" style="display: none;" id="modal-wallet-commission-div">
                            <div class="mb-1">
                                <label for="modal-wallet-commission" class="form-label">{{ __('field.commission') }} <span class="text-danger">*</span></label>
                                <select class="form-control" name="commission" id="modal-wallet-commission" required>
                                    <option value="">{{ __('field.select_commission') }}</option>
                                    @for ($i = 1; $i <= 100; $i++)
                                        <option value="{{ $i }}">{{ $i }}%</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="save-wallet-user-btn">
                    <i class="ti ti-check me-1"></i>
                    {{ __('general.save') }}
                </button>
            </div>
        </div>
    </div>
</div>
