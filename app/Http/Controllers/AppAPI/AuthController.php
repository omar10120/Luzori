<?php

namespace App\Http\Controllers\AppAPI;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Helpers\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'country_code' => 'nullable|string|max:20',
            'phone' => 'required|string|unique:users,phone',    
            'password' => 'required|string|min:6|confirmed',
            'address' => 'nullable|string|max:255',
            'birth' => 'nullable|date',
            'gender' => 'nullable|string|max:255',
            'fcm_token' => 'nullable|string',
        ]);

        $user = AppUser::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'country_code' => $request->country_code,
            'phone' => $request->phone,
            'password' => $request->password,
            'is_active' => 1,
            'wallet' => 0,
            'address' => $request->address,
            'birth' => $request->birth,
            'gender' => $request->gender,
        ]);

        if ($request->filled('fcm_token')) {
            $user->fcmTokens()->firstOrCreate(['token' => $request->fcm_token]);
        }

        $token = $user->createToken('app_auth_token')->plainTextToken;

        return MyHelper::responseJSON(__('api.registerSuccessfully'), Response::HTTP_CREATED, [
            'user' => $user,
            'token' => $token
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'address' => 'nullable|string|max:255',
            'birth' => 'nullable|date',
            'gender' => 'nullable|string|max:255',
            'fcm_token' => 'nullable|string',
        ]);

        $user = AppUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return MyHelper::responseJSON(__('auth.failed'), Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->is_active) {
            return MyHelper::responseJSON(__('auth.inactive'), Response::HTTP_FORBIDDEN);
        }

        if ($request->filled('fcm_token')) {
            $user->fcmTokens()->firstOrCreate(['token' => $request->fcm_token]);
        }

        $token = $user->createToken('app_auth_token')->plainTextToken;

        return MyHelper::responseJSON(__('api.loginSuccessfully'), Response::HTTP_OK, [
            'user' => $user,
            'token' => $token
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user();
        $user->image_url = $user->getFirstMediaUrl('PrimaryImage');
        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK, $user);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'nullable|string|max:255',
            'birth' => 'nullable|date',
            'gender' => 'nullable|string|max:255',
            'fcm_token' => 'nullable|string',
        ]);

        $data = $request->only(['first_name', 'last_name', 'email', 'phone', 'address', 'birth', 'gender']);
        
        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update(array_filter($data));

        if ($request->hasFile('image')) {
            $user->clearMediaCollection('PrimaryImage');
            $user->addMediaFromRequest('image')->toMediaCollection('PrimaryImage');
        }

        if ($request->filled('fcm_token')) {
            $user->fcmTokens()->firstOrCreate(['token' => $request->fcm_token]);
        }

        $user->image_url = $user->getFirstMediaUrl('PrimaryImage');

            return MyHelper::responseJSON(__('api.updateSuccessfully') ?? 'Profile updated successfully', Response::HTTP_OK, $user);
    }

    public function logout(Request $request)
    {
        if ($request->filled('fcm_token')) {
            $request->user()->fcmTokens()->where('token', $request->fcm_token)->delete();
        }

        $request->user()->currentAccessToken()->delete();
        return MyHelper::responseJSON(__('api.doneSuccessfully'), Response::HTTP_OK);
    }

    /**
     * Send a password-reset OTP to the user's email (uses config/mail.php).
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = trim($request->email);
        $user = AppUser::where('email', $email)->first();

        if (!$user) {
            return MyHelper::responseJSON(__('api.userNotFound'), Response::HTTP_NOT_FOUND);
        }

        if (!$user->is_active) {
            return MyHelper::responseJSON(__('auth.inactive'), Response::HTTP_FORBIDDEN);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::connection('central')->table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        try {
            Mail::send('emails.app_forgot_password', [
                'name' => $user->name,
                'code' => $code,
                'minutes' => 10,
                'subject' => __('api.forgot_password_mail_subject'),
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                    ->subject(__('api.forgot_password_mail_subject'));
            });
        } catch (\Throwable $e) {
            Log::error('App forgot password email failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return MyHelper::responseJSON(__('api.sendEmailSuccessfully'), Response::HTTP_OK, [
            'email' => $user->email,
            'expires_in_minutes' => 10,
        ]);
    }

    /**
     * Verify OTP and set a new password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email = trim($request->email);
        $user = AppUser::where('email', $email)->first();

        if (!$user) {
            return MyHelper::responseJSON(__('api.userNotFound'), Response::HTTP_NOT_FOUND);
        }

        $reset = DB::connection('central')->table('password_resets')
            ->where('email', $user->email)
            ->first();

        if (!$reset) {
            return MyHelper::responseJSON(__('api.incorrectCode'), Response::HTTP_BAD_REQUEST);
        }

        if (Carbon::parse($reset->created_at)->addMinutes(10)->isPast()) {
            DB::connection('central')->table('password_resets')->where('email', $user->email)->delete();
            return MyHelper::responseJSON(__('api.codeExpired'), Response::HTTP_BAD_REQUEST);
        }

        if (!Hash::check($request->code, $reset->token)) {
            return MyHelper::responseJSON(__('api.incorrectCode'), Response::HTTP_BAD_REQUEST);
        }

        $user->update([
            'password' => $request->password,
        ]);

        DB::connection('central')->table('password_resets')->where('email', $user->email)->delete();
        $user->tokens()->delete();

        $token = $user->createToken('app_auth_token')->plainTextToken;

        return MyHelper::responseJSON(__('api.updatePasswordSuccessfully'), Response::HTTP_OK, [
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'nullable|string',
        ]);

        // Confirm password when the account has a local password
        if (!empty($user->password) && $user->provider === 'email') {
            $request->validate(['password' => 'required|string']);
            if (!Hash::check($request->password, $user->password)) {
                return MyHelper::responseJSON(__('api.passwordDontMatch'), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        try {
            $user->tokens()->delete();
            $user->fcmTokens()->delete();
            $user->clearMediaCollection('PrimaryImage');

            // Free unique fields so the same email/phone can register again
            $user->update([
                'email' => $user->email ? 'deleted_' . $user->id . '_' . time() . '@deleted.local' : null,
                'phone' => 'deleted_' . $user->id . '_' . time(),
                'firebase_uid' => null,
                'is_active' => 0,
            ]);

            $user->delete();

            return MyHelper::responseJSON(__('api.accountDeleted'), Response::HTTP_OK);
        } catch (\Throwable $e) {
            return MyHelper::responseJSON(__('api.unknownError'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
