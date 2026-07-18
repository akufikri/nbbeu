#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-sponsor-admin-branding.sh
# 1) Sponsor step (registration wizard step 4) -> free text, no active-member gate
# 2) Fix /admin/admins "no role named admin for guard admin" crash
# 3) Site Settings: add recommended photo size hints to card/certificate uploads
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
# 1a) RegistrationWizard.php — full rewrite
# ------------------------------------------------------------
cat > app/Livewire/RegistrationWizard.php <<'PHP'
<?php

namespace App\Livewire;

use App\Models\MemberProfile;
use App\Models\Payment;
use App\Models\User;
use App\Services\ToyyibpayService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

class RegistrationWizard extends Component
{
    use WithFileUploads;

    public const TOTAL_STEPS = 6;

    // Session-backed so a browser refresh resumes at the same step instead of
    // bouncing back to Step 1 — the per-step data itself is already durable
    // in `users`/`member_profiles` (saved on every nextStep() call), so only
    // "which step" and "which draft user row" need to survive a reload.
    #[Session]
    public int $currentStep = 1;

    #[Session]
    public ?int $userId = null;

    // Step 1 — Account & Contact
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    // Step 2 — Personal Data
    public ?string $gender = null;

    public ?string $race = null;

    public ?string $race_sub_group = null;

    public ?string $date_of_birth = null;

    public ?string $place_of_birth = null;

    public ?string $ic_no = null;

    public ?string $postal_address = null;

    public ?string $residential_address = null;

    // Step 3 — Employment
    public ?string $occupation = null;

    public ?string $position = null;

    public ?string $employer_name = null;

    public ?string $employer_address = null;

    public ?string $employment_date = null;

    public ?string $bank_name = null;

    public ?string $bank_branch = null;

    public ?string $bank_address = null;

    public ?string $office_tel = null;

    public ?string $office_fax = null;

    public ?string $present_salary = null;

    // Step 4 — Sponsor
    public ?string $proposed_by_name = null;

    public ?string $seconded_by_name = null;

    // Step 5 — Declaration & Signature
    public bool $declaration_truth = false;

    public bool $declaration_constitution = false;

    public bool $declaration_pdpa = false;

    public ?string $signatureData = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $signatureFile = null;

    public function mount(): void
    {
        $this->hydrateFromDraft();
    }

    /**
     * Resume an in-progress application after a refresh: `userId`/`currentStep`
     * survive via #[Session], but the individual form fields don't — repopulate
     * them from the already-saved User/MemberProfile rows.
     */
    protected function hydrateFromDraft(): void
    {
        if (! $this->userId) {
            return;
        }

        $user = User::where('id', $this->userId)->where('status', 'pending')->first();

        if (! $user) {
            $this->startOver();

            return;
        }

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;

        $profile = $user->memberProfile;

        if (! $profile) {
            return;
        }

        $this->gender = $profile->gender;
        $this->race = $profile->race;
        $this->race_sub_group = $profile->race_sub_group;
        $this->date_of_birth = $profile->date_of_birth?->toDateString();
        $this->place_of_birth = $profile->place_of_birth;
        $this->ic_no = $profile->ic_no;
        $this->postal_address = $profile->postal_address;
        $this->residential_address = $profile->residential_address;

        $this->occupation = $profile->occupation;
        $this->position = $profile->position;
        $this->employer_name = $profile->employer_name;
        $this->employer_address = $profile->employer_address;
        $this->employment_date = $profile->employment_date?->toDateString();
        $this->bank_name = $profile->bank_name;
        $this->bank_branch = $profile->bank_branch;
        $this->bank_address = $profile->bank_address;
        $this->office_tel = $profile->office_tel;
        $this->office_fax = $profile->office_fax;
        $this->present_salary = $profile->present_salary;

        $this->proposed_by_name = $profile->proposed_by_name;
        $this->seconded_by_name = $profile->seconded_by_name;
    }

    /**
     * Explicit reset for a user who wants to abandon the in-progress draft
     * and start clean (e.g. clicking "Start Over" or "Back to Home").
     */
    public function startOver()
    {
        $this->reset();

        return redirect(route('registration.create'));
    }

