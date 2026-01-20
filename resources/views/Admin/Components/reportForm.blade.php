<div class="row">
    <div class="col-md-3">
        <div class="mb-1">
            <label for="year" class="form-label">{{ __('field.year') }}</label>
            <select class="form-control" name="year" id="year">
                <option value="2024">2024</option>
                <option value="2023">2023</option>
                <option value="2022">2022</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-1">
            <label for="month" class="form-label">{{ __('field.month') }}</label>
            <select class="form-control" name="month" id="month">
                @foreach (App\Enums\MonthEnum::cases() as $month)
                    <option value="{{ $month->value }}">{{ $month->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-1">
            <label for="branch" class="form-label">{{ __('field.branch') }}</label>
            <select class="form-control" name="branch" id="branch">
                <option value="">{{ __('field.all_branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="mt-4">
            <button type="submit" class="btn btn-primary mx-2" id="viewButtonn">{{ __('field.view') }}</button>
            <button type="button" class="btn-success" id="pdf" hidden>PDF</button>
        </div>
    </div>
</div>

@section('page-script')
    <script>
        $(document).ready(function() {
            $("#dailyForm").on("submit", function(event) {
                event.preventDefault();
                let dataReport = {};

                var date = $('#date').val();
                var branch = $('#branch').val();

                dataReport.date = date;
                dataReport.branch = branch;

                console.log(dataReport);

                let reportTable = ``
                $('#reportTable').html(reportTable);
                if (date) {
                    reportTable +=
                        `<hr />
                       <table class="table table-bordered mb-4">
                           <thead>
                               <tr>
                               <th class="fw-bolder" scope="col" colspan="16">DAILY REPORT FOR : ${date}</th>
                               </tr>
                           </thead>
                           <tbody>
                               <tr>
                               @foreach ($branches as $branch)
                                   <td colspan="">Name</td>
                               @endforeach
                               </tr>
                               <tr>
                               @foreach ($branches as $branch)
                                   <td colspan="">AED 0.00</td>
                               @endforeach
                               </tr>
                               <tr>
                                   <th class="fw-bolder" scope="col"  colspan="16">{{ __('field.commission') }}</th>
                               </tr>
                               <tr>
                               @foreach ($branches as $branch)
                                   <td colspan="">0.00</td>
                               @endforeach
                               </tr>
                               <tr>
                                   <th class="fw-bolder" scope="col"  colspan="16">{{ __('field.tips') }}</th>
                               </tr>
                               <tr>
                               @foreach ($branches as $branch)
                                   <td colspan="">0.00</td>
                               @endforeach
                               </tr>
                           </tbody>
                       </table>`;
                    $('#reportTable').html(reportTable);
                    $("#pdf").addClass('btn');
                }
            });

            $("#salesForm").on("submit", function(event) {
                event.preventDefault();
                let dataReport = {};

                var year = $('#year').val();
                var month = $('#month').val();
                var branch = $('#branch').val();

                dataReport.year = year;
                dataReport.month = month;
                dataReport.branch = branch;

                console.log(dataReport);

                let reportTable = ``
                $('#reportTable').html(reportTable);
                if (year && month) {
                    reportTable +=
                        `<hr />
                        <table class="table table-bordered mb-4">
                            <thead>
                                <tr>
                                  <th class="fw-bolder" scope="col">Date</th>
                                  <th class="fw-bolder" scope="col">Service Cash</th>
                                  <th class="fw-bolder" scope="col">Service Visa</th>
                                  <th class="fw-bolder" scope="col">Sales Cash</th>
                                  <th class="fw-bolder" scope="col">Sales Visa</th>
                                  <th class="fw-bolder" scope="col">Sales Cash(CP)</th>
                                  <th class="fw-bolder" scope="col">Sales Visa(CP)</th>
                                  <th class="fw-bolder" scope="col">Free</th>
                                  <th class="fw-bolder" scope="col">Coupon</th>
                                  <th class="fw-bolder" scope="col">Tips Visa</th>
                                  <th class="fw-bolder" scope="col">Commission</th>
                                  <th class="fw-bolder" scope="col">Total Without Free</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>`;
                    $('#reportTable').html(reportTable);
                    $("#pdf").addClass('btn');
                }
            });

            $("#staffForm").on("submit", function(event) {
                event.preventDefault();
                let dataReport = {};

                var year = $('#year').val();
                var month = $('#month').val();
                var branch = $('#branch').val();

                dataReport.year = year;
                dataReport.month = month;
                dataReport.branch = branch;

                console.log(dataReport);

                let reportTable = ``
                $('#reportTable').html(reportTable);
                if (year && month) {
                    reportTable +=
                        `<hr />
                       <table class="table table-bordered mb-4">
                           <thead>
                               <tr>
                                 <th class="fw-bolder" scope="col">Commission</th>
                                 <th class="fw-bolder" scope="col">Total Without Free</th>
                               </tr>
                           </thead>
                           <tbody>
                           </tbody>
                       </table>`;
                    $('#reportTable').html(reportTable);
                    $("#pdf").addClass('btn');
                }


            });
        });
    </script>
@endsection
