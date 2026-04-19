<script>
    $(document).ready(function() {
        // POS Manager Object
        const POS = {
            cart: @json($cart['items'] ?? []),
            clientId: @json($cart['client_id'] ?? null),
            tax: 0,
            tip: 0,
            wizardData: {
                services: [],
                packages: [],
                user_package_ids: [],
                name: null,
                mobile: null
            },
            
            // Recalculate cart from backend
            calculate: function() {
                const self = this;
                $.ajax({
                    url: '{{ route("center_user.sales.calculate-cart") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cart: self.cart,
                        client_id: self.clientId,
                        tax: self.tax,
                        tip: self.tip
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#cart-summary-container').html(response.html);
                            const meta = $('#recalculation-totals');
                            if (meta.length) {
                                $('#cart-subtotal').text(meta.data('subtotal'));
                                $('#cart-tax').text(meta.data('tax'));
                                $('#cart-total').text(meta.data('total'));
                                self.updateBadges(meta);
                            }
                            self.validateCheckout();
                        }
                    }
                });
            },
            
            updateBadges: function(meta) {
                const bCount = meta.data('booking-count');
                const pCount = meta.data('product-count');
                const wCount = meta.data('wallet-count');
                $('#cart-booking-count').text(bCount).toggle(bCount > 0);
                $('#cart-products-count').text(pCount).toggle(pCount > 0);
                $('#cart-user-wallet-count').text(wCount).toggle(wCount > 0);
            },
            
            validateCheckout: function() {
                const canContinue = this.clientId && (this.cart.length > 0 || this.wizardData.services.length > 0 || this.wizardData.packages.length > 0);
                $('#continueToPayment').prop('disabled', !canContinue);
            },

            calculateWizardReview: function(callback) {
                const self = this;
                const draftItems = [];
                
                if (self.wizardData.services.length > 0) {
                    draftItems.push({
                        type: 'service',
                        services: self.wizardData.services,
                        user_package_ids: self.wizardData.user_package_ids || [],
                        discount_id: self.wizardData.discount_id || null,
                        membership_id: self.wizardData.membership_id || null,
                        wallet_id: self.wizardData.wallet_id || null
                    });
                }
                
                if (self.wizardData.packages.length > 0) {
                    self.wizardData.packages.forEach(pkg => {
                        draftItems.push({ type: 'package', id: pkg.id, name: pkg.name, price: pkg.price });
                    });
                }

                $.ajax({
                    url: '{{ route("center_user.sales.calculate-cart") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        cart: draftItems,
                        client_id: self.clientId,
                        wizard_review: true,
                        client_name: self.wizardData.name,
                        client_mobile: self.wizardData.mobile,
                        payment_method_display: self.wizardData.payment_method_display
                    },
                    success: function(response) {
                        if (response.status) {
                            $('#booking-review-content').html(response.reviewHtml);
                            if (callback) callback();
                        }
                    }
                });
            },
            
            saveToSession: function() {
                $.ajax({
                    url: '{{ route("center_user.sales.cart") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', cart: this.cart, client_id: this.clientId }
                });
            },

            // Customer Management
            selectCustomer: function(userId, name, phone, email, image, branchId) {
                const self = this;
                self.clientId = userId;
                self.wizardData.name = name;
                self.wizardData.mobile = phone;
                
                $.ajax({
                    url: '{{ route("center_user.sales.cart") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', cart: self.cart, client_id: userId },
                    success: function() {
                        self.updateCustomerUI(userId, name, phone, email, image);
                        if (branchId) self.updateWorkersByBranch(branchId);
                        $('#selectCustomerModal').modal('hide');
                        
                        $('#booking-step3-customer-name').text(name);
                        $('#booking-step3-customer-mobile').text(phone || '{{ __("field.no_mobile") }}');
                        self.loadCustomerHistory(userId);
                        
                        if (typeof toastr !== 'undefined') toastr.success('{{ __("field.customer_selected") }}');
                    }
                });
            },

            removeCustomer: function() {
                const self = this;
                self.clientId = null;
                self.wizardData.name = null;
                self.wizardData.mobile = null;
                $.ajax({
                    url: '{{ route("center_user.sales.cart") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', cart: self.cart, client_id: null },
                    success: function() {
                        self.updateCustomerUI(null);
                        const defaultBranchId = {{ auth('center_user')->user()->branch_id ?? 'null' }};
                        if (defaultBranchId) {
                            self.updateWorkersByBranch(defaultBranchId);
                        }
                        $('#booking-packagesElement, #booking-walletsElement, #booking-membershipsElement').empty();
                        $('#booking-step3-customer-name, #booking-step3-customer-mobile').text('');
                        if (typeof validateStep3 === 'function') validateStep3();
                        if (typeof validateCheckout === 'function') self.validateCheckout();
                        if (typeof toastr !== 'undefined') toastr.info('{{ __("field.customer_removed") }}');
                    }
                });
            },

            updateCustomerUI: function(userId, name, phone, email, image) {
                if (userId) {
                    $('#selected-customer-display').show();
                    $('#no-customer-display').hide();
                    $('#selected-customer-display').html(`
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-lg me-3">
                                <img src="${image || '{{ asset('assets/img/avatars/1.png') }}'}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                            </div>
                            <div>
                                <h5 class="mb-0">${name}</h5>
                                ${email ? `<small class="text-muted d-block">${email}</small>` : ''}
                                <small class="text-muted d-block">${phone || ''}</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#selectCustomerModal">Change</button>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-remove-customer">Remove</button>
                        </div>
                    `);
                } else {
                    $('#selected-customer-display').hide();
                    $('#no-customer-display').show();
                }
                this.validateCheckout();
            },

            updateWorkersByBranch: function(branchId) {
                $.ajax({
                    url: '{{ route("center_user.workers.get-workers-by-branch") }}',
                    type: 'GET',
                    data: { branch_id: branchId },
                    success: function(workers) {
                        const centerUserName = '{{ $centerUser->name ?? "" }}';
                        ['#product-sales_worker', '#product-worker'].forEach(selector => {
                            const $select = $(selector);
                            const currentVal = $select.val();
                            $select.empty().append('<option value="">Select Worker</option>');
                            $.each(workers, function(i, w) {
                                let label = w.name + ' - ' + (w.phone || '');
                                if (w.is_center_user) label += ' (' + centerUserName + ')';
                                $select.append(new Option(label, w.id, false, w.id == currentVal));
                            });
                            $select.trigger('change');
                        });
                    }
                });
            },

            loadCustomerHistory: function(userId) {
                const self = this;
                if (!userId) return;
                
                $.ajax({
                    url: '{{ route("center_user.sales.get-customer-services") }}',
                    method: 'GET',
                    data: { user_id: userId },
                    success: function(response) {
                        if (response && response.status) {
                            // 1. Render Packages
                            if (response.packages && Array.isArray(response.packages) && response.packages.length > 0) {
                                let html = `<hr /><h5>{{ __("field.packages") }}</h5><div class="row g-2">`;
                                response.packages.forEach(up => {
                                    html += `
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check p-2 border rounded bg-primary text-white">
                                                <input class="form-check-input booking-package-checkbox" type="checkbox" name="user_package_ids" value="${up.id}" id="bp${up.id}">
                                                <label class="form-check-label ms-2" for="bp${up.id}">${up.package ? up.package.name : 'Package'}</label>
                                            </div>
                                        </div>`;
                                });
                                html += `</div>`;
                                $('#booking-packagesElement').html(html);
                            } else {
                                $('#booking-packagesElement').empty();
                            }

                            // 2. Render Wallets
                            if (response.wallets && Array.isArray(response.wallets) && response.wallets.length > 0) {
                                let html = `<hr /><h5>{{ __("field.wallets") }}</h5><div class="row g-2 mb-4">`;
                                response.wallets.forEach(w => {
                                    html += `
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                            <div class="form-check wallet-item" style="padding: 10px;color: #fff;background-color: #28a745;border-color: #218838;border-radius: 4px;min-height: 50px;display: flex;align-items: center;gap: 10px;font-size: 14px;width: 100%;">
                                                <label class="form-check-label flex-grow-1 text-start" for="booking-wallet${w.id}" style="word-break: break-word;white-space: normal;overflow: hidden;min-width: 0;margin: 0;">
                                                    ${w.wallet ? w.wallet.code : 'Wallet'} [${w.remaining_balance} {{ get_currency() }}]
                                                </label>
                                                <input class="form-check-input flex-shrink-0 booking-wallet-radio" type="radio" name="discount_id" value="${w.id}" id="booking-wallet${w.id}" style="margin-top: 0;width: 18px;height: 18px;flex-shrink: 0;">
                                            </div>
                                        </div>`;
                                });
                                html += `</div>`;
                                $('#booking-walletsElement').html(html);
                            } else {
                                $('#booking-walletsElement').empty();
                            }

                            // 3. Render Memberships
                            if (response.memberships && Array.isArray(response.memberships) && response.memberships.length > 0) {
                                let html = `<hr /><h5>{{ __("field.memberships") }}</h5><div class="row g-2 mb-4">`;
                                response.memberships.forEach(m => {
                                    html += `
                                        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                            <div class="form-check membership-item" style="padding: 10px;color: #fff;background-color: #ffc107;border-color: #e0a800;border-radius: 4px;min-height: 50px;display: flex;align-items: center;gap: 10px;font-size: 14px;width: 100%;">
                                                <label class="form-check-label flex-grow-1 text-start" for="booking-membership${m.id}" style="word-break: break-word;white-space: normal;overflow: hidden;min-width: 0;margin: 0; color: #000;">
                                                    ${m.membership_no} [${m.percent}%]
                                                </label>
                                                <input class="form-check-input flex-shrink-0 booking-membership-radio" type="radio" name="discount_id" value="${m.id}" id="booking-membership${m.id}" data-membership-percent="${m.percent}" data-membership-no="${m.membership_no}" style="margin-top: 0;width: 18px;height: 18px;flex-shrink: 0;">
                                            </div>
                                        </div>`;
                                });
                                html += `</div>`;
                                $('#booking-membershipsElement').html(html);
                            } else {
                                $('#booking-membershipsElement').empty();
                            }
                            if (typeof validateStep3 === 'function') validateStep3();
                        }
                    }
                });
            }
        };

        // Configuration and Data
        const posConfig = {
            hasCommissionPermission: {{ has_commission_permission() ? 'true' : 'false' }},
            allowedCommissionType: '{{ get_allowed_commission_type("booking") }}',
            currency: '{{ get_currency() }}',
            translations: {
                max_commission: '{{ __("field.max_commission") }}',
                commission_cannot_exceed: '{{ __("field.commission_cannot_exceed_service_price") }}',
                select_commission: '{{ __("field.select_commission") }}'
            }
        };

        let servicesData = {};
        @foreach ($services as $service)
            servicesData[{{ $service->id }}] = { 
                id: {{ $service->id }}, 
                name: '{{ addslashes($service->name) }}', 
                price: {{ $service->price ?? 0 }},
                has_commission: {{ $service->has_commission ? "true" : "false" }}
            };
        @endforeach

        let productsData = {};
        @foreach ($products as $product)
            @php $price = ($product->retail_price > 0) ? $product->retail_price : ($product->supply_price ?? 0); @endphp
            productsData[{{ $product->id }}] = { id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', price: {{ $price }} };
        @endforeach

        // Initialization
        function initPOS() {
            $('#booking-services, #product-products, #product-sales_worker, #product-worker, #modal-wallet-user').select2();
            
            $('#select-customer-dropdown').select2({
                dropdownParent: $('#selectCustomerModal'),
                placeholder: '{{ __("field.search_by_name_phone_or_email") }}',
                allowClear: true
            });

            $('#wizard_category_tree_jstree').on("select_node.jstree", function (e, data) {
                filterWizardServices(data.node.id);
            });

            POS.calculate(); 
            if (POS.clientId) {
                POS.loadCustomerHistory(POS.clientId);
            }
        }

        const bookingStepper = new Stepper(document.querySelector('.bs-stepper'));
        let $allServicesOptions = $('#booking-services').find('option').clone();

        function filterWizardServices(categoryId) {
            const $select = $('#booking-services');
            if (!categoryId) { $('#wizard-services-container').hide(); return; }
            $('#wizard-services-container').show();
            
            const current = $select.val() || [];
            $select.empty();
            $allServicesOptions.each(function() {
                const id = $(this).val();
                if (current.includes(id) || $(this).data('category-id') == categoryId) {
                    $select.append($(this).clone());
                }
            });
            $select.val(current).trigger('change');
        }

        function validateStep1() {
            const services = $('#booking-services').val() || [];
            const packages = $('#booking-packages').val() || [];
            $('#booking-nextStep1').prop('disabled', services.length === 0 && packages.length === 0);
        }

        function validateStep3() {
            const paymentType = $('#booking-payment_type').val();
            const isMultiple = $('#booking-multiple_payments_toggle').is(':checked');
            const hasCard = $('input[name="discount_id"]:checked').length > 0;
            const hasPackage = $('input[name="user_package_ids"]:checked').length > 0;
            
            let isValid = false;
            // If split payment is on, we allow proceeding to review where total calculation happens
            if (isMultiple) {
                isValid = true; 
            } else {
                // Either a direct payment method is selected, or a wallet/package is picked
                isValid = (paymentType && paymentType !== '') || hasCard || hasPackage;
            }
            
            $('#booking-nextStep3').prop('disabled', !isValid);
        }

        initPOS();

        // Event Listeners
        $('#booking-services, #booking-packages').on('change', validateStep1);
        $('#booking-payment_type, #booking-multiple_payments_toggle').on('change', validateStep3);
        $(document).on('change', 'input[name="discount_id"], input[name="user_package_ids"]', validateStep3);

        $('#select-customer-dropdown').on('change select2:select', function() {
            const $opt = $(this).find('option:selected');
            if ($opt.val()) {
                $('#selected-customer-info').show();
                $('#selected-customer-name').text($opt.data('name'));
                $('#confirm-select-customer').prop('disabled', false);
            }
        });

        $('#confirm-select-customer').on('click', function() {
            const $opt = $('#select-customer-dropdown').find('option:selected');
            POS.selectCustomer($opt.val(), $opt.data('name'), $opt.data('phone'), $opt.data('email'), $opt.data('image'), $opt.data('branch-id'));
        });

        $(document).on('click', '.btn-remove-customer', function() {
            POS.removeCustomer();
        });

        $(document).on('change', 'input[name="user_package_ids"]', function() {
            if ($('input[name="user_package_ids"]:checked').length > 0) {
                $('#booking-payment_type').val('package').trigger('change');
                $('#booking-payment-method-container').slideUp();
            } else {
                $('#booking-payment-method-container').slideDown();
            }
        });

        // Wizard Transitions
        $('#booking-nextStep1').on('click', function() {
            const services = $('#booking-services').val() || [];
            const packages = $('#booking-packages').val() || [];
            if (services.length === 0 && packages.length === 0) return;

            POS.wizardData.services = services.map(id => ({ 
                id: id, 
                name: servicesData[id].name, 
                price: servicesData[id].price,
                has_commission: servicesData[id].has_commission 
            }));
            POS.wizardData.packages = packages.map(id => {
                const opt = $('#booking-packages option[value="'+id+'"]');
                return { id: id, name: opt.text(), price: parseFloat(opt.data('price')) };
            });

            // Render Step 2
            $('#booking-service-container').empty();
            const now = new Date();
            const fromTimeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
            const toTimeStr = (now.getHours() + 1).toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

            POS.wizardData.services.forEach(s => {
                let commissionHtml = '';
                if (posConfig.hasCommissionPermission && posConfig.allowedCommissionType) {
                    commissionHtml = `
                        <div class="col-md-2">
                             <label class="form-label">{{ __('field.commission_type') }}</label>
                             <input type="hidden" name="service[${s.id}][commission_type]" value="${posConfig.allowedCommissionType}">
                             <select class="form-control" disabled>
                                 <option selected>${posConfig.allowedCommissionType === 'percentage' ? '{{ __("field.percentage") }}' : '{{ __("field.fixed_value") }}'}</option>
                             </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __('field.commission') }}</label>
                            ${posConfig.allowedCommissionType === 'percentage' ? `
                                <select class="form-control" name="service[${s.id}][commission]">
                                    <option value="">{{ __("field.select_commission") }}</option>
                                    ${Array.from({length: 100}, (_, i) => `<option value="${i+1}">${i+1}%</option>`).join('')}
                                </select>` : `
                                <input type="number" class="form-control" name="service[${s.id}][commission]" step="0.01" min="0" max="${s.price}">
                                <small class="text-muted">${posConfig.translations.max_commission}: ${s.price}</small>`}
                        </div>`;
                }

                const $row = $(`
                    <div class="row mb-4 border-bottom pb-3">
                        <div class="col-12"><h6>${s.name}</h6></div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __("field.date") }}</label>
                            <input type="date" class="form-control" name="service[${s.id}][date]" value="${now.toISOString().split('T')[0]}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __("field.worker") }}</label>
                            <select class="form-control worker-select" name="service[${s.id}][worker_id]" required></select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __("field.from") }}</label>
                            <input type="time" class="form-control" name="service[${s.id}][from_time]" value="${fromTimeStr}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">{{ __("field.to") }}</label>
                            <input type="time" class="form-control" name="service[${s.id}][to_time]" value="${toTimeStr}">
                        </div>
                        ${commissionHtml}
                    </div>
                `);
                
                const workers = get_workers(s.id);
                const $select = $row.find('.worker-select');
                $select.append('<option value="">Select Worker</option>');
                workers.forEach(w => { $select.append(new Option(`${w.name} - ${w.phone || ''}`, w.id)); });
                
                $('#booking-service-container').append($row);
            });
            bookingStepper.next();
        });

        $('#booking-nextStep2').on('click', function() {
            let isValid = true;
            POS.wizardData.services.forEach(s => {
                const $date = $(`input[name="service[${s.id}][date]"]`);
                const $worker = $(`select[name="service[${s.id}][worker_id]"]`);
                s.date = $date.val();
                s.worker_id = $worker.val();
                s.from_time = $(`input[name="service[${s.id}][from_time]"]`).val();
                s.to_time = $(`input[name="service[${s.id}][to_time]"]`).val();
                s.commission = $(`[name="service[${s.id}][commission]"]`).val();
                s.commission_type = $(`input[name="service[${s.id}][commission_type]"]`).val();
                
                if (!s.date || !s.worker_id) {
                    $date.toggleClass('is-invalid', !s.date);
                    $worker.toggleClass('is-invalid', !s.worker_id);
                    isValid = false;
                }
            });
            
            if (!isValid) return;
            if (!POS.clientId) { bookingStepper.to(0); if (typeof toastr !== 'undefined') toastr.warning('{{ __("field.customer_required") }}'); return; }
            
            $('#booking-step3-customer-name').text(POS.wizardData.name);
            $('#booking-step3-customer-mobile').text(POS.wizardData.mobile || 'No Mobile');
            POS.loadCustomerHistory(POS.clientId);
            bookingStepper.next();
        });

        $('#booking-nextStep3').on('click', function() {
            if (!POS.clientId) { bookingStepper.to(0); return; }
            const selectedPackageIds = [];
            $('input[name="user_package_ids"]:checked').each(function() { selectedPackageIds.push($(this).val()); });
            POS.wizardData.user_package_ids = selectedPackageIds;
            
            // Collect selected wallet/membership for the review
            const $wallet = $('input[name="discount_id"].booking-wallet-radio:checked');
            const $membership = $('input[name="discount_id"].booking-membership-radio:checked');
            
            POS.wizardData.wallet_id = $wallet.val() || null;
            POS.wizardData.membership_id = $membership.val() || null;
            
            let paymentDisplay = $('#booking-payment_type').val() || 'Package';
            if ($wallet.length) paymentDisplay = 'Wallet: ' + $wallet.closest('.wallet-item').find('label').text().trim();
            else if ($membership.length) paymentDisplay = 'Membership: ' + $membership.closest('.membership-item').find('label').text().trim();
            
            POS.wizardData.payment_method_display = paymentDisplay;
            
            POS.calculateWizardReview(() => bookingStepper.next());
        });

        $('#addBookingToCart').on('click', function() {
            POS.cart.push({ type: 'service', ...POS.wizardData });
            POS.saveToSession();
            POS.calculate();
            bookingStepper.to(1);
            if (typeof toastr !== 'undefined') toastr.success('Added to cart');
        });

        $('#addProductBtn').on('click', function() {
            const ids = $('#product-products').val();
            if (!ids) return;
            ids.forEach(id => {
                POS.cart.push({ type: 'product', id: id, name: productsData[id].name, price: productsData[id].price, quantity: 1 });
            });
            POS.saveToSession();
            POS.calculate();
        });

        $(document).on('click', '.remove-item', function() {
            POS.cart.splice($(this).data('index'), 1);
            POS.saveToSession();
            POS.calculate();
        });

        $('#continueToPayment').on('click', function() {
            POS.saveToSession();
            window.location.href = '{{ route("center_user.sales.payment") }}';
        });
        
        validateStep1();
        validateStep3();
    });

    function get_workers(service_id) {
        let workers = [];
        $.ajax({
            url: "{{ route('center_user.workers.get-workers-by-service') }}",
            method: 'GET', async: false,
            data: { service_id: service_id },
            success: (res) => { workers = res; }
        });
        return workers;
    }
</script>
