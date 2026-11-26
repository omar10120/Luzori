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
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Models\Info;
use Spatie\Permission\Models\Permission;
use App\Models\Invoice_Settings;

class LoginController extends Controller
{

    public function logout()
    {
        auth('center_user')->logout();
        session()->forget('active_center_domain');
        return redirect('center_user/login');
    }

    public function signup()
    {
        return view('CenterUser.register');
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
                        
                        // If we are on dashboard or base domain, we need to find the right database based on the auth token data
                        $host = request()->getHost();
                        $parts = explode('.', $host);
                        $isBaseOrDashboard = (count($parts) <= 2 || (count($parts) === 3 && $parts[0] === 'www') || in_array('dashboard', $parts));

                        if ($isBaseOrDashboard && isset($userData['center_domain'])) {
                            \Log::info("Cross-domain auth attempt. Domain: " . $userData['center_domain']);
                            $center = Center::where('domain', $userData['center_domain'])->first();
                            if ($center) {
                                if ($center->expire_date && now()->gt($center->expire_date)) {
                                    return view('CenterUser.login')->with('error', 'Expired center subscription');
                                }
                                \Log::info("Switching to database: " . $center->database);
                                \Config::set('database.connections.mysql.database', $center->database);
                                \DB::purge('mysql');
                                \DB::reconnect('mysql');
                            } else {
                                \Log::warning("Center not found for domain: " . $userData['center_domain']);
                            }
                        }

                        // Get the center user
                        $centerUser = \App\Models\CenterUser::withTrashed()
                            ->where('id', $userData['id'])
                            ->where('email', $userData['email'])
                            ->where('statusWeb', 1)
                            ->first();
                        
                        if ($centerUser) {
                            // Set active center domain in session for dashboard/root domain DB switching
                            session(['active_center_domain' => $userData['center_domain']]);

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
                                $this->sendVerificationCode($centerUser->email, $code);
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
    
        // Case 1: Login from root domain (luzori.com or www.luzori.com) or dashboard
        // Middleware will keep default DB, so we need to check all centers
        
        $isBaseOrDashboard = (count($parts) <= 2 || (count($parts) === 3 && $parts[0] === 'www') || in_array('dashboard', $parts));

        if ($isBaseOrDashboard) {
            $centers = Center::all();
            $originalDatabase = \Config::get('database.connections.mysql.database');
    
            foreach ($centers as $center) {
                try {
                    if (!$this->switchTenantDatabase($center->database)) {
                        continue;
                    }
    
                    $centerUser = CenterUser::withTrashed()
                        ->where('statusWeb', 1)
                        ->where('email', $request->email)
                        ->first();
    
                    if ($centerUser && \Hash::check($request->password, $centerUser->password)) {
                        if ($center->expire_date && now()->gt($center->expire_date)) {
                            $this->switchTenantDatabase($originalDatabase);
                            return MyHelper::responseJSON('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.', 400);
                        }
                        // Generate a signed URL with user data for cross-domain authentication
                        $userData = [
                            'id' => $centerUser->id,
                            'email' => $centerUser->email,
                            'center_domain' => $center->domain,
                            'expires' => now()->addMinutes(10)->timestamp
                        ];
                        
                        $encryptedData = encrypt(json_encode($userData));
                        
                        // Determine redirect domain
                        $segments = explode('.', $host);
                        if (in_array('127.0.0.1', $segments) || in_array('localhost', $segments)) {
                            $redirectDomain = $host;
                        } else {
                            // Extract base domain (e.g., luzori.com) and prepend the center's domain + dashboard
                            $baseDomain = implode('.', array_slice($segments, -2));
                            $redirectDomain = "{$center->domain}.dashboard.{$baseDomain}";
                        }
                        
                        $protocol = request()->secure() ? 'https://' : 'http://';

                        return MyHelper::responseJSON('تم تسجيل الدخول بنجاح', 200, [
                            'redirect_url' => "{$protocol}{$redirectDomain}/center_user/login?auth=" . urlencode($encryptedData)
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error("DB check failed for center {$center->database}: " . $e->getMessage());
                }
            }
            
            $this->switchTenantDatabase($originalDatabase);
    
            return MyHelper::responseJSON('فشلت عملية تسجيل الدخول, حقل اسم المستخدم او كلمة المرور غير صحيحة', 400);
        }
    
        // Case 2: Login from subdomain (center1.luzori.com or www.center1.luzori.com)
        // Middleware already switched DB, so we can use the current connection
        $centerUser = CenterUser::withTrashed()
            ->where('statusWeb', 1)
            ->where('email', $request->email)
            ->first();
    
        if ($centerUser && Hash::check($request->password, $centerUser->password)) {
            // Set active center domain for session
            $dbName = \Config::get('database.connections.mysql.database');
            $center = Center::where('database', $dbName)->first();
            if ($center) {
                if ($center->expire_date && now()->gt($center->expire_date)) {
                    return MyHelper::responseJSON('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.', 400);
                }
                session(['active_center_domain' => $center->domain]);
            }

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
                $this->sendVerificationCode($centerUser->email, $code);
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

        $this->sendVerificationCode($pending['email'], $code);

        return MyHelper::responseJSON('تم إرسال رمز التحقق مرة أخرى', 200);
    }

    /**
     * Point the shared mysql connection at a tenant database without reconnecting.
     */
    private function switchTenantDatabase(?string $database): bool
    {
        if (!is_string($database) || $database === '') {
            return false;
        }

        Config::set('database.connections.mysql.database', $database);

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $database)) {
            DB::purge('mysql');
            DB::reconnect('mysql');

            return true;
        }
        $connection = DB::connection('mysql');

        try {
            $connection->getPdo()->exec('USE `'.$database.'`');
        } catch (\Throwable $e) {
            DB::purge('mysql');
            $connection = DB::connection('mysql');
            $connection->reconnect();
        }

        $connection->setDatabaseName($database);

        return true;
    }
    /**
 * Dispatch the verification code via whichever channel is configured
 * in Invoice_Settings (sms | email). Falls back to email on any SMS failure.
 */
private function sendVerificationCode(string $userEmail, string $code): void
{
    $settings = Invoice_Settings::query()->first();
    $channel  = $settings?->otp_channel ?? 'email';

    if ($channel === 'sms') {
        $this->sendVerificationCodeSms($settings, $userEmail, $code);
    } else {
        $this->sendVerificationCodeEmail($settings, $userEmail, $code);
    }
}

/**
 * Send the verification code as an SMS to every configured otp_phones entry.
 * Falls back to email when:
 *   • otp_phones is empty / not an array
 *   • every SMS send fails
 *   • the SMS gateway throws
 */
/**
 * Send the verification code as an SMS to every configured otp_phones entry.
 * Falls back to email when:
 *   • otp_phones is empty / not an array
 *   • every SMS send fails (either returns success=false, or throws)
 *   • the SMS gateway class cannot be resolved
 */
private function sendVerificationCodeSms(?Invoice_Settings $settings, string $userEmail, string $code): void
{
    $phones = $settings?->otp_phones ?? [];

    if (!is_array($phones) || empty($phones)) {
        \Log::warning('OTP channel = sms but invoice_settings.otp_phones is empty; falling back to email');
        $this->sendVerificationCodeEmail($settings, $userEmail, $code);
        return;
    }

    $message = "Your verification code: {$code}. Valid for 10 minutes. User: {$userEmail}";

    // Match SMSGatewayService's expected lang codes:
    //   1 = English, 2 = Arabic  (same as SaleOtpService)
    $lang = app()->getLocale() === 'ar' ? 2 : 1;

    try {
        /** @var \App\Services\SMSGatewayService $smsGateway */
        $smsGateway = app(\App\Services\SMSGatewayService::class);

        $sent = 0;

        foreach ($phones as $rawPhone) {
            $to = preg_replace('/\D+/', '', (string) $rawPhone);
            if ($to === '') {
                continue;
            }

            try {
                $result = $smsGateway->sendSMS($to, $message, $lang);

                if (!empty($result['success'])) {
                    $sent++;
                } else {
                    // API-level failure — no exception thrown, so log explicitly
                    \Log::warning("SMS OTP to {$to} failed (API error)", [
                        'user_email' => $userEmail,
                        'result'     => $result,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning("SMS OTP to {$to} threw: " . $e->getMessage(), [
                    'user_email' => $userEmail,
                ]);
            }
        }

        if ($sent === 0) {
            throw new \RuntimeException('No SMS could be delivered');
        }

        \Log::info('Login OTP sent via SMS', [
            'user_email' => $userEmail,
            'count'      => $sent,
        ]);
    } catch (\Throwable $e) {
        \Log::error('Login OTP SMS failed, falling back to email: ' . $e->getMessage(), [
            'user_email' => $userEmail,
        ]);
        $this->sendVerificationCodeEmail($settings, $userEmail, $code);
    }
}

    /**
     * Send the verification code by email to Info.email + invoice_settings.emails.
     */
    private function sendVerificationCodeEmail(?Invoice_Settings $settings, string $userEmail, string $code): void
    {
        $recipients = [];

        // 1) Info.email — always included
        try {
            $infoEmail = optional(Info::query()->select('email')->first())->email;
            if ($infoEmail && filter_var($infoEmail, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = $infoEmail;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to read Info.email: ' . $e->getMessage());
        }

        // 2) Invoice_Settings.emails
        try {
            $emails = $settings?->emails ?? [];
            if (is_array($emails)) {
                foreach ($emails as $email) {
                    $email = is_string($email) ? trim($email) : '';
                    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $recipients[] = $email;
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to read invoice_settings.emails: ' . $e->getMessage());
        }

        // 3) Last-resort fallback
        if (empty($recipients)) {
            $recipients[] = 'support@etechnocode.com';
        }

        $recipients = array_values(array_unique(array_map('strtolower', $recipients)));

        $subject = 'Center User Login Verification Code';
        $body = "User Email: {$userEmail}\n"
            . "Verification Code: {$code}\n"
            . "Valid For: 10 minutes\n"
            . "Time: " . now()->toDateTimeString();

        try {
            Mail::raw($body, function ($message) use ($recipients, $subject) {
                $message->to($recipients)->subject($subject);
            });
            \Log::info('Login OTP sent via email', [
                'user_email' => $userEmail,
                'recipients' => $recipients,
            ]);
        } catch (\Exception $e) {
            \Log::error('Login OTP email failed: ' . $e->getMessage(), [
                'user_email' => $userEmail,
                'recipients' => $recipients,
            ]);
        }
}


    private function generateVerificationCode(): string
    {
        return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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
