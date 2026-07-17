<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GuardSeparationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_session_does_not_authenticate_the_public_member_guard(): void
    {
        Role::firstOrCreate(['name' => 'admin']);

        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $this->actingAs($admin, 'admin');

        $this->assertTrue(Auth::guard('admin')->check());
        $this->assertFalse(Auth::guard('web')->check());

        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_member_web_session_does_not_authenticate_the_admin_guard(): void
    {
        Role::firstOrCreate(['name' => 'member']);

        $member = User::factory()->create(['status' => 'approved']);
        $member->assignRole('member');

        $this->actingAs($member);

        $this->assertTrue(Auth::guard('web')->check());
        $this->assertFalse(Auth::guard('admin')->check());

        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
