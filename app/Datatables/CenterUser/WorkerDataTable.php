<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WorkerDataTable extends DataTable
{
    private $model = 'Worker';
    private $plural = 'workers';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'vacation' => true,
                    'edit' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->editColumn('services.service.translation.name', function ($row) {
                $services = '';
                foreach ($row->services as $service) {
                    $name = e($service->service->name ?? '');
                    if ($name === '') {
                        continue;
                    }
                    $services .= '<span class="lz-service-badge">' . $name . '</span>';
                }
                return $services !== '' ? $services : '—';
            })
            ->editColumn('image', function ($row) {
                return '<img src="' . e($row->image) . '" alt="">';
            })
            ->editColumn('status', function ($row) {
                $checked = $row->deleted_at ? '' : 'checked';
                $operation = $row->deleted_at ? DeleteActionEnum::RESTORE_DELETED->value : DeleteActionEnum::SOFT_DELETE->value;
                return '<label class="switch switch-square" onclick="event.stopPropagation()">
                            <input onChange="changeStatus(\'' . $this->model . '\',\'' . $row->id . '\',\'' . $operation . '\')"
                                type="checkbox" class="switch-input"' . $checked . '>
                            <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                            </span>
                        </label>';
            })
            ->editColumn('name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->name ?? '');
            })
            ->editColumn('percentage', function ($row) {
                return number_format((float) $row->percentage, 2) . ' %';
            })
            ->editColumn('is_professional', function ($row) {
                return $row->is_professional ? __('general.yes') : __('general.no');
            })
            ->rawColumns(['action', 'services.service.translation.name', 'image', 'status', 'name', 'percentage'], true)
            ->setRowId('id');
    }

    public function query(Worker $model): QueryBuilder
    {
        $user = auth('center_user')->user();
        $branchId = $user->branch_id ?? null;

        $query = $model->query()->withTrashed()->with(['services' => function ($q) {
            return $q->with(['service' => function ($q) {
                $q->with(['translation']);
            }]);
        }, 'branch' => function ($q) {
            $q->with(['translation']);
        }]);

        if ($branchId !== null) {
            $query->where('branch_id', $branchId);
        }

        $query->where('is_center_user', false);

        return $query->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->responsive([
                'details' => [
                    'type' => 'column',
                    'target' => 0,
                ],
            ])
            ->dom('
                <"card-header border-bottom p-3 d-flex justify-content-between align-items-center"
                    <"head-label"><"dt-action-buttons"B>
                >
                <"d-flex justify-content-between align-items-center mx-0 row px-1 pt-2"
                    <"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"fr>
                >
                <"table-responsive"t>
                <"d-flex justify-content-between mx-0 row px-1 pt-2"
                    <"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>
                >
            ')
            ->selectStyleSingle()
            ->addAction([
                'printable' => false,
                'exportable' => false,
                'responsivePriority' => 1,
                'className' => 'dt-center lz-actions-col all',
                'title' => __('general.actions'),
            ])
            ->buttons([
                Button::make('colvis')->addClass($buttonClass . ' btn-luzori-beige')->text(__('general.column_visibility')),
                [
                    'extend' => 'collection',
                    'text' => __('general.export'),
                    'className' => $buttonClass . ' btn-luzori-muted',
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
            ->addTableClass('table table-hover')
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == "ar" ?
            asset('js/lang/ar_custom.json') :
            "https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json";
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('dtr-control all'),
            Column::computed('branch.translation.name')->searchable(true)->title(__('field.branch'))->responsivePriority(1)->addClass('all'),
            Column::computed('name')->searchable(true)->title(__('field.name'))->responsivePriority(1)->addClass('all'),
            Column::computed('image')->searchable(false)->title(__('field.image'))->responsivePriority(2)->addClass('all'),
            Column::computed('services.service.translation.name')->searchable(true)->title(__('field.services'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->title(__('field.status'))->responsivePriority(2)->addClass('all'),
            // Expandable details
            Column::computed('email')->searchable(true)->title(__('field.email'))->addClass('none'),
            Column::computed('phone')->searchable(true)->title(__('field.phone'))->addClass('none'),
            Column::computed('percentage')->searchable(true)->title(__('field.percentage'))->addClass('none'),
            Column::computed('salary')->searchable(true)->title(__('field.salary'))->addClass('none'),
            Column::computed('visa_start_date')->searchable(true)->title(__('field.visa_start_date'))->addClass('none'),
            Column::computed('visa_end_date')->searchable(true)->title(__('field.visa_end_date'))->addClass('none'),
            Column::computed('is_professional')->searchable(true)->title(__('field.is_professional'))->addClass('none'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
