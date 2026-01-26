<?php
namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ServiceDataTable extends DataTable
{
    private $model = 'Service';
    private $plural = 'services';

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
            ->editColumn('price', function ($row) {
                return $row->price . ' ' . trim(get_currency());
            })
            ->editColumn('is_top', function ($row) {
                return $row->is_top ? __('general.yes') : __('general.no');
            })
            ->editColumn('status', function ($row) {
                $checked = $row->deleted_at ? '' : 'checked';
                $operation = $row->deleted_at ? DeleteActionEnum::RESTORE_DELETED->value : DeleteActionEnum::SOFT_DELETE->value;
                return '<label class="switch switch-square">
                            <input onChange="changeStatus(\'' . $this->model . '\',\'' . $row->id . '\',\'' . $operation . '\')"
                                type="checkbox" class="switch-input"' . $checked . '>
                            <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->editColumn('image', function ($row) {
                return '<img src="' . $row->image . '" style="width:75px;height:75px;" >';
            })
            ->editColumn('translation.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->translation->name ?? '');
            })
            ->editColumn('translation.description', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->translation->description ?? '');
            })
            ->rawColumns(['image', 'status', 'translation.name', 'translation.description'], true)
            ->setRowId('id');
    }

    public function query(Service $model): QueryBuilder
    {
        return $model->query()->withTrashed()->with(['translation'])->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';
        $addRoute = route('center_user.' . $this->plural . '.create');

        // Check if user has permission to create services
        $canCreate = auth('center_user')->check() && auth('center_user')->user()->can('CREATE_SERVICES', 'center_api');
        
        // Build initComplete JavaScript - conditionally include add button
        $initCompleteJs = 'function () {';
        if ($canCreate) {
            $initCompleteJs .= '$(".dt-action-buttons").append("<a href=\'' . $addRoute . '\' class=\"btn btn-primary btn-sm mx-1 mx-md-2 px-2 px-md-3 py-1 py-md-2\">' . __('general.add_new') . '<i class=\"ti ti-plus\"></i></a>");';
        }
        $initCompleteJs .= '}';

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
            ->initComplete($initCompleteJs)
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
            Column::computed('image')->searchable(false)->title(__('field.image')),
            Column::computed('translation.name')->searchable(true)->title(__('field.name')),
            Column::computed('translation.description')->searchable(true)->title(__('field.description')),
            Column::make('rooms_no')->searchable(true)->title(__('field.rooms_no')),
            Column::make('free_book')->searchable(true)->title(__('field.free_book')),
            Column::computed('price')->searchable(true)->title(__('field.price')),
            Column::make('sort_order')->searchable(true)->title(__('field.sort_order')),
            Column::computed('is_top')->searchable(true)->title(__('field.is_top')),
            Column::computed('status')->searchable(false)->title(__('field.status')),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
