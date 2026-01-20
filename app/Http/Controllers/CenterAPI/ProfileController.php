<?php

namespace App\Http\Controllers\CenterAPI;

use App\Helpers\MyHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\CenterAPI\LanguageRequest;
use App\Http\Requests\CenterAPI\PasswordRequest;
use App\Http\Requests\CenterAPI\ProfileRequest;
use App\Http\Resources\CenterUserResource;
use App\Services\CRUDService;
use App\Services\CenterUserService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller
{
	public function get(CRUDService $cRUDService)
    {
        $center = $cRUDService->find('CenterUser', auth('center_api')->user()->id, ['media'], 0);
        if ($center) {
            $center = CenterUserResource::make($center);
            return MyHelper::responseJSON(__('api.centeruserExists'), Response::HTTP_OK, $center);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(ProfileRequest $request, CRUDService $cRUDService)
    {
        $newRequest = $request->only([
            'name',
            'email',
            'country_code',
            'phone',
            'image',
        ]);
        $newRequest['id'] = auth('center_api')->user()->id;
        $center = $cRUDService->updateOrCreate('CenterUser', $newRequest, 0);
        if ($center) {
            $center = CenterUserResource::make($center);
            return MyHelper::responseJSON(__('api.centeruserExists'), Response::HTTP_OK, $center);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changeLanguage(LanguageRequest $request, CenterUserService $centerUserService)
    {
        $center = $centerUserService->changeLanguage($request->language_id, auth('center_api')->user()->id);
        if ($center) {
            $center = CenterUserResource::make($center);
            return MyHelper::responseJSON(__('api.updateLanguageSuccessfully'), Response::HTTP_OK, $center);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function changePassword(PasswordRequest $request, CenterUserService $centerUserService)
    {
        $center = $centerUserService->changePassword($request->only('current_password', 'password'), auth('center_api')->user()->id);
        if ($center) {
            $center = CenterUserResource::make($center);
            return MyHelper::responseJSON(__('api.updatePasswordSuccessfully'), Response::HTTP_OK, $center);
        } else {
            return MyHelper::responseJSON(__('api.currentPasswordDontMatch'), Response::HTTP_BAD_REQUEST);
        }
    }
}
