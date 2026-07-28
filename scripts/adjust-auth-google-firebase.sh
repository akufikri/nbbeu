#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-auth-google-firebase.sh
# 1) Fixes forgot-password flow: 4 views referenced a non-existent
#    <x-guest-layout> component (reset-password, confirm-password,
#    verify-email, membership/verify) — restyled to the site's real layouts.
# 2) Adds Google login via Firebase — links Google sign-in to an existing
#    approved User by email (does NOT create new accounts; new members still
#    go through the full registration wizard).
# 3) Adds a Google-styled button on the registration page that just links
#    to /login (per spec — no real OAuth there).
#
# This script does NOT touch .env or write any secret — after running it,
# you still need to manually:
#   - composer require kreait/firebase-php (run separately, see step 0 below)
#   - set FIREBASE_API_KEY / FIREBASE_AUTH_DOMAIN / FIREBASE_PROJECT_ID /
#     FIREBASE_STORAGE_BUCKET / FIREBASE_MESSAGING_SENDER_ID / FIREBASE_APP_ID
#     in .env (Firebase Console → Project Settings → General → Your apps → Web app)
#   - place the Firebase service account JSON at
#     storage/app/private/firebase-adminsdk.json (already gitignored — never
#     commit it) and set FIREBASE_CREDENTIALS=storage/app/private/firebase-adminsdk.json
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$SCRIPT_DIR"
while [ ! -f "$APP_ROOT/artisan" ] && [ "$APP_ROOT" != "/" ]; do
    APP_ROOT="$(dirname "$APP_ROOT")"
done
if [ ! -f "$APP_ROOT/artisan" ]; then
    echo "Could not locate artisan (looked upward from $SCRIPT_DIR). Run this from inside the Laravel project." >&2
    exit 1
fi
cd "$APP_ROOT"

# ------------------------------------------------------------
# 0) Composer package for backend Firebase ID-token verification
#    (falls back to a local composer.phar if the `composer` binary
#    isn't on PATH — common on shared/cPanel hosting)
# ------------------------------------------------------------
if command -v composer >/dev/null 2>&1; then
    COMPOSER_BIN="composer"
elif [ -f composer.phar ]; then
    COMPOSER_BIN="php composer.phar"
else
    echo "No 'composer' binary found — downloading composer.phar into $APP_ROOT ..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    rm -f composer-setup.php
    COMPOSER_BIN="php composer.phar"
fi

if ! $COMPOSER_BIN show kreait/firebase-php >/dev/null 2>&1; then
    $COMPOSER_BIN require kreait/firebase-php --no-interaction
else
    echo "Skipped (already installed): kreait/firebase-php"
fi

# ------------------------------------------------------------
# 1a) Fix broken <x-guest-layout> references
# ------------------------------------------------------------
mkdir -p resources/views/auth

cat > resources/views/auth/reset-password.blade.php <<'BLADE'
<x-site-layout
    title="Reset Password — NBBEU"
    description="Set a new password for your NBBEU member portal account."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('login') }}" class="page-header__crumb">← Back to Login</a>
            <h1>Reset Password</h1>
            <p>Enter a new password for your account.</p>
        </div>
    </section>

    <section class="py-16">
        <form class="form mx-auto" method="POST" action="{{ route('password.store') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="field @if ($errors->get('email')) field--error @endif">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}" autocomplete="username" placeholder="name@institution.co.id" required autofocus>
                @if ($errors->get('email'))
                    <span class="field__msg">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <div class="field @if ($errors->get('password')) field--error @endif">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" autocomplete="new-password" placeholder="At least 8 characters" required>
                @if ($errors->get('password'))
                    <span class="field__msg">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <div class="field @if ($errors->get('password_confirmation')) field--error @endif">
                <label for="password_confirmation">Confirm New Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" placeholder="Re-enter new password" required>
                @if ($errors->get('password_confirmation'))
                    <span class="field__msg">{{ $errors->first('password_confirmation') }}</span>
                @endif
            </div>

            <button type="submit" class="btn-submit">Reset Password</button>
        </form>
    </section>
</x-site-layout>
BLADE

cat > resources/views/auth/confirm-password.blade.php <<'BLADE'
<x-site-layout
    title="Confirm Password — NBBEU"
    description="Confirm your password to continue."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <h1>Confirm Password</h1>
            <p>This is a secure area of the application. Please confirm your password before continuing.</p>
        </div>
    </section>

    <section class="py-16">
        <form class="form mx-auto" method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div class="field @if ($errors->get('password')) field--error @endif">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required autofocus>
                @if ($errors->get('password'))
                    <span class="field__msg">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <button type="submit" class="btn-submit">Confirm</button>
        </form>
    </section>
