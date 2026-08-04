#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-registration-and-emails.sh
# 1) Registration wizard step 6 (final notice) gets a "Login" button.
# 2) "Application Approved" email drops the "Create Account Password" button
#    (password is already set during registration step 1).
# 3) Every markdown email (member-approved, payment-link, renewal-reminder)
#    gets the NBBEU logo in its header — done once, project-wide, by
#    publishing + patching Laravel's mail theme.
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
# 1) Registration wizard — full rewrite, adds Login button on step 6
# ------------------------------------------------------------
mkdir -p resources/views/livewire
cat > resources/views/livewire/registration-wizard.blade.php <<'BLADE'
<div>
    <ol class="wizard-steps" aria-label="Registration progress">
        @foreach (['Account', 'Personal Data', 'Employment', 'Sponsor', 'Declaration', 'Payment'] as $i => $label)
            @php $step = $i + 1; @endphp
            <li class="wizard-steps__item @if ($step === $currentStep) is-current @elseif ($step < $currentStep) is-done @endif">
                <span class="wizard-steps__num">{{ $step }}</span>
                <span class="wizard-steps__label">{{ $label }}</span>
            </li>
        @endforeach
    </ol>
    <p class="wizard-steps__caption">
        Step {{ $currentStep }} of {{ self::TOTAL_STEPS }}
        @if ($currentStep > 1)
            · <button type="button" class="wizard-steps__reset" wire:click="startOver" wire:confirm="Start over? Your progress on this application will be cleared.">Start Over</button>
        @endif
    </p>

    <form class="form mx-auto" wire:submit.prevent="nextStep">

        @if ($currentStep === 1)
            <div class="field @if ($errors->get('name')) field--error @endif">
                <label for="name">Full Name</label>
                <input type="text" id="name" wire:model="name" placeholder="Full name" autofocus>
                @error('name') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('email')) field--error @endif">
                <label for="email">Email</label>
                <input type="email" id="email" wire:model="email" placeholder="name@email.com">
                @error('email') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('phone')) field--error @endif">
                <label for="phone">Phone Number</label>
                <input type="text" id="phone" wire:model="phone" placeholder="+60 12-345 6789">
                @error('phone') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('password')) field--error @endif">
                <label for="password">Password</label>
                <input type="password" id="password" wire:model="password" placeholder="At least 8 characters">
                @error('password') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" wire:model="password_confirmation" placeholder="Re-enter password">
            </div>
        @endif

        @if ($currentStep === 2)
            <div class="field @if ($errors->get('gender')) field--error @endif">
                <label for="gender">Gender</label>
                <select id="gender" wire:model="gender">
                    <option value="">Select gender</option>
                    @foreach (\App\Models\MemberProfile::GENDERS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('gender') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('race')) field--error @endif">
                <label for="race">Race</label>
                <select id="race" wire:model.live="race">
                    <option value="">Select race</option>
                    @foreach (\App\Models\MemberProfile::RACES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('race') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            @if ($race === 'bumiputra')
                <div class="field @if ($errors->get('race_sub_group')) field--error @endif">
                    <label for="race_sub_group">Sub-Ethnic Group</label>
                    <input type="text" id="race_sub_group" wire:model="race_sub_group" placeholder="e.g. Kadazan, Dusun, Murut">
                    @error('race_sub_group') <span class="field__msg">{{ $message }}</span> @enderror
                </div>
            @endif

            <div class="field @if ($errors->get('date_of_birth')) field--error @endif">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" wire:model="date_of_birth">
                @error('date_of_birth') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('place_of_birth')) field--error @endif">
                <label for="place_of_birth">Place of Birth</label>
                <input type="text" id="place_of_birth" wire:model="place_of_birth">
                @error('place_of_birth') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('ic_no')) field--error @endif">
                <label for="ic_no">IC Number</label>
                <input type="text" id="ic_no" wire:model="ic_no">
                @error('ic_no') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('postal_address')) field--error @endif">
                <label for="postal_address">Postal Address</label>
                <textarea id="postal_address" wire:model="postal_address" rows="3"></textarea>
                @error('postal_address') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('residential_address')) field--error @endif">
                <label for="residential_address">Residential Address</label>
                <textarea id="residential_address" wire:model="residential_address" rows="3"></textarea>
                @error('residential_address') <span class="field__msg">{{ $message }}</span> @enderror
            </div>
        @endif

        @if ($currentStep === 3)
            <div class="field @if ($errors->get('occupation')) field--error @endif">
                <label for="occupation">Occupation</label>
                <input type="text" id="occupation" wire:model="occupation">
                @error('occupation') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('position')) field--error @endif">
                <label for="position">Position</label>
                <input type="text" id="position" wire:model="position">
                @error('position') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('employer_name')) field--error @endif">
                <label for="employer_name">Employer Name</label>
                <input type="text" id="employer_name" wire:model="employer_name">
                @error('employer_name') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('employer_address')) field--error @endif">
                <label for="employer_address">Employer Address</label>
                <textarea id="employer_address" wire:model="employer_address" rows="3"></textarea>
                @error('employer_address') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('employment_date')) field--error @endif">
                <label for="employment_date">Employment Start Date</label>
                <input type="date" id="employment_date" wire:model="employment_date">
                @error('employment_date') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('bank_name')) field--error @endif">
                <label for="bank_name">Bank Name</label>
                <input type="text" id="bank_name" wire:model="bank_name">
                @error('bank_name') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('bank_branch')) field--error @endif">
                <label for="bank_branch">Bank Branch</label>
                <input type="text" id="bank_branch" wire:model="bank_branch">
                @error('bank_branch') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('bank_address')) field--error @endif">
                <label for="bank_address">Bank Address (optional)</label>
                <textarea id="bank_address" wire:model="bank_address" rows="3"></textarea>
                @error('bank_address') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('office_tel')) field--error @endif">
                <label for="office_tel">Office Tel (optional)</label>
                <input type="text" id="office_tel" wire:model="office_tel">
                @error('office_tel') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('office_fax')) field--error @endif">
                <label for="office_fax">Office Fax (optional)</label>
                <input type="text" id="office_fax" wire:model="office_fax">
                @error('office_fax') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('present_salary')) field--error @endif">
                <label for="present_salary">Present Salary (RM)</label>
                <input type="number" step="0.01" min="0" id="present_salary" wire:model="present_salary">
                @error('present_salary') <span class="field__msg">{{ $message }}</span> @enderror
            </div>
        @endif

        @if ($currentStep === 4)
            <div class="field @if ($errors->get('proposed_by_name')) field--error @endif">
                <label for="proposed_by_name">Proposed By</label>
                <input type="text" id="proposed_by_name" wire:model="proposed_by_name" placeholder="Full name">
                @error('proposed_by_name') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('seconded_by_name')) field--error @endif">
                <label for="seconded_by_name">Seconded By</label>
                <input type="text" id="seconded_by_name" wire:model="seconded_by_name" placeholder="Full name">
                @error('seconded_by_name') <span class="field__msg">{{ $message }}</span> @enderror
            </div>
        @endif

        @if ($currentStep === 5)
            <div class="field field--checkbox @if ($errors->get('declaration_truth')) field--error @endif">
                <label>
                    <input type="checkbox" wire:model="declaration_truth">
                    I declare the above information is true and accurate.
                </label>
                @error('declaration_truth') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field field--checkbox @if ($errors->get('declaration_constitution')) field--error @endif">
                <label>
                    <input type="checkbox" wire:model="declaration_constitution">
                    I agree to be bound by the NBBEU Constitution & Regulations.
                </label>
                @error('declaration_constitution') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field field--checkbox @if ($errors->get('declaration_pdpa')) field--error @endif">
                <label>
                    <input type="checkbox" wire:model="declaration_pdpa">
                    I consent to NBBEU collecting and processing my personal data (including IC number and salary information) for the purpose of processing my membership application, in accordance with the Personal Data Protection Act (PDPA) Malaysia.
                </label>
                @error('declaration_pdpa') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field @if ($errors->get('signatureData')) field--error @endif"
                x-data="{
                    ctx: null,
                    drawing: false,
                    pos(e) {
                        const rect = this.$refs.canvas.getBoundingClientRect();
                        const t = e.touches ? e.touches[0] : e;
                        return { x: t.clientX - rect.left, y: t.clientY - rect.top };
                    },
                    start(e) { this.drawing = true; const p = this.pos(e); this.ctx.beginPath(); this.ctx.moveTo(p.x, p.y); },
                    move(e) { if (!this.drawing) return; const p = this.pos(e); this.ctx.lineTo(p.x, p.y); this.ctx.stroke(); },
                    stop() { this.drawing = false; },
                    clear() { this.ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height); }
                }"
                x-init="ctx = $refs.canvas.getContext('2d'); ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#1c2b4a';"
            >
                <label>Signature</label>
                <canvas
                    x-ref="canvas"
                    width="500" height="180"
                    class="signature-pad"
                    x-on:mousedown="start" x-on:mousemove="move" x-on:mouseup="stop" x-on:mouseleave="stop"
                    x-on:touchstart.prevent="start" x-on:touchmove.prevent="move" x-on:touchend.prevent="stop"
                ></canvas>
                <button type="button" class="signature-pad__clear" x-on:click="clear()">Clear signature</button>
                @error('signatureData') <span class="field__msg">{{ $message }}</span> @enderror
            </div>

            <div class="field field--signature-upload @if ($errors->get('signatureFile')) field--error @endif">
                <label for="signatureFile">Or upload a signature image</label>
                <input type="file" id="signatureFile" wire:model="signatureFile" accept="image/*">
                <p class="field__hint">PNG or JPG, max 2MB. Uploading a file will be used instead of the drawn signature above.</p>
                <div wire:loading wire:target="signatureFile" class="field__hint">Uploading…</div>
                @if ($signatureFile)
                    <p class="field__hint">Selected: {{ $signatureFile->getClientOriginalName() }}</p>
                @endif
                @error('signatureFile') <span class="field__msg">{{ $message }}</span> @enderror
            </div>
        @endif

        @if ($currentStep === 6)
            <div class="field--notice">
                <p class="field--notice__title">Registration sent!</p>
                <p class="form-aside">NBBEU will inform you shortly.</p>
                <a href="{{ route('login') }}" class="btn-submit mt-4 inline-block">Login</a>
            </div>
        @endif

        @if ($currentStep < self::TOTAL_STEPS)
            <div class="wizard-actions">
                @if ($currentStep > 1)
                    <button type="button" class="btn-back" wire:click="previousStep" wire:loading.attr="disabled">Back</button>
                @endif

                @if ($currentStep === 5)
                    <button type="button" class="btn-submit"
                        x-data
                        x-on:click="
                            @if ($signatureFile)
                                $wire.call('nextStep')
                            @else
                                $wire.set('signatureData', $el.closest('form').querySelector('.signature-pad').toDataURL('image/png')).then(() => $wire.call('nextStep'))
                            @endif
                        "
                        wire:loading.attr="disabled" wire:target="nextStep">
                        Continue
                    </button>
                @else
                    <button type="submit" class="btn-submit" wire:loading.attr="disabled" wire:target="nextStep">
                        Continue
                    </button>
                @endif
            </div>
        @endif

        @if ($currentStep < self::TOTAL_STEPS)
            <p class="form-aside">
                Already applied? <a href="{{ route('registration.status') }}">Check status</a>.
            </p>
        @endif
    </form>
