@extends('layouts/layoutMaster')

@section('title', $title)

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        <div class="row">
            <form class="pt-0" id="frmSubmit">
                @csrf
                <input type="hidden" name="guard_name" value="center_api">
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="mb-4">
                                <label class="form-label">{{__('field.name')}}</label>
                                <input type="text" class="form-control" name="name" placeholder="{{__('field.name')}}"
                                    value="{{ $item ? $item->name : '' }}" />
                            </div>
                        </div>
                        <div class="row">
                            @foreach ($permissions as $group => $permission)
                                <hr>
                                <h2 class="mb-2">{{ $group }}</h2>
                                @foreach ($permission as $perm)
                                    @php
                                        // Check if this is a commission type permission
                                        $isCommissionType = in_array($perm->name, [
                                            'COMMISSION_BOOKING_PERCENTAGE',
                                            'COMMISSION_BOOKING_FIXED',
                                            'COMMISSION_PRODUCT_PERCENTAGE',
                                            'COMMISSION_PRODUCT_FIXED',
                                            'COMMISSION_COUPON_PERCENTAGE',
                                            'COMMISSION_COUPON_FIXED'
                                        ]);
                                        // Hide commission type permissions from main list (they'll be shown in commission section)
                                        $shouldHide = $isCommissionType;
                                    @endphp
                                    @if(!$shouldHide)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check form-check-custom form-check-solid mb-2">
                                            <input class="form-check-input permission-checkbox" data-perm-name="{{ $perm->name }}" type="checkbox" value="{{ $perm->name }}"
                                                name="permissions[]"
                                                {{ $item ? (in_array($perm->id, array_column($item->permissions->toArray(), 'id')) ? 'checked' : null) : null }} />
                                            <label class="form-check-label" for="flexCheckDefault">
                                                {{ $perm->name_ar }}
                                            </label>
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                                
                                @if($group == 'Commission' && $permission->contains(function($p) { return in_array($p->name, ['COMMISSION_BOOKING_PERCENTAGE', 'COMMISSION_BOOKING_FIXED', 'COMMISSION_PRODUCT_PERCENTAGE', 'COMMISSION_PRODUCT_FIXED', 'COMMISSION_COUPON_PERCENTAGE', 'COMMISSION_COUPON_FIXED']); }))
                                    <!-- Commission Type Settings within Commission section -->
                                    <div class="col-md-12" id="commissionTypeSettingsInline" style="display: none;">
                                        <div class="card mt-3">
                                            <div class="card-body">
                                                <h5 class="mb-3">{{ __('field.commission') }} {{ __('field.types') }}</h5>
                                                
                                                <!-- Booking Commission Type -->
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label mb-2">{{ __('locale.bookings') }} {{ __('field.commission_type') }}</label>
                                                        <div class="d-flex gap-3">
                                                            @php
                                                                $bookingPercentagePerm = $permission->firstWhere('name', 'COMMISSION_BOOKING_PERCENTAGE');
                                                                $bookingFixedPerm = $permission->firstWhere('name', 'COMMISSION_BOOKING_FIXED');
                                                            @endphp
                                                            @if($bookingPercentagePerm)
                                                            <div class="form-check">
                                                                <input class="form-check-input commission-booking-type" type="radio" name="commission_booking_type" 
                                                                    id="commission_booking_percentage" value="COMMISSION_BOOKING_PERCENTAGE"
                                                                    {{ $item && in_array('COMMISSION_BOOKING_PERCENTAGE', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="commission_booking_percentage">
                                                                    {{ __('field.percentage') }}
                                                                </label>
                                                            </div>
                                                            @endif
                                                            @if($bookingFixedPerm)
                                                            <div class="form-check">
                                                                <input class="form-check-input commission-booking-type" type="radio" name="commission_booking_type" 
                                                                    id="commission_booking_fixed" value="COMMISSION_BOOKING_FIXED"
                                                                    {{ $item && in_array('COMMISSION_BOOKING_FIXED', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="commission_booking_fixed">
                                                                    {{ __('field.fixed_value') }}
                                                                </label>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        <!-- Hidden checkboxes for commission type permissions -->
                                                        @if($bookingPercentagePerm)
                                                            <input type="checkbox" class="commission-permission-checkbox" data-perm-name="COMMISSION_BOOKING_PERCENTAGE" 
                                                                name="permissions[]" value="COMMISSION_BOOKING_PERCENTAGE" 
                                                                id="perm_COMMISSION_BOOKING_PERCENTAGE"
                                                                style="display: none;"
                                                                {{ $item && in_array('COMMISSION_BOOKING_PERCENTAGE', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                        @endif
                                                        @if($bookingFixedPerm)
                                                            <input type="checkbox" class="commission-permission-checkbox" data-perm-name="COMMISSION_BOOKING_FIXED" 
                                                                name="permissions[]" value="COMMISSION_BOOKING_FIXED" 
                                                                id="perm_COMMISSION_BOOKING_FIXED"
                                                                style="display: none;"
                                                                {{ $item && in_array('COMMISSION_BOOKING_FIXED', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <!-- Product Commission Type -->
                                                <!-- <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label mb-2">{{ __('field.products') }} {{ __('field.commission_type') }}</label>
                                                        <div class="d-flex gap-3">
                                                            @php
                                                                $productPercentagePerm = $permission->firstWhere('name', 'COMMISSION_PRODUCT_PERCENTAGE');
                                                                $productFixedPerm = $permission->firstWhere('name', 'COMMISSION_PRODUCT_FIXED');
                                                            @endphp
                                                            @if($productPercentagePerm)
                                                            <div class="form-check">
                                                                <input class="form-check-input commission-product-type" type="radio" name="commission_product_type" 
                                                                    id="commission_product_percentage" value="COMMISSION_PRODUCT_PERCENTAGE"
                                                                    {{ $item && in_array('COMMISSION_PRODUCT_PERCENTAGE', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="commission_product_percentage">
                                                                    {{ __('field.percentage') }}
                                                                </label>
                                                            </div>
                                                            @endif
                                                            @if($productFixedPerm)
                                                            <div class="form-check">
                                                                <input class="form-check-input commission-product-type" type="radio" name="commission_product_type" 
                                                                    id="commission_product_fixed" value="COMMISSION_PRODUCT_FIXED"
                                                                    {{ $item && in_array('COMMISSION_PRODUCT_FIXED', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="commission_product_fixed">
                                                                    {{ __('field.fixed_value') }}
                                                                </label>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    
                                                        @if($productPercentagePerm)
                                                            <input type="checkbox" class="commission-permission-checkbox" data-perm-name="COMMISSION_PRODUCT_PERCENTAGE" 
                                                                name="permissions[]" value="COMMISSION_PRODUCT_PERCENTAGE" 
                                                                id="perm_COMMISSION_PRODUCT_PERCENTAGE"
                                                                style="display: none;"
                                                                {{ $item && in_array('COMMISSION_PRODUCT_PERCENTAGE', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                        @endif
                                                        @if($productFixedPerm)
                                                            <input type="checkbox" class="commission-permission-checkbox" data-perm-name="COMMISSION_PRODUCT_FIXED" 
                                                                name="permissions[]" value="COMMISSION_PRODUCT_FIXED" 
                                                                id="perm_COMMISSION_PRODUCT_FIXED"
                                                                style="display: none;"
                                                                {{ $item && in_array('COMMISSION_PRODUCT_FIXED', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                        @endif
                                                    </div>
                                                </div> -->
                                                
                                                <!-- Coupon Commission Type -->
                                                <!-- <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label mb-2">{{ __('field.coupon') }} {{ __('field.commission_type') }}</label>
                                                        <div class="d-flex gap-3">
                                                            @php
                                                                $couponPercentagePerm = $permission->firstWhere('name', 'COMMISSION_COUPON_PERCENTAGE');
                                                                $couponFixedPerm = $permission->firstWhere('name', 'COMMISSION_COUPON_FIXED');
                                                            @endphp
                                                            @if($couponPercentagePerm)
                                                            <div class="form-check">
                                                                <input class="form-check-input commission-coupon-type" type="radio" name="commission_coupon_type" 
                                                                    id="commission_coupon_percentage" value="COMMISSION_COUPON_PERCENTAGE"
                                                                    {{ $item && in_array('COMMISSION_COUPON_PERCENTAGE', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="commission_coupon_percentage">
                                                                    {{ __('field.percentage') }}
                                                                </label>
                                                            </div>
                                                            @endif
                                                            @if($couponFixedPerm)
                                                            <div class="form-check">
                                                                <input class="form-check-input commission-coupon-type" type="radio" name="commission_coupon_type" 
                                                                    id="commission_coupon_fixed" value="COMMISSION_COUPON_FIXED"
                                                                    {{ $item && in_array('COMMISSION_COUPON_FIXED', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="commission_coupon_fixed">
                                                                    {{ __('field.fixed_value') }}
                                                                </label>
                                                            </div>
                                                            @endif
                                                        </div>
                                                        
                                                        @if($couponPercentagePerm)
                                                            <input type="checkbox" class="commission-permission-checkbox" data-perm-name="COMMISSION_COUPON_PERCENTAGE" 
                                                                name="permissions[]" value="COMMISSION_COUPON_PERCENTAGE" 
                                                                id="perm_COMMISSION_COUPON_PERCENTAGE"
                                                                style="display: none;"
                                                                {{ $item && in_array('COMMISSION_COUPON_PERCENTAGE', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                        @endif
                                                        @if($couponFixedPerm)
                                                            <input type="checkbox" class="commission-permission-checkbox" data-perm-name="COMMISSION_COUPON_FIXED" 
                                                                name="permissions[]" value="COMMISSION_COUPON_FIXED" 
                                                                id="perm_COMMISSION_COUPON_FIXED"
                                                                style="display: none;"
                                                                {{ $item && in_array('COMMISSION_COUPON_FIXED', array_column($item->permissions->toArray(), 'name')) ? 'checked' : '' }}>
                                                        @endif
                                                    </div>
                                                </div> -->
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
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
@endsection

@section('vendor-script')
@endsection

@section('page-script')
    @include('CenterUser.Components.submit-form-ajax')
    <script>
        (function() {
            var pendingCheckboxEl = null;
            var savedSuccessfully = false;

            function openSenderEmailModal(prefillEmail, sourceCheckbox) {
                pendingCheckboxEl = sourceCheckbox || null;
                savedSuccessfully = false;
                var existing = document.getElementById('senderEmailModal');
                if (existing) existing.remove();
                var wrapper = document.createElement('div');
                wrapper.innerHTML = `
<div class="modal fade" id="senderEmailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('admin.email') ?? 'Email' }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">{{ __('admin.sender_email') ?? 'Sender Email' }}</label>
          <input type="email" class="form-control" id="senderEmailInput" placeholder="name@example.com" value="${prefillEmail || ''}">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
        <button type="button" id="saveSenderEmailBtn" class="btn btn-primary">{{ __('general.save') }}</button>
      </div>
    </div>
  </div>
</div>`;
                document.body.appendChild(wrapper);
                var modalEl = document.getElementById('senderEmailModal');
                var modal = new bootstrap.Modal(modalEl);
                modalEl.addEventListener('hidden.bs.modal', function () {
                    if (!savedSuccessfully && pendingCheckboxEl) {
                        pendingCheckboxEl.checked = false;
                    }
                });
                modal.show();
                document.getElementById('saveSenderEmailBtn').addEventListener('click', function() {
                    var email = document.getElementById('senderEmailInput').value.trim();
                    var valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                    if (!valid) {
                        try { window.toastr ? toastr.error('{{ __('validation.email') ?? 'Invalid email' }}') : alert('{{ __('validation.email') ?? 'Invalid email' }}'); } catch(e) { alert('Invalid email'); }
                        return;
                    }
                    fetch("{{ route('center_user.infos.updateOrCreate', ['id' => 1]) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ id: 1, email: email, email_only: true })
                    }).then(function(r){ if(!r.ok) throw new Error('failed'); return r.json(); }).then(function(){
                        savedSuccessfully = true;
                        modal.hide();
                        try {
                            window.toastr ? toastr.success("{{ __('admin.operation_done_successfully') }}") : alert("{{ __('admin.operation_done_successfully') }}");
                        } catch(e) { alert("{{ __('admin.operation_done_successfully') }}"); }
                    }).catch(function(){
                        if (pendingCheckboxEl) pendingCheckboxEl.checked = false;
                        alert('{{ __('admin.an_error_occurred') }}');
                    });
                });
            }

            // Prefetch current email on demand
            function fetchCurrentEmail(cb) {
                fetch("{{ route('center_user.infos.index') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(){ cb(''); }) // fallback: unknown without API; modal will show empty
                    .catch(function(){ cb(''); });
            }

            document.querySelectorAll('.permission-checkbox').forEach(function(el){
                el.addEventListener('change', function(){
                    var perm = el.getAttribute('data-perm-name');
                    if (perm === 'VIEW_TWO_FACTOR_AUTH' && el.checked) {
                        fetchCurrentEmail(function(email){ openSenderEmailModal(email, el); });
                    }
                    // Show/hide commission type settings based on VIEW_COMMISSION permission
                    if (perm === 'VIEW_COMMISSION') {
                        var commissionSettings = document.getElementById('commissionTypeSettingsInline');
                        if (commissionSettings) {
                            commissionSettings.style.display = el.checked ? 'block' : 'none';
                            // Uncheck all commission type permissions if VIEW_COMMISSION is unchecked
                            if (!el.checked) {
                                document.querySelectorAll('.commission-booking-type, .commission-product-type, .commission-coupon-type').forEach(function(radio) {
                                    radio.checked = false;
                                    // Uncheck hidden permission checkboxes
                                    var permissionName = radio.value;
                                    var permissionCheckbox = document.getElementById('perm_' + permissionName);
                                    if (permissionCheckbox) {
                                        permissionCheckbox.checked = false;
                                    }
                                });
                            }
                        }
                    }
                });
                
                // Handle commission type radio buttons - sync with hidden permission checkboxes
                document.querySelectorAll('.commission-booking-type, .commission-product-type, .commission-coupon-type').forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        var permissionName = this.value;
                        var isChecked = this.checked;
                        
                        if (isChecked) {
                            // Uncheck all other radios in the same group
                            var allRadios = document.querySelectorAll('[name="' + this.name + '"]');
                            allRadios.forEach(function(r) {
                                if (r !== radio) {
                                    r.checked = false;
                                    // Uncheck the corresponding hidden permission checkbox
                                    var otherPermissionName = r.value;
                                    var otherCheckbox = document.getElementById('perm_' + otherPermissionName);
                                    if (otherCheckbox) {
                                        otherCheckbox.checked = false;
                                    }
                                }
                            });
                            
                            // Check the corresponding hidden permission checkbox
                            var permissionCheckbox = document.getElementById('perm_' + permissionName);
                            if (permissionCheckbox) {
                                permissionCheckbox.checked = true;
                            }
                        } else {
                            // Uncheck the corresponding hidden permission checkbox
                            var permissionCheckbox = document.getElementById('perm_' + permissionName);
                            if (permissionCheckbox) {
                                permissionCheckbox.checked = false;
                            }
                        }
                    });
                });
            });
            
            // Check on page load if VIEW_COMMISSION is already checked
            document.addEventListener('DOMContentLoaded', function() {
                var commissionCheckbox = document.querySelector('.permission-checkbox[data-perm-name="VIEW_COMMISSION"]');
                if (commissionCheckbox && commissionCheckbox.checked) {
                    var commissionSettings = document.getElementById('commissionTypeSettingsInline');
                    if (commissionSettings) {
                        commissionSettings.style.display = 'block';
                    }
                }
                
                // Sync radio buttons with existing hidden permission checkboxes
                document.querySelectorAll('.commission-booking-type, .commission-product-type, .commission-coupon-type').forEach(function(radio) {
                    var permissionName = radio.value;
                    var permissionCheckbox = document.getElementById('perm_' + permissionName);
                    if (permissionCheckbox && permissionCheckbox.checked) {
                        radio.checked = true;
                    }
                });
            });
        })();
    </script>
@endsection