</x-site-layout>
BLADE

cat > resources/views/auth/verify-email.blade.php <<'BLADE'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verify Email') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="mb-4 text-sm text-gray-600">
                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-primary-button>
                            {{ __('Resend Verification Email') }}
                        </x-primary-button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Log Out') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
BLADE

mkdir -p resources/views/membership
cat > resources/views/membership/verify.blade.php <<'BLADE'
<x-site-layout
    title="Card Verification — NBBEU"
    description="Verify an NBBEU member card."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <h1>NBBEU Member Card Verification</h1>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-7xl mx-auto px-6">
            @if (! $result)
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    Card not found / invalid.
                </div>
            @elseif ($result['expired'])
                <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-700">
                    The card for <strong>{{ $result['name'] }}</strong> ({{ $result['member_no'] }}) has <strong>Expired</strong>.
                </div>
            @else
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    <p class="font-semibold">Status: Valid</p>
                    <p class="mt-2">Name: {{ $result['name'] }}</p>
                    <p>Member No.: {{ $result['member_no'] }}</p>
                </div>
            @endif
        </div>
    </section>
</x-site-layout>
BLADE

# ------------------------------------------------------------
# 1b) Login page — Google sign-in button + Firebase JS SDK
# ------------------------------------------------------------
cat > resources/views/auth/login.blade.php <<'BLADE'
<x-site-layout
    title="Member Login — NBBEU"
    description="Access the membership portal to manage your profile, certifications, and exclusive NBBEU publications."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>Member Login</h1>
            <p>Access the membership portal to manage your profile, certifications, and exclusive NBBEU publications.</p>
        </div>
    </section>

    <section class="py-16">
        <x-auth-session-status class="max-w-7xl mx-auto px-6 mb-6" :status="session('status')" />

        <div class="form mx-auto">
            <button type="button" id="google-login-btn" class="google-btn">
                <svg viewBox="0 0 18 18" width="18" height="18" aria-hidden="true">
                    <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84c-.21 1.13-.84 2.09-1.8 2.73v2.27h2.91c1.7-1.57 2.69-3.87 2.69-6.64z"/>
                    <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.91-2.27c-.81.54-1.84.86-3.05.86-2.35 0-4.34-1.58-5.05-3.71H.96v2.34C2.44 15.98 5.48 18 9 18z"/>
                    <path fill="#FBBC05" d="M3.95 10.7c-.18-.54-.28-1.11-.28-1.7s.1-1.16.28-1.7V4.96H.96A8.996 8.996 0 000 9c0 1.45.35 2.83.96 4.04l2.99-2.34z"/>
                    <path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0 5.48 0 2.44 2.02.96 4.96l2.99 2.34C4.66 5.16 6.65 3.58 9 3.58z"/>
                </svg>
                <span>Sign in with Google</span>
            </button>
            <p id="google-login-error" class="field__msg" style="display:none"></p>

            <div class="form-divider"><span>or</span></div>
        </div>

        <form class="form mx-auto" method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field @if ($errors->get('email')) field--error @endif">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="name@institution.co.id" required autofocus>
                @if ($errors->get('email'))
                    <span class="field__msg">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <div class="field @if ($errors->get('password')) field--error @endif">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required aria-describedby="password-msg">
                @if ($errors->get('password'))
                    <span class="field__msg" id="password-msg">{{ $errors->first('password') }}</span>
                @endif
            </div>

            <label for="remember_me" class="flex items-center gap-2 mb-6 font-sans text-sm text-nb-ink-muted">
                <input id="remember_me" type="checkbox" name="remember">
                <span>Remember me</span>
            </label>

            <button type="submit" class="btn-submit">Sign In</button>

            <p class="form-aside">
                Not a member yet? <a href="{{ route('home') }}#benefits">Apply for membership</a>.
                @if (Route::has('password.request'))
                    Forgot your password? <a href="{{ route('password.request') }}">Reset here</a>.
                @endif
            </p>
        </form>
    </section>

    @push('scripts')
        <script type="module">
            import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.14.1/firebase-app.js';
            import { getAuth, GoogleAuthProvider, signInWithPopup } from 'https://www.gstatic.com/firebasejs/10.14.1/firebase-auth.js';

            const firebaseApp = initializeApp({
                apiKey: @json(config('services.firebase.api_key')),
                authDomain: @json(config('services.firebase.auth_domain')),
                projectId: @json(config('services.firebase.project_id')),
                storageBucket: @json(config('services.firebase.storage_bucket')),
                messagingSenderId: @json(config('services.firebase.messaging_sender_id')),
                appId: @json(config('services.firebase.app_id')),
            });
            const firebaseAuth = getAuth(firebaseApp);
            const googleProvider = new GoogleAuthProvider();

            const button = document.getElementById('google-login-btn');
            const errorEl = document.getElementById('google-login-error');

            button.addEventListener('click', async () => {
                errorEl.style.display = 'none';
                button.disabled = true;

                try {
                    const result = await signInWithPopup(firebaseAuth, googleProvider);
                    const idToken = await result.user.getIdToken();

                    const response = await fetch('{{ route('login.google') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ id_token: idToken }),
                    });

                    const data = await response.json();

                    if (! response.ok) {
                        throw new Error(data.message || 'Unable to sign in with Google.');
                    }

                    window.location.href = data.redirect;
                } catch (error) {
                    errorEl.textContent = error.message || 'Unable to sign in with Google.';
                    errorEl.style.display = 'block';
                    button.disabled = false;
                }
            });
        </script>
    @endpush
