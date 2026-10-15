<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PaymentMethodDataTable extends DataTable
{
    private $model = 'PaymentMethod';
    private $plural = 'payment_methods';

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
                return view()->make('_partials.center_action_icons', compact('id', 'route', 'options', 'model'))->render();
            })
            ->editColumn('types', function ($row) {
                if (!$row->types || empty($row->types)) {
                    return '<span class="lz-type-badge is-default">—</span>';
                }

                $badges = '';
                foreach ($row->types as $type) {
                    $class = match ($type) {
                        'booking' => 'is-booking',
                        'product' => 'is-product',
                        'wallet' => 'is-wallet',
                        'general' => 'is-general',
                        'tips' => 'is-tips',
                        default => 'is-default',
                    };
                    $badges .= '<span class="lz-type-badge ' . $class . '">' . e(ucfirst($type)) . '</span>';
                }
                return $badges;
            })
            ->editColumn('status', function ($row) {
                $checked = $row->status ? 'checked' : '';
                return '<label class="switch switch-square" onclick="event.stopPropagation()">
                            <input onChange="updatePaymentMethodStatus(' . $row->id . ', this.checked)"
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
            ->rawColumns(['action', 'types', 'status', 'name'], true)
            ->setRowId('id');
    }

    public function query(PaymentMethod $model): QueryBuilder
    {
        return $model->query()->withTrashed()->orderBy($this->plural . '.id', 'DESC');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 px-3 py-2 btn-sm';
        $addRoute = route('center_user.' . $this->plural . '.create');

        $initCompleteJs = 'function () {';
        $initCompleteJs .= '
             window.updatePaymentMethodStatus = function (id, status) {
                 $.ajax({
                     url: "' . route('center_user.payment_methods.changeStatus') . '",
                     type: "POST",
                     dataType: "json",
                     data: {
                         _token: "' . csrf_token() . '",
                         id: id,
                         status: status ? 1 : 0
                     },
                     success: function () {
                         $("#payment_methods-table").DataTable().ajax.reload(null, false);
                     },
                     error: function () {
                         $("#payment_methods-table").DataTable().ajax.reload(null, false);
                         if (typeof fireMessage === "function") {
                             fireMessage("' . __('admin.ok') . '", "' . __('admin.an_error_occurred') . '", "", "error");
                         }
                     }
                 });
             };
            }';

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
                'className' => 'dt-center lz-actions-col',
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
            ->initComplete($initCompleteJs)
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
            Column::make('name')->searchable(true)->title(__('field.name'))->responsivePriority(1)->addClass('all'),
            Column::computed('types')->searchable(false)->title(__('field.type'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->title(__('field.status'))->responsivePriority(2)->addClass('all'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