    protected function rules(): array
    {
        return match ($this->currentStep) {
            1 => [
                'name' => ['required', 'string', 'max:255'],
                'email' => [
                    'required', 'string', 'email', 'max:255',
                    Rule::unique('users', 'email')->where(fn ($q) => $q->where('status', 'approved')),
                ],
                'phone' => [
                    'required', 'string', 'max:20',
                    Rule::unique('users', 'phone')->ignore($this->userId)->where(fn ($q) => $q->where('status', '!=', 'rejected')),
                ],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            2 => [
                'gender' => ['required', Rule::in(array_keys(MemberProfile::GENDERS))],
                'race' => ['required', Rule::in(array_keys(MemberProfile::RACES))],
                'race_sub_group' => [$this->race === 'bumiputra' ? 'required' : 'nullable', 'string', 'max:50'],
                'date_of_birth' => ['required', 'date', 'before:today'],
                'place_of_birth' => ['required', 'string', 'max:255'],
                'ic_no' => [
                    'required', 'string', 'max:255',
                    function (string $attribute, mixed $value, \Closure $fail) {
                        $exists = MemberProfile::where('ic_no_hash', MemberProfile::hashIcNo($value))
                            ->where('user_id', '!=', $this->userId)
                            ->exists();

                        if ($exists) {
                            $fail('This IC number is already registered.');
                        }
                    },
                ],
                'postal_address' => ['required', 'string'],
                'residential_address' => ['required', 'string'],
            ],
            3 => [
                'occupation' => ['required', 'string', 'max:255'],
                'position' => ['required', 'string', 'max:255'],
                'employer_name' => ['required', 'string', 'max:255'],
                'employer_address' => ['required', 'string'],
                'employment_date' => ['required', 'date'],
                'bank_name' => ['required', 'string', 'max:255'],
                'bank_branch' => ['required', 'string', 'max:255'],
                'bank_address' => ['nullable', 'string'],
                'office_tel' => ['nullable', 'string', 'max:20'],
                'office_fax' => ['nullable', 'string', 'max:20'],
                'present_salary' => ['required', 'numeric', 'min:0'],
            ],
            4 => [
                'proposed_by_name' => ['required', 'string', 'max:255'],
                'seconded_by_name' => ['required', 'string', 'max:255', 'different:proposed_by_name'],
            ],
            5 => [
                'declaration_truth' => ['accepted'],
                'declaration_constitution' => ['accepted'],
                'declaration_pdpa' => ['accepted'],
                'signatureData' => ['required_without:signatureFile', 'nullable', 'string'],
                'signatureFile' => ['required_without:signatureData', 'nullable', 'image', 'max:2048'],
            ],
            default => [],
        };
    }

    protected function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered as an approved member.',
            'ic_no.required' => 'IC Number is required.',
            'seconded_by_name.different' => 'Proposed By and Seconded By must be two different people.',
            'declaration_truth.accepted' => 'You must confirm the information above is true and accurate.',
            'declaration_constitution.accepted' => 'You must agree to be bound by the NBBEU Constitution & Regulations.',
            'declaration_pdpa.accepted' => 'You must consent to NBBEU processing your personal data.',
            'signatureData.required_without' => 'Please draw your signature or upload a signature image.',
            'signatureFile.required_without' => 'Please draw your signature or upload a signature image.',
            'signatureFile.image' => 'Signature file must be an image (PNG/JPG).',
        ];
    }

    public function nextStep(): void
    {
        $this->validate($this->rules(), $this->messages());

        match ($this->currentStep) {
            1 => $this->saveStep1(),
            2 => $this->saveStep2(),
            3 => $this->saveStep3(),
            4 => $this->saveStep4(),
            5 => $this->saveStep5(),
            default => null,
        };

        if ($this->currentStep < self::TOTAL_STEPS) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function saveStep1(): void
    {
        // Reuse the pending row for this email instead of creating a duplicate
        // (lets someone resume the wizard under the same email without erroring).
        $user = User::where('email', $this->email)->where('status', 'pending')->first();

        $attributes = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            // 'hashed' cast on User::password handles the hashing on save.
            'password' => $this->password,
        ];

        if ($user) {
            $user->fill($attributes)->save();
        } else {
            $user = User::create($attributes + ['company' => '']);
            $user->assignRole('member');
        }

        $this->userId = $user->id;
    }

    protected function saveStep2(): void
    {
        $this->persistProfile([
            'gender' => $this->gender,
            'race' => $this->race,
            'race_sub_group' => $this->race === 'bumiputra' ? $this->race_sub_group : null,
            'date_of_birth' => $this->date_of_birth,
            'place_of_birth' => $this->place_of_birth,
            'ic_no' => $this->ic_no,
            'postal_address' => $this->postal_address,
            'residential_address' => $this->residential_address,
        ]);
    }

    protected function saveStep3(): void
    {
        $this->persistProfile([
            'occupation' => $this->occupation,
            'position' => $this->position,
            'employer_name' => $this->employer_name,
            'employer_address' => $this->employer_address,
            'employment_date' => $this->employment_date,
            'bank_name' => $this->bank_name,
            'bank_branch' => $this->bank_branch,
            'bank_address' => $this->bank_address,
            'office_tel' => $this->office_tel,
            'office_fax' => $this->office_fax,
            'present_salary' => $this->present_salary,
        ]);

        // `users.company` predates member_profiles and still powers cards/certificates/exports —
        // keep it in sync with the employer name collected here rather than adding a new column.
        User::whereKey($this->userId)->update(['company' => $this->employer_name]);
    }

    protected function saveStep4(): void
    {
        $this->persistProfile([
            'proposed_by_name' => $this->proposed_by_name,
            'seconded_by_name' => $this->seconded_by_name,
        ]);
    }

    protected function saveStep5(): void
    {
        if ($this->signatureFile) {
            $path = $this->signatureFile->storeAs('signatures', "{$this->userId}.".$this->signatureFile->extension(), 'cloudinary');
        } else {
            $path = "signatures/{$this->userId}.png";
            $imageData = preg_replace('#^data:image/\w+;base64,#i', '', (string) $this->signatureData);
            Storage::disk('cloudinary')->put($path, base64_decode($imageData));
        }

        $this->persistProfile([
            'declaration_accepted_at' => now(),
            'signature_path' => $path,
        ]);
    }

    protected function persistProfile(array $data): void
    {
        MemberProfile::updateOrCreate(['user_id' => $this->userId], $data);
    }

    public function submit(ToyyibpayService $toyyibpay)
    {
        $user = User::findOrFail($this->userId);

        $amount = (int) config('services.toyyibpay.registration_amount');

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $amount / 100,
            'purpose' => 'registration',
            'status' => 'pending',
        ]);

        $billCode = $toyyibpay->createBill([
            'bill_name' => 'NBBEU Membership Registration',
            'bill_description' => "NBBEU membership registration - {$user->name}",
            'return_url' => route('registration.return', $payment),
            'callback_url' => route('registration.callback'),
            'reference_no' => (string) $payment->id,
            'payer_name' => $user->name,
            'payer_email' => $user->email,
            'payer_phone' => $user->phone,
        ], $amount);

        $payment->update(['toyyibpay_bill_code' => $billCode]);

        return redirect()->away($toyyibpay->billUrl($billCode));
    }

    public function render()
    {
        return view('livewire.registration-wizard');
    }
}
PHP

