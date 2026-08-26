<?php

namespace App\Http\Controllers\AppAPI;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Helpers\MyHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
}
