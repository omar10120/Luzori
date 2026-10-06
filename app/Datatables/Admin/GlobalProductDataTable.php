<?php

namespace App\Datatables\Admin;

use App\Enums\DeleteActionEnum;
use App\Models\GlobalProduct;
use App\Services\GlobalProductService;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GlobalProductDataTable extends DataTable
{
    public function __construct(private GlobalProductService $globalProductService)
    {
    }

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('center_name', fn (GlobalProduct $item) => e($item->center?->domain ?? '-'))
            ->editColumn('name', fn (GlobalProduct $item) => e($item->name ?? '-'))
            
            // 1. Separate Status Column
            ->editColumn('status', function (GlobalProduct $item) {
                $checked = $item->trashed() ? '' : 'checked';
                $operation = $item->trashed()
                    ? DeleteActionEnum::RESTORE_DELETED->value
                    : DeleteActionEnum::SOFT_DELETE->value;

                return '<label class="switch switch-square" title="' . e(__('field.status')) . '">'
                    . '<input type="checkbox" class="switch-input" ' . $checked
                    . ' onchange="changeStatus(\'GlobalProduct\', \'' . $item->id . '\', \'' . $operation . '\')">'
                    . '<span class="switch-toggle-slider"><span class="switch-on"></span><span class="switch-off"></span></span>'
                    . '</label>';
            })
            
            // 2. Clean Action Column (Only buttons)
            ->editColumn('action', function (GlobalProduct $item) {
                $route = 'admin.global-products';
                $id = $item->id;
                $model = 'GlobalProduct';
                $options = [
                    'edit' => false,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                return view()->make('_partials.actions', compact('id', 'route', 'options', 'model'))->render();
            })
            
            // 3. Include both columns in rawColumns
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    public function query(GlobalProduct $model): QueryBuilder
    {
        return $this->globalProductService->query();
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-2 px-4';
        $addRoute = route('admin.global-products.create');

        return $this->builder()
            ->setTableId('global-products-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
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
                    'buttons' => ['excel', 'csv', 'pdf', 'print', 'copy'],
                ],
            ])
            ->language(app()->getLocale() === 'ar'
                ? asset('js/lang/ar_custom.json')
                : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json')
            ->addTableClass('table table-bordered table-hover')
            ->initComplete('function () { $(".dt-action-buttons").append("<a href=\'' . $addRoute . '\' class=\"btn btn-primary mx-2\">' . e(__('general.add_new')) . ' <i class=\"ti ti-plus\"></i></a>"); }')
            ->parameters([]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->title('#'),
            Column::computed('center_name')->title(__('field.center'))->searchable(false)->orderable(false),
            Column::make('name')->title(__('field.product_name')),
            Column::make('product_id')->title(__('field.product_id')),
            Column::make('sort_order')->title(__('field.sort_order')),
            // 4. Add the status column definition here
            Column::computed('status')->title(__('field.status'))->searchable(false)->orderable(false),
            Column::make('created_at')->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return 'global_products_' . date('YmdHis');
    }
}