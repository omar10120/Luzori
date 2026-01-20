<?php
namespace App\Datatables\Admin;

use App\Enums\DeleteActionEnum;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AdminDataTable extends DataTable
{
    private $model = 'Admin';
    private $plural = 'admins';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'admin.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'edit' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                $html = view()->make('_partials.actions', compact('id', 'route', 'options', 'model'))->render();
                return $html;
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
            ->editColumn('role', function ($row) {
                $role = 'لا يوجد';
                if ($row->roles()?->count()) {
                    $role = $row->roles()?->first()?->name;
                }
                return $role;
            })
            ->editColumn('name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->name ?? '');
            })
            ->rawColumns(['status', 'name'], true)
            ->setRowId('id');
    }

    public function query(Admin $model): QueryBuilder
    {
        return $model->query()->withTrashed()->with(['media'])->where('id', '!=', 1)->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-2 px-4';
        $addRoute = route('admin.' . $this->plural . '.create');

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
                    'text' => __('general.export'),
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
                $(".dt-action-buttons").append("<a href=' . $addRoute . ' class=\"btn btn-primary mx-2\">' . __('general.add_new') . '<i class=\"ti ti-plus\"></i></a>");
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
            Column::make('id')->title('#'),
            Column::make('name')->title(__('field.name')),
            Column::make('email')->title(__('field.email')),
            Column::make('phone')->title(__('field.phone')),
            Column::computed('role')->title(__('field.role')),
            Column::computed('status')->title(__('field.status')),
            Column::make('created_at')->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
