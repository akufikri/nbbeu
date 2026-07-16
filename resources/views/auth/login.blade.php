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
</x-site-layout>