# ------------------------------------------------------------
# 1b) registration-wizard.blade.php — full rewrite
# ------------------------------------------------------------
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

    <form class="form mx-auto" wire:submit.prevent="{{ $currentStep === self::TOTAL_STEPS ? 'submit' : 'nextStep' }}">

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
            <p class="form-aside">
                Your application details are complete. Continue to pay the one-time registration fee via Toyyibpay to finish submitting your application.
            </p>
        @endif

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
            @elseif ($currentStep === self::TOTAL_STEPS)
                <button type="submit" class="btn-submit" wire:loading.attr="disabled" wire:target="submit">
                    Continue to Payment
                </button>
            @else
                <button type="submit" class="btn-submit" wire:loading.attr="disabled" wire:target="nextStep">
                    Continue
                </button>
            @endif
        </div>

        <p class="form-aside">
            Already applied? <a href="{{ route('registration.status') }}">Check status</a>.
        </p>
    </form>
</div>
BLADE

# ------------------------------------------------------------
# 1c) MemberProfile.php — full rewrite
# ------------------------------------------------------------
cat > app/Models/MemberProfile.php <<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'gender', 'race', 'race_sub_group', 'date_of_birth', 'place_of_birth', 'ic_no',
    'postal_address', 'residential_address', 'occupation', 'position', 'employer_name',
    'employer_address', 'employment_date', 'bank_name', 'bank_branch', 'bank_address',
    'office_tel', 'office_fax', 'present_salary', 'salary_increment_date',
    'proposed_by_name', 'seconded_by_name', 'declaration_accepted_at', 'signature_path',
])]
class MemberProfile extends Model
{
    public const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
    ];

    public const RACES = [
        'malay' => 'Malay',
        'chinese' => 'Chinese',
        'indian' => 'Indian',
        'bumiputra' => 'Bumiputra',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'employment_date' => 'date',
            'salary_increment_date' => 'date',
            'declaration_accepted_at' => 'datetime',
            // PDPA-sensitive fields — encrypted at rest via Laravel's encrypted cast.
            'ic_no' => 'encrypted',
            'present_salary' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $profile) {
            if ($profile->isDirty('ic_no')) {
                $profile->ic_no_hash = $profile->ic_no ? hash('sha256', $profile->ic_no) : null;
            }
        });
    }

    public static function hashIcNo(string $icNo): string
    {
        return hash('sha256', $icNo);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maskedIcNo(): string
    {
        $ic = (string) $this->ic_no;

        return $ic === '' ? '' : str_repeat('*', max(strlen($ic) - 4, 0)).substr($ic, -4);
    }

    public function maskedSalary(): string
    {
        return 'RM ***.**';
    }
}
PHP

