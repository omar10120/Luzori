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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.branch') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_branch_from_the_list')}}</small>
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
                                <div class="mb-1">
                                    <div class="d-flex inline gap-4">
                                        <div class="form-check mb-2 ">
                                            <input class="form-check-input" type="checkbox" id="salaryCheckbox" name="is_salary" value="1"
                                                {{ $item && $item->expense_name == 'Salary' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="salaryCheckbox">
                                                {{ __('field.salary') }}
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="fastExpenseCheckbox" name="is_fast_expense" value="1">
                                            <label class="form-check-label" for="fastExpenseCheckbox">
                                                {{ __('field.fast_expense') }}
                                            </label>
                                        </div>
                                    </div>
                                    <div id="expenseNameInput" style="{{ $item && $item->expense_name == 'Salary' ? 'display: none;' : '' }}">
                                        <label class="form-label">{{ __('field.expense_name') }}</label>
                                        <!-- <input type="text" name="expense_name" id="expenseNameField" class="form-control" 
                                            placeholder="{{ __('field.expense_name') }}" 
                                            value="{{ $item && $item->expense_name != 'Salary' ? $item->expense_name : '' }}" /> -->
                                        <select name="expense_name" id="expenseNameField" class="form-control">
                                            <option value="">{{ __('field.select_expense_name') }}</option>
                                            <option value="utilities" {{ $item && $item->expense_name == 'utilities' ? 'selected' : '' }}>{{ __('field.utilities') }}</option>
                                            <option value="no_supplier" {{ $item && $item->expense_name == 'no_supplier' ? 'selected' : '' }}>{{ __('field.no_supplier') }}</option>
                                            <option value="salary" {{ $item && $item->expense_name == 'salary' ? 'selected' : '' }}>{{ __('field.salary') }}</option>
                                            <option value="local" {{ $item && $item->expense_name == 'local' ? 'selected' : '' }}>{{ __('field.local') }}</option>
                                            <option value="suppliers" {{ $item && $item->expense_name == 'suppliers' ? 'selected' : '' }}>{{ __('field.suppliers') }}</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="expense_name_hidden" id="expenseNameHidden" 
                                        value="{{ $item && $item->expense_name == 'Salary' ? 'Salary' : '' }}" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.payee') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_payee_from_the_list')}}</small>
                                    <select class="form-control" name="payee" id="payeeSelect" data-select="true" required>
                                        <option value="">{{ __('field.select_payee') }}</option>
                                        <option value="no_payee">{{ __('field.no_payee') }}</option>
                                    </select>
                                    <!-- Hidden field to store supplier_id -->
                                    <input type="hidden" name="supplier_id" id="supplierIdField" value="{{ $item ? $item->supplier_id : '' }}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="hidden" name="date" value="{{ $item ? $item->date : date('Y-m-d') }}" required />
                            </div>
                        </div>
                        <div class="row" id="dateRangeContainer" style="display: {{ ($item && ($item->expense_name == 'Salary' || $item->expense_name == 'suppliers')) ? 'block' : 'none' }};">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.start_date') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_start_date')}}</small>
                                    <input type="date" name="start_date" class="form-control"
                                        placeholder="{{ __('field.start_date') }}" value="{{ $item ? $item->start_date : '' }}" required />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.end_date') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_end_date')}}</small>
                                    <input type="date" name="end_date" class="form-control"
                                        placeholder="{{ __('field.end_date') }}" value="{{ $item ? $item->end_date : '' }}" required />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.amount') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_amount_of_the_expense')}}</small>
                                    <input type="number" name="amount" id="expenseAmount" class="form-control"
                                        placeholder="{{ __('field.amount') }} (e.g. 100.00)" value="{{ $item ? $item->amount : '' }}" 
                                        step="0.01" max="99999" required />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.receipt') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.select_a_receipt_from_the_list')}}</small>
                                    <input type="file" class="form-control" id="receiptImage" name="receipt_image" />
                                </div>
                                <img id="show_receipt" src="{{ $item ? $item->receipt_image_url : '' }}"
                                    style="{{ $item ? '' : 'display:none;' }} width:200px;height:200px;margin:20px;"
                                    alt="expense receipt" />
                            </div>
                            <div class="col-md-6">
                                <div class="mb-1">
                                    <label class="form-label">{{ __('field.notes') }} <span class="text-danger">*</span></label>
                                    <small class="text-muted">{{__('general.enter_the_notes_of_the_expense')}}</small>
                                    <textarea name="notes" class="form-control" rows="4"
                                        placeholder="{{ __('field.notes') }}">{{ $item ? $item->notes : '' }}</textarea>
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
                const isSalaryChecked = $('#salaryCheckbox').is(':checked');
                const expenseType = $('#expenseNameField').val();
                const dateRangeContainer = $('#dateRangeContainer');
                const startDateInput = $('input[name="start_date"]');
                const endDateInput = $('input[name="end_date"]');
                
                // Show date range if salary is checked OR expense type is "suppliers"
                if (isSalaryChecked || expenseType === 'suppliers') {
                    dateRangeContainer.show();
                    startDateInput.attr('required', 'required');
                    endDateInput.attr('required', 'required');
                } else {
                    dateRangeContainer.hide();
                    startDateInput.removeAttr('required');
                    endDateInput.removeAttr('required');
                }
            }
            
            // Function to handle salary checkbox changes
            function handleSalaryCheckbox() {
                const isSalaryChecked = $('#salaryCheckbox').is(':checked');
                const expenseNameInput = $('#expenseNameInput');
                const expenseNameField = $('#expenseNameField');
                const expenseNameHidden = $('#expenseNameHidden');
                
                
                if (isSalaryChecked) {
                    // Hide expense name input, set hidden value to "Salary"
                    expenseNameInput.hide();
                    expenseNameField.removeAttr('required');
                    expenseNameHidden.val('Salary');
                } else {
                    // Show expense name input, make it required
                    expenseNameInput.show();
                    expenseNameField.attr('required', 'required');
                    expenseNameHidden.val('');
                }
                
                // Update payee options
                updatePayeeOptions();
                
                // Update date range visibility
                handleDateRangeVisibility();
            }
            
            // Function to handle fast expense checkbox changes
            function handleFastExpenseCheckbox() {
                const isFastExpenseChecked = $('#fastExpenseCheckbox').is(':checked');
                const payeeSelect = $('#payeeSelect');
                
                if (isFastExpenseChecked) {
                    // If checked, remove required attribute
                    payeeSelect.removeAttr('required');
                } else {
                    // If unchecked, make it required again
                    payeeSelect.attr('required', 'required');
                }
            }
            
            // Function to populate payee options based on salary checkbox and branch
            function updatePayeeOptions() {
                const isSalaryChecked = $('#salaryCheckbox').is(':checked');
                const branchId = $('#branchSelect').val();
                const payeeSelect = $('#payeeSelect');
                const currentPayee = '{{ $item ? $item->payee : "" }}';
                
                // Clear existing options
                payeeSelect.empty();
                payeeSelect.append('<option value="">{{ __('field.select_payee') }}</option>');
                payeeSelect.append('<option value="no_payee">{{ __('field.no_payee') }}</option>');
                
                if (isSalaryChecked) {
                    // If salary is checked, show workers from selected branch
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
                } else {
                    // If salary is not checked, show all suppliers
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
                }
                
                // Select current payee if it's "no_payee"
                if (currentPayee === 'no_payee') {
                    payeeSelect.find('option[value="no_payee"]').attr('selected', 'selected');
                }
                
                // Reinitialize Select2 with placeholder
                try {
                    payeeSelect.select2('destroy');
                    payeeSelect.select2({
                        placeholder: "{{ __('field.select_payee') }}",
                        allowClear: true,
                        dropdownParent: payeeSelect.parent()
                    });
                } catch (e) {
                    // Select2 not available, using regular select
                }
            }
            
            // Initialize form state
            handleSalaryCheckbox();
            updatePayeeOptions();
            handleFastExpenseCheckbox();
            handleExpenseTypeChange();
            handleDateRangeVisibility();
            
            // Listen for salary checkbox changes
            $('#salaryCheckbox').on('change', function() {
                handleSalaryCheckbox();
            });
            
            // Listen for fast expense checkbox changes
            $('#fastExpenseCheckbox').on('change', function() {
                handleFastExpenseCheckbox();
            });
            
            // Listen for branch changes
            $('#branchSelect').on('change', function() {
                updatePayeeOptions();
            });
            
            // Function to handle expense type changes
            function handleExpenseTypeChange() {
                const expenseType = $('#expenseNameField').val();
                const payeeSelect = $('#payeeSelect');
                
                if (expenseType === 'utilities' || expenseType === 'local') {
                    // Ensure "no_payee" option exists
                    if (payeeSelect.find('option[value="no_payee"]').length === 0) {
                        payeeSelect.prepend('<option value="no_payee">{{ __('field.no_payee') }}</option>');
                    }
                    
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
                } else {
                    // Re-enable the payee dropdown
                    payeeSelect.prop('disabled', false);
                    payeeSelect.attr('required', 'required');
                    
                    // Re-enable Select2 if it's initialized
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
                        // Select2 not available
                    }
                    
                    // Update payee options when re-enabled (unless it's salary)
                    if (!$('#salaryCheckbox').is(':checked')) {
                        updatePayeeOptions();
                    }
                }
                
                // Update date range visibility
                handleDateRangeVisibility();
            }
            
            // Listen for expense type changes
            $('#expenseNameField').on('change', function() {
                handleExpenseTypeChange();
            });
            
            // Initialize expense type handling on page load
            handleExpenseTypeChange();
            
            // If branch is pre-selected and disabled, trigger updatePayeeOptions on load
            @if($userBranchId)
                updatePayeeOptions();
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
                    notes: $('textarea[name="notes"]').val(),
                    is_salary: $('#salaryCheckbox').is(':checked'),
                    is_fast_expense: $('#fastExpenseCheckbox').is(':checked')
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
                    if (data.is_salary) {
                        $('#salaryCheckbox').prop('checked', true).trigger('change');
                    }
                    if (data.is_fast_expense) {
                        $('#fastExpenseCheckbox').prop('checked', true).trigger('change');
                    }
                    if (data.expense_name) $('#expenseNameField').val(data.expense_name);
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
