<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Services\PageService;
use App\Helpers\MyHelper;
use App\Http\Resources\PageResource;
use Illuminate\Http\Response;

class PageController extends Controller
{
	public function all(PageService $pageService)
    {
        $pages = $pageService->all();
        if ($pages) {
            $pages = PageResource::collection($pages);
            return MyHelper::responseJSON(__('api.pageExists'), Response::HTTP_OK, $pages);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function privacyPolicy(PageService $pageService)
    {
        $page = $pageService->privacyPolicy();
        if ($page) {
            $page = PageResource::make($page);
            return MyHelper::responseJSON(__('api.pageExists'), Response::HTTP_OK, $page);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function termsConditions(PageService $pageService)
    {
        $page = $pageService->termsConditions();
        if ($page) {
            $page = PageResource::make($page);
            return MyHelper::responseJSON(__('api.pageExists'), Response::HTTP_OK, $page);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function aboutUs(PageService $pageService)
    {
        $page = $pageService->aboutUs();
        if ($page) {
            $page = PageResource::make($page);
            return MyHelper::responseJSON(__('api.pageExists'), Response::HTTP_OK, $page);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
