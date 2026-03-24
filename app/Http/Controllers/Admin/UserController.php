<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\UserDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $plural = 'users';

    public function index(UserDataTable $dataTable)
    {
        $title = "App Users"; // You can change this to __('locale.users') if locale exists
        return $dataTable->render("Admin.SubViews.core-table", compact('title'));
    }
}
