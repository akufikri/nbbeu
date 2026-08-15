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
                Not a member yet? <a href="{{ route('registration.create') }}">Apply for membership</a>.
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
