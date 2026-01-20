<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthRequest;
use Exception;
use App\Helpers\MyHelper;

class LoginController extends Controller
{
    public function logout()
    {
        auth('admin')->logout();
        return redirect('admin/login');
    }

    public function authenticate(AuthRequest $request)
    {
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];
        
        try {
            if (auth('admin')->attempt($credentials)) {
                if (!auth('admin')->user()->roles()->first()) {
                    return MyHelper::responseJSON('الأدمن ليس لديه دور', 400);
                }
                return MyHelper::responseJSON('تم تسجيل الدخول بنجاح', 200);

            }
            else {
                return MyHelper::responseJSON('فشلت عملية تسجيل الدخول, حقل اسم المستخدم او كلمة المرور غير صحيحة', 400);
            }
        } catch (Exception $e) {
            return MyHelper::responseJSON(__('admin.an_error_occurred'), 500);
        }
    }
}
