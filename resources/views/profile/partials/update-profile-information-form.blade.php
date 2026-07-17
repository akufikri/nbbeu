<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('You may update your name, phone number, and profile photo (used on your member card). Email and other details from your registration are locked — contact the secretariat to change them.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <div class="mt-6">
        <x-input-label :value="__('Email')" />
        <p class="mt-1 text-sm text-gray-800">{{ $user->email }}</p>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <p class="text-sm mt-2 text-gray-800">
                {{ __('Your email address is unverified.') }}

                <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Click here to re-send the verification email.') }}
                </button>
            </p>

            @if (session('status') === 'verification-link-sent')
                <p class="mt-2 font-medium text-sm text-green-600">
                    {{ __('A new verification link has been sent to your email address.') }}
                </p>
            @endif
        @endif
    </div>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="photo" :value="__('Profile Photo')" />
            @if ($user->photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($user->photo) }}" alt="" class="mt-2 w-20 h-20 rounded-full object-cover">
            @endif
            <input id="photo" name="photo" type="file" accept="image/*" class="mt-2 block w-full text-sm text-gray-600" />
            <p class="mt-1 text-xs text-gray-500">Used on your member card. JPG/PNG, max 2MB.</p>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" placeholder="Full name" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="phone" :value="__('Phone Number')" />
            <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" placeholder="+60 12-345 6789" required />
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
