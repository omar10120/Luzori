@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    <style>
        .lz-role-form-page .lz-perm-group {
            border: 1px solid #efe6d8;
            border-radius: 14px;
            background: #fbfaf7;
            padding: 1rem 1.15rem 0.85rem;
            margin-bottom: 1rem;
        }
        .lz-role-form-page .lz-perm-group-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
            font-weight: 700;
            color: #002b26;
            margin: 0 0 0.85rem;
            padding-bottom: 0.65rem;
            border-bottom: 1px solid #efe6d8;
        }
        .lz-role-form-page .lz-perm-group-title i {
            color: #c5a070;
            font-size: 1.1rem;
        }
        .lz-role-form-page .lz-perm-item {
            background: #fff;
            border: 1px solid #ebe3d7;
            border-radius: 10px;
            padding: 0.55rem 0.75rem;
            margin-bottom: 0.55rem;
            height: calc(100% - 0.55rem);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .lz-role-form-page .lz-perm-item:hover {
            border-color: #c5a070;
            box-shadow: 0 2px 8px rgba(197, 160, 112, 0.12);
        }
        .lz-role-form-page .lz-perm-item .form-check {
            margin: 0;
            min-height: auto;
            display: flex;
            align-items: flex-start;
            gap: 0.55rem;
        }
        .lz-role-form-page .lz-perm-item .form-check-input {
            margin-top: 0.15rem;
            border-color: #c5a070;
            width: 1.1em;
            height: 1.1em;
            flex-shrink: 0;
        }
        .lz-role-form-page .lz-perm-item .form-check-input:checked {
            background-color: #002b26;
            border-color: #002b26;
        }
        .lz-role-form-page .lz-perm-item .form-check-label {
            color: #002b26;
            font-weight: 600;
            font-size: 0.82rem;
            line-height: 1.35;
            word-break: break-word;
            cursor: pointer;
        }
        .lz-role-form-page .lz-commission-card {
            border: 1px solid #efe6d8 !important;
            border-radius: 12px !important;
            background: #fff !important;
            box-shadow: none !important;
        }
        .lz-role-form-page .lz-commission-card .card-body {
            padding: 1rem 1.15rem !important;
        }
        .lz-role-form-page .lz-commission-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
            padding: .75rem 0;
            border-bottom: 1px solid #efe6d8;
        }
        .lz-role-form-page .lz-commission-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .lz-role-form-page .lz-commission-row-label {
            font-weight: 700;
            color: #002b26;
            min-width: 11rem;
        }
        .lz-role-form-page .lz-commission-switch {
            display: inline-flex;
            background: #f7f3ec;
            border: 1px solid #efe6d8;
            border-radius: 999px;
            padding: .2rem;
            gap: .15rem;
        }
        .lz-role-form-page .lz-commission-switch .form-check {
            margin: 0;
            padding: 0;
        }
        .lz-role-form-page .lz-commission-switch .form-check-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .lz-role-form-page .lz-commission-switch .form-check-label {
            display: inline-block;
            border-radius: 999px;
            padding: .35rem .85rem;
            font-size: .82rem;
            font-weight: 700;
            color: #5c6b68;
            cursor: pointer;
        }
        .lz-role-form-page .lz-commission-switch .form-check-input:checked + .form-check-label {
            background: #002b26;
            color: #fff;
        }
        .lz-role-form-page .lz-commission-actions {
            display: flex;
            align-items: center;
            gap: .6rem;
            flex-wrap: wrap;
            width: 100%;
        }
        .lz-role-form-page .lz-commission-edit {
            border: 1px solid #002b26;
            background: #fff;
            color: #002b26;
            border-radius: 999px;
            padding: .32rem .8rem;
            font-size: .75rem;
            font-weight: 700;
        }
        .lz-role-form-page .lz-commission-summary {
            color: #7a5a2f;
            font-size: .78rem;
            font-weight: 700;
        }
        .lz-role-form-page .lz-commission-picker {
            display: none;
        }
        #commissionValuesBody .lz-commission-picker {
            display: block;
            width: 100%;
        }
        #commissionValuesModal .lz-range-row {
            display: flex;
            align-items: center;
            gap: .4rem;
            flex-wrap: wrap;
            margin-bottom: .55rem;
        }
        #commissionValuesModal .lz-range-row select {
            border: 1px solid #e4d7c4;
            border-radius: 8px;
            padding: .3rem .45rem;
            color: #002b26;
            background: #fff;
        }
        #commissionValuesModal .lz-range-apply {
            border: 0;
            background: #002b26;
            color: #fff;
            border-radius: 999px;
            padding: .32rem .8rem;
            font-size: .75rem;
            font-weight: 700;
        }
        #commissionValuesModal .lz-value-grid {
            display: grid;
            grid-template-columns: repeat(10, minmax(0, 1fr));
            gap: .28rem;
            max-height: 360px;
            overflow: auto;
            padding: .15rem;
        }
        #commissionValuesModal .lz-value-grid label {
            position: relative;
            margin: 0;
            cursor: pointer;
        }
        #commissionValuesModal .lz-value-grid input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        #commissionValuesModal .lz-value-grid span {
            display: block;
            text-align: center;
            border: 1px solid #efe6d8;
            border-radius: 8px;
            background: #fff;
            color: #002b26;
            font-size: .72rem;
            font-weight: 700;
            padding: .32rem 0;
        }
        #commissionValuesModal .lz-value-grid input:checked + span {
            background: #002b26;
            border-color: #002b26;
            color: #fff;
        }
        .lz-role-form-page #senderEmailModal .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
        }
        .lz-role-form-page #senderEmailModal .modal-header {
            background: #f7f3ec;
            border-bottom: 1px solid #efe6d8;
        }
        .lz-role-form-page #senderEmailModal .modal-title {
            color: #002b26;
            font-weight: 700;
        }
        .lz-role-form-page #senderEmailModal .btn-primary,
        #commissionValuesModal .btn-primary {
            background: #002b26;
            border-color: #002b26;
        }
        #commissionValuesModal .modal-content {
            border: none;
            border-radius: 16px;
            overflow: hidden;
        }
        #commissionValuesModal .modal-header {
            background: #f7f3ec;
            border-bottom: 1px solid #efe6d8;
        }
        #commissionValuesModal .modal-title {
            color: #002b26;
            font-weight: 700;
        }
    </style>
