<?php
namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SuppliersDataTable extends DataTable
{
    private $model = 'Supplier';
    private $plural = 'suppliers';

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
            ->editColumn('logo', function ($row) {
                if ($row->logo) {
                    $logoUrl = $row->logo_url ?: asset('storage/' . $row->logo);
                    return '<img src="' . $logoUrl . '" alt="Logo" class="img-thumbnail" style="width: 40px; height: 40px;">';
                }
                return '<img src="https://via.placeholder.com/40x40" alt="Logo" class="img-thumbnail" style="width: 40px; height: 40px;">';
            })
            ->editColumn('description', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->description ?? '');
            })
            ->rawColumns(['logo', 'description', 'action'], true)
            ->setRowId('id');
    }

    public function query(Supplier $model): QueryBuilder
    {
        return $model->newQuery();
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn btn-sm mx-1 px-2';
        $addRoute = route('center_user.suppliers.create');

        return $this->builder()
            ->setTableId('suppliers-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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
            Column::computed('logo')->searchable(false)->title(__('field.logo')),
            Column::make('name')->searchable(true)->title(__('field.name')),
            Column::make('email')->searchable(true)->title(__('field.email')),
            Column::make('phone')->searchable(true)->title(__('field.phone_number')),
            Column::computed('description')->searchable(false)->title(__('field.description')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
