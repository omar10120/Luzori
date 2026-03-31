<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\BookingDataTable;
use App\Http\Controllers\Controller;
use App\Models\Center;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    private $plural = 'bookings';

    public function index(BookingDataTable $dataTable)
    {
        $title = __('locale.bookings') ?? 'Bookings';
        $centers = Center::all()->pluck('name', 'id');
        return $dataTable->render('Admin.SubViews.Booking.index', compact('centers', 'title'));
    }
}
