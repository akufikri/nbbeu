<x-site-layout
    title="Membership Registration — NBBEU"
    description="Fill in the details below, then proceed to payment. Your application will be reviewed by an admin after payment is received."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>NBBEU Membership Registration</h1>
            <p>Fill in the details below, then proceed to payment. Your application will be reviewed by an admin after payment is received.</p>
        </div>
    </section>

    <section class="py-16">
        <form class="form mx-auto" method="POST" action="{{ route('registration.store') }}">
            @csrf

            <div class="field @if ($errors->get('name')) field--error @endif">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Full name" required autofocus>
                @if ($errors->get('name'))
                    <span class="field__msg">{{ $errors->first('name') }}</span>
                @endif
            </div>

            <div class="field @if ($errors->get('email')) field--error @endif">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@email.com" required>
                @if ($errors->get('email'))
                    <span class="field__msg">{{ $errors->first('email') }}</span>
                @endif
            </div>

            <div class="field @if ($errors->get('phone')) field--error @endif">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+60 12-345 6789" required>
                @if ($errors->get('phone'))
                    <span class="field__msg">{{ $errors->first('phone') }}</span>
                @endif
            </div>

            <div class="field @if ($errors->get('company')) field--error @endif">
                <label for="company">Company / Bank</label>
                <input type="text" id="company" name="company" value="{{ old('company') }}" placeholder="Company / bank name" required>
                @if ($errors->get('company'))
                    <span class="field__msg">{{ $errors->first('company') }}</span>
                @endif
            </div>

            <button type="submit" class="btn-submit">Continue to Payment</button>

            <p class="form-aside">
                Already applied? <a href="{{ route('registration.status') }}">Check status</a>.
            </p>
        </form>
    </section>
</x-site-layout>