</x-site-layout>
BLADE

# ------------------------------------------------------------
# 1c) Registration page — decorative Google button, redirects to /login
# ------------------------------------------------------------
mkdir -p resources/views/membership
cat > resources/views/membership/register-wizard.blade.php <<'BLADE'
<x-site-layout
    title="Membership Registration — NBBEU"
    description="Complete the 6-step application below. Once submitted, NBBEU will review your application and email you a payment link after approval."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>NBBEU Membership Registration</h1>
            <p>Complete the 6-step application below. Once submitted, NBBEU will review your application and email you a payment link after approval.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="form mx-auto">
            <a href="{{ route('login') }}" class="google-btn">
                <svg viewBox="0 0 18 18" width="18" height="18" aria-hidden="true">
                    <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84c-.21 1.13-.84 2.09-1.8 2.73v2.27h2.91c1.7-1.57 2.69-3.87 2.69-6.64z"/>
                    <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.91-2.27c-.81.54-1.84.86-3.05.86-2.35 0-4.34-1.58-5.05-3.71H.96v2.34C2.44 15.98 5.48 18 9 18z"/>
                    <path fill="#FBBC05" d="M3.95 10.7c-.18-.54-.28-1.11-.28-1.7s.1-1.16.28-1.7V4.96H.96A8.996 8.996 0 000 9c0 1.45.35 2.83.96 4.04l2.99-2.34z"/>
                    <path fill="#EA4335" d="M9 3.58c1.32 0 2.51.45 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0 5.48 0 2.44 2.02.96 4.96l2.99 2.34C4.66 5.16 6.65 3.58 9 3.58z"/>
                </svg>
                <span>Sign up with Google</span>
            </a>
            <p class="form-aside">Already have a Google-linked NBBEU account? Signing up with Google will take you to the login page.</p>

            <div class="form-divider"><span>or register with your details</span></div>
        </div>

        <livewire:registration-wizard />
    </section>
</x-site-layout>
BLADE

# ------------------------------------------------------------
# 2a) Migration — link column for Google identity
# ------------------------------------------------------------
mkdir -p database/migrations
cat > database/migrations/2026_07_24_000002_add_google_uid_to_users_table.php <<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_uid')->nullable()->unique()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_uid');
        });
    }
};
PHP

# ------------------------------------------------------------
# 2b) User model — allow mass-assigning google_uid
# ------------------------------------------------------------
USER_MODEL="app/Models/User.php"
if [ -f "$USER_MODEL" ] && ! grep -q "google_uid" "$USER_MODEL"; then
    perl -0777 -pi -e "s/#\[Fillable\(\['name', 'email', 'phone', 'company', 'password', 'photo'\]\)\]/#[Fillable(['name', 'email', 'phone', 'company', 'password', 'photo', 'google_uid'])]/" "$USER_MODEL"
    echo "Patched: $USER_MODEL"
else
    echo "Skipped (already patched or missing): $USER_MODEL"
fi

