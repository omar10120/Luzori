<div class="mb-1">
    <label class="form-label">{{ __('field.country_code') }} <span class="text-danger">*</span></label>
    <select class="form-control" name="country_code" required>
        <option {{ $item ? ($item->country_code == '+971' ? 'selected' : null) : null }} value="+971">
            {{ __('UAE') }} (+971)</option>
        <option {{ $item ? ($item->country_code == '+966' ? 'selected' : null) : null }} value="+966">
            {{ __('Saudi Arabia') }} (+966)</option>
        <option {{ $item ? ($item->country_code == '+974' ? 'selected' : null) : null }} value="+974">
            {{ __('Qatar') }} (+974)</option>
        <option {{ $item ? ($item->country_code == '+965' ? 'selected' : null) : null }} value="+965">
            {{ __('Kuwait') }} (+965)</option>
        <option {{ $item ? ($item->country_code == '+968' ? 'selected' : null) : null }} value="+968">
            {{ __('Oman') }} (+968)</option>
        <option {{ $item ? ($item->country_code == '+973' ? 'selected' : null) : null }} value="+973">
            {{ __('Bahrain') }} (+973)</option>
    </select>
</div>
