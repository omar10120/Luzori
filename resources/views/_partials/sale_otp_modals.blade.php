{{-- Sale edit/delete: reason → SMS OTP → action --}}
<div class="modal fade" id="saleReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saleReasonModalTitle">{{ __('general.reason') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="saleOtpAction">
                <input type="hidden" id="saleOtpSaleId">
                <input type="hidden" id="saleOtpNewDate">
                <div class="mb-3" id="saleOtpDateWrap" style="display:none;">
                    <label class="form-label" for="saleOtpDateInput">{{ __('field.date') }}</label>
                    <input type="date" class="form-control" id="saleOtpDateInput">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="saleOtpReason">{{ __('general.reason') }} <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="saleOtpReason" rows="3" maxlength="200"
                        placeholder="{{ __('general.reason_placeholder') }}"></textarea>
                    <small class="text-muted">{{ __('general.reason_length_hint') }}</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="saleOtpSendBtn" onclick="saleOtpSend()">
                    {{ __('general.send_otp') }}
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="saleOtpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('general.otp_verify') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2" id="saleOtpPhonesHint"></p>
                <p class="text-muted small">{{ __('general.otp_enter_code') }}</p>
                <div class="mb-3">
                    <label class="form-label" for="saleOtpCode">{{ __('general.otp_code') }}</label>
                    <input type="text" class="form-control" id="saleOtpCode" maxlength="8" autocomplete="one-time-code"
                        inputmode="numeric" placeholder="000000">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('general.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="saleOtpVerifyBtn" onclick="saleOtpVerify()">
                    {{ __('general.confirm') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    if (window.__saleOtpWired) return;
    window.__saleOtpWired = true;

    var requestUrl = @json(route('center_user.sales.request-otp'));
    var verifyUrl = @json(route('center_user.sales.verify-otp'));
    var csrf = @json(csrf_token());
    var labels = {
        delete: @json(__('general.delete')),
        edit: @json(__('general.edit')),
        reasonRequired: @json(__('general.reason_length_invalid')),
        dateRequired: @json(__('general.date_required')),
        success: @json(__('general.success')),
        error: @json(__('general.error') ?? __('admin.an_error_occurred')),
        otpSentTo: @json(__('general.otp_sent_to')),
    };

    function reasonModal() {
        return bootstrap.Modal.getOrCreateInstance(document.getElementById('saleReasonModal'));
    }

    function otpModal() {
        return bootstrap.Modal.getOrCreateInstance(document.getElementById('saleOtpModal'));
    }

    function ajaxMessage(xhr, fallback) {
        return (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : fallback;
    }

    window.saleOtpStart = function (action, saleId, newDate) {
        document.getElementById('saleOtpAction').value = action;
        document.getElementById('saleOtpSaleId').value = saleId;
        document.getElementById('saleOtpReason').value = '';
        document.getElementById('saleOtpCode').value = '';
        document.getElementById('saleReasonModalTitle').textContent =
            (action === 'delete' ? labels.delete : labels.edit) + ' — ' + @json(__('general.reason'));

        var dateWrap = document.getElementById('saleOtpDateWrap');
        var dateInput = document.getElementById('saleOtpDateInput');
        if (action === 'edit') {
            dateWrap.style.display = '';
            var preset = newDate || document.getElementById('saleShowDateInput')?.value || '';
            dateInput.value = preset;
            document.getElementById('saleOtpNewDate').value = preset;
        } else {
            dateWrap.style.display = 'none';
            dateInput.value = '';
            document.getElementById('saleOtpNewDate').value = '';
        }

        reasonModal().show();
    };

    window.saleOtpSend = function () {
        var action = document.getElementById('saleOtpAction').value;
        var saleId = document.getElementById('saleOtpSaleId').value;
        var reason = (document.getElementById('saleOtpReason').value || '').trim();
        var newDate = document.getElementById('saleOtpDateInput').value || document.getElementById('saleOtpNewDate').value;

        if (reason.length < 3 || reason.length > 200) {
            Swal.fire({ title: labels.error, text: labels.reasonRequired, icon: 'error' });
            return;
        }
        if (action === 'edit' && !newDate) {
            Swal.fire({ title: labels.error, text: labels.dateRequired, icon: 'error' });
            return;
        }

        var btn = document.getElementById('saleOtpSendBtn');
        btn.disabled = true;

        $.ajax({
            url: requestUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                _token: csrf,
                sale_id: saleId,
                action: action,
                reason: reason,
                new_date: action === 'edit' ? newDate : null,
            },
            success: function (response) {
                reasonModal().hide();
                var phones = (response.data && response.data.masked_phones) ? response.data.masked_phones.join(', ') : '';
                document.getElementById('saleOtpPhonesHint').textContent = phones
                    // ? labels.otpSentTo.replace(':phones', phones)
                    ? labels.otpSentTo.replace(':phones', '')
                    : (response.message || '');
                document.getElementById('saleOtpCode').value = '';
                otpModal().show();
            },
            error: function (xhr) {
                Swal.fire({
                    title: labels.error,
                    text: ajaxMessage(xhr, labels.error),
                    icon: 'error',
                });
            },
            complete: function () {
                btn.disabled = false;
            }
        });
    };

    window.saleOtpVerify = function () {
        var action = document.getElementById('saleOtpAction').value;
        var saleId = document.getElementById('saleOtpSaleId').value;
        var code = (document.getElementById('saleOtpCode').value || '').trim();
        var btn = document.getElementById('saleOtpVerifyBtn');
        btn.disabled = true;

        $.ajax({
            url: verifyUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                _token: csrf,
                sale_id: saleId,
                action: action,
                code: code,
            },
            success: function (response) {
                otpModal().hide();
                Swal.fire({
                    title: labels.success,
                    text: response.message || '',
                    icon: 'success',
                    timer: 2500,
                });

                if (action === 'delete') {
                    if (typeof $ !== 'undefined' && $('.table').length && $.fn.DataTable && $.fn.DataTable.isDataTable('.table')) {
                        $('.table').DataTable().ajax.reload();
                    } else {
                        window.location.href = @json(route('center_user.sales.index'));
                    }
                    return;
                }

                var createdAt = response.data && response.data.created_at;
                var display = document.getElementById('saleShowDateDisplay');
                var input = document.getElementById('saleShowDateInput');
                if (createdAt && display) display.textContent = createdAt;
                if (createdAt && input) input.value = createdAt.substring(0, 10);
                if (!display && !input) {
                    window.location.reload();
                }
            },
            error: function (xhr) {
                Swal.fire({
                    title: labels.error,
                    text: ajaxMessage(xhr, labels.error),
                    icon: 'error',
                });
            },
            complete: function () {
                btn.disabled = false;
            }
        });
    };
})();
</script>
