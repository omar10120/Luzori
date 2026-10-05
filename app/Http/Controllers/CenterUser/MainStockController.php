<?php

namespace App\Http\Controllers\CenterUser;

use App\Datatables\CenterUser\MainStockDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class MainStockController extends Controller
{
    private string $plural = 'mainstocks';

    public function index(MainStockDataTable $dataTable)
    {
        $permission = 'VIEW_' . Str::upper($this->plural);
        if (!auth('center_user')->user()->can($permission, 'center_api')) {
            return abort(403);
        }

        $title = __('locale.' . $this->plural);
        return $dataTable->render('CenterUser.SubViews.core-table', compact('title'));
    }
}
