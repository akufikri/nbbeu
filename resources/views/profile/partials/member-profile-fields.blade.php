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
