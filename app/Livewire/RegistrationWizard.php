<?php

namespace App\Livewire;

use App\Models\MemberProfile;
use App\Models\User;
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

    public function render()
    {
        return view('livewire.registration-wizard');
    }
}