# ------------------------------------------------------------
# 1d) member_profiles sponsor columns migration
# ------------------------------------------------------------
mkdir -p database/migrations
cat > database/migrations/2026_07_18_000001_replace_sponsor_ids_with_names_on_member_profiles_table.php <<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropForeign(['proposed_by_user_id']);
            $table->dropForeign(['seconded_by_user_id']);
            $table->dropColumn(['proposed_by_user_id', 'seconded_by_user_id']);

            $table->string('proposed_by_name')->nullable()->after('present_salary');
            $table->string('seconded_by_name')->nullable()->after('proposed_by_name');
        });
    }

    public function down(): void
    {
        Schema::table('member_profiles', function (Blueprint $table) {
            $table->dropColumn(['proposed_by_name', 'seconded_by_name']);

            $table->foreignId('proposed_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->foreignId('seconded_by_user_id')->nullable()->constrained('users')->restrictOnDelete();
        });
    }
};
PHP

# ------------------------------------------------------------
# 1e) manual SQL for cPanel (only if 2026-07-17 SQL was already run
#     and proposed_by_user_id/seconded_by_user_id already exist live)
# ------------------------------------------------------------
mkdir -p database
cat > database/manual-migrations-2026-07-18.sql <<'SQL'
-- Manual SQL for migration added 2026-07-18 (run via cPanel phpMyAdmin / SQL tool).
-- Only needed if member_profiles.proposed_by_user_id / seconded_by_user_id already
-- exist on the server (i.e. 2026-07-17's manual-migrations SQL was already run).

ALTER TABLE `member_profiles`
  DROP FOREIGN KEY `member_profiles_proposed_by_user_id_foreign`,
  DROP FOREIGN KEY `member_profiles_seconded_by_user_id_foreign`,
  DROP COLUMN `proposed_by_user_id`,
  DROP COLUMN `seconded_by_user_id`,
  ADD COLUMN `proposed_by_name` VARCHAR(255) NULL AFTER `present_salary`,
  ADD COLUMN `seconded_by_name` VARCHAR(255) NULL AFTER `proposed_by_name`;

SET @next_batch = (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
  ('2026_07_18_000001_replace_sponsor_ids_with_names_on_member_profiles_table', @next_batch);
SQL

# ------------------------------------------------------------
# 1f) Admin infolist — drop proposedBy/secondedBy relation lookups
# ------------------------------------------------------------
INFOLIST="app/Filament/Resources/Users/Schemas/UserInfolist.php"
if [ -f "$INFOLIST" ]; then
    perl -0777 -pi -e "s/TextEntry::make\('memberProfile\.proposedBy\.name'\)\s*\n\s*->label\('Proposed By'\)\s*\n\s*->placeholder\('-'\)\s*\n\s*->formatStateUsing\(fn \(\?string \\\$state, \\\$record\) => filled\(\\\$state\) && filled\(\\\$record->memberProfile->proposedBy\?->member_no\)\s*\n\s*\? \"\{\\\$state\} \(\{\\\$record->memberProfile->proposedBy->member_no\}\)\"\s*\n\s*: \\\$state\),\s*\n\s*TextEntry::make\('memberProfile\.secondedBy\.name'\)\s*\n\s*->label\('Seconded By'\)\s*\n\s*->placeholder\('-'\)\s*\n\s*->formatStateUsing\(fn \(\?string \\\$state, \\\$record\) => filled\(\\\$state\) && filled\(\\\$record->memberProfile->secondedBy\?->member_no\)\s*\n\s*\? \"\{\\\$state\} \(\{\\\$record->memberProfile->secondedBy->member_no\}\)\"\s*\n\s*: \\\$state\),/TextEntry::make('memberProfile.proposed_by_name')\n                            ->label('Proposed By')\n                            ->placeholder('-'),\n                        TextEntry::make('memberProfile.seconded_by_name')\n                            ->label('Seconded By')\n                            ->placeholder('-'),/" "$INFOLIST"
    echo "Patched: $INFOLIST"
else
    echo "WARNING: $INFOLIST not found — skipped." >&2
fi

# ------------------------------------------------------------
# 2) Fix admin role/guard mismatch
# ------------------------------------------------------------
USER_MODEL="app/Models/User.php"
if [ -f "$USER_MODEL" ] && ! grep -q '\$guard_name' "$USER_MODEL"; then
    perl -0777 -pi -e "s/(use HasFactory, HasRoles, Notifiable;\n)/\$1\n    \/\/ Filament's admin panel authenticates on a separate 'admin' session guard\n    \/\/ (AdminPanelProvider), but roles are seeded\/assigned under the default\n    \/\/ 'web' guard. Pinning this keeps Spatie role lookups consistent\n    \/\/ regardless of which guard the current request is authenticated on.\n    protected string \\\$guard_name = 'web';\n/" "$USER_MODEL"
    echo "Patched: $USER_MODEL"
elif [ -f "$USER_MODEL" ]; then
    echo "Skipped (already patched): $USER_MODEL"
else
    echo "WARNING: $USER_MODEL not found — skipped." >&2
fi

# ------------------------------------------------------------
# 3) Site Settings — photo size hints on card/certificate uploads
# ------------------------------------------------------------
SETTINGS_PAGE="app/Filament/Pages/SiteSettings.php"
if [ -f "$SETTINGS_PAGE" ] && ! grep -q "Recommended size" "$SETTINGS_PAGE"; then
    perl -0777 -pi -e "s/(FileUpload::make\('card_logo'\)\s*\n\s*->image\(\)\s*\n\s*->maxSize\(2048\)\s*\n\s*->disk\('cloudinary'\)\s*\n\s*->directory\('branding'\)),/\$1\n                                    ->helperText('Recommended size: 500x500px, square, transparent PNG. Max 2MB.'),/" "$SETTINGS_PAGE"
    perl -0777 -pi -e "s/(FileUpload::make\('card_signature'\)\s*\n\s*->image\(\)\s*\n\s*->maxSize\(2048\)\s*\n\s*->disk\('cloudinary'\)\s*\n\s*->directory\('branding'\)),/\$1\n                                    ->helperText('Recommended size: 400x150px, transparent PNG. Max 2MB.'),/" "$SETTINGS_PAGE"
    echo "Patched: $SETTINGS_PAGE"
elif [ -f "$SETTINGS_PAGE" ]; then
    echo "Skipped (already patched): $SETTINGS_PAGE"
else
    echo "WARNING: $SETTINGS_PAGE not found — skipped." >&2
fi

# ------------------------------------------------------------
# 4) Clear caches
# ------------------------------------------------------------
php artisan optimize:clear

echo "Done: sponsor step is free text, admin role/guard fixed, branding upload hints added."
echo "If member_profiles.proposed_by_user_id/seconded_by_user_id already exist on this server,"
echo "also run database/manual-migrations-2026-07-18.sql via phpMyAdmin (or 'php artisan migrate' if CLI is available)."
