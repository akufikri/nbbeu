<?php

namespace Tests\Feature\Member;

use App\Jobs\SendRenewalReminders;
use App\Models\MemberCard;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberAreaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'member']);
    }

    public function test_dashboard_shows_status_and_documents_for_approved_member(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'status' => 'approved',
            'member_no' => 'NBBEU-2026-0001',
            'renewal_expires_at' => now()->addMonths(6),
        ]);
        MemberCard::create([
            'user_id' => $user->id,
            'card_number' => $user->member_no,
            'qr_token' => 'tok123',
            'file_path' => 'cards/x.pdf',
            'issued_at' => now(),
            'expires_at' => $user->renewal_expires_at,
        ]);
        Storage::put('cards/x.pdf', 'dummy');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('NBBEU-2026-0001')
            ->assertSee(route('member.documents.card'));
    }

    public function test_member_can_download_own_card_but_not_when_missing(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['status' => 'approved']);
        MemberCard::create([
            'user_id' => $user->id,
            'card_number' => 'X',
            'qr_token' => 'tokabc',
            'file_path' => 'cards/mine.pdf',
            'issued_at' => now(),
            'expires_at' => now()->addYear(),
        ]);
        Storage::put('cards/mine.pdf', 'dummy content');

        $this->actingAs($user)
            ->get(route('member.documents.card'))
            ->assertOk();

        $userWithoutCard = User::factory()->create(['status' => 'approved']);

        $this->actingAs($userWithoutCard)
            ->get(route('member.documents.card'))
            ->assertNotFound();
    }

    public function test_renewal_payment_extends_renewal_expires_at_only_after_confirmed_paid(): void
    {
        $user = User::factory()->create([
            'status' => 'approved',
            'renewal_expires_at' => now()->addDays(5),
        ]);

        Http::fake([
            '*/index.php/api/createBill' => Http::response([['BillCode' => 'RENEW001']]),
        ]);

        $this->actingAs($user)->post(route('member.renewal'))->assertRedirect();

        $payment = Payment::where('user_id', $user->id)->where('purpose', 'renewal')->first();
        $this->assertNotNull($payment);
        $this->assertSame('RENEW001', $payment->toyyibpay_bill_code);

        $originalExpiry = $user->renewal_expires_at;

        Http::fake([
            '*/index.php/api/getBillTransactions' => Http::response([['billpaymentStatus' => '1']]),
        ]);

        $this->post(route('registration.callback'), ['billcode' => 'RENEW001'])->assertOk();

        $user->refresh();
        $this->assertTrue($user->renewal_expires_at->greaterThan($originalExpiry));
        $this->assertEqualsWithDelta(
            $originalExpiry->addYear()->timestamp,
            $user->renewal_expires_at->timestamp,
            5
        );
    }

    public function test_renewal_reminder_sent_once_within_window_and_not_duplicated(): void
    {
        Mail::fake();

        User::factory()->create([
            'status' => 'approved',
            'renewal_expires_at' => now()->addDays(5),
        ]);

        (new SendRenewalReminders)->handle();
        Mail::assertQueued(\App\Mail\RenewalReminderMail::class, 1);

        // Running again same day should not send a second reminder (dedup via audit_logs).
        (new SendRenewalReminders)->handle();
        Mail::assertQueued(\App\Mail\RenewalReminderMail::class, 1);
    }
}
