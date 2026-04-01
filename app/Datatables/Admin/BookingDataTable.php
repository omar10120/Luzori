<?php

namespace App\Datatables\Admin;

use App\Models\Booking;
use App\Models\Center;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use App\Helpers\MyHelper;
use Yajra\DataTables\Services\DataTable;

class BookingDataTable extends DataTable
{
    private $plural = 'bookings';

    public function dataTable($query)
    {
        $dataTable = $query instanceof Collection ? new CollectionDataTable($query) : new EloquentDataTable($query);

        return $dataTable
            ->addColumn('booking_number', function ($row) {
                return $row->id;
            })
            ->addColumn('client_name', function ($row) {
                return $row->user->name ?? $row->full_name ?? '---';
            })
            ->addColumn('wallet_balance', function ($row) {
                return $row->user->wallet ?? '0.00';
            })
            ->addColumn('phone', function ($row) {
                return $row->user->phone ?? $row->mobile ?? '---';
            })
            ->addColumn('email', function ($row) {
                return $row->user->email ?? '---';
            })
            ->addColumn('booking_date', function ($row) {
                return $row->booking_date;
            })
            ->addColumn('assigned_employee', function ($row) {
                return $row->details->map(fn($d) => $d->worker->name ?? '---')->unique()->implode(', ') ?: '---';
            })
            ->addColumn('price', function ($row) {
                return number_format($row->details->sum('price'), 2);
            })
            ->addColumn('services', function ($row) {
                return $row->details->map(fn($d) => $d->service->translation->name ?? $d->service->name ?? '---')->implode(', ');
            })
            ->addColumn('branch_name', function ($row) {
                return $row->branch->name ?? '---';
            })
            ->addColumn('center_email', function ($row) {
                return $row->center_email ?? '---';
            })
            ->addColumn('payment_method', function ($row) {
                return ucfirst(str_replace('_', ' ', $row->payment_type));
            })
            ->addColumn('service_time', function ($row) {
                return $row->details->map(fn($d) => ($d->from_time ?? '') . ' - ' . ($d->to_time ?? ''))->implode('<br>');
            })
            ->addColumn('purchased_products', function ($row) {
                if ($row->sale) {
                    return $row->sale->buyProducts->map(function($bp) {
                        return $bp->details->map(fn($d) => $d->product->translation->name ?? '---')->implode(', ');
                    })->implode(', ') ?: '---';
                }
                return '---';
            })
            ->addColumn('booking_status', function ($row) {
                $status = $row->deleted_at ? 'Cancelled' : 'Confirmed';
                $class = $row->deleted_at ? 'bg-label-danger' : 'bg-label-success';
                return '<span class="badge ' . $class . '">' . $status . '</span>';
            })
            ->addColumn('booking_source', function ($row) {
                $source = $row->details->first()->booking_source ?? 'inside_booking';
                $class = $source === 'outside_booking' ? 'bg-label-info' : 'bg-label-secondary';
                $label = $source === 'outside_booking' ? (__('api.outside_booking') ?? 'Outside') : (__('api.inside_booking') ?? 'Inside');
                return '<span class="badge ' . $class . '">' . $label . '</span>';
            })
            ->addColumn('commission', function ($row) {
                return $row->details->map(fn($d) => ($d->commission ?? '0') . ($d->commission_type === 'percent' ? '%' : ''))->implode(', ');
            })
            ->rawColumns(['booking_status', 'service_time', 'booking_source'])
            ->setRowId('id');
    }

