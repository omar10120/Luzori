<?php

namespace App\Http\Controllers\Admin;

use App\Datatables\Admin\BannerDataTable;
use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class BannerController extends Controller
{
    public function __construct(private BannerService $bannerService)
    {
    }

    public function index(BannerDataTable $dataTable)
    {
        abort_unless(auth('admin')->user()->can('VIEW_' . Str::upper('banners')), 403);

        return $dataTable->render('Admin.SubViews.core-table', [
            'title' => __('locale.banners'),
        ]);
    }

    public function create(Request $request)
    {
        LOG::info('BannerController@create called with request: ' . json_encode($request->all()));
        $permission = $request->filled('id') ? 'UPDATE_BANNERS' : 'CREATE_BANNERS';
        abort_unless(auth('admin')->user()->can($permission), 403);

        $item = $request->filled('id')
            ? Banner::withTrashed()->findOrFail($request->integer('id'))
            : null;
        $options = $this->bannerService->options();

        return view('Admin.SubViews.Banner.index', [
            'item' => $item,
            'options' => $options,
            'requestUrl' => route('admin.banners.updateOrCreate'),
            'title' => $item ? __('general.edit') : __('general.add'),
            'menu' => __('locale.banners'),
            'menu_link' => route('admin.banners.index'),
        ]);
    }

    public function updateOrCreate(BannerRequest $request)
    {
        $permission = $request->filled('id') ? 'UPDATE_BANNERS' : 'CREATE_BANNERS';
        abort_unless(auth('admin')->user()->can($permission), 403);

        $this->bannerService->store($request->validated());

        return MyHelper::responseJSON(
            'redirect_to_home',
            Response::HTTP_CREATED,
            route('admin.banners.index')
        );
    }
}
