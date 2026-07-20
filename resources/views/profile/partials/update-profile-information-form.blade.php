<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Update your photo and personal details.') }}
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

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6" x-data="{ preview: null }">
        @csrf
        @method('patch')

        <div>
            <x-input-label :value="__('Profile Photo')" />
            <div class="mt-2 flex items-center gap-4">
                <div class="relative">
                    <template x-if="preview">
                        <img :src="preview" alt="" class="w-20 h-20 rounded-full object-cover ring-2 ring-nbbeu-navy/10">
                    </template>
                    <template x-if="!preview">
                        @if ($user->photo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($user->photo) }}" alt="" class="w-20 h-20 rounded-full object-cover ring-2 ring-nbbeu-navy/10">
                        @else
                            <div class="w-20 h-20 rounded-full bg-nbbeu-navy/10 flex items-center justify-center text-nbbeu-navy font-semibold text-xl">
                                {{ collect(explode(' ', $user->name))->map(fn ($n) => mb_substr($n, 0, 1))->take(2)->implode('') }}
                            </div>
                        @endif
                    </template>
                </div>
                <div>
                    <label for="photo" class="inline-block px-3 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer">
                        {{ __('Change Photo') }}
                    </label>
                    <input
                        id="photo" name="photo" type="file" accept="image/*" class="hidden"
                        x-on:change="preview = $event.target.files.length ? URL.createObjectURL($event.target.files[0]) : null"
                    />
                    <p class="mt-1 text-xs text-gray-500">{{ __('Used on your member card. JPG/PNG, max 2MB.') }}</p>
                </div>
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('photo')" />
        </div>

        <div class="grid sm:grid-cols-2 gap-6">
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
        </div>

        @include('profile.partials.member-profile-fields')

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
