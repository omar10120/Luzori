<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\AuthRequest;
use Exception;
use App\Helpers\MyHelper;
use App\Models\CenterUser;
use App\Models\Center;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Info;
use Spatie\Permission\Models\Permission;

class LoginController extends Controller
{

    public function logout()
    {
        auth('center_user')->logout();
        return redirect('center_user/login');
    }

    public function index()
    {
        // Check if there's an auth parameter for cross-domain authentication
        $authData = request()->query('auth');
        
        if ($authData) {
            try {
                // Decrypt the auth data
                $decryptedData = decrypt($authData);
                $userData = json_decode($decryptedData, true);
                
                // Check if the data is valid and not expired
                if ($userData && isset($userData['id'], $userData['email'], $userData['expires'])) {
                    if ($userData['expires'] > now()->timestamp) {
                        // Get the center user
                        $centerUser = \App\Models\CenterUser::withTrashed()
                            ->where('id', $userData['id'])
                            ->where('email', $userData['email'])
                            ->where('statusWeb', 1)
                            ->first();
                        
                        if ($centerUser) {
                            // If 2FA is enabled for this center, require verification; else login immediately
                            if ($this->isTwoFactorEnabled()) {
                                $code = $this->generateVerificationCode();
                                session([
                                    'center_user_pending_verification' => [
                                        'id' => $centerUser->id,
                                        'email' => $centerUser->email,
                                        'expires' => now()->addMinutes(10)->timestamp,
                                        'code' => $code,
                                    ],
                                ]);
                                $this->sendVerificationCodeEmail($centerUser->email, $code);
                                return redirect()->route('center_user.verify.show');
                            }

                            auth('center_user')->login($centerUser);
                            return redirect()->route('center_user.cp');
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Auth data decryption failed: " . $e->getMessage());
            }
            
            // Invalid or expired auth data, show error
            return view('CenterUser.login')->with('error', 'Invalid or expired authentication data');
        }
        
        // Normal login page
        return view('CenterUser.login');
    }

    public function authenticate(AuthRequest $request)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
    
        // Case 1: Login from root domain (luzori.com or www.luzori.com)
        // Middleware will keep default DB, so we need to check all centers
        
        if (count($parts) <= 2 || (count($parts) === 3 && $parts[0] === 'www')) {
            $centers = Center::all();
    
            foreach ($centers as $center) {
                try {
                    // Switch DB dynamically
                    \Config::set('database.connections.mysql.database', $center->database);
                    \DB::purge('mysql');
                    \DB::reconnect('mysql');
    
                    $centerUser = \App\Models\CenterUser::withTrashed()
                        ->where('statusWeb', 1)
                        ->where('email', $request->email)
                        ->first();
    
                    if ($centerUser && \Hash::check($request->password, $centerUser->password)) {
                        // Generate a signed URL with user data for cross-domain authentication
                        $userData = [
                            'id' => $centerUser->id,
                            'email' => $centerUser->email,
                            'center_domain' => $center->domain,
                            'expires' => now()->addMinutes(5)->timestamp
                        ];
                        
                        $encryptedData = encrypt(json_encode($userData));
                        
                        return MyHelper::responseJSON('تم تسجيل الدخول بنجاح', 200, [
                            'redirect_url' => "https://{$center->domain}.luzori.com/center_user/login?auth=" . urlencode($encryptedData)
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error("DB check failed for center {$center->database}: " . $e->getMessage());
                }
            }
    
            return MyHelper::responseJSON('فشلت عملية تسجيل الدخول, حقل اسم المستخدم او كلمة المرور غير صحيحة', 400);
        }
    
        // Case 2: Login from subdomain (center1.luzori.com or www.center1.luzori.com)
        // Middleware already switched DB, so we can use the current connection
        $centerUser = CenterUser::withTrashed()
            ->where('statusWeb', 1)
            ->where('email', $request->email)
            ->first();
    
        if ($centerUser && Hash::check($request->password, $centerUser->password)) {
            if ($this->isTwoFactorEnabled()) {
                // Require verification
                $code = $this->generateVerificationCode();
                session([
                    'center_user_pending_verification' => [
                        'id' => $centerUser->id,
                        'email' => $centerUser->email,
                        'expires' => now()->addMinutes(10)->timestamp,
                        'code' => $code,
                    ],
                ]);
                $this->sendVerificationCodeEmail($centerUser->email, $code);
                return MyHelper::responseJSON('تم إرسال رمز التحقق إلى بريد المسؤول', 200, [
                    'requires_verification' => true,
                    'redirect_url' => route('center_user.verify.show'),
                ]);
            }

            // 2FA disabled: login immediately
            auth('center_user')->login($centerUser);
            return MyHelper::responseJSON('تم تسجيل الدخول بنجاح', 200, [
                'requires_verification' => false,
                'redirect_url' => route('center_user.cp'),
            ]);
        }
    
        return MyHelper::responseJSON('فشلت عملية تسجيل الدخول, حقل اسم المستخدم او كلمة المرور غير صحيحة', 400);
    }
    
    public function showVerifyForm()
    {
        $pending = session('center_user_pending_verification');
        if (!$pending) {
            return redirect()->route('center_user.login.view');
        }
        return view('CenterUser.Components.verify');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $pending = session('center_user_pending_verification');
        if (!$pending) {
            return redirect()->route('center_user.login.view')->withErrors(['code' => 'لا توجد عملية تحقق قيد الانتظار']);
        }

        if (now()->timestamp > ($pending['expires'] ?? 0)) {
            session()->forget('center_user_pending_verification');
            return redirect()->route('center_user.login.view')->withErrors(['code' => 'انتهت صلاحية رمز التحقق، يرجى تسجيل الدخول مرة أخرى']);
        }

        if (trim($request->code) !== (string)($pending['code'] ?? '')) {
            return back()->withErrors(['code' => 'رمز التحقق غير صحيح'])->withInput();
        }

        $centerUser = CenterUser::withTrashed()
            ->where('id', $pending['id'])
            ->where('email', $pending['email'])
            ->where('statusWeb', 1)
            ->first();

        if (!$centerUser) {
            session()->forget('center_user_pending_verification');
            return redirect()->route('center_user.login.view')->withErrors(['code' => 'تعذر إتمام تسجيل الدخول']);
        }

        auth('center_user')->login($centerUser);
        session()->forget('center_user_pending_verification');

        return redirect()->route('center_user.cp');
    }

    public function resendCode(Request $request)
    {
        $pending = session('center_user_pending_verification');
        if (!$pending) {
            return MyHelper::responseJSON('لا توجد عملية تحقق قيد الانتظار', 400);
        }

        $code = $this->generateVerificationCode();
        $pending['code'] = $code;
        $pending['expires'] = now()->addMinutes(10)->timestamp;
        session(['center_user_pending_verification' => $pending]);

        $this->sendVerificationCodeEmail($pending['email'], $code);

        return MyHelper::responseJSON('تم إرسال رمز التحقق مرة أخرى', 200);
    }

    private function generateVerificationCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function sendVerificationCodeEmail(string $userEmail, string $code): void
    {
        try {
            $to = optional(Info::query()->select('email')->first())->email ?: 'support@etechnocode.com';
        } catch (\Exception $e) {   
            \Log::warning('Failed to read recipient email from infos table: ' . $e->getMessage());
            $to = 'support@etechnocode.com';
        }
        $subject = 'Center User Login Verification Code';
        $body = "User Email: {$userEmail}\nVerification Code: {$code}\nValid For: 10 minutes\nTime: " . now()->toDateTimeString();

        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        } catch (\Exception $e) {
            \Log::error('Failed to send verification email: ' . $e->getMessage());
        }
    }

    private function isTwoFactorEnabled(): bool
    {
        try {
            $permission = Permission::where('name', 'VIEW_TWO_FACTOR_AUTH')->first();
            if (!$permission) {
                return false;
            }
            return DB::table('role_has_permissions')->where('permission_id', $permission->id)->exists();
        } catch (\Exception $e) {
            \Log::error('2FA permission check failed: ' . $e->getMessage());
            return false;
        }
    }
    
    
}
