<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;

class GoogleAuthController extends Controller
{
    /**
     * Google sign-in is for existing approved members only — it links a
     * Google account to an already-approved User row by email, it does not
     * create new accounts. New members still go through the full
     * registration wizard (IC, sponsor, payment, admin review).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $credentialsPath = config('services.firebase.credentials');

        if (! $credentialsPath) {
            Log::error('Google login attempted but FIREBASE_CREDENTIALS is not configured.');

            return response()->json([
                'message' => 'Google sign-in is not available right now. Please use your email and password.',
            ], 503);
        }

        try {
            $auth = (new Factory)->withServiceAccount($credentialsPath)->createAuth();
            $verifiedToken = $auth->verifyIdToken($request->input('id_token'));
        } catch (FailedToVerifyToken $e) {
            return response()->json([
                'message' => 'We could not verify your Google sign-in. Please try again.',
            ], 401);
        }

        $email = $verifiedToken->claims()->get('email');
        $googleUid = $verifiedToken->claims()->get('sub');

        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'No membership account found for this Google account. Please register as a member first.',
            ], 404);
        }

        if ($user->google_uid !== $googleUid) {
            $user->forceFill(['google_uid' => $googleUid])->save();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => route('dashboard'),
        ]);
    }
}