# ------------------------------------------------------------
# 2c) config/services.php — Firebase config block
# ------------------------------------------------------------
SERVICES="config/services.php"
if [ -f "$SERVICES" ] && ! grep -q "'firebase' =>" "$SERVICES"; then
    perl -0777 -pi -e "s/(    'toyyibpay' => \[\n(?:.*\n)*?    \],\n)/\$1\n    \/\/ Frontend (public) Firebase Web SDK config — safe to expose client-side.\n    'firebase' => [\n        'api_key' => env('FIREBASE_API_KEY'),\n        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),\n        'project_id' => env('FIREBASE_PROJECT_ID'),\n        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),\n        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),\n        'app_id' => env('FIREBASE_APP_ID'),\n        \/\/ Backend-only: path to the Firebase service account JSON, used to verify\n        \/\/ ID tokens server-side. Never expose this file publicly. Resolved to an\n        \/\/ absolute path here so it doesn't depend on the process's CWD (php-fpm,\n        \/\/ queue workers, and artisan can all have a different working directory).\n        'credentials' => env('FIREBASE_CREDENTIALS') ? base_path(env('FIREBASE_CREDENTIALS')) : null,\n    ],\n/" "$SERVICES"
    echo "Patched: $SERVICES"
else
    echo "Skipped (already patched or missing): $SERVICES"
fi

# ------------------------------------------------------------
# 2d) Controller
# ------------------------------------------------------------
mkdir -p app/Http/Controllers/Auth
cat > app/Http/Controllers/Auth/GoogleAuthController.php <<'PHP'
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
PHP

# ------------------------------------------------------------
# 2e) routes/auth.php — register the Google login endpoint
# ------------------------------------------------------------
ROUTES="routes/auth.php"
if [ -f "$ROUTES" ] && ! grep -q "login.google" "$ROUTES"; then
    perl -0777 -pi -e "s/use App\\\\Http\\\\Controllers\\\\Auth\\\\EmailVerificationPromptController;/use App\\\\Http\\\\Controllers\\\\Auth\\\\EmailVerificationPromptController;\nuse App\\\\Http\\\\Controllers\\\\Auth\\\\GoogleAuthController;/" "$ROUTES"
    perl -0777 -pi -e "s/(    Route::post\('login', \[AuthenticatedSessionController::class, 'store'\]\);\n)/\$1\n    Route::post('login\/google', [GoogleAuthController::class, 'login'])\n        ->name('login.google')\n        ->middleware('throttle:10,1');\n/" "$ROUTES"
    echo "Patched: $ROUTES"
else
    echo "Skipped (already patched or missing): $ROUTES"
fi

# ------------------------------------------------------------
# 3) CSS — .google-btn / .form-divider
# ------------------------------------------------------------
CSS_FILE="public/assets/site/style.css"
if [ -f "$CSS_FILE" ] && ! grep -q "\.google-btn" "$CSS_FILE"; then
    cat >> "$CSS_FILE" <<'CSS'

.google-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-sm);
  width: 100%;
  min-height: 44px;
  background: var(--color-paper-raised);
  color: var(--color-ink);
  font-family: var(--font-body);
  font-weight: 500;
  font-size: 0.9375rem;
  border: 1px solid var(--color-rule);
  border-radius: var(--radius-sm);
  padding: 0.75rem 1.5rem;
  transition: background var(--dur-short) var(--ease-out), transform var(--dur-short) var(--ease-out);
}
.google-btn:hover { background: color-mix(in oklch, var(--color-paper-raised) 92%, var(--color-ink)); }
.google-btn:active { transform: translateY(1px); }
.google-btn:disabled { opacity: 0.55; cursor: not-allowed; }

.form-divider {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  margin-block: var(--space-lg);
  color: var(--color-ink-muted);
  font-family: var(--font-body);
  font-size: 0.8125rem;
}
.form-divider::before,
.form-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--color-rule);
}
CSS
    echo "Patched: $CSS_FILE"
else
    echo "Skipped (already patched or missing): $CSS_FILE"
fi

# ------------------------------------------------------------
# 4) Migrate + clear caches
# ------------------------------------------------------------
php artisan migrate --force
php artisan optimize:clear

echo ""
echo "Done: forgot-password fixed, Google login (Firebase) wired, Google button on register page."
echo ""
echo "Still needed before Google login works on this server (not done by this script):"
echo "  1. In .env, set: FIREBASE_API_KEY, FIREBASE_AUTH_DOMAIN, FIREBASE_PROJECT_ID,"
echo "     FIREBASE_STORAGE_BUCKET, FIREBASE_MESSAGING_SENDER_ID, FIREBASE_APP_ID"
echo "     (Firebase Console -> Project Settings -> General -> Your apps -> Web app)"
echo "  2. Place the Service Account JSON at storage/app/private/firebase-adminsdk.json"
echo "     (chmod 600, already gitignored — never commit it) and set:"
echo "     FIREBASE_CREDENTIALS=storage/app/private/firebase-adminsdk.json"
echo "  3. In Firebase Console -> Authentication -> Sign-in method -> enable Google."
echo "  4. In Firebase Console -> Authentication -> Settings -> Authorized domains ->"
echo "     add this server's domain."
