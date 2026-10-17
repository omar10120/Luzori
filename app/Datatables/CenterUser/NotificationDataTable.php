<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class NotificationDataTable extends DataTable
{
    private $model = 'Notification';
    private $plural = 'notifications';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'show' => true,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 0,
                ];
                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->addColumn('title', function ($row) {
                return '<span class="lz-notif-title">' . e($row->translation->title ?? '—') . '</span>';
            })
            ->addColumn('text', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->translation->text ?? '');
            })
            ->editColumn('users', function ($row) {
                $count = $row->users->count();
                return '<span class="lz-user-count">' . (int) $count . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                try {
                    return $row->created_at ? $row->created_at->diffForHumans() : '—';
                } catch (\Throwable $e) {
                    return e((string) ($row->created_at ?? '—'));
                }
            })
            ->rawColumns(['action', 'text', 'title', 'users'])
            ->setRowId('id');
    }

    public function query(Notification $model): QueryBuilder
    {
        return $model->query()->with(['translation', 'users'])->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->responsive(false)
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
                'className' => 'dt-center lz-actions-col all noVis',
                'title' => __('general.actions'),
            ])
            ->buttons([
                [
                    'extend' => 'colvis',
                    'className' => $buttonClass . ' btn-luzori-beige',
                    'text' => __('general.column_visibility'),
                    'columns' => ':not(.noVis)',
                ],
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
        return app()->getLocale() == 'ar'
            ? asset('js/lang/ar_custom.json')
            : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json';
    }

    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(true)->title('#')->responsivePriority(1)->addClass('all'),
            Column::computed('title')->searchable(true)->title(__('field.title'))->responsivePriority(1)->addClass('all'),
            Column::computed('users')->searchable(false)->orderable(false)->title(__('field.users'))->responsivePriority(2)->addClass('all'),
            Column::computed('text')->searchable(true)->title(__('field.text'))->visible(false),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->visible(false),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
