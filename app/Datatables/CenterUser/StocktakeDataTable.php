<?php
namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Stocktake;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StocktakeDataTable extends DataTable
{
    private $model = 'Stocktake';
    private $plural = 'stocktakes';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'edit' => false,
                    'delete' => true,
                    'operation' => DeleteActionEnum::FORCE_DELETE->value,
                    'with_trashed' => 1,
                ];
                
                // Add custom action buttons
                $actions = '';
                if ($item->status == 'draft') {
                    $actions .= '<a href="' . route('center_user.stocktakes.create', ['id' => $id]) . '" class="btn btn-sm btn-primary me-1" title="Edit"><i class="ti ti-edit"></i></a>';
                }
                if ($item->status == 'in_progress') {
                    $actions .= '<a href="' . route('center_user.stocktakes.count', ['id' => $id]) . '" class="btn btn-sm btn-info me-1" title="Count Products"><i class="ti ti-list-numbers"></i></a>';
                }
                if (in_array($item->status, ['completed', 'reviewed'])) {
                    $actions .= '<a href="' . route('center_user.stocktakes.details', ['id' => $id]) . '" class="btn btn-sm btn-success me-1" title="View Details"><i class="ti ti-eye"></i></a>';
                }
                
                $html = view()->make('_partials.center_actions', compact('id', 'route', 'options', 'model'))->render();
                return $actions . $html;
            })
            ->editColumn('name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->name ?: 'Stocktake #' . $row->id);
            })
            ->rawColumns(['action', 'status', 'name'])
            ->setRowId('id');
    }

    public function query(Stocktake $model): QueryBuilder
    {
        return $model->query()->withTrashed()->with(['startedBy', 'reviewedBy', 'branches.translation'])->orderBy($this->plural . '.id', 'DESC');
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
                $(".dt-action-buttons").append("<a href=' . $addRoute . ' class=\"btn btn-primary mx-2\">' . __('general.add_new') . ' <i class=\"ti ti-plus\"></i></a>");
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
            Column::make('name')->searchable(true)->title('Stocktake name')->defaultContent('-'),
            Column::computed('status')->searchable(false)->title('Status'),
            Column::computed('started_at')->searchable(false)->title('Started on'),
            Column::computed('completed_at')->searchable(false)->title('Completed on'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}

