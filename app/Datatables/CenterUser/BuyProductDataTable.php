<?php
namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\BuyProduct;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BuyProductDataTable extends DataTable
{
    private $model = 'BuyProduct';
    private $plural = 'buyproducts';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'print' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                $html = view()->make('_partials.center_actions', compact('id', 'route', 'options', 'model'))->render();
                return $html;
            })
            ->editColumn('details.product.translation.name', function ($row) {
                $products = '';
                foreach ($row->details as $detail) {
                    $products .= '<span class="badge"
                        style="border-radius:10px;background-color:blue;margin:5px;padding:7px;">' . $detail->product?->name . ' ' . ($detail->product?->supply_price ?? $detail->product?->retail_price) . '</span>';
                }
                return $products;
            })
            ->editColumn('total', function ($row) {
                $total = 0;
                foreach ($row->details as $detail) {
                    $total += ($detail->product?->supply_price ?? $detail->product?->retail_price) ?? 0;
                }
                return $total . ' ' . trim(get_currency());
            })
            ->editColumn('sales_worker.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->sales_worker?->name ?? '-');
            })
            ->rawColumns(['details.product.translation.name', 'status', 'sales_worker.name'], true)
            ->setRowId('id');
    }

    public function query(BuyProduct $model): QueryBuilder
    {
        return $model->query()->withTrashed()->with(['sales_worker', 'details' => function ($q) {
            $q->with(['product' => function ($q) {
                $q->with(['translation']);
            }]);
        }])->orderBy('buy_products.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';
        $addRoute = route('center_user.' . $this->plural . '.create');

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
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
                    'text' => __('general.export'),  'className' => $buttonClass,
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
             $(".dt-action-buttons").append("<a href=' . $addRoute . ' class=\"btn btn-primary btn-sm mx-1 mx-md-2 px-2 px-md-3 py-1 py-md-2\">' . __('general.add_new') . '<i class=\"ti ti-plus\"></i></a>");
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
            Column::computed('details.product.translation.name')->searchable(true)->title(__('locale.products')),
            Column::make('payment_type')->searchable(true)->title(__('field.payment_type')),
            Column::computed('total')->searchable(false)->title(__('field.total')),
            Column::computed('sales_worker.name')->searchable(true)->title(__('field.sales_worker')),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
