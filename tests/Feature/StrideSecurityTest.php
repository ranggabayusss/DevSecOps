<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrideSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_gps_tracking_endpoints_are_not_available(): void
    {
        $this->getJson('/api/tracking/get')->assertNotFound();
        $this->postJson('/api/tracking/update', [
            'latitude' => -6.9,
            'longitude' => 107.6,
        ])->assertNotFound();
    }

    public function test_public_registration_cannot_assign_an_admin_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Member',
            'email' => 'member@example.test',
            'phone' => '08123456789',
            'motorcycle_type' => 'Honda CB150R',
            'license_plate' => 'D 1234 ABC',
            'password' => 'SecurePass!2026',
            'password_confirmation' => 'SecurePass!2026',
            'role' => 'admin',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'member@example.test',
            'role' => 'member',
            'member_number' => 'unrider-01',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'auth.register',
        ]);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', [
                'email' => 'unknown@example.test',
                'password' => 'WrongPassword!2026',
            ])->assertRedirect();
        }

        $this->post('/login', [
            'email' => 'unknown@example.test',
            'password' => 'WrongPassword!2026',
        ])->assertTooManyRequests();
    }

    public function test_regular_member_cannot_view_another_communitys_join_requests(): void
    {
        $leader = User::factory()->create();
        $leader->role = 'ketua';
        $leader->save();

        $member = User::factory()->create();
        $community = Community::create([
            'name' => 'Komunitas Uji',
            'leader_id' => $leader->id,
        ]);

        $this->actingAs($member)
            ->get(route('ketua.communities.requests.index', $community))
            ->assertForbidden();
    }

    public function test_regular_member_cannot_access_admin_dashboard(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/admin/dashboard')
            ->assertForbidden();
    }
}
