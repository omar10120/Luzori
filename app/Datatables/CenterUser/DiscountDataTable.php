<?php

namespace App\Datatables\CenterUser;

use App\Enums\DeleteActionEnum;
use App\Models\Discount;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DiscountDataTable extends DataTable
{
    private $model = 'Discount';
    private $plural = 'discounts';

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
            ->editColumn('code', function ($row) {
                return '<span class="lz-discount-code">' . e($row->code ?? '—') . '</span>';
            })
            ->editColumn('type', function ($row) {
                $label = $row->type === 'percentage'
                    ? __('field.percentage')
                    : __('field.fixed');
                $class = $row->type === 'percentage' ? 'lz-type-percent' : 'lz-type-fixed';
                return '<span class="lz-type-badge ' . $class . '">' . e($label) . '</span>';
            })
            ->editColumn('amount', function ($row) {
                if ($row->type === 'percentage') {
                    return '<span class="lz-amount">' . e($row->amount) . '%</span>';
                }
                return '<span class="lz-amount">' . e(number_format((float) $row->amount, 2)) . ' ' . e(trim(get_currency())) . '</span>';
            })
            ->editColumn('using_type', function ($row) {
                $label = $row->using_type === 'multi'
                    ? __('field.multi')
                    : __('field.single');
                return e($label);
            })
            ->editColumn('benefit_numbers', function ($row) {
                return $row->using_type === 'multi' ? e($row->benefit_numbers ?? '—') : '—';
            })
            ->editColumn('start_at', function ($row) {
                return e($row->start_at ?: '—');
            })
            ->editColumn('end_at', function ($row) {
                return e($row->end_at ?: '—');
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->diffForHumans() : '—';
            })
            ->rawColumns(['action', 'status', 'code', 'type', 'amount'], true)
            ->setRowId('id');
    }

    public function query(Discount $model): QueryBuilder
    {
        return $model->query()->withTrashed()->orderBy('discounts.id', 'DESC');
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
            Column::make('code')->searchable(true)->title(__('field.code'))->responsivePriority(1)->addClass('all'),
            Column::computed('type')->searchable(false)->title(__('field.type'))->responsivePriority(2)->addClass('all'),
            Column::make('amount')->searchable(true)->title(__('field.amount'))->responsivePriority(2)->addClass('all'),
            Column::computed('status')->searchable(false)->title(__('field.status'))->responsivePriority(2)->addClass('all'),
            Column::make('start_at')->searchable(true)->title(__('field.start_at'))->addClass('none'),
            Column::make('end_at')->searchable(true)->title(__('field.end_at'))->addClass('none'),
            Column::computed('using_type')->searchable(false)->title(__('field.using_type'))->addClass('none'),
            Column::computed('benefit_numbers')->searchable(false)->title(__('field.benefit_numbers'))->addClass('none'),
            Column::make('created_at')->searchable(true)->title(__('field.created_at'))->addClass('none'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
