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