</div>
BLADE

# ------------------------------------------------------------
# 2) Application Approved email — drop the "Create Account Password" button
# ------------------------------------------------------------
mkdir -p resources/views/emails
cat > resources/views/emails/member-approved.blade.php <<'BLADE'
<x-mail::message>
# Congratulations, {{ $user->name }}!

Your membership application at **North Borneo Banking Executive Union (NBBEU)** has been approved.

- Member No.: **{{ $user->member_no }}**
- Company: {{ $user->company }}

Your member card and certificate are attached to this email (PDF).

You can log in to the member dashboard anytime using the email and password you registered with.

Thank you,<br>
NBBEU Admin
</x-mail::message>
BLADE

# ------------------------------------------------------------
# 3) NBBEU logo in every markdown email — publish Laravel's mail theme
#    (idempotent, --force is safe to rerun) and patch the header slot.
# ------------------------------------------------------------
php artisan vendor:publish --tag=laravel-mail --force

mkdir -p resources/views/vendor/mail/html
cat > resources/views/vendor/mail/html/message.blade.php <<'BLADE'
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<img src="{{ asset('assets/images/logo.png') }}" alt="{{ config('app.name') }}" height="72" style="height: 72px; max-height: 72px;">
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
BLADE

# ------------------------------------------------------------
# 4) Clear caches
# ------------------------------------------------------------
php artisan optimize:clear

echo "Done: step 6 has a Login button, approval email drops the password-setup button, all markdown emails now show the NBBEU logo."
echo "Reminder: APP_URL in .env must be the real public URL (not localhost) for the logo <img> to load in received emails."
