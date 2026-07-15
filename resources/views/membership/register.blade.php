<x-guest-layout>
    <h1 class="text-lg font-semibold mb-4">NBBEU Membership Registration</h1>

    <p class="text-sm text-gray-600 mb-4">
        Fill in the details below, then proceed to payment. Your application will be reviewed
        by an admin after payment is received.
    </p>

    <form method="POST" action="{{ route('registration.store') }}">
        @csrf

        <div>
            <x-input-label for="name" value="Full Name" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" placeholder="Full name" required autofocus />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" placeholder="name@email.com" required />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" value="Phone Number" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" placeholder="+60 12-345 6789" required />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="company" value="Company / Bank" />
            <x-text-input id="company" class="block mt-1 w-full" type="text" name="company" :value="old('company')" placeholder="Company / bank name" required />
            <x-input-error :messages="$errors->get('company')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 mr-4" href="{{ route('registration.status') }}">
                Already applied? Check status
            </a>

            <x-primary-button>
                Continue to Payment
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
