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
