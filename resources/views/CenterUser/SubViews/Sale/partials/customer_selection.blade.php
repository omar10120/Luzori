<!-- Customer Selection Card -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('field.customer') }} <span class="text-danger">*</span></h5>
            </div>
            <div class="card-body">
                <div id="selected-customer-display" style="{{ empty($cart['client_id']) ? 'display: none;' : '' }}">
                    @if(!empty($cart['client_id']))
                        @php
                            $selectedUser = $users->firstWhere('id', $cart['client_id']);
                        @endphp
                        @if($selectedUser)
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-lg me-3">
                                    <img src="{{ $selectedUser->image ?? asset('assets/img/avatars/1.png') }}" 
                                         alt="{{ $selectedUser->name }}" 
                                         class="rounded-circle" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <div>
                                    <h5 class="mb-0">{{ $selectedUser->name }}</h5>
                                    <small class="text-muted d-block">{{ $selectedUser->email ?? $selectedUser->full_phone }}</small>
                                </div>
                            </div>
                        @endif
                    @endif
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <button type="button" class="btn btn-outline-primary w-100 w-sm-auto" id="selectCustomerBtn" data-bs-toggle="modal" data-bs-target="#selectCustomerModal">
                            <i class="ti ti-user me-1"></i>
                            <span class="d-none d-sm-inline">{{ empty($cart['client_id']) ? __('field.select_customer') : __('field.change_customer') }}</span>
                            <span class="d-inline d-sm-none">{{ empty($cart['client_id']) ? __('field.select') : __('field.change') }}</span>
                        </button>
                        <button type="button" class="btn btn-primary w-100 w-sm-auto" id="addCustomerBtn" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="ti ti-plus me-1"></i>
                            <span class="d-none d-sm-inline">{{ __('general.add') }} {{ __('field.customer') }}</span>
                            <span class="d-inline d-sm-none">{{ __('general.add') }}</span>
                        </button>
                        @if(!empty($cart['client_id']))
                            <button type="button" class="btn btn-info w-100 w-sm-auto" id="editCustomerBtn" data-bs-toggle="modal" data-bs-target="#editCustomerModal">
                                <i class="ti ti-edit me-1"></i>
                                <span class="d-none d-sm-inline">{{ __('general.edit') }}</span>
                                <span class="d-inline d-sm-none">{{ __('general.edit') }}</span>
                            </button>
                            <button type="button" class="btn btn-outline-danger w-100 w-sm-auto" id="removeCustomerBtn">
                                <i class="ti ti-x me-1"></i>
                                <span class="d-none d-sm-inline">{{ __('field.remove') }}</span>
                                <span class="d-inline d-sm-none">{{ __('field.remove') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
                <div id="no-customer-display" class="text-center py-3" style="{{ !empty($cart['client_id']) ? 'display: none;' : '' }}">
                    <i class="ti ti-user-off" style="font-size: 3rem; color: #ff6b6b;"></i>
                    <p class="text-danger mb-0 mt-2"><strong>{{ __('field.customer_required') }}</strong></p>
                    <p class="text-muted mb-0">{{ __('field.please_select_or_add_customer') }}</p>
                    <div class="d-flex flex-column flex-sm-row gap-2 mt-3 justify-content-center">
                        <button type="button" class="btn btn-outline-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#selectCustomerModal">
                            <i class="ti ti-user me-1"></i>
                            {{ __('field.select_customer') }}
                        </button>
                        <button type="button" class="btn btn-primary w-100 w-sm-auto" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            <i class="ti ti-plus me-1"></i>
                            {{ __('general.add') }} {{ __('field.customer') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