@endsection

@section('content')
    <div class="luzori-page lz-role-form-page">
        @include('CenterUser.Components.breadcrumbs')

        <div class="lz-page-toolbar">
            <div class="lz-page-title-wrap">
                <div class="lz-page-icon">
                    <i class="ti ti-shield-lock"></i>
                </div>
                <h1 class="lz-page-title">
                    @if (is_null($item))
                        {{ __('general.add_new') }} {{ __('locale.centeruserroles') }}
                    @else
                        {{ __('general.edit') }} {{ __('locale.centeruserroles') }}
                    @endif
                </h1>
            </div>
        </div>

        <form class="pt-0" id="frmSubmit">
            @csrf
            <input type="hidden" name="guard_name" value="center_api">
            <div class="card lz-form-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-4">
                            <label class="lz-field-label">{{ __('field.name') }} <span class="text-danger">*</span></label>
                            <small class="lz-field-help">{{ __('general.enter_the_name_of_the_role') }}</small>
                            <input type="text" class="form-control" name="name" placeholder="{{ __('field.name') }}"
                                value="{{ $item ? $item->name : '' }}" />
                        </div>
                    </div>

                    <div class="row">
                        @foreach ($permissions as $group => $permission)
                            <div class="col-12">
                                <div class="lz-perm-group">
                                    <h2 class="lz-perm-group-title">
                                        <i class="ti ti-key"></i>
                                        <span>{{ $group }}</span>
                                    </h2>
                                    <div class="row">
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
                                                <div class="lz-perm-item">
                                                    <div class="form-check form-check-custom form-check-solid">
                                                        <input class="form-check-input permission-checkbox" data-perm-name="{{ $perm->name }}" type="checkbox" value="{{ $perm->name }}"
                                                            id="perm_check_{{ $perm->id }}"
                                                            name="permissions[]"
                                                            {{ $item ? (in_array($perm->id, array_column($item->permissions->toArray(), 'id')) ? 'checked' : null) : null }} />
                                                        <label class="form-check-label" for="perm_check_{{ $perm->id }}">
                                                            {{ app()->getLocale() == 'ar' ? ($perm->name_ar ?? $perm->name) : $perm->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>

                                    @if($group == 'Commission' && $permission->contains(function($p) { return in_array($p->name, ['COMMISSION_BOOKING_PERCENTAGE', 'COMMISSION_BOOKING_FIXED', 'COMMISSION_PRODUCT_PERCENTAGE', 'COMMISSION_PRODUCT_FIXED', 'COMMISSION_COUPON_PERCENTAGE', 'COMMISSION_COUPON_FIXED']); }))
                                        @php
                                            $commissionPanelOpen = $item && $item->permissions->contains(fn ($assigned) => $assigned->name === 'VIEW_COMMISSION');
                                        @endphp
                                        <div class="col-md-12 p-0" id="commissionTypeSettingsInline" @unless($commissionPanelOpen) style="display: none;" @endunless>
                                            <div class="card lz-commission-card mt-2">
                                                <div class="card-body">
                                                    <h5 class="lz-field-label mb-1">{{ __('field.commission') }} {{ __('field.types') }}</h5>
                                                    @php
                                                        $commissionKinds = [
                                                            'booking' => __('field.commission_services'),
                                                            'coupon' => __('field.commission_coupon'),
                                                            'product' => __('field.commission_product'),
                                                        ];
                                                        $rolePermissionNames = $item ? array_column($item->permissions->toArray(), 'name') : [];
                                                    @endphp
                                                    @foreach ($commissionKinds as $kind => $kindLabel)
                                                        @php
                                                            $percentName = 'COMMISSION_'.strtoupper($kind).'_PERCENTAGE';
                                                            $fixedName = 'COMMISSION_'.strtoupper($kind).'_FIXED';
                                                            $percentPerm = $permission->firstWhere('name', $percentName);
                                                            $fixedPerm = $permission->firstWhere('name', $fixedName);
                                                        @endphp
                                                        @if($percentPerm || $fixedPerm)
                                                            <div class="lz-commission-row" data-kind="{{ $kind }}">
                                                                <div class="lz-commission-row-label">{{ $kindLabel }}</div>
                                                                <div class="lz-commission-switch">
                                                                    @if($fixedPerm)
                                                                        <div class="form-check">
                                                                            <input class="form-check-input commission-{{ $kind }}-type" type="radio" name="commission_{{ $kind }}_type"
                                                                                id="commission_{{ $kind }}_fixed" value="{{ $fixedName }}"
                                                                                {{ in_array($fixedName, $rolePermissionNames, true) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="commission_{{ $kind }}_fixed">{{ __('field.fixed_value') }}</label>
                                                                        </div>
                                                                    @endif
                                                                    @if($percentPerm)
                                                                        <div class="form-check">
                                                                            <input class="form-check-input commission-{{ $kind }}-type" type="radio" name="commission_{{ $kind }}_type"
                                                                                id="commission_{{ $kind }}_percentage" value="{{ $percentName }}"
                                                                                {{ in_array($percentName, $rolePermissionNames, true) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="commission_{{ $kind }}_percentage">%</label>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <div class="lz-commission-actions">
                                                                    <button type="button" class="lz-commission-edit" data-kind="{{ $kind }}" hidden>{{ __('field.edit_values') }}</button>
                                                                    <span class="lz-commission-summary" data-kind="{{ $kind }}"></span>
                                                                </div>
                                                                <input type="hidden" name="commission_rates_present" value="1">
                                                                @foreach (['percentage' => '%', 'fixed' => __('field.fixed_value')] as $mode => $modeLabel)
                                                                    @php
                                                                        $selectedValues = get_allowed_commission_rates($kind, $mode);
                                                                        $fieldName = $mode === 'fixed' ? 'commission_fixed' : 'commission_rates';
                                                                    @endphp
                                                                    <div class="lz-commission-picker" data-kind="{{ $kind }}" data-mode="{{ $mode }}" data-title="{{ $kindLabel }} - {{ $modeLabel }}">
                                                                        <div class="lz-range-row">
                                                                            <span>{{ $modeLabel }}</span>
                                                                            <select class="lz-range-from" aria-label="{{ __('field.from') }}">
                                                                                @foreach (commission_value_steps() as $step)
                                                                                    <option value="{{ $step }}">{{ $step == floor($step) ? (int) $step : $step }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            <span>{{ __('field.to') }}</span>
                                                                            <select class="lz-range-to" aria-label="{{ __('field.to') }}">
                                                                                @foreach (commission_value_steps() as $step)
                                                                                    <option value="{{ $step }}" @selected($step == 100)>{{ $step == floor($step) ? (int) $step : $step }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                            <button type="button" class="lz-range-apply">{{ __('field.apply_range') }}</button>
                                                                        </div>
                                                                        <div class="lz-value-grid">
                                                                            @foreach (commission_value_steps() as $step)
                                                                                @php $stepLabel = $step == floor($step) ? (int) $step : $step; @endphp
                                                                                <label>
                                                                                    <input type="checkbox" name="{{ $fieldName }}[{{ $kind }}][]" value="{{ $step }}" @checked(in_array((float) $step, array_map('floatval', $selectedValues), true))>
                                                                                    <span>{{ $stepLabel }}{{ $mode === 'percentage' ? '%' : '' }}</span>
                                                                                </label>
                                                                            @endforeach
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                                @if($percentPerm)
                                                                    <input type="checkbox" class="commission-permission-checkbox" name="permissions[]" value="{{ $percentName }}"
                                                                        id="perm_{{ $percentName }}" style="display: none;"
                                                                        {{ in_array($percentName, $rolePermissionNames, true) ? 'checked' : '' }}>
                                                                @endif
                                                                @if($fixedPerm)
                                                                    <input type="checkbox" class="commission-permission-checkbox" name="permissions[]" value="{{ $fixedName }}"
                                                                        id="perm_{{ $fixedName }}" style="display: none;"
                                                                        {{ in_array($fixedName, $rolePermissionNames, true) ? 'checked' : '' }}>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="{{ route('center_user.centeruserroles.index') }}" class="btn btn-luzori-beige">
                        {{ __('general.cancel') }}
                    </a>
                    <button type="submit" class="btn btn-luzori-primary submitFrom">
                        <i class="ti ti-check me-1"></i>
                        <span>{{ __('general.save') }}</span>
                    </button>
                </div>
            </div>
            <div class="modal fade" id="commissionValuesModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="commissionValuesTitle"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('general.cancel') }}"></button>
                        </div>
                        <div class="modal-body" id="commissionValuesBody"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">{{ __('general.save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
                                    var permissionName = radio.value;
                                    var permissionCheckbox = document.getElementById('perm_' + permissionName);
                                    if (permissionCheckbox) {
                                        permissionCheckbox.checked = false;
                                    }
                                });
                                refreshCommissionSummaries();
                            }
                        }
                    }
                });
            });

            var commissionModalEl = document.getElementById('commissionValuesModal');
            var commissionModal = null;

            function commissionDialog() {
                if (!commissionModalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
                if (!commissionModal) {
                    commissionModal = bootstrap.Modal.getOrCreateInstance
                        ? bootstrap.Modal.getOrCreateInstance(commissionModalEl)
                        : new bootstrap.Modal(commissionModalEl);
                }
                return commissionModal;
            }

            function commissionMode(value) {
                return value && value.indexOf('PERCENTAGE') !== -1 ? 'percentage' : 'fixed';
            }

            function openCommissionDialog(kind, mode) {
                var picker = document.querySelector('.lz-commission-picker[data-kind="' + kind + '"][data-mode="' + mode + '"]');
                var body = document.getElementById('commissionValuesBody');
                if (!picker || !body || !commissionDialog()) return;
                body.querySelectorAll('.lz-commission-picker').forEach(function (openPicker) {
                    var row = document.querySelector('.lz-commission-row[data-kind="' + openPicker.dataset.kind + '"]');
                    if (row) row.appendChild(openPicker);
                });
                document.getElementById('commissionValuesTitle').textContent = picker.dataset.title || '';
                body.appendChild(picker);
                document.body.appendChild(commissionModalEl);
                commissionDialog().show();
            }

            function refreshCommissionSummaries() {
                document.querySelectorAll('.lz-commission-edit').forEach(function (button) {
                    var kind = button.dataset.kind;
                    var selected = document.querySelector('input[name="commission_' + kind + '_type"]:checked');
                    var summary = document.querySelector('.lz-commission-summary[data-kind="' + kind + '"]');
                    if (!selected) {
                        button.hidden = true;
                        if (summary) summary.textContent = '';
                        return;
                    }
                    var mode = commissionMode(selected.value);
                    button.hidden = false;
                    button.dataset.mode = mode;
                    var picker = document.querySelector('.lz-commission-picker[data-kind="' + kind + '"][data-mode="' + mode + '"]');
                    var values = picker ? Array.from(picker.querySelectorAll('input:checked')).map(function (input) {
                        return input.nextElementSibling.textContent;
                    }) : [];
                    if (summary) {
                        summary.textContent = values.length > 12 ? values.slice(0, 12).join('، ') + '…' : values.join('، ');
                    }
                });
            }

            document.querySelectorAll('.commission-booking-type, .commission-product-type, .commission-coupon-type').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    var permissionName = this.value;
                    var isChecked = this.checked;

                    if (isChecked) {
                        var allRadios = document.querySelectorAll('[name="' + this.name + '"]');
                        allRadios.forEach(function(r) {
                            if (r !== radio) {
                                r.checked = false;
                                var otherCheckbox = document.getElementById('perm_' + r.value);
                                if (otherCheckbox) otherCheckbox.checked = false;
                            }
                        });

                        var permissionCheckbox = document.getElementById('perm_' + permissionName);
                        if (permissionCheckbox) permissionCheckbox.checked = true;
                        var kind = this.name.replace('commission_', '').replace('_type', '');
                        refreshCommissionSummaries();
                        openCommissionDialog(kind, commissionMode(permissionName));
                    } else {
                        var permissionCheckbox = document.getElementById('perm_' + permissionName);
                        if (permissionCheckbox) permissionCheckbox.checked = false;
                        refreshCommissionSummaries();
                    }
                });
            });

            document.addEventListener('click', function (event) {
                var editButton = event.target.closest('.lz-commission-edit');
                if (!editButton || editButton.hidden) return;
                openCommissionDialog(editButton.dataset.kind, editButton.dataset.mode);
            });

            if (commissionModalEl) {
                commissionModalEl.addEventListener('hidden.bs.modal', function () {
                    var body = document.getElementById('commissionValuesBody');
                    body.querySelectorAll('.lz-commission-picker').forEach(function (picker) {
                        var row = document.querySelector('.lz-commission-row[data-kind="' + picker.dataset.kind + '"]');
                        if (row) row.appendChild(picker);
                    });
                    refreshCommissionSummaries();
                });
            }

            document.addEventListener('click', function (event) {
                var apply = event.target.closest('.lz-range-apply');
                if (!apply) return;
                var picker = apply.closest('.lz-commission-picker');
                var from = parseFloat(picker.querySelector('.lz-range-from').value);
                var to = parseFloat(picker.querySelector('.lz-range-to').value);
                var start = Math.min(from, to);
                var end = Math.max(from, to);
                picker.querySelectorAll('.lz-value-grid input').forEach(function (input) {
                    var value = parseFloat(input.value);
                    if (value >= start - 0.001 && value <= end + 0.001) {
                        input.checked = true;
                    }
                });
            });

            function showCommissionTypes() {
                var commissionCheckbox = document.querySelector('.permission-checkbox[data-perm-name="VIEW_COMMISSION"]');
                var commissionSettings = document.getElementById('commissionTypeSettingsInline');
                if (commissionCheckbox && commissionCheckbox.checked && commissionSettings) {
                    commissionSettings.style.display = 'block';
                }
                document.querySelectorAll('.commission-booking-type, .commission-product-type, .commission-coupon-type').forEach(function(radio) {
                    var permissionCheckbox = document.getElementById('perm_' + radio.value);
                    if (permissionCheckbox && permissionCheckbox.checked) {
                        radio.checked = true;
                    }
                });
                refreshCommissionSummaries();
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showCommissionTypes);
            } else {
                showCommissionTypes();
            }
        })();
    </script>
@endsection
