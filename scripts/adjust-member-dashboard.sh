#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-member-dashboard.sh
# 1) Profile page: all member_profiles fields become editable (name/phone/photo
#    already were), with a redesigned photo upload + preview.
# 2) Member dashboard: new "Latest News" section (reuses the same published
#    Post query as the public landing page).
# 3) Remove "Union Dues" and "Renewal" links from the member nav (desktop +
#    mobile) — routes/controllers untouched, just the nav entries.
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$SCRIPT_DIR"
while [ ! -f "$APP_ROOT/artisan" ] && [ "$APP_ROOT" != "/" ]; do
    APP_ROOT="$(dirname "$APP_ROOT")"
done
if [ ! -f "$APP_ROOT/artisan" ]; then
    echo "Could not locate artisan (looked upward from $SCRIPT_DIR). Run this from inside the Laravel project." >&2
    exit 1
fi
cd "$APP_ROOT"

# ------------------------------------------------------------
# 1) ProfileUpdateRequest — widen validation to all member_profiles fields
# ------------------------------------------------------------
mkdir -p app/Http/Requests
cat > app/Http/Requests/ProfileUpdateRequest.php <<'PHP'
<?php

namespace App\Http\Requests;

use App\Models\MemberProfile;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'photo' => ['nullable', 'image', 'max:2048'],

            'gender' => ['nullable', 'string', 'in:'.implode(',', array_keys(MemberProfile::GENDERS))],
            'race' => ['nullable', 'string', 'in:'.implode(',', array_keys(MemberProfile::RACES))],
            'race_sub_group' => ['nullable', 'string', 'max:50'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'ic_no' => [
                'nullable', 'string', 'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $exists = MemberProfile::where('ic_no_hash', MemberProfile::hashIcNo($value))
                        ->where('user_id', '!=', $this->user()->id)
                        ->exists();

                    if ($exists) {
                        $fail('This IC number is already registered.');
                    }
                },
            ],
            'postal_address' => ['nullable', 'string'],
            'residential_address' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'employer_name' => ['nullable', 'string', 'max:255'],
            'employer_address' => ['nullable', 'string'],
            'employment_date' => ['nullable', 'date'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_branch' => ['nullable', 'string', 'max:255'],
            'bank_address' => ['nullable', 'string'],
            'office_tel' => ['nullable', 'string', 'max:20'],
            'office_fax' => ['nullable', 'string', 'max:20'],
            'present_salary' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
PHP

# ------------------------------------------------------------
# 2) ProfileController — split user vs member_profiles updates
# ------------------------------------------------------------
mkdir -p app/Http/Controllers
cat > app/Http/Controllers/ProfileController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\MemberProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $userData = array_intersect_key($data, array_flip(['name', 'phone']));

        if ($request->hasFile('photo')) {
            $userData['photo'] = $request->file('photo')->store('members', 'cloudinary');
        }

        $request->user()->fill($userData)->save();

        $profileData = array_diff_key($data, array_flip(['name', 'phone', 'photo']));

        MemberProfile::updateOrCreate(['user_id' => $request->user()->id], $profileData);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
PHP

# ------------------------------------------------------------
# 3) Profile edit page — drop the old separate read-only details card
# ------------------------------------------------------------
mkdir -p resources/views/profile
cat > resources/views/profile/edit.blade.php <<'BLADE'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
BLADE

# ------------------------------------------------------------
# 4) Profile info form — redesigned avatar upload + preview, all fields
# ------------------------------------------------------------
mkdir -p resources/views/profile/partials
cat > resources/views/profile/partials/update-profile-information-form.blade.php <<'BLADE'
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
BLADE

# ------------------------------------------------------------
# 5) Membership details — now editable fields (was a read-only <dl>)
# ------------------------------------------------------------
cat > resources/views/profile/partials/member-profile-fields.blade.php <<'BLADE'
@php $profile = $user->memberProfile; @endphp

<div class="pt-6 border-t border-gray-100">
    <h3 class="text-base font-medium text-gray-900">{{ __('Membership Details') }}</h3>
    <p class="mt-1 text-sm text-gray-600">{{ __('This information appears on your member card and certificate.') }}</p>

    <div class="mt-6 grid sm:grid-cols-2 gap-6">
        <div>
            <x-input-label for="gender" :value="__('Gender')" />
            <select id="gender" name="gender" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-</option>
                @foreach (\App\Models\MemberProfile::GENDERS as $value => $label)
                    <option value="{{ $value }}" @selected(old('gender', $profile?->gender) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('gender')" />
        </div>

        <div>
            <x-input-label for="race" :value="__('Race')" />
            <select id="race" name="race" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                <option value="">-</option>
                @foreach (\App\Models\MemberProfile::RACES as $value => $label)
                    <option value="{{ $value }}" @selected(old('race', $profile?->race) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('race')" />
        </div>

        <div>
            <x-input-label for="race_sub_group" :value="__('Sub-Ethnic Group')" />
            <x-text-input id="race_sub_group" name="race_sub_group" type="text" class="mt-1 block w-full" :value="old('race_sub_group', $profile?->race_sub_group)" placeholder="e.g. Kadazan, Dusun, Murut" />
            <x-input-error class="mt-2" :messages="$errors->get('race_sub_group')" />
        </div>

        <div>
            <x-input-label for="date_of_birth" :value="__('Date of Birth')" />
            <x-text-input id="date_of_birth" name="date_of_birth" type="date" class="mt-1 block w-full" :value="old('date_of_birth', $profile?->date_of_birth?->toDateString())" />
            <x-input-error class="mt-2" :messages="$errors->get('date_of_birth')" />
        </div>

        <div>
            <x-input-label for="place_of_birth" :value="__('Place of Birth')" />
            <x-text-input id="place_of_birth" name="place_of_birth" type="text" class="mt-1 block w-full" :value="old('place_of_birth', $profile?->place_of_birth)" />
            <x-input-error class="mt-2" :messages="$errors->get('place_of_birth')" />
        </div>

        <div>
            <x-input-label for="ic_no" :value="__('IC Number')" />
            <x-text-input id="ic_no" name="ic_no" type="text" class="mt-1 block w-full" :value="old('ic_no', $profile?->ic_no)" />
            <x-input-error class="mt-2" :messages="$errors->get('ic_no')" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="postal_address" :value="__('Postal Address')" />
            <textarea id="postal_address" name="postal_address" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('postal_address', $profile?->postal_address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('postal_address')" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="residential_address" :value="__('Residential Address')" />
            <textarea id="residential_address" name="residential_address" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('residential_address', $profile?->residential_address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('residential_address')" />
        </div>

        <div>
            <x-input-label for="occupation" :value="__('Occupation')" />
            <x-text-input id="occupation" name="occupation" type="text" class="mt-1 block w-full" :value="old('occupation', $profile?->occupation)" />
            <x-input-error class="mt-2" :messages="$errors->get('occupation')" />
        </div>

        <div>
            <x-input-label for="position" :value="__('Position')" />
            <x-text-input id="position" name="position" type="text" class="mt-1 block w-full" :value="old('position', $profile?->position)" />
            <x-input-error class="mt-2" :messages="$errors->get('position')" />
        </div>

        <div>
            <x-input-label for="employer_name" :value="__('Employer Name')" />
            <x-text-input id="employer_name" name="employer_name" type="text" class="mt-1 block w-full" :value="old('employer_name', $profile?->employer_name)" />
            <x-input-error class="mt-2" :messages="$errors->get('employer_name')" />
        </div>

        <div>
            <x-input-label for="employment_date" :value="__('Employment Start Date')" />
            <x-text-input id="employment_date" name="employment_date" type="date" class="mt-1 block w-full" :value="old('employment_date', $profile?->employment_date?->toDateString())" />
            <x-input-error class="mt-2" :messages="$errors->get('employment_date')" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="employer_address" :value="__('Employer Address')" />
            <textarea id="employer_address" name="employer_address" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('employer_address', $profile?->employer_address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('employer_address')" />
        </div>

        <div>
            <x-input-label for="office_tel" :value="__('Office Tel')" />
            <x-text-input id="office_tel" name="office_tel" type="text" class="mt-1 block w-full" :value="old('office_tel', $profile?->office_tel)" />
            <x-input-error class="mt-2" :messages="$errors->get('office_tel')" />
        </div>

        <div>
            <x-input-label for="office_fax" :value="__('Office Fax')" />
            <x-text-input id="office_fax" name="office_fax" type="text" class="mt-1 block w-full" :value="old('office_fax', $profile?->office_fax)" />
            <x-input-error class="mt-2" :messages="$errors->get('office_fax')" />
        </div>

        <div>
            <x-input-label for="bank_name" :value="__('Bank Name')" />
            <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" :value="old('bank_name', $profile?->bank_name)" />
            <x-input-error class="mt-2" :messages="$errors->get('bank_name')" />
        </div>

        <div>
            <x-input-label for="bank_branch" :value="__('Bank Branch')" />
            <x-text-input id="bank_branch" name="bank_branch" type="text" class="mt-1 block w-full" :value="old('bank_branch', $profile?->bank_branch)" />
            <x-input-error class="mt-2" :messages="$errors->get('bank_branch')" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="bank_address" :value="__('Bank Address')" />
            <textarea id="bank_address" name="bank_address" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('bank_address', $profile?->bank_address) }}</textarea>
            <x-input-error class="mt-2" :messages="$errors->get('bank_address')" />
        </div>

        <div>
            <x-input-label for="present_salary" :value="__('Present Salary (RM)')" />
            <x-text-input id="present_salary" name="present_salary" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('present_salary', $profile?->present_salary)" />
            <x-input-error class="mt-2" :messages="$errors->get('present_salary')" />
        </div>
    </div>
</div>
BLADE

# ------------------------------------------------------------
# 6) Drop the old read-only details partial (merged into member-profile-fields)
# ------------------------------------------------------------
rm -f resources/views/profile/partials/member-profile-details.blade.php

# ------------------------------------------------------------
# 7) DashboardController — add latest published posts
# ------------------------------------------------------------
mkdir -p app/Http/Controllers/Member
cat > app/Http/Controllers/Member/DashboardController.php <<'PHP'
<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('member.dashboard', [
            'user' => $user,
            'latestPayment' => $user->payments()->latest('id')->first(),
            'renewalDue' => $user->renewal_expires_at && $user->renewal_expires_at->lte(now()->addDays(30)),
            'latestPosts' => Post::where('status', 'published')->latest('published_at')->limit(3)->get(),
        ]);
    }
}
PHP

# ------------------------------------------------------------
# 8) Dashboard view — add "Latest News" section
# ------------------------------------------------------------
mkdir -p resources/views/member
cat > resources/views/member/dashboard.blade.php <<'BLADE'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Member Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if ($user->status === 'pending')
                @php
                    $paymentDone = $latestPayment && $latestPayment->status === 'paid';
                @endphp
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="font-medium text-gray-900">Application Status</h3>
                    <div class="mt-4 flex items-center">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-white bg-nbbeu-navy">1</div>
                            <span class="mt-1 text-xs text-gray-600">Submitted</span>
                        </div>
                        <div class="flex-1 h-0.5 mx-2 {{ $paymentDone ? 'bg-nbbeu-navy' : 'bg-gray-200' }}"></div>
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold {{ $paymentDone ? 'text-white bg-nbbeu-navy' : 'text-gray-500 bg-gray-200' }}">2</div>
                            <span class="mt-1 text-xs text-gray-600">Payment</span>
                        </div>
                        <div class="flex-1 h-0.5 mx-2 bg-gray-200"></div>
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold text-gray-500 bg-gray-200">3</div>
                            <span class="mt-1 text-xs text-gray-600">Admin Review</span>
                        </div>
                    </div>
                    <p class="mt-4 text-sm text-amber-700 bg-amber-50 rounded-md p-3">
                        Your application is still awaiting admin review.
                        @if (! $paymentDone)
                            Payment is also not yet complete —
                            <a href="{{ route('registration.status', ['email' => $user->email]) }}" class="underline">check status</a>.
                        @endif
                    </p>
                </div>
            @elseif ($user->status === 'rejected')
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="font-medium text-gray-900">Application Status</h3>
                    <p class="mt-2 text-sm text-red-700 bg-red-50 rounded-md p-3">
                        Your application was rejected. {{ $user->rejection_reason ? 'Reason: '.$user->rejection_reason : '' }}
                    </p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-900">Membership Status</h3>

                @if ($user->status === 'approved')
                    <div class="mt-3 grid sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Member No.</p>
                            <p class="font-mono font-medium">{{ $user->member_no }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Valid Until</p>
                            <p class="font-medium">{{ $user->renewal_expires_at?->format('d M Y') }}</p>
                        </div>
                    </div>

                    @if ($renewalDue)
                        <div class="mt-4 text-sm bg-red-50 border border-red-200 rounded-md p-3">
                            <p class="text-red-700 font-medium">Your membership needs renewal soon.</p>
                            <a href="{{ route('member.renewal.index') }}" class="mt-2 inline-block px-4 py-2 text-sm rounded-sm bg-nbbeu-gold text-nbbeu-navy-deep font-medium">
                                Go to Renewal
                            </a>
                        </div>
                    @endif
                @else
                    <p class="mt-2 text-sm text-gray-500">Your membership details will appear here once your application is approved.</p>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-900">Profile Details</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $user->name }} &bull; {{ $user->email }} &bull; {{ $user->phone }}</p>
                <a href="{{ route('profile.edit') }}" class="mt-3 inline-block text-sm underline text-gray-600 hover:text-gray-900">
                    Update profile
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <h3 class="font-medium text-gray-900">Latest News</h3>
                    <a href="{{ route('blog.index') }}" class="text-sm underline text-gray-600 hover:text-gray-900">View all</a>
                </div>

                @if ($latestPosts->isEmpty())
                    <p class="mt-2 text-sm text-gray-500">No articles published yet.</p>
                @else
                    <div class="mt-4 grid sm:grid-cols-3 gap-4">
                        @foreach ($latestPosts as $post)
                            <a href="{{ route('blog.show', $post) }}" class="group block">
                                <div class="aspect-[16/10] rounded-md bg-gray-100 overflow-hidden">
                                    @if ($post->cover_image)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($post->cover_image) }}" alt="{{ $post->title }}" class="w-full h-full object-cover group-hover:opacity-90">
                                    @endif
                                </div>
                                <p class="mt-2 text-sm font-medium text-gray-900 group-hover:underline">{{ $post->title }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $post->published_at?->format('d M Y') }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
BLADE

# ------------------------------------------------------------
# 9) Navigation — remove Union Dues + Renewal links (desktop + mobile)
# ------------------------------------------------------------
mkdir -p resources/views/layouts
cat > resources/views/layouts/navigation.blade.php <<'BLADE'
<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="NBBEU" class="block h-9 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if (Auth::user()->status === 'approved')
                        <x-nav-link :href="route('member.card')" :active="request()->routeIs('member.card')">
                            {{ __('My Card') }}
                        </x-nav-link>
                        <x-nav-link :href="route('member.certificate')" :active="request()->routeIs('member.certificate')">
                            {{ __('My Certificate') }}
                        </x-nav-link>
                    @endif
                    <x-nav-link :href="route('member.payments')" :active="request()->routeIs('member.payments')">
                        {{ __('Payment History') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if (Auth::user()->status === 'approved')
                <x-responsive-nav-link :href="route('member.card')" :active="request()->routeIs('member.card')">
                    {{ __('My Card') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('member.certificate')" :active="request()->routeIs('member.certificate')">
                    {{ __('My Certificate') }}
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('member.payments')" :active="request()->routeIs('member.payments')">
                {{ __('Payment History') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
BLADE

# ------------------------------------------------------------
# 10) Clear caches
# ------------------------------------------------------------
php artisan optimize:clear

echo "Done: profile fully editable with redesigned photo upload, dashboard shows Latest News, Union Dues + Renewal removed from nav."
