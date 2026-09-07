<?php

namespace App\Datatables\CenterUser;

use App\Models\InventoryMovement;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InventoryMovementDataTable extends DataTable
{
    private $model = 'InventoryMovement';
    private $plural = 'inventorymovements';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $url = route('center_user.inventorymovements.show', ['productId' => $item->id]);
                return '<a href="' . $url . '" class="btn btn-sm btn-success" title="' . e(__('general.show')) . '"><i class="ti ti-eye"></i></a>';
            })
            ->editColumn('select', function ($row) {
                return '<input type="checkbox" class="form-check-input product-select" value="' . $row->id . '" aria-label="Select product">';
            })
            ->editColumn('translation.name', function ($row) {
                return e($row->translation->name ?? $row->name ?? '-');
            })
            ->editColumn('barcode', function ($row) {
                return e($row->barcode ?: '-');
            })
            ->editColumn('current_stock', function ($row) {
                return (int) ($row->current_stock ?? 0);
            })
            ->editColumn('net_sold', function ($row) {
                $soldOut = abs((int) ($row->sold_out ?? 0));
                $restored = (int) ($row->sold_restored ?? 0);
                return max(0, $soldOut - $restored);
            })
            ->editColumn('total_ordered', function ($row) {
                return (int) ($row->total_ordered ?? 0);
            })
            ->rawColumns(['action', 'select'])
            ->setRowId('id');
    }

    public function query(Product $model): QueryBuilder
    {
        $branchId = request()->integer('branch_id') ?: null;

        $query = $model->query()
            ->with(['translation'])
            ->withSum([
                'productBranches as current_stock' => function ($q) use ($branchId) {
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                },
            ], 'stock_quantity')
            ->withSum([
                'inventoryMovements as sold_out' => function ($q) use ($branchId) {
                    $q->where('movement_type', InventoryMovement::TYPE_SALE);
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                },
            ], 'quantity')
            ->withSum([
                'inventoryMovements as sold_restored' => function ($q) use ($branchId) {
                    $q->where('movement_type', InventoryMovement::TYPE_SALE_DELETED);
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                },
            ], 'quantity')
            ->withSum([
                'inventoryMovements as total_ordered' => function ($q) use ($branchId) {
                    $q->whereIn('movement_type', [
                        InventoryMovement::TYPE_STOCK_ORDER,
                        InventoryMovement::TYPE_PURCHASE,
                        InventoryMovement::TYPE_INITIAL,
                    ])->where('quantity', '>', 0);
                    if ($branchId) {
                        $q->where('branch_id', $branchId);
                    }
                },
            ], 'quantity')
            ->orderBy('products.id', 'DESC');

        return $query;
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';
        $exportRoute = route('center_user.inventorymovements.export', ['format' => 'csv']);
        $branches = Branch::with('translation')->orderBy('id')->get();

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax('', null, [
                'branch_id' => 'function () { return $("#inventoryBranchFilter").val(); }',
            ])
            ->orderBy(1)
            ->responsive(true)
            ->dom('
                <"card-header border-bottom p-3 d-flex justify-content-between align-items-center"
                    <"head-label"><"dt-action-buttons"B>
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
                    'text' => __('general.export'),
                    'className' => $buttonClass,
                    'buttons' => [
                        [
                            'text' => __('general.export') . ' Excel',
                            'action' => "function () { window.exportMovementDetails('csv'); }",
                        ],
                        [
                            'text' => __('general.export') . ' PDF',
                            'action' => "function () { window.exportMovementDetails('pdf'); }",
                        ],
                    ],
                ],
            ])
            ->initComplete('function () {
                            var table = $("#inventorymovements-table").DataTable();
                            $(".dt-action-buttons").prepend(`
                                <select id="inventoryBranchFilter" class="form-select form-select-sm mx-1" style="width: auto; min-width: 180px;">
                                    <option value="">' . e(__('general.all')) . ' ' . e(__('field.branch')) . '</option>
                                    ' . $branches->map(function ($branch) {
                                        return '<option value="' . $branch->id . '">' . e($branch->name) . '</option>';
                                    })->implode('') . '
                                </select>
                            `);

                            $("#inventorymovements-table thead tr th:first").html("<input type=\"checkbox\" id=\"selectAllProducts\" class=\"form-check-input\" aria-label=\"Select all products\">");
                            $("#inventoryBranchFilter").on("change", function () {
                                table.ajax.reload();
                            });
                            $(document).on("change", "#selectAllProducts", function () {
                                $("#inventorymovements-table .product-select").prop("checked", this.checked);
                            });
                            window.exportMovementDetails = function (format) {
                            var selectedProducts = $("#inventorymovements-table .product-select:checked").map(function () {
                                return this.value;
                            }).get();
                            var params = new URLSearchParams();
                            var branchId = $("#inventoryBranchFilter").val();
                            if (branchId) params.append("branch_id", branchId);
                            selectedProducts.forEach(function (productId) {
                                params.append("product_ids[]", productId);
                            });
                            window.location.href = "' . $exportRoute . '".replace("/csv", "/" + format) + "?" + params.toString();
                            };
            }')
            ->language($this->getDataTableLanguageUrl())
            ->addTableClass('table table-bordered table-hover')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == 'ar'
            ? 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/ar.json'
            : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json';
    }

    public function getColumns(): array
    {
        return [
            Column::computed('select')->searchable(false)->orderable(false)->title(''),
            Column::make('id')->searchable(true)->title('#'),
            Column::computed('translation.name')->searchable(true)->title(__('field.product_name')),
            Column::make('barcode')->searchable(true)->title(__('field.barcode')),
            Column::computed('current_stock')->searchable(false)->title(__('field.current_stock')),
            Column::computed('net_sold')->searchable(false)->title(__('field.net_sold')),
            Column::computed('total_ordered')->searchable(false)->title(__('field.total_ordered')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
