<?php
namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Vacation;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VacationDataTable extends DataTable
{
    private $model = 'Vacation';
    private $plural = 'vacations';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                $html = view()->make('_partials.center_actions', compact('id', 'route', 'options', 'model'))->render();
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
            ->editColumn('text', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->text ?? '');
            })
            ->rawColumns(['status', 'text'], true)
            ->setRowId('id');
    }

    public function query(Vacation $model): QueryBuilder
    {
        return $model->query()->withTrashed()->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
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
            ->addAction()
            ->buttons([])
            ->language($this->getDataTableLanguageUrl())
            ->addTableClass('table table-bordered table-hover')
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
            Column::make('date')->searchable(true)->title(__('field.day')),
            Column::make('text')->searchable(true)->title(__('field.description')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
