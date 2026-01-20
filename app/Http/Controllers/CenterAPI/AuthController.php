<?php

namespace App\Http\Controllers\CenterAPI;

use App\Http\Controllers\Controller;
use App\Helpers\MyHelper;
use App\Http\Requests\CenterAPI\AuthRequest;
use App\Http\Requests\CenterAPI\CheckCodeRequest;
use App\Http\Requests\CenterAPI\ForgetRequest;
use App\Http\Requests\CenterAPI\ResetRequest;
use App\Http\Resources\CenterUserResource;
use App\Services\AuthCenterUserService;
use App\Services\CenterUserService;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function login(AuthRequest $request, AuthCenterUserService $authCenterUserService)
    {
        $centeruser = $authCenterUserService->login($request->only('username', 'password', 'fcm_token'), $reason);
        if ($centeruser) {
            $centeruser['center_user'] = CenterUserResource::make($centeruser['center_user']);
            return MyHelper::responseJSON(__('api.loginSuccessfully'), Response::HTTP_OK, $centeruser);
        } else {
            if ($reason == 'CENTER_USER_BLOCKED') {
                return MyHelper::responseJSON(__('api.centerUserIsBlocked'), Response::HTTP_BAD_REQUEST);
            } else if ($reason == 'INVALID_PASSWORD') {
                return MyHelper::responseJSON(__('api.emailOrPasswordDontMatch'), Response::HTTP_BAD_REQUEST);
            } else {
                return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function forget(ForgetRequest $request, AuthCenterUserService $authCenterUserService)
    {
        $centeruser = $authCenterUserService->forget($request->username, $reason);
        if ($centeruser) {
            $centeruser = CenterUserResource::make($centeruser);
            $type = (preg_match("/^[^@]*@[^@]*\.[^@]*$/", $request->username)) ? 'email' : 'phone';
            if ($type == 'email') {
                return MyHelper::responseJSON(__('api.sendEmailSuccessfully'), Response::HTTP_OK, $centeruser);
            } else {
                return MyHelper::responseJSON(__('api.sendSMSSuccessfully'), Response::HTTP_OK, $centeruser);
            }
        } else {
            if ($reason == 'CENTER_USER_BLOCKED') {
                return MyHelper::responseJSON(__('api.centerUserIsBlocked'), Response::HTTP_BAD_REQUEST);
            } else {
                return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function checkCode(CheckCodeRequest $request, AuthCenterUserService $authCenterUserService)
    {
        $centeruser = $authCenterUserService->checkCode($request->only('username', 'verification_code'), $reason);
        if ($centeruser) {
            $centeruser = CenterUserResource::make($centeruser);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $centeruser);
        } else {
            if ($reason == 'CODE_NOT_MATCH') {
                return MyHelper::responseJSON(__('api.incorrectCode'), Response::HTTP_BAD_REQUEST);
            } elseif ($reason == 'CENTER_USER_BLOCKED') {
                return MyHelper::responseJSON(__('api.centerUserIsBlocked'), Response::HTTP_BAD_REQUEST);
            } else {
                return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function reset(ResetRequest $request, AuthCenterUserService $authCenterUserService)
    {
        $centeruser = $authCenterUserService->reset($request->only('username', 'password', 'fcm_token'), $reason);
        if ($centeruser) {
            $centeruser['center_user'] = CenterUserResource::make($centeruser['center_user']);
            return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $centeruser);
        } else {
            if ($reason == 'ACCOUNT_NOT_READY_TO_RESET') {
                return MyHelper::responseJSON(__('api.accountNotReady'), Response::HTTP_BAD_REQUEST);
            } elseif ($reason == 'CENTER_USER_BLOCKED') {
                return MyHelper::responseJSON(__('api.centerUserIsBlocked'), Response::HTTP_BAD_REQUEST);
            } else {
                return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function logout()
    {
        auth('center_api')->user()?->tokens()?->delete();
        auth('center_api')->user()?->fcmTokens()?->delete();
        return MyHelper::responseJSON(__('api.logoutSuccessfully'), Response::HTTP_OK);
    }

    public function delete(CenterUserService $centerUserService)
    {
        $centeruser = $centerUserService->delete(auth('center_api')->user()->id);
        if ($centeruser) {
            $centeruser = CenterUserResource::make($centeruser);
            return MyHelper::responseJSON(__('api.deleteSuccessfully'), Response::HTTP_OK, $centeruser);
        } else {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