    public function query(Booking $model)
    {
        $centerId = request()->get('center_id');
        $bookingSource = request()->get('booking_source');
        
        if ($centerId) {
            $center = Center::find($centerId);
            if ($center) {
                Config::set('database.connections.mysql.database', $center->database);
                DB::purge('mysql');
                DB::reconnect('mysql');
                
                $query = $model->newQuery()->with(['details.service.translation', 'details.worker', 'user', 'branch', 'sale.buyProducts.details.product.translation'])->orderBy('id', 'desc');
                
                if ($bookingSource) {
                    $query->whereHas('details', function($q) use ($bookingSource) {
                        $q->where('booking_source', $bookingSource);
                    });
                }
                
                return $query;
            }
        }

        // Aggregate All Bookings (Limited for performance)
        $allBookings = collect();
        $centers = Center::all();
        foreach ($centers as $center) {
            try {
                Config::set('database.connections.mysql.database', $center->database);
                DB::purge('mysql');
                DB::reconnect('mysql');
                
                $query = Booking::with(['details.service.translation', 'details.worker', 'user', 'branch', 'sale.buyProducts.details.product.translation'])
                    ->orderBy('id', 'desc');
                
                if ($bookingSource) {
                    $query->whereHas('details', function($q) use ($bookingSource) {
                        $q->where('booking_source', $bookingSource);
                    });
                }
                
                $bookings = $query->limit(20)->get();
                    
                foreach ($bookings as $booking) {
                    $booking->center_email = $center->email;
                    $booking->center_name = $center->name;
                    $allBookings->push($booking);
                }
            } catch (\Exception $e) {
                // Skip if DB doesn't exist
                continue;
            }
        }
        
        return $allBookings->sortByDesc('created_at');
    }

    public function html(): HtmlBuilder
    {
        $buttonClass = 'btn mx-1 mx-md-2 px-2 px-md-4 py-1 py-md-2 btn-sm';

        return $this->builder()
            ->setTableId($this->plural . '-table')
            ->addTableClass('dt-responsive')
            ->columns($this->getColumns())
            ->ajax([
                'url' => route('admin.bookings.index'),
                'data' => 'function(d) {
                    d.center_id = $("#center_filter").val();
                    d.booking_source = $("#booking_source_filter").val();
                }',
            ])
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
                var centers = ' . json_encode(Center::all()->pluck('name', 'id')) . ';
                var filterHtml = \'<select id="center_filter" class="form-select form-select-sm d-inline-block ms-2" style="width: auto;">\' +
                    \'<option value="">' . __('general.all_centers') . '</option>\';
                
                $.each(centers, function(id, name) {
                    filterHtml += \'<option value="\' + id + \'">\' + name + \'</option>\';
                });
                
                filterHtml += \'</select>\';
                
                var sourceFilterHtml = \'<select id="booking_source_filter" class="form-select form-select-sm d-inline-block ms-2" style="width: auto;">\' +
                    \'<option value="">' . __('api.booking_source') . '</option>\' +
                    \'<option value="inside_booking">' . __('api.inside_booking') . '</option>\' +
                    \'<option value="outside_booking">' . __('api.outside_booking') . '</option>\' +
                \'</select>\';
                
                $(".dt-action-buttons").prepend(sourceFilterHtml);
                $(".dt-action-buttons").prepend(filterHtml);
                
                $("#center_filter, #booking_source_filter").on("change", function() {
                    window.LaravelDataTables["bookings-table"].draw();
                });
            }');
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
            Column::computed('booking_number')->title(__('field.id') ?? 'Booking #'),
            Column::computed('client_name')->title(__('field.client') ?? 'Client'),
            Column::computed('wallet_balance')->title(__('field.wallet') ?? 'Wallet'),
            Column::computed('phone')->title(__('field.phone') ?? 'Phone'),
            Column::computed('email')->title(__('field.email') ?? 'Email'),
            Column::computed('booking_date')->title(__('field.date') ?? 'Date'),
            Column::computed('assigned_employee')->title(__('field.employee') ?? 'Employee'),
            Column::computed('price')->title(__('field.price') ?? 'Price'),
            Column::computed('services')->title(__('field.services') ?? 'Services'),
            Column::computed('branch_name')->title(__('field.branch') ?? 'Branch'),
            Column::computed('center_email')->title(__('field.center_email') ?? 'Center Email'),
            Column::computed('payment_method')->title(__('field.payment_method') ?? 'Payment'),
            Column::computed('service_time')->title(__('field.time') ?? 'Time'),
            Column::computed('purchased_products')->title(__('field.products') ?? 'Products'),
            Column::computed('booking_status')->title(__('field.status') ?? 'Status'),
            Column::computed('booking_source')->title(__('api.booking_source') ?? 'Source'),
            Column::computed('commission')->title(__('field.commission') ?? 'Commission (%)'),
        ];
    }

    protected function filename(): string
    {
        return $this->plural . '_' . date('YmdHis');
    }
}
