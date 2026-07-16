<x-site-layout
    title="Forgot Password — NBBEU"
    description="Reset your NBBEU member portal password."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('login') }}" class="page-header__crumb">← Back to Login</a>
            <h1>Forgot Password</h1>
            <p>Enter your email address and we will send you a password reset link.</p>
        </div>
    </section>

    <section class="py-16">
        <x-auth-session-status class="max-w-7xl mx-auto px-6 mb-6" :status="session('status')" />

        <form class="form mx-auto" method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field @if ($errors->get('email')) field--error @endif">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" placeholder="name@institution.co.id" required autofocus>
                @if ($errors->get('email'))
                    <span class="field__msg">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <button type="submit" class="btn-submit">Email Password Reset Link</button>

            <p class="form-aside">
                Remembered your password? <a href="{{ route('login') }}">Sign in here</a>.
            </p>
        </form>
    </section>
</x-site-layout>
