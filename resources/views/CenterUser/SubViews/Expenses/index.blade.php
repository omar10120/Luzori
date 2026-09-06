<style>



@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Tajawal:wght@200;300;400;500;700;800;900&display=swap');

:root {
    --app-font: 'Cairo', sans-serif;
}

html,
body,

table,
th,
td,
tr,
button,
input,
select,
textarea,
label,
div,
span,
a,
li,
p,
h1,
h2,
h3,
h4,
h5,
h6,
.dataTables_wrapper {
    font-family: var(--app-font) !important;
}

</style>
@extends('layouts/layoutMaster')

@section('title', $title)

@section('vendor-style')
    @vite('resources/assets/vendor/libs/select2/select2.scss')
@endsection

@section('content')
    <div class="container">
        @include('CenterUser.Components.breadcrumbs')

        @if (\Session::has('success'))
            <div class="alert alert-success">
                <div>{!! \Session::get('success') !!}</div>
            </div>
        @endif
        @if (\Session::has('error'))
            <div class="alert alert-danger">
                <div>{!! \Session::get('error') !!}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

      

        <div class="row">
            <form class="pt-0" method="POST" action="{{ $requestUrl }}" enctype="multipart/form-data">
                @csrf
                @if($item)
                    <input type="hidden" name="id" value="{{ $item->id }}">
                @endif
                <div class="card">
                    <div class="card-header">
                        <h2>{{ $title }}</h2>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="date" value="{{ $item ? $item->date : date('Y-m-d') }}" required />
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.branch') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted d-block mb-2">{{__('general.select_a_branch_from_the_list')}}</small>
                                    @php
                                        $userBranchId = auth('center_user')->user()->branch_id;
                                        $selectedBranchId = $item ? $item->branch_id : $userBranchId;
                                    @endphp
                                    <select class="form-control" name="branch_id" id="branchSelect" data-select="true" required {{ $userBranchId ? 'disabled' : '' }}>    
                                        <option value="">{{ __('field.select_branch') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $selectedBranchId == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($userBranchId)
                                        <input type="hidden" name="branch_id" value="{{ $userBranchId }}">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.expense_name') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted d-block mb-2">{{__('field.select_expense_name')}}</small>
                                    <select name="expense_name" id="expenseNameField" class="form-control" required>
                                        <option value="">{{ __('field.select_expense_name') }}</option>
                                        <option value="local_utilities" {{ $item && ($item->expense_name == 'utilities' || $item->expense_name == 'local' || $item->expense_name == 'local_utilities') ? 'selected' : '' }}>{{ __('field.local') }} {{ __('field.utilities') }}</option>
                                        <option value="salary" {{ $item && ($item->expense_name == 'salary' || $item->expense_name == 'Salary') ? 'selected' : '' }}>{{ __('field.salary') }}</option>
                                        <option value="suppliers" {{ $item && $item->expense_name == 'suppliers' ? 'selected' : '' }}>{{ __('field.suppliers') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.payee') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted d-block mb-2">{{__('general.select_a_payee_from_the_list')}}</small>
                                    <select class="form-control" name="payee" id="payeeSelect" data-select="true" required>
                                        <option value="">{{ __('field.select_payee') }}</option>
                                        <option value="no_payee">{{ __('field.no_payee') }}</option>
                                    </select>
                                    <!-- Hidden field to store supplier_id -->
                                    <input type="hidden" name="supplier_id" id="supplierIdField" value="{{ $item ? $item->supplier_id : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.notes') }}</label>
                                    <small class="text-muted d-block mb-2">{{__('general.enter_the_notes_of_the_expense')}}</small>
                                    <textarea name="notes" class="form-control" rows="4"
                                        placeholder="{{ __('field.notes') }}">{{ $item ? $item->notes : '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row" id="dateRangeContainer" style="display: {{ ($item && ($item->expense_name == 'suppliers' || $item->expense_name == 'salary' || $item->expense_name == 'Salary')) ? 'block' : 'none' }};">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.start_date') }}</label>
                                    <small class="text-muted d-block mb-2">{{__('general.select_a_start_date')}}</small>
                                    <input type="date" name="start_date" class="form-control"
                                        placeholder="{{ __('field.start_date') }}" value="{{ $item ? $item->start_date : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.end_date') }}</label>
                                    <small class="text-muted d-block mb-2">{{__('general.select_a_end_date')}}</small>
                                    <input type="date" name="end_date" class="form-control"
                                        placeholder="{{ __('field.end_date') }}" value="{{ $item ? $item->end_date : '' }}" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.amount') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted d-block mb-2">{{__('general.enter_the_amount_of_the_expense')}}</small>
                                    <input type="phone" name="amount" id="expenseAmount" class="form-control"
                                        placeholder="{{ __('field.amount') }} (e.g. 100.00)" value="{{ $item ? $item->amount : '' }}" 
                                        step="0.01" max="99999" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">{{ __('field.receipt') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted d-block mb-2">{{__('general.select_a_receipt_from_the_list')}}</small>
                                    <input type="file" class="form-control" id="receiptImage" name="receipt_image" accept="image/*" />
                                    <div class="mt-2">
                                        <img id="show_receipt" src="{{ $item ? $item->receipt_image_url : '' }}"
                                            style="{{ $item ? '' : 'display:none;' }} max-width:100%; height:auto; max-height:200px; border-radius:8px; border:2px solid #e0e0e0;"
                                            alt="expense receipt" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary submitFrom" id="submitBtn">
                            <i class="menu-icon tf-icons ti ti-check"></i>
                            <span id="submitText">{{ $item ? __('general.update') : __('general.save') }}</span>
                            <span id="submitSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                        </button>
                        <a href="{{ route('center_user.expenses.providers') }}" class="btn btn-secondary ms-2">
                            {{ __('general.back') }}
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection 

@section('vendor-script')
    @vite('resources/assets/vendor/libs/select2/select2.js')
@endsection

@section('page-script')
    <style>
        .btn-loading {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .form-loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        #branchSelect:disabled + .select2-container,
        #branchSelect:disabled ~ .select2-container {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>

    <script>
        // Workers and suppliers data from PHP
        const workers = @json($workers);
        const suppliers = @json($suppliers);
        
        // Initialize select2 with custom placeholders
        $(document).ready(function() {
            // Initialize branch select only if not disabled
            @if($userBranchId)
                // If user has a branch, pre-select it and disable the field
                var userBranchId = {{ $userBranchId }};
                $('#branchSelect').val(userBranchId).prop('disabled', true);
                // Initialize Select2 in disabled state (read-only)
                $('#branchSelect').select2({
                    placeholder: "{{ __('field.select_branch') }}",
                    allowClear: false,
                    dropdownParent: $('#branchSelect').parent()
                });
                // Disable Select2 dropdown and style it
                $('#branchSelect').next('.select2-container').css({
                    'pointer-events': 'none',
                    'opacity': '0.7'
                });
            @else
                // Initialize branch select normally if user has no branch
                $('#branchSelect').select2({
                    placeholder: "{{ __('field.select_branch') }}",
                    allowClear: true,
                    dropdownParent: $('#branchSelect').parent()
                });
            @endif
            
            // Initialize payee select
            $('#payeeSelect').select2({
                placeholder: "{{ __('field.select_payee') }}",
                allowClear: true,
                dropdownParent: $('#payeeSelect').parent()
            });
            
            // Receipt image preview
            document.getElementById('receiptImage').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    document.getElementById("show_receipt").style.display = "block";
                    document.getElementById("show_receipt").src = URL.createObjectURL(file);
                }
            });
            
            // Show initial loading state
            $('#submitBtn').prop('disabled', false);
            
            // Function to handle date range visibility
            function handleDateRangeVisibility() {
                const expenseType = $('#expenseNameField').val();
                const dateRangeContainer = $('#dateRangeContainer');
                const startDateInput = $('input[name="start_date"]');
                const endDateInput = $('input[name="end_date"]');
                
                // Show date range if expense type is "suppliers" or "salary"
                if (expenseType === 'suppliers' || expenseType === 'salary') {
                    dateRangeContainer.show();
                    // Not required - fields are optional
                    startDateInput.removeAttr('required');
                    endDateInput.removeAttr('required');
                } else {
                    dateRangeContainer.hide();
                    startDateInput.removeAttr('required');
                    endDateInput.removeAttr('required');
                }
            }
            
            // Function to populate payee options based on expense type and branch
            function updatePayeeOptions() {
                const expenseType = $('#expenseNameField').val();
                const branchId = $('#branchSelect').val();
                const payeeSelect = $('#payeeSelect');
                const currentPayee = '{{ $item ? $item->payee : "" }}';
                
                // Clear existing options
                payeeSelect.empty();
                payeeSelect.append('<option value="">{{ __('field.select_payee') }}</option>');
                
                if (expenseType === 'salary') {
                    // If salary is selected, show workers from selected branch with phone
                    if (branchId) {
                        const branchWorkers = workers.filter(worker => worker.branch_id == branchId);
                        
                        branchWorkers.forEach(function(worker) {
                            // Format phone number
                            const phoneDisplay = worker.country_code && worker.phone 
                                ? ` (${worker.country_code}${worker.phone})` 
                                : worker.phone 
                                    ? ` (${worker.phone})` 
                                    : '';
                            const displayText = worker.name + phoneDisplay;
                            
                            const option = $('<option></option>')
                                .attr('value', worker.name)
                                .text(displayText);
                            
                            // Select current payee if it matches
                            if (currentPayee && worker.name === currentPayee) {
                                option.attr('selected', 'selected');
                            }
                            
                            payeeSelect.append(option);
                        });
                    }
                } else if (expenseType === 'suppliers') {
                    // If suppliers is selected, show suppliers list
                    suppliers.forEach(function(supplier) {
                        const option = $('<option></option>')
                            .attr('value', supplier.name)
                            .attr('data-supplier-id', supplier.id)
                            .text(supplier.name);
                        
                        // Select current payee if it matches
                        if (currentPayee && supplier.name === currentPayee) {
                            option.attr('selected', 'selected');
                            $('#supplierIdField').val(supplier.id);
                        }
                        
                        payeeSelect.append(option);
                    });
                } else if (expenseType === 'local_utilities' || expenseType === 'utilities' || expenseType === 'local') {
                    // If local_utilities is selected, show only no_payee
                    payeeSelect.append('<option value="no_payee">{{ __('field.no_payee') }}</option>');
                    
                    // Select "no_payee" and disable the payee dropdown
                    payeeSelect.val('no_payee').trigger('change');
                    payeeSelect.prop('disabled', true);
                    payeeSelect.removeAttr('required');
                    
                    // Disable Select2 if it's initialized
                    try {
                        payeeSelect.select2('destroy');
                        payeeSelect.select2({
                            placeholder: "{{ __('field.select_payee') }}",
                            allowClear: false,
                            dropdownParent: payeeSelect.parent()
                        });
                        payeeSelect.next('.select2-container').css({
                            'pointer-events': 'none',
                            'opacity': '0.7'
                        });
                    } catch (e) {
                        // Select2 not available
                    }
                    
                    // Reinitialize Select2 and return early
                    return;
                }
                
                // Select current payee if it's "no_payee" (for other expense types)
                if (currentPayee === 'no_payee') {
                    payeeSelect.append('<option value="no_payee" selected>{{ __('field.no_payee') }}</option>');
                }
                
                // Re-enable payee dropdown if it was disabled
                payeeSelect.prop('disabled', false);
                payeeSelect.attr('required', 'required');
                
                // Reinitialize Select2 with placeholder
                try {
                    payeeSelect.select2('destroy');
                    payeeSelect.select2({
                        placeholder: "{{ __('field.select_payee') }}",
                        allowClear: true,
                        dropdownParent: payeeSelect.parent()
                    });
                    payeeSelect.next('.select2-container').css({
                        'pointer-events': 'auto',
                        'opacity': '1'
                    });
                } catch (e) {
                    // Select2 not available, using regular select
                }
            }
            
            // Function to handle expense type changes
            function handleExpenseTypeChange() {
                updatePayeeOptions();
                handleDateRangeVisibility();
            }
            
            // Initialize form state
            updatePayeeOptions();
            handleDateRangeVisibility();
            
            // Listen for expense type changes
            $('#expenseNameField').on('change', function() {
                handleExpenseTypeChange();
            });
            
            // Listen for branch changes (only update if salary is selected)
            $('#branchSelect').on('change', function() {
                const expenseType = $('#expenseNameField').val();
                if (expenseType === 'salary') {
                    updatePayeeOptions();
                }
            });
            
            // If branch is pre-selected and disabled, trigger updatePayeeOptions on load
            @if($userBranchId)
                const expenseTypeOnLoad = $('#expenseNameField').val();
                if (expenseTypeOnLoad) {
                    updatePayeeOptions();
                }
            @endif
            
            // Listen for payee selection changes to update supplier_id
            $('#payeeSelect').on('change', function() {
                const selectedValue = $(this).val();
                const selectedOption = $(this).find('option:selected');
                const supplierId = selectedOption.data('supplier-id');
                
                if (selectedValue === 'no_payee') {
                    $('#supplierIdField').val('');
                } else if (supplierId) {
                    $('#supplierIdField').val(supplierId);
                } else {
                    $('#supplierIdField').val('');
                }
            });
            
            // Prevent 'e' in amount field
            $('#expenseAmount').on('keydown', function(e) {
                if (['e', 'E', '+', '-'].includes(e.key)) {
                    e.preventDefault();
                }
            });

            // LocalStorage Persistence for form values
            const storageKey = 'expense_form_data';
            
            function saveFormData() {
                const formData = {
                    branch_id: $('#branchSelect').val(),
                    expense_name: $('#expenseNameField').val(),
                    payee: $('#payeeSelect').val(),
                    date: $('input[name="date"]').val(),
                    start_date: $('input[name="start_date"]').val(),
                    end_date: $('input[name="end_date"]').val(),
                    amount: $('#expenseAmount').val(),
                    notes: $('textarea[name="notes"]').val()
                };
                localStorage.setItem(storageKey, JSON.stringify(formData));
            }

            function loadFormData() {
                const savedData = localStorage.getItem(storageKey);
                if (savedData && !@json($item)) { // Only load if not editing an existing item
                    const data = JSON.parse(savedData);
                    @if(!$userBranchId)
                        // Only load branch from localStorage if user doesn't have a fixed branch
                        if (data.branch_id) $('#branchSelect').val(data.branch_id).trigger('change');
                    @endif
                    if (data.expense_name) {
                        $('#expenseNameField').val(data.expense_name).trigger('change');
                    }
                    if (data.date) $('input[name="date"]').val(data.date);
                    if (data.start_date) $('input[name="start_date"]').val(data.start_date);
                    if (data.end_date) $('input[name="end_date"]').val(data.end_date);
                    if (data.amount) $('#expenseAmount').val(data.amount);
                    if (data.notes) $('textarea[name="notes"]').val(data.notes);
                    
                    // Delay payee selection to ensure options are loaded
                    setTimeout(() => {
                        if (data.payee) $('#payeeSelect').val(data.payee).trigger('change');
                    }, 500);
                }
            }

            // Save data on change
            $('input, select, textarea').on('change input', function() {
                saveFormData();
            });

            // Load data on page load
            loadFormData();

            // Clear storage on successful submit
            $('form').on('submit', function(e) {
                // ... existing submit code ...
                localStorage.removeItem(storageKey);
                
                const submitBtn = $('#submitBtn');
                const submitText = $('#submitText');
                const submitSpinner = $('#submitSpinner');
                
                submitBtn.prop('disabled', true);
                submitText.text('{{ __("general.processing") }}...');
                submitSpinner.show();
            });
                
        });
    </script>

@endsection
