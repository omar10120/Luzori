<div class="row">
    <div class="col-12">
        <div id="alertError" class="alert alert-danger" role="alert"
            style="display:none;{{ session()->get('locale') == 'en' ? 'text-align:left;' : 'text-align:right;' }}">
            <h4 class="alert-heading">{{ __('general.the_request_failed') }}</h4>
            <div class="alert-body">
                <div id="errorMessage"></div>
                <ul id="listError"></ul>
            </div>
        </div>
        <div id="alertSuccess" class="alert alert-success" role="alert"
            style="display:none;{{ session()->get('locale') == 'en' ? 'text-align:left;' : 'text-align:right;' }}">
            <h4 class="alert-heading">{{ __('general.the_request_success') }}</h4>
            <div class="alert-body">
                <div id="successMessage"></div>
            </div>
        </div>
    </div>
</div>