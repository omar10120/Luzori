<?php
namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ExpensesProviderDataTable extends DataTable
{
    private $model = 'ExpensesProvider';
    private $plural = 'expenses';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'edit' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                $html = view()->make('_partials.center_actions', compact('id', 'route', 'options', 'model'))->render();
                return $html;
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('Y-m-d') : '';
            })
            ->editColumn('end_date', function ($row) {
                return $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('Y-m-d') : '';
            })
            ->editColumn('amount', function ($row) {
                return number_format($row->amount, 0) . ' SAR';
            })
            ->editColumn('date', function ($row) {
                return $row->date ? \Carbon\Carbon::parse($row->date)->format('Y-m-d') : '';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d H:i') : '';
            })
            ->editColumn('receipt_image', function ($row) {
                if ($row->receipt_image) {
                    return '<img src="' . $row->receipt_image_url . '" alt="Receipt" class="img-thumbnail receipt-thumbnail" style="width: 50px; height: 50px; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#receiptModal" data-receipt-url="' . $row->receipt_image_url . '">';
                }
                return '<img src="https://via.placeholder.com/50x50" alt="No Receipt" class="img-thumbnail" style="width: 50px; height: 50px;">';
            })
            ->editColumn('worker_description', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->worker_description ?? '');
            })
            ->editColumn('notes', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->notes ?? '');
            })
            ->rawColumns(['start_date', 'end_date', 'receipt_image', 'notes', 'action', 'worker_description'], true)
            ->setRowId('id');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn btn-sm mx-1 px-2';
        $addRoute = route('center_user.expenses.create');

        return $this->builder()
            ->setTableId('expenses_providers-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(10, 'desc')
            ->responsive(true)
            ->dom('
                <"card-header border-bottom p-3 d-flex flex-column flex-md-row justify-content-between align-items-center"
                    <"head-label"><"dt-action-buttons d-flex flex-column flex-md-row gap-2"B>
                >
                <"d-flex justify-content-between align-items-center mx-0 row"
                    <"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"fr>
                >
                <"table-responsive"t>
                <"d-flex justify-content-between mx-0 row"
                    <"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>
                >
            ')
            ->selectStyleSingle()
            ->addAction(['printable' => false, 'exportable' => false, 'className' => 'dt-center', 'title' => __('general.actions')])
            ->buttons([
                Button::make('colvis')->addClass($buttonClass . ' btn-warning')->text(__('general.column_visibility')),
                [
                    'extend' => 'collection',
                    'text' => __('general.export'),  'className' => $buttonClass,
                    'className' => $buttonClass . ' btn-secondary',
                    'buttons' => [
                        'excel',
                        'csv',
                        'pdf',
                        'print',
                        'copy',
                    ],
                ],
            ])
            ->language($this->getDataTableLanguageUrl())
            ->addTableClass('table table-bordered table-hover')
            ->initComplete('function () {
                $(".dt-action-buttons").append("<a href=' . $addRoute . ' class=\"btn btn-primary btn-sm mx-1 px-2\">' . __('general.add_new') . '<i class=\"ti ti-plus ms-1\"></i></a>");
                
                // Add filter controls
                var filterHtml = `
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small">Branch:</label>
                                    <select id="branchFilter" class="form-select form-select-sm">
                                        <option value="">All Branches</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Date:</label>
                                    <input type="date" id="dateFilter" class="form-control form-control-sm" placeholder="Select date">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Expense:</label>
                                    <select id="nameFilter" class="form-select form-select-sm">
                                        <option value="">All Expenses</option>
                                        <option value="Salary">Salary</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm">
                                        <i class="ti ti-x"></i> Clear
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $(".dt-action-buttons").before(filterHtml);
                
                // Add receipt modal
                var modalHtml = `
                    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="receiptModalLabel">Receipt Image</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img id="receiptModalImage" src="" alt="Receipt" class="img-fluid" style="max-width: 80%; max-height: 80vh; object-fit: contain;">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                $("body").append(modalHtml);
                
                // Populate filter dropdowns from table data
                var table = $("#expenses_providers-table").DataTable();
                
                // Wait for data to load, then populate filters
                setTimeout(function() {
                    // Populate branch filter
                    var branchData = table.column(5).data().unique().sort();
                    var branchSelect = $("#branchFilter");
                    branchData.each(function(branch) {
                        if (branch && branch.trim() !== "") {
                            branchSelect.append("<option value=\"" + branch + "\">" + branch + "</option>");
                        }
                    });
                    
                    // Populate expense name filter
                    var expenseData = table.column(1).data().unique().sort();
                    var expenseSelect = $("#nameFilter");
                    expenseData.each(function(expense) {
                        if (expense && expense.trim() !== "") {
                            expenseSelect.append("<option value=\"" + expense + "\">" + expense + "</option>");
                        }
                    });
                }, 1000);
                
                // Store the table reference
                var expensesTable = $("#expenses_providers-table").DataTable();
                
                // Clear any existing search functions
                $.fn.dataTable.ext.search = [];
                
                // Custom search function for this table only
                var customSearch = function(settings, data, dataIndex) {
                    // Only apply to expenses table
                    if (settings.nTable.id !== "expenses_providers-table") {
                        return true;
                    }
                    
                    var branchFilter = $("#branchFilter").val();
                    var dateFilter = $("#dateFilter").val();
                    var nameFilter = $("#nameFilter").val();
                    
                    // Debug logging
                    console.log("Custom search - Branch:", branchFilter, "Date:", dateFilter, "Name:", nameFilter);
                    console.log("Data row:", data);
                    
                    // Check if any filters are active
                    if (!branchFilter && !dateFilter && !nameFilter) {
                        return true;
                    }
                    
                    // data[5] is branch.name column
                    var branchMatch = !branchFilter || (data[5] && data[5].toLowerCase().includes(branchFilter.toLowerCase()));
                    // data[3] is date column
                    var dateMatch = !dateFilter || (data[3] && data[3].includes(dateFilter));
                    // data[1] is expense_name column
                    var nameMatch = !nameFilter || (data[1] && data[1].toLowerCase().includes(nameFilter.toLowerCase()));
                    
                    console.log("Matches - Branch:", branchMatch, "Date:", dateMatch, "Name:", nameMatch);
                    console.log("Final result:", branchMatch && dateMatch && nameMatch);
                    
                    return branchMatch && dateMatch && nameMatch;
                };
                
                // Add the custom search function
                $.fn.dataTable.ext.search.push(customSearch);
                
                // Filter change handlers
                $("#branchFilter, #dateFilter, #nameFilter").on("change", function() {
                    console.log("Filter changed, redrawing table...");
                    console.log("Branch:", $("#branchFilter").val(), "Date:", $("#dateFilter").val(), "Name:", $("#nameFilter").val());
                    expensesTable.draw();
                });
                
                // Receipt modal functionality
                $(document).on(\'click\', \'.receipt-thumbnail\', function() {
                    var receiptUrl = $(this).data(\'receipt-url\');
                    $(\'#receiptModalImage\').attr(\'src\', receiptUrl);
                });
                
                
                // Clear all filters
                $("#clearFilters").on("click", function() {
                    // Reset filter inputs
                    $("#branchFilter").val("");
                    $("#dateFilter").val("");
                    $("#nameFilter").val("");
                    
                    // Redraw table to apply cleared filters
                    expensesTable.draw();
                    
                    // Show success message
                    $(this).html("<i class=\"ti ti-check\"></i> Cleared!").removeClass("btn-outline-secondary").addClass("btn-success");
                    
                    // Reset button after 2 seconds
                    setTimeout(function() {
                        $("#clearFilters").html("<i class=\"ti ti-x\"></i> Clear").removeClass("btn-success").addClass("btn-outline-secondary");
                    }, 2000);
                });
            }')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == "ar" ?
        "https://cdn.datatables.net/plug-ins/2.0.7/i18n/ar.json" :
        "https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json";
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(true)->title('#'),
            Column::make('expense_name')->searchable(true)->title(__('field.expense_name')),
            Column::make('payee')->searchable(true)->title(__('field.payee')),
            Column::make('date')->searchable(true)->title(__('field.date')),
            Column::make('branch.name')->searchable(false)->title(__('field.branch')),
            Column::make('start_date')->searchable(true)->title(__('field.start_date')),
            Column::make('end_date')->searchable(true)->title(__('field.end_date')),
            Column::computed('amount')->searchable(false)->title(__('field.amount')),
            Column::computed('receipt_image')->searchable(false)->title(__('field.receipt')),
            Column::computed('notes')->searchable(false)->title(__('field.notes')),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
        ];
    }

    public function query(Expense $model): QueryBuilder
    {
        return $model->newQuery()->with(['branch', 'supplier']);
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
