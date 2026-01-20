<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Membership;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class MembershipDataTable extends DataTable
{
    private $model = 'Membership';
    private $plural = 'memberships';

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
            ->editColumn('user.first_name', function ($row) {
                return $row->user->name ?? '-';
            })
            ->editColumn('user.phone', function ($row) {
                return $row->user->full_phone ?? '-';
            })
            ->editColumn('percent', function ($row) {
                return $row->percent . '%';
            })
            ->editColumn('created_user.name', function ($row) {
                return $row->created_user->name ?? '-';
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
            ->editColumn('user.first_name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->user->name ?? '');
            })
            ->rawColumns(['status', 'user.first_name'], true)
            ->setRowId('id');
    }

    public function query(Membership $model): QueryBuilder
    {
        $model = $model->query()->with(['user', 'created_user'])
            ->withTrashed()->orderBy('memberships_cards.id', 'DESC');
        return $this->applyScopes($model);
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
            Column::make('user.first_name')->searchable(true)->title(__('field.customer_name')),
            Column::make('user.phone')->searchable(true)->title(__('field.customer_phone')),
            Column::make('membership_no')->searchable(true)->title(__('field.membership_no')),
            Column::computed('percent')->searchable(true)->title(__('field.percent')),
            Column::make('start_at')->searchable(true)->title(__('field.start_at')),
            Column::make('end_at')->searchable(true)->title(__('field.end_at')),
            Column::computed('status')->searchable(false)->title(__('field.status')),
            Column::make('created_user.name')->searchable(false)->title(__('field.created_by')),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
