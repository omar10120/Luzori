<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BookingWithTipsDataTable extends DataTable
{
    private $model = 'Booking';
    private $plural = 'booking_with_tips';

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('action', function ($item) {
                $route = 'center_user.' . $this->plural;
                $id = $item->id;
                $model = $this->model;
                $options = [
                    'print' => true,
                    'edit' => true,
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
            ->editColumn('details._date', function ($row) {
                return $row->details->first()->_date ?? '-';
            })
            ->editColumn('branch.translation.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->branch->translation->name ?? '');
            })
            ->editColumn('details.worker.name', function ($row) {
                return \App\Helpers\MyHelper::truncateWithReadMore($row->details->first()->worker->name ?? '-');
            })
            ->rawColumns(['status', 'branch.translation.name', 'details.worker.name'], true)
            ->setRowId('id');
    }

    public function query(Booking $model): QueryBuilder
    {
        $relations = ['branch' => function ($q) {
            $q->with(['translation']);
        }, 'details' => function ($q) {
            $q->with(['worker']);
        }];
        // Get all payment methods that support tips
        $tipsPaymentMethods = \App\Models\PaymentMethod::forTips()->pluck('name')->toArray();
        
        return $model->query()->with($relations)->whereIn('payment_type', $tipsPaymentMethods)->withTrashed()->orderBy('bookings.id', 'DESC');
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
            Column::computed('branch.translation.name')->searchable(true)->title(__('field.branch')),
            Column::computed('details._date')->searchable(true)->title(__('field.date_time')),
            Column::computed('details.worker.name')->searchable(true)->title(__('field.worker')),
            Column::computed('details.tip')->searchable(true)->title(__('field.tip')),
            Column::computed('payment_type')->searchable(true)->title(__('field.payment_type')),
            Column::make('created_at')->searchable(true)->title(__('field.created_at')),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
