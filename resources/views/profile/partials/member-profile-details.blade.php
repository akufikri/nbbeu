<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Membership Details') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('These details were submitted during registration and are locked. Contact the secretariat if you need to update them.') }}
        </p>
    </header>

    <dl class="mt-6 grid sm:grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="text-gray-500">{{ __('Gender') }}</dt>
            <dd class="font-medium text-gray-900">{{ $user->memberProfile->gender ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Race') }}</dt>
            <dd class="font-medium text-gray-900">{{ $user->memberProfile->race ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Date of Birth') }}</dt>
            <dd class="font-medium text-gray-900">{{ $user->memberProfile->date_of_birth?->format('d M Y') ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Occupation') }}</dt>
            <dd class="font-medium text-gray-900">{{ $user->memberProfile->occupation ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Employer') }}</dt>
            <dd class="font-medium text-gray-900">{{ $user->memberProfile->employer_name ?? '-' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ __('Bank') }}</dt>
            <dd class="font-medium text-gray-900">{{ $user->memberProfile->bank_name ?? '-' }}</dd>
        </div>
    </dl>
</section>
