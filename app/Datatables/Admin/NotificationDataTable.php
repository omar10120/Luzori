<?php

namespace App\Datatables\Admin;

use App\Models\AppNotification;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class NotificationDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('translation.title', fn ($row) => $row->title ?? $row->translation?->title ?? '-')
            ->editColumn('translation.text', function ($row) {
                $text = $row->text ?? $row->translation?->text ?? '';
                return \App\Helpers\MyHelper::truncateWithReadMore($text);
            })
            ->editColumn('image', function ($row) {
                if (!$row->image_url) {
                    return '-';
                }
                return '<img src="' . e($row->image_url) . '" style="width:50px;height:50px;object-fit:cover;border-radius:6px;" />';
            })
            ->editColumn('target_type', function ($row) {
                return $row->target_type === 'centers'
                    ? __('field.centers')
                    : __('field.users');
            })
            ->editColumn('status', function ($row) {
                $checked = $row->status ? 'checked' : '';
                return '<div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input toggle-notification-status" data-id="' . $row->id . '" ' . $checked . '>
                </div>';
            })
            ->addColumn('resend', function ($row) {
                return '<button type="button" class="btn btn-sm btn-success resend-notification" data-id="' . $row->id . '" title="' . __('field.resend') . '">
                    <i class="ti ti-refresh"></i>
                </button>';
            })
            ->rawColumns(['translation.text', 'image', 'status', 'resend'])
            ->setRowId('id');
    }

    public function query(AppNotification $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['translations'])
            ->orderByDesc('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('notifications-table')
            ->addTableClass('dt-responsive table table-bordered table-hover')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->responsive(true)
            ->dom('
                <"d-flex justify-content-between align-items-center mx-0 row mb-2"
                    <"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"fr>
                >
                <"table-responsive"t>
                <"d-flex justify-content-between mx-0 row"
                    <"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>
                >
            ')
            ->language($this->getDataTableLanguageUrl())
            ->parameters([]);
    }

    public function getDataTableLanguageUrl()
    {
        return app()->getLocale() == 'ar'
            ? 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/ar.json'
            : 'https://cdn.datatables.net/plug-ins/2.0.7/i18n/en-GB.json';
    }

    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('#')->orderable(false)->searchable(false),
            Column::make('translation.title')->title(__('field.title'))->orderable(false)->searchable(false),
            Column::make('translation.text')->title(__('field.description'))->orderable(false)->searchable(false),
            Column::computed('image')->title(__('field.image'))->orderable(false)->searchable(false),
            Column::make('target_type')->title(__('field.target_type'))->searchable(true),
            Column::make('sent_count')->title(__('field.notification_count'))->searchable(false),
            Column::computed('status')->title(__('field.status'))->orderable(false)->searchable(false),
            Column::computed('resend')->title(__('field.resend'))->orderable(false)->searchable(false),
            Column::make('created_at')->title(__('field.created_at'))->searchable(false),
        ];
    }

    protected function filename(): string
    {
        return 'notifications_' . date('YmdHis');
    }
}
