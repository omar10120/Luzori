<?php

namespace App\Http\Controllers\AppAPI;

use App\Http\Controllers\Controller;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Exception;

class FirebaseAuthController extends Controller
{
    /**
     * Handle Firebase social login.
     * Expects: { "token": "...", "provider": "google" }
     */
    public function socialLogin(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'provider' => 'required|string|in:google,phone,email',
            'fcm_token' => 'nullable|string',
        ]);

        try {
            $factory = (new Factory)->withServiceAccount(config('firebase.credentials.file'));
            $auth = $factory->createAuth();

            // Verify the Firebase ID token
            $verifiedIdToken = $auth->verifyIdToken($request->token);
            $firebaseUserId = $verifiedIdToken->claims()->get('sub');

            // Get user details from Firebase
            $firebaseUser = $auth->getUser($firebaseUserId);
            
            // Extract details
            $email = $firebaseUser->email;
            $name = $firebaseUser->displayName ?? 'User';
            $nameParts = explode(' ', $name);
            $firstName = $nameParts[0] ?? 'User';
            $lastName = $nameParts[1] ?? '';

            if (!$email) {
                 return response()->json([
                     'status' => 'error',
                     'message' => 'Email address is required from social provider.',
                 ], 400);
            }

            // Create or update the user based on email
            $user = AppUser::updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'firebase_uid' => $firebaseUserId,
                    'provider' => $request->provider,
                ]
            );

            if ($request->filled('fcm_token')) {
                $user->fcmTokens()->firstOrCreate(['token' => $request->fcm_token]);
            }

            // Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'User successfully logged in via ' . ucfirst($request->provider),
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or expired Firebase token.',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}
