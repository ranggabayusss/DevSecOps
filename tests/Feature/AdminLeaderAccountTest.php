<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLeaderAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_creates_a_member_account_and_ignores_role_input(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.leader-accounts.store'), [
            'leader_name' => 'Calon Ketua Baru',
            'leader_email' => 'KETUA.BARU@example.test',
            'leader_phone' => '081234567890',
            'leader_password' => 'SecurePass!2026',
            'leader_password_confirmation' => 'SecurePass!2026',
            'role' => 'admin',
        ]);

        $leader = User::where('email', 'ketua.baru@example.test')->firstOrFail();

        $response->assertRedirect(route('admin.communities.create'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('created_user_id', $leader->id);

        $this->assertSame('Calon Ketua Baru', $leader->name);
        $this->assertSame('member', $leader->role);
        $this->assertSame('081234567890', $leader->phone);
        $this->assertTrue(Hash::check('SecurePass!2026', $leader->password));

        $this->assertDatabaseHas('activity_logs', [
            'activity' => 'admin.leader_account.created',
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_create_a_leader_account_with_an_existing_email(): void
    {
        $admin = $this->createAdmin();
        User::factory()->create(['email' => 'existing@example.test']);

        $response = $this->actingAs($admin)->post(route('admin.leader-accounts.store'), [
            'leader_name' => 'Calon Ketua Baru',
            'leader_email' => 'existing@example.test',
            'leader_password' => 'SecurePass!2026',
            'leader_password_confirmation' => 'SecurePass!2026',
        ]);

        $response->assertSessionHasErrors('leader_email');

        $this->assertSame(1, User::where('email', 'existing@example.test')->count());
    }

    public function test_admin_cannot_create_a_leader_account_with_a_weak_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.leader-accounts.store'), [
            'leader_name' => 'Calon Ketua Baru',
            'leader_email' => 'ketua.baru@example.test',
            'leader_password' => 'weakpassword',
            'leader_password_confirmation' => 'weakpassword',
        ]);

        $response->assertSessionHasErrors('leader_password');

        $this->assertDatabaseMissing('users', [
            'email' => 'ketua.baru@example.test',
        ]);
    }

    public function test_admin_must_provide_required_leader_account_fields(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.leader-accounts.store'), []);

        $response->assertSessionHasErrors([
            'leader_name',
            'leader_email',
            'leader_password',
        ]);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_member_cannot_create_a_leader_account(): void
    {
        $member = User::factory()->create();

        $response = $this->actingAs($member)->post(route('admin.leader-accounts.store'), [
            'leader_name' => 'Calon Ketua Baru',
            'leader_email' => 'ketua.baru@example.test',
            'leader_password' => 'SecurePass!2026',
            'leader_password_confirmation' => 'SecurePass!2026',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'ketua.baru@example.test',
        ]);
    }

    public function test_admin_sees_leader_account_form_when_no_member_is_available(): void
    {
        $this->withoutVite();

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.communities.create'));

        $response->assertOk();
        $response->assertSee('Buat akun calon Ketua baru');
        $response->assertSee('Belum ada member yang bisa dijadikan Ketua');
        $response->assertDontSee('Simpan Komunitas');
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->role = 'admin';
        $admin->save();

        return $admin;
    }
}
