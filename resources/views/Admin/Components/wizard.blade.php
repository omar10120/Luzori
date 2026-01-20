<script>
    $(document).ready(function() {

        let ids = {};
        let wizardData = {};

        $('#services').on('change', function() {
            $('#services').val().length === 0 ? $('#nextStep1').prop('disabled', true) : $('#nextStep1')
                .prop('disabled', false)
        });

        $('#payment_type').on('change', function() {
            if ($('#payment_type').val() === '' || $('#name').val() === '' || $('#mobile').val() ===
                '') {
                $('#nextStep3').prop('disabled', true)
            } else {
                $('#nextStep3').prop('disabled', false)
            }
        });

        $("#name").bind('keyup mouseup', function() {
            if ($('#payment_type').val() === '' || $('#name').val() === '' || $('#mobile').val() ===
                '') {
                $('#nextStep3').prop('disabled', true)
            } else {
                $('#nextStep3').prop('disabled', false)
            }
        });

        $("#mobile").bind('keyup mouseup', function() {
            if ($('#payment_type').val() === '' || $('#name').val() === '' || $('#mobile').val() ===
                '') {
                $('#nextStep3').prop('disabled', true)
            } else {
                $('#nextStep3').prop('disabled', false)
            }
        });

        $('#nextStep1').on('click', function(e) {
            e.preventDefault();

            let services = $('#services').val();
            if (!services || services.length === 0) {
                alert('Please select at least one service.');
                return false;
            }

            ids = services;

            let servicesArray = [];
            services.forEach(service => {
                var name = $('#services').find('option[value="' + service + '"]').text();
                var serviceInfo = {
                    id: service,
                    name: name
                };
                servicesArray.push(serviceInfo);
            });

            $('#service-container').empty();
            servicesArray.forEach(service => {
                var workers = get_workers(service.id);
                var service_info = `
                    <div class="row mb-4">
                        <h2>${service.name}</h2>
                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">{{ __('field.date') }}</label>
                                <input type="date" class="form-control" name="service[${service.id}][date]"
                                    value="{{ Carbon\Carbon::now()->toDateString() }}" />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">{{ __('field.worker') }}</label>
                                <select class="form-control" name="service[${service.id}][worker_id]" id="emoloyee_id">`;
                $.each(workers, function(index, worker) {
                    service_info +=
                        `<option value="${worker.id}">${worker.name}</option>`;
                });
                service_info += `</select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1">
                                <label class="form-label">{{ __('field.from') }}</label>
                                <input type="time" class="form-control" name="service[${service.id}][from_time]"
                                    value="{{ Carbon\Carbon::now()->format('H:i') }}" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1">
                                <label class="form-label">{{ __('field.to') }}</label>
                                <input type="time" class="form-control" name="service[${service.id}][to_time]"
                                    value="{{ Carbon\Carbon::now()->addHour()->format('H:i') }}" />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1">
                                <label class="form-label">{{ __('field.commission') }}</label>
                                <select class="form-control" name="service[${service.id}][commission]">
                                    @for ($i = 1; $i <= 100; $i++)
                                        <option value="{{ $i }}">{{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>`;

                $('#service-container').append(service_info);
            });
        });

        $('#nextStep2').on('click', function(e) {
            e.preventDefault();

            var servicesArray = [];
            let isValid = true;

            ids.forEach(service => {
                var date = $('input[name="service[' + service + '][date]"]').val();
                var worker_id = $('select[name="service[' + service + '][worker_id]"]')
                    .val();
                var from_time = $('input[name="service[' + service + '][from_time]"]').val();
                var to_time = $('input[name="service[' + service + '][to_time]"]').val();
                var commission = $('select[name="service[' + service + '][commission]"]').val();

                if (!date || !worker_id || !from_time || !to_time || !commission) {
                    alert('Please fill all fields for each service.');
                    isValid = false;
                    return false;
                }

                var serviceInfo = {
                    id: service,
                    name: $('#services').find('option[value="' + service + '"]').text(),
                    date: date,
                    worker_id: worker_id,
                    from_time: from_time,
                    to_time: to_time,
                    commission: commission
                };

                servicesArray.push(serviceInfo);
            });

            if (!isValid) return;

            wizardData.services = servicesArray;
        });

        $('#nextStep3').on('click', function(e) {
            e.preventDefault();
            var name = $('#name').val();
            var mobile = $('#mobile').val();
            var discount_id = $('#discount_id').val();

            var payment_type = $('#payment_type').val();

            if (!name || !mobile || !payment_type) {
                alert('Please fill all the required fields.');
                return false;
            }

            wizardData.name = name;
            wizardData.mobile = mobile;

            var discount_name = $('input[name="discount_id"]:checked').parent().find(
                '.form-check-label').html();
            wizardData.discount = {
                id: discount_id,
                type: $('input[name="discount_id"]:checked').attr('id'),
                name: discount_name
            }
            wizardData.payment_type = {
                id: payment_type,
                name: $('#payment_type option:selected').text(),
            }

            let reviewHtml =
                `<table class="table table-bordered">
                    <thead>
                        <tr>
                        <th class="fw-bolder" scope="col">{{__('field.services')}}</th>
                        <th class="fw-bolder" scope="col">{{__('field.price')}}</th>
                        <th class="fw-bolder" scope="col">{{__('field.date')}}</th>
                        <th class="fw-bolder" scope="col">{{__('field.worker')}}</th>
                        <th class="fw-bolder" scope="col">{{__('field.from')}}</th>
                        <th class="fw-bolder" scope="col">{{__('field.to')}}</th>
                        </tr>
                    </thead>
                    <tbody>`
            $.each(wizardData.services, function(index, item) {
                worker = get_worker(item.worker_id);
                service = get_service(item.id);
                reviewHtml +=
                    `<tr>
                        <td>${item.name}</td>
                        <td>${service.price}</td>
                        <td>${item.date}</td>
                        <td>${worker.name}</td>
                        <td>${item.from_time}</td>
                        <td>${item.to_time}</td>
                    </tr>`;
            });
            reviewHtml +=
                `<tr>
                    <th class="fw-bolder" scope="row">{{__('field.full_name')}}</th>
                    <td colspan="5">${wizardData.name}</td>
                </tr>
                <tr>
                    <th class="fw-bolder" scope="row">{{__('field.mobile')}}</th>
                    <td colspan="5">${wizardData.mobile}</td>
                </tr>`;
            if (wizardData.discount.id != '') {
                if (wizardData.discount.type.includes("discount")) {
                    reviewHtml += `
                            <tr>
                                <th class="fw-bolder" scope="row">{{__('field.discount_code')}}</th>
                                <td colspan="5">${wizardData.discount.name}</td>
                            </tr>`;
                } else if (wizardData.discount.type.includes("wallets")) {
                    reviewHtml += `
                            <tr>
                                <th class="fw-bolder" scope="row">Wallet</th>
                                <td colspan="5">${wizardData.discount.name}</td>
                            </tr>`;
                } else {
                    reviewHtml += `
                            <tr>
                                <th class="fw-bolder" scope="row">MemberShip Cards</th>
                                <td colspan="5">${wizardData.discount.name}</td>
                            </tr>`;
                }
            }
            reviewHtml += `<tr>
                                <th class="fw-bolder" scope="row">{{__('field.payment_method')}}</th>
                                <td colspan="5">${wizardData.payment_type.name}</td>
                            </tr></tbody></table>`

            $('#review-content').html(reviewHtml);
        });

        $('#checkButton').on('click', function(e) {
            e.preventDefault();
            var response = get_services($('#mobile').val());
            if (response.status) {
                var user = response.user;
                $('#name').val(user.name);

                if (response.services) {
                    var services = response.services;
                    let servicesTable = ``
                    $('#servicesTable').html(servicesTable);
                    if (services.length != 0) {
                        servicesTable +=
                            `<hr />
                            <h5>User Services</h5>
                            <table class="table table-bordered mb-4">
                                <thead>
                                    <tr>
                                    <th class="fw-bolder" scope="col">{{__('field.services')}}</th>
                                    <th class="fw-bolder" scope="col">1</th>
                                    <th class="fw-bolder" scope="col">2</th>
                                    <th class="fw-bolder" scope="col">3</th>
                                    <th class="fw-bolder" scope="col">4</th>
                                    <th class="fw-bolder" scope="col">5</th>
                                    <th class="fw-bolder" scope="col">{{__('field.free')}}</th>
                                    <th class="fw-bolder" scope="col">{{__('field.more_than')}} 5</th>
                                    </tr>
                                </thead>
                                <tbody>`

                        $.each(services, function(index, item) {
                            var service = item[0].service;

                            servicesTable += `<td>${service.name}</td>`
                            if (item.length <= 5) {
                                for (let i = 1; i <= item.length; i++) {
                                    servicesTable +=
                                        `<td style='background: #2ff92f5e'>Yes</td>`
                                }
                                for (let i = 1; i <= 5 - item.length; i++) {
                                    servicesTable += `<td>No</td>`
                                }
                                servicesTable += `<td>No</td><td>No</td>`
                            } else {
                                for (let i = 1; i <= 5; i++) {
                                    servicesTable +=
                                        `<td style='background: #2ff92f5e'>Yes</td>`
                                }
                                servicesTable += `<td>No</td><td>${item.length}</td>`
                            }
                            servicesTable += `</tr>`
                        });
                        servicesTable +=
                            `</tbody>
                            </table>`;

                        $('#servicesTable').html(servicesTable);
                    }
                }

                if (response.wallets) {
                    var wallets = response.wallets;
                    let walletsElement = ``;
                    $('#walletsElement').html(walletsElement);
                    if (wallets.length != 0) {
                        walletsElement += `<hr /><h5>Wallet</h5>`;

                        walletsElement += `<div class="row">`;
                        $.each(wallets, function(index, item) {
                            var wallet = item.wallet;
                            walletsElement += `<div class="col-md-4">
                                                    <div class="form-check" style="width: 200px;padding: 10px;color: #fff;
                                                                                    background-color: #428bca;
                                                                                    border-color: #357ebd;text-align: center;
                                                                                    display: flex;justify-content: space-between;font-size: 14px;">
                                                        <label class="form-check-label" for="wallets${wallet.id}">
                                                            ${wallet.code + ' [' + wallet.amount + ' AED]'}
                                                        </label>
                                                        <input class="form-check-input" type="radio" name="discount_id" value="${wallet.id}" id="wallets${wallet.id}">
                                                    </div>
                                                </div>`;
                        });

                        walletsElement += `</div>`;
                        $('#walletsElement').html(walletsElement);
                    }
                }

                if (response.memberships) {
                    var memberships = response.memberships;
                    let membershipsElement = ``;
                    $('#membershipsElement').html(membershipsElement);
                    if (memberships.length != 0) {
                        membershipsElement += `<hr /><h5>MemberShip Cards</h5>`;

                        membershipsElement += `<div class="row">`;
                        $.each(memberships, function(index, item) {

                            membershipsElement += `<div class="col-md-4">
                                                    <div class="form-check" style="width: 200px;padding: 10px;color: #fff;
                                                                                    background-color: #428bca;
                                                                                    border-color: #357ebd;text-align: center;
                                                                                    display: flex;justify-content: space-between;font-size: 14px;">
                                                        <label class="form-check-label" for="memberships${item.id}">
                                                            ${item.membership_no + ' [' + item.percent + '%]'}
                                                        </label>
                                                        <input class="form-check-input" type="radio" name="discount_id" value="${item.id}" id="memberships${item.id}">
                                                    </div>
                                                </div>`;
                        });

                        membershipsElement += `</div>`;
                        $('#membershipsElement').html(membershipsElement);
                    }
                }

            } else {
                $('#name').val('');
                $('#servicesTable').html('');
                $('#walletsElement').html('');
                $('#membershipsElement').html('');
                alert('No wallet and member ship found.');
            }
        });

        function get_service(service_id) {
            var service;
            $.ajax({
                url: "{{ route('admin.services.info') }}",
                method: 'GET',
                async: false,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    service_id: service_id,
                },
                success: function(response) {
                    service = response;
                }
            });
            return service;
        }

        function get_services(user_phone) {
            var services = [];
            $.ajax({
                url: "{{ route('admin.bookings.get-services-by-user') }}",
                method: 'GET',
                async: false,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    user_phone: user_phone,
                },
                success: function(response) {
                    services = response;
                }
            });
            return services;
        }

        function get_worker(worker_id) {
            var worker = '';
            $.ajax({
                url: "{{ route('admin.workers.info') }}",
                method: 'GET',
                async: false,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    worker_id: worker_id,
                },
                success: function(response) {
                    worker = response;
                }
            });
            return worker;
        }

        function get_workers(service_id) {
            var workers = [];
            $.ajax({
                url: "{{ route('admin.workers.get-workers-by-service') }}",
                method: 'GET',
                async: false,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    service_id: service_id,
                },
                success: function(response) {
                    workers = response;
                }
            });
            return workers;
        }
    });
</script>
