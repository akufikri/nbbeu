<?php

namespace Tests\Feature\Membership;

use App\Events\UserApproved;
use App\Events\UserRejected;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Livewire\RegistrationWizard;
use App\Mail\MemberApprovedMail;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'member']);
    }

    public function test_registration_form_renders(): void
    {
        $this->get(route('registration.create'))->assertOk()->assertSeeLivewire(RegistrationWizard::class);
    }

    public function test_submitting_registration_creates_user_and_redirects_to_toyyibpay(): void
    {
        Storage::fake('cloudinary');

        Http::fake([
            '*/index.php/api/createBill' => Http::response([
                ['BillCode' => 'ABC123'],
            ]),
        ]);

        // Seed two approved members so the sponsor step has candidates to pick from.
        $proposer = User::factory()->create(['status' => 'approved', 'member_no' => 'NBBEU-2026-0001']);
        $proposer->assignRole('member');
        $seconder = User::factory()->create(['status' => 'approved', 'member_no' => 'NBBEU-2026-0002']);
        $seconder->assignRole('member');

        $component = Livewire::test(RegistrationWizard::class)
            ->set('name', 'Budi Santoso')
            ->set('email', 'budi@example.com')
            ->set('phone', '0812345678')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->set('gender', 'male')
            ->set('race', 'malay')
            ->set('date_of_birth', '1990-01-01')
            ->set('place_of_birth', 'Tanjung Selor')
            ->set('ic_no', '900101-12-3456')
            ->set('postal_address', 'PO Box 1, Tanjung Selor')
            ->set('residential_address', 'Jl. Contoh No. 1, Tanjung Selor')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->set('occupation', 'Banker')
            ->set('position', 'Manager')
            ->set('employer_name', 'Bank Contoh')
            ->set('employer_address', 'Jl. Bank No. 1')
            ->set('employment_date', '2015-01-01')
            ->set('bank_name', 'Bank Contoh')
            ->set('bank_branch', 'Main Branch')
            ->set('present_salary', 5000)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->set('proposed_by_user_id', $proposer->id)
            ->set('seconded_by_user_id', $seconder->id)
            ->call('nextStep')
            ->assertHasNoErrors()
            ->set('declaration_truth', true)
            ->set('declaration_constitution', true)
            ->set('declaration_pdpa', true)
            ->set('signatureData', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->call('submit');

        $user = User::where('email', 'budi@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('pending', $user->status);
        $this->assertTrue($user->hasRole('member'));
        $this->assertSame('Bank Contoh', $user->company);

        $this->assertNotNull($user->memberProfile);
        $this->assertNotNull($user->memberProfile->declaration_accepted_at);
        Storage::disk('cloudinary')->assertExists($user->memberProfile->signature_path);

        $payment = Payment::where('user_id', $user->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('ABC123', $payment->toyyibpay_bill_code);
        $this->assertSame('pending', $payment->status);

        $component->assertRedirectContains('ABC123');
    }

    public function test_toyyibpay_callback_marks_payment_paid_after_verifying_with_api(): void
    {
        $user = User::factory()->create(['status' => 'pending']);
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 50,
            'purpose' => 'registration',
            'status' => 'pending',
            'toyyibpay_bill_code' => 'BILL999',
        ]);

        Http::fake([
            '*/index.php/api/getBillTransactions' => Http::response([
                ['billpaymentStatus' => '1'],
            ]),
        ]);

        $this->post(route('registration.callback'), ['billcode' => 'BILL999'])
            ->assertOk();

        $this->assertSame('paid', $payment->fresh()->status);
    }

    public function test_toyyibpay_callback_does_not_trust_status_field_without_api_confirmation(): void
    {
        $user = User::factory()->create(['status' => 'pending']);
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => 50,
            'purpose' => 'registration',
            'status' => 'pending',
            'toyyibpay_bill_code' => 'BILL999',
        ]);

        Http::fake([
            '*/index.php/api/getBillTransactions' => Http::response([
                ['billpaymentStatus' => '3'],
            ]),
        ]);

        // Attacker-controlled callback claims status=1 (paid) but the
        // server-side re-check against Toyyibpay says otherwise.
        $this->post(route('registration.callback'), ['billcode' => 'BILL999', 'status' => '1'])
            ->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_admin_approving_user_generates_member_number_and_sends_email(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create(['status' => 'pending', 'member_no' => null]);

        event(new UserApproved($user, $admin));

        $user->refresh();

        $this->assertMatchesRegularExpression('/^NBBEU-'.now()->year.'-\d{4}$/', $user->member_no);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'member.approved',
            'subject_id' => $user->id,
        ]);

        Mail::assertQueued(MemberApprovedMail::class);
    }

    public function test_admin_rejecting_user_writes_audit_log(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create(['status' => 'pending']);
        $user->forceFill(['status' => 'rejected', 'rejection_reason' => 'Data tidak lengkap'])->save();

        event(new UserRejected($user, $admin));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'member.rejected',
            'subject_id' => $user->id,
        ]);
    }

    public function test_approving_user_generates_real_card_and_certificate_pdfs_and_verify_endpoint_works(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user = User::factory()->create(['status' => 'pending', 'member_no' => null]);
        $user->forceFill(['status' => 'approved'])->save();

        event(new UserApproved($user, $admin));

        $user->refresh();

        $memberCard = $user->memberCards()->first();
        $certificate = $user->certificates()->first();

        $this->assertNotNull($memberCard);
        $this->assertNotNull($certificate);
        Storage::assertExists($memberCard->file_path);
        Storage::assertExists($certificate->file_path);

        $this->get(route('verify.card', $memberCard->qr_token))
            ->assertOk()
            ->assertSee('Valid')
            ->assertSee($user->member_no);

        $this->get(route('verify.card', 'random-nonexistent-token'))
            ->assertOk()
            ->assertSee('not found', escape: false);
    }

    public function test_approve_button_in_admin_panel_updates_status_and_member_no(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'admin');

        $applicant = User::factory()->create(['status' => 'pending']);

        Livewire::test(ListUsers::class)
            ->callTableAction('approve', $applicant);

        $applicant->refresh();

        $this->assertSame('approved', $applicant->status);
        $this->assertNotNull($applicant->member_no);
        $this->assertNotNull($applicant->approved_at);
        $this->assertSame($admin->id, $applicant->approved_by);
    }

    public function test_reject_button_in_admin_panel_updates_status_and_reason(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin, 'admin');

        $applicant = User::factory()->create(['status' => 'pending']);

        Livewire::test(ListUsers::class)
            ->callTableAction('reject', $applicant, data: [
                'rejection_reason' => 'Data tidak lengkap',
            ]);

        $applicant->refresh();

        $this->assertSame('rejected', $applicant->status);
        $this->assertSame('Data tidak lengkap', $applicant->rejection_reason);
    }
}
