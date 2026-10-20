{{-- Shared Admin DataTables toolbar: title + actions slot + optional filters --}}
<div class="lz-page-toolbar">
    <div class="lz-page-title-wrap">
        <div class="lz-page-icon">
            <i class="ti {{ $icon ?? 'ti-table' }}"></i>
        </div>
        <h1 class="lz-page-title">{{ $heading ?? ((__('general.manage') . ' ' . ($title ?? ''))) }}</h1>
    </div>
    <div class="lz-page-actions" id="lzAdminDtActions"></div>
</div>
@isset($filters)
    <div class="lz-admin-filters mb-3" id="lzAdminBookingFilters">
        {!! $filters !!}
    </div>
@endisset
