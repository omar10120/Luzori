<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Http\Requests\CenterUser\AuthRequest;
use App\Helpers\MyHelper;
use App\Models\CenterUser;
use App\Models\Center;
use App\Models\Info;
use App\Models\Invoice_Settings;
use App\Services\TrustedDeviceService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class LoginController extends Controller
{
    public function __construct(private TrustedDeviceService $devices)
    {
    }

    public function logout()
    {
        $user = auth('center_user')->user();
        $centerId = session('trusted_center_id');
        $deviceUuid = session('trusted_device_uuid');

        if ($user && $centerId) {
            $this->devices->endActiveSessionsForUser((int) $centerId, (int) $user->id, $deviceUuid);
            $this->devices->log(
                (int) $centerId,
                'logout',
                (int) $user->id,
                $deviceUuid,
                request()->ip(),
                request()->userAgent()
            );
        }

        auth('center_user')->logout();
        session()->forget(['active_center_domain', 'trusted_device_uuid', 'trusted_center_id', 'trusted_device_id', 'center_user_pending_verification']);

        return redirect('center_user/login');
    }

    public function signup()
    {
        return view('CenterUser.register');
    }

    public function index()
    {
        $authData = request()->query('auth');

        if ($authData) {
            try {
                $decryptedData = decrypt($authData);
                $userData = json_decode($decryptedData, true);

                if ($userData && isset($userData['id'], $userData['email'], $userData['expires'])) {
                    if ($userData['expires'] > now()->timestamp) {
                        $host = request()->getHost();
                        $parts = explode('.', $host);
                        $isBaseOrDashboard = (count($parts) <= 2 || (count($parts) === 3 && $parts[0] === 'www') || in_array('dashboard', $parts));

                        $center = null;
                        if ($isBaseOrDashboard && isset($userData['center_domain'])) {
                            $center = Center::where('domain', $userData['center_domain'])->first();
                            if ($center) {
                                if ($center->expire_date && now()->gt($center->expire_date)) {
                                    return view('CenterUser.login')->with('error', 'Expired center subscription');
                                }
                                Config::set('database.connections.mysql.database', $center->database);
                                DB::purge('mysql');
                                DB::reconnect('mysql');
                            }
                        } else {
                            $dbName = Config::get('database.connections.mysql.database');
                            $center = Center::where('database', $dbName)->first();
                        }

                        $centerUser = CenterUser::withTrashed()
                            ->where('id', $userData['id'])
                            ->where('email', $userData['email'])
                            ->where('statusWeb', 1)
                            ->first();

                        if ($centerUser && $center) {
                            session(['active_center_domain' => $center->domain]);

                            if ($this->isTrustedDevicesEnabled($center)) {
                                $result = $this->handleDeviceGate($center, $centerUser, request(), $userData);
                                if ($result['status'] === 'login') {
                                    return redirect()->route('center_user.cp');
                                }
                                if ($result['status'] === 'otp') {
                                    return redirect()->route('center_user.verify.show');
                                }

                                return view('CenterUser.login')->with('error', $result['message']);
                            }

                            if ($this->isTwoFactorEnabled()) {
                                $this->startLegacyOtp($centerUser);
                                return redirect()->route('center_user.verify.show');
                            }

                            auth('center_user')->login($centerUser);
                            return redirect()->route('center_user.cp');
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Auth data decryption failed: ' . $e->getMessage());
            }

            return view('CenterUser.login')->with('error', 'Invalid or expired authentication data');
        }

        return view('CenterUser.login');
    }

    public function authenticate(AuthRequest $request)
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        $deviceUuid = $this->devices->resolveDeviceUuid($request);
        Cookie::queue(cookie('luzori_device_uuid', $deviceUuid, 60 * 24 * 365));

        $isBaseOrDashboard = (count($parts) <= 2 || (count($parts) === 3 && $parts[0] === 'www') || in_array('dashboard', $parts));

        if ($isBaseOrDashboard) {
            $centers = Center::all();
            $originalDatabase = Config::get('database.connections.mysql.database');

            foreach ($centers as $center) {
                try {
                    if (!$this->switchTenantDatabase($center->database)) {
                        continue;
                    }

                    $centerUser = CenterUser::withTrashed()
                        ->where('statusWeb', 1)
                        ->where('email', $request->email)
                        ->first();

                    if ($centerUser && Hash::check($request->password, $centerUser->password)) {
                        if ($center->expire_date && now()->gt($center->expire_date)) {
                            $this->switchTenantDatabase($originalDatabase);
                            return MyHelper::responseJSON('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.', 400);
                        }

                        if ($this->isTrustedDevicesEnabled($center)) {
                            $gate = $this->evaluateDeviceGate($center, $centerUser, $request, $deviceUuid);
                            if ($gate['status'] === 'blocked' || $gate['status'] === 'limit') {
                                $this->switchTenantDatabase($originalDatabase);
                                return MyHelper::responseJSON($gate['message'], 400);
                            }
                        }

                        $userData = [
                            'id' => $centerUser->id,
                            'email' => $centerUser->email,
                            'center_domain' => $center->domain,
                            'device_uuid' => $deviceUuid,
                            'expires' => now()->addMinutes(10)->timestamp,
                        ];

                        $encryptedData = encrypt(json_encode($userData));
                        $segments = explode('.', $host);
                        if (in_array('127.0.0.1', $segments) || in_array('localhost', $segments)) {
                            $redirectDomain = $host;
                        } else {
                            $baseDomain = implode('.', array_slice($segments, -2));
                            $redirectDomain = "{$center->domain}.dashboard.{$baseDomain}";
                        }

                        $protocol = request()->secure() ? 'https://' : 'http://';

                        return MyHelper::responseJSON('تم تسجيل الدخول بنجاح', 200, [
                            'redirect_url' => "{$protocol}{$redirectDomain}/center_user/login?auth=" . urlencode($encryptedData),
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error("DB check failed for center {$center->database}: " . $e->getMessage());
                }
            }

            $this->switchTenantDatabase($originalDatabase);
            return MyHelper::responseJSON('فشلت عملية تسجيل الدخول, حقل اسم المستخدم او كلمة المرور غير صحيحة', 400);
        }

        $centerUser = CenterUser::withTrashed()
            ->where('statusWeb', 1)
            ->where('email', $request->email)
            ->first();

        $dbName = Config::get('database.connections.mysql.database');
        $center = Center::where('database', $dbName)->first();

        if ($centerUser && Hash::check($request->password, $centerUser->password) && $center) {
            if ($center->expire_date && now()->gt($center->expire_date)) {
                return MyHelper::responseJSON('انتهت فترة صلاحية هذا المركز. يرجى التواصل مع الدعم الفني.', 400);
            }
            session(['active_center_domain' => $center->domain]);

            if ($this->isTrustedDevicesEnabled($center)) {
                $result = $this->handleDeviceGate($center, $centerUser, $request, ['device_uuid' => $deviceUuid]);
                if ($result['status'] === 'login') {
                    return MyHelper::responseJSON('تم تسجيل الدخول بنجاح', 200, [
                        'requires_verification' => false,
                        'redirect_url' => route('center_user.cp'),
                    ]);
                }
                if ($result['status'] === 'otp') {
                    return MyHelper::responseJSON('تم إرسال رمز التحقق إلى بريد المسؤول', 200, [
                        'requires_verification' => true,
                        'redirect_url' => route('center_user.verify.show'),
                    ]);
                }

                return MyHelper::responseJSON($result['message'], 400);
            }

            if ($this->isTwoFactorEnabled()) {
                $this->startLegacyOtp($centerUser);
                return MyHelper::responseJSON('تم إرسال رمز التحقق إلى بريد المسؤول', 200, [
                    'requires_verification' => true,
                    'redirect_url' => route('center_user.verify.show'),
                ]);
            }

            auth('center_user')->login($centerUser);
            return MyHelper::responseJSON('تم تسجيل الدخول بنجاح', 200, [
                'requires_verification' => false,
                'redirect_url' => route('center_user.cp'),
            ]);
        }

        if ($center && $this->isTrustedDevicesEnabled($center)) {
            $this->devices->log(
                (int) $center->id,
                'login_failed',
                $centerUser?->id,
                $deviceUuid,
                $request->ip(),
                $request->userAgent(),
                'Invalid credentials'
            );
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

        $mode = $pending['mode'] ?? 'legacy';

        if ($mode === 'trusted') {
            return $this->verifyTrustedOtp($request, $pending);
        }

        if (trim($request->code) !== (string) ($pending['code'] ?? '')) {
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

        $mode = $pending['mode'] ?? 'legacy';

        if ($mode === 'trusted') {
            $center = Center::find($pending['center_id'] ?? null);
            if (!$center) {
                return MyHelper::responseJSON('تعذر إرسال الرمز', 400);
            }

            $deviceUuid = (string) ($pending['device_uuid'] ?? $this->devices->resolveDeviceUuid($request));
            $this->devices->createAndSendOtp($center, (int) $pending['id'], (string) $pending['email'], $deviceUuid, $request);
            $pending['expires'] = now()->addMinutes(10)->timestamp;
            $pending['device_uuid'] = $deviceUuid;
            session(['center_user_pending_verification' => $pending]);

            return MyHelper::responseJSON('تم إرسال رمز التحقق مرة أخرى', 200);
        }

        $code = $this->generateVerificationCode();
        $pending['code'] = $code;
        $pending['expires'] = now()->addMinutes(10)->timestamp;
        session(['center_user_pending_verification' => $pending]);
        $this->sendVerificationCode($pending['email'], $code);

        return MyHelper::responseJSON('تم إرسال رمز التحقق مرة أخرى', 200);
    }

    private function verifyTrustedOtp(Request $request, array $pending)
    {
        $center = Center::find($pending['center_id'] ?? null);
        if (!$center || !$this->isTrustedDevicesEnabled($center)) {
            session()->forget('center_user_pending_verification');
            return redirect()->route('center_user.login.view')->withErrors(['code' => 'تعذر إتمام تسجيل الدخول']);
        }

        $deviceUuid = (string) ($pending['device_uuid'] ?? $this->devices->resolveDeviceUuid($request));
        $ok = $this->devices->verifyOtp($center, (int) $pending['id'], $deviceUuid, (string) $request->code);

        if (!$ok) {
            $this->devices->log(
                (int) $center->id,
                'otp_failed',
                (int) $pending['id'],
                $deviceUuid,
                $request->ip(),
                $request->userAgent(),
                'Invalid OTP'
            );
            return back()->withErrors(['code' => 'رمز التحقق غير صحيح'])->withInput();
        }

        $this->devices->log(
            (int) $center->id,
            'otp_verified',
            (int) $pending['id'],
            $deviceUuid,
            $request->ip(),
            $request->userAgent()
        );

        $existing = $this->devices->findDevice((int) $center->id, $deviceUuid);
        if ($this->devices->limitReached($center, $existing)) {
            session()->forget('center_user_pending_verification');
            return redirect()->route('center_user.login.view')
                ->withErrors(['code' => __('field.device_limit_reached')]);
        }

        $this->switchTenantDatabase($center->database);
        $centerUser = CenterUser::withTrashed()
            ->where('id', $pending['id'])
            ->where('email', $pending['email'])
            ->where('statusWeb', 1)
            ->first();

        if (!$centerUser) {
            session()->forget('center_user_pending_verification');
            return redirect()->route('center_user.login.view')->withErrors(['code' => 'تعذر إتمام تسجيل الدخول']);
        }

        $device = $this->devices->trustDevice($center, (int) $centerUser->id, $deviceUuid, $request);
        $this->devices->startSession($device, $request);
        $this->rememberTrustedSession($center, $device);
        auth('center_user')->login($centerUser);
        session(['active_center_domain' => $center->domain]);
        session()->forget('center_user_pending_verification');
        $this->devices->log(
            (int) $center->id,
            'login_success',
            (int) $centerUser->id,
            $deviceUuid,
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->route('center_user.cp');
    }

    private function handleDeviceGate(Center $center, CenterUser $centerUser, Request $request, array $payload = []): array
    {
        $deviceUuid = $this->devices->resolveDeviceUuid($request, $payload);
        Cookie::queue(cookie('luzori_device_uuid', $deviceUuid, 60 * 24 * 365));

        $gate = $this->evaluateDeviceGate($center, $centerUser, $request, $deviceUuid);
        if ($gate['status'] === 'login') {
            $device = $gate['device'];
            $this->devices->startSession($device, $request);
            $this->rememberTrustedSession($center, $device);
            auth('center_user')->login($centerUser);
            $this->devices->log(
                (int) $center->id,
                'login_success',
                (int) $centerUser->id,
                $deviceUuid,
                $request->ip(),
                $request->userAgent(),
                'Trusted device'
            );

            return ['status' => 'login'];
        }

        if ($gate['status'] === 'blocked' || $gate['status'] === 'limit') {
            return $gate;
        }

        $this->devices->createAndSendOtp($center, (int) $centerUser->id, (string) $centerUser->email, $deviceUuid, $request);
        session([
            'center_user_pending_verification' => [
                'mode' => 'trusted',
                'id' => $centerUser->id,
                'email' => $centerUser->email,
                'center_id' => $center->id,
                'device_uuid' => $deviceUuid,
                'expires' => now()->addMinutes(10)->timestamp,
            ],
        ]);

        return ['status' => 'otp'];
    }

    private function evaluateDeviceGate(Center $center, CenterUser $centerUser, Request $request, string $deviceUuid): array
    {
        $device = $this->devices->findDevice((int) $center->id, $deviceUuid);

        if ($device && $device->is_blocked) {
            $this->devices->log(
                (int) $center->id,
                'login_failed',
                (int) $centerUser->id,
                $deviceUuid,
                $request->ip(),
                $request->userAgent(),
                'Blocked device'
            );

            return [
                'status' => 'blocked',
                'message' => __('field.device_blocked_login'),
            ];
        }

        if ($device && !$device->is_blocked) {
            return [
                'status' => 'login',
                'device' => $device,
            ];
        }

        if ($this->devices->limitReached($center, $device)) {
            $this->devices->log(
                (int) $center->id,
                'login_failed',
                (int) $centerUser->id,
                $deviceUuid,
                $request->ip(),
                $request->userAgent(),
                'Device limit reached'
            );

            return [
                'status' => 'limit',
                'message' => __('field.device_limit_reached'),
            ];
        }

        return ['status' => 'otp'];
    }

    private function rememberTrustedSession(Center $center, $device): void
    {
        session([
            'trusted_center_id' => $center->id,
            'trusted_device_uuid' => $device->device_uuid,
            'trusted_device_id' => $device->id,
        ]);
    }

    private function startLegacyOtp(CenterUser $centerUser): void
    {
        $code = $this->generateVerificationCode();
        session([
            'center_user_pending_verification' => [
                'mode' => 'legacy',
                'id' => $centerUser->id,
                'email' => $centerUser->email,
                'expires' => now()->addMinutes(10)->timestamp,
                'code' => $code,
            ],
        ]);
        $this->sendVerificationCode($centerUser->email, $code);
    }

    private function isTrustedDevicesEnabled(?Center $center): bool
    {
        return (bool) ($center?->trusted_devices_enabled ?? false);
    }

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

    private function sendVerificationCode(string $userEmail, string $code): void
    {
        $settings = Invoice_Settings::query()->first();
        $channel = $settings?->otp_channel ?? 'email';

        if ($channel === 'sms') {
            $this->sendVerificationCodeSms($settings, $userEmail, $code);
        } else {
            $this->sendVerificationCodeEmail($settings, $userEmail, $code);
        }
    }

    private function sendVerificationCodeSms(?Invoice_Settings $settings, string $userEmail, string $code): void
    {
        $phones = $settings?->otp_phones ?? [];

        if (!is_array($phones) || empty($phones)) {
            \Log::warning('OTP channel = sms but invoice_settings.otp_phones is empty; falling back to email');
            $this->sendVerificationCodeEmail($settings, $userEmail, $code);
            return;
        }

        $message = "Your verification code: {$code}. Valid for 10 minutes. User: {$userEmail}";
        $lang = app()->getLocale() === 'ar' ? 2 : 1;

        try {
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
                        \Log::warning("SMS OTP to {$to} failed (API error)", [
                            'user_email' => $userEmail,
                            'result' => $result,
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
        } catch (\Throwable $e) {
            \Log::error('Login OTP SMS failed, falling back to email: ' . $e->getMessage(), [
                'user_email' => $userEmail,
            ]);
            $this->sendVerificationCodeEmail($settings, $userEmail, $code);
        }
    }

    private function sendVerificationCodeEmail(?Invoice_Settings $settings, string $userEmail, string $code): void
    {
        $recipients = [];

        try {
            $infoEmail = optional(Info::query()->select('email')->first())->email;
            if ($infoEmail && filter_var($infoEmail, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = $infoEmail;
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to read Info.email: ' . $e->getMessage());
        }

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
        } catch (\Exception $e) {
            \Log::error('Login OTP email failed: ' . $e->getMessage(), [
                'user_email' => $userEmail,
                'recipients' => $recipients,
            ]);
        }
    }

    private function generateVerificationCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
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
