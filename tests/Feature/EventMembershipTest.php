<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use App\Support\MemberNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventMembershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_numbers_are_generated_sequentially(): void
    {
        $first = User::factory()->create();
        $second = User::factory()->create();

        $this->assertSame('unrider-01', $first->member_number);
        $this->assertSame('unrider-02', $second->member_number);
    }

    public function test_member_number_service_maps_trusted_sequences_to_roles(): void
    {
        $service = app(MemberNumberService::class);

        $this->assertSame('admin', $service->roleForSequence(1));
        $this->assertSame('ketua', $service->roleForSequence(2));
        $this->assertSame('member', $service->roleForSequence(3));
    }

    public function test_public_registration_ignores_privileged_member_fields(): void
    {
        $this->post('/register', [
            'name' => 'Rider Baru',
            'email' => 'rider.baru@example.test',
            'phone' => '08123456789',
            'motorcycle_type' => 'Yamaha R15',
            'license_plate' => 'd 5678 xyz',
            'password' => 'SecurePass!2026',
            'password_confirmation' => 'SecurePass!2026',
            'role' => 'admin',
            'member_number' => 'unrider-99',
        ])->assertRedirect();

        $user = User::where('email', 'rider.baru@example.test')->firstOrFail();

        $this->assertSame('member', $user->role);
        $this->assertSame('unrider-01', $user->member_number);
        $this->assertSame('D 5678 XYZ', $user->license_plate);
    }

    public function test_profile_update_cannot_change_role_or_member_number(): void
    {
        $user = $this->memberWithCompleteProfile();
        $originalNumber = $user->member_number;

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Nama Baru',
            'phone' => '081200000000',
            'motorcycle_type' => 'Kawasaki Ninja 250',
            'license_plate' => 'd 9999 zz',
            'role' => 'admin',
            'member_number' => 'unrider-99',
        ])->assertRedirect();

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('member', $user->role);
        $this->assertSame($originalNumber, $user->member_number);
        $this->assertSame('D 9999 ZZ', $user->license_plate);
    }

    public function test_regular_member_cannot_manage_events_or_export_attendance(): void
    {
        $member = $this->memberWithCompleteProfile();
        $event = $this->makeEvent($this->leaderWithCompleteProfile());

        $this->actingAs($member)->post(route('events.store'), [
            'title' => 'Acara Liar',
            'description' => 'Tidak boleh dibuat.',
            'location' => 'Bandung',
            'starts_at' => '2026-11-20 07:00:00',
            'status' => 'upcoming',
        ])->assertForbidden();

        $this->actingAs($member)
            ->get(route('events.edit', $event))
            ->assertForbidden();

        $this->actingAs($member)
            ->get(route('events.export', $event))
            ->assertForbidden();

        $this->actingAs($member)
            ->delete(route('events.destroy', $event))
            ->assertForbidden();
    }

    public function test_leader_can_create_event_and_server_controls_audit_fields(): void
    {
        $leader = $this->leaderWithCompleteProfile();
        $otherUser = $this->memberWithCompleteProfile();

        $this->actingAs($leader)->post(route('events.store'), [
            'title' => '  Sunmori Lembang  ',
            'description' => 'Kumpul di Tugu Lembang.',
            'location' => '  Tugu Lembang  ',
            'starts_at' => '2026-11-20 07:00:00',
            'status' => 'upcoming',
            'created_by' => $otherUser->id,
            'updated_by' => $otherUser->id,
        ])->assertRedirect();

        $event = Event::firstOrFail();

        $this->assertSame('Sunmori Lembang', $event->title);
        $this->assertSame('Tugu Lembang', $event->location);
        $this->assertSame($leader->id, $event->created_by);
        $this->assertSame($leader->id, $event->updated_by);
        $this->assertDatabaseHas('activity_logs', ['activity' => 'event.created']);
    }

    public function test_rsvp_uses_authenticated_user_and_can_be_changed(): void
    {
        $member = $this->memberWithCompleteProfile();
        $victim = $this->memberWithCompleteProfile();
        $event = $this->makeEvent($this->leaderWithCompleteProfile());

        $this->actingAs($member)->post(route('events.rsvp', $event), [
            'status' => 'attending',
            'user_id' => $victim->id,
            'event_id' => 999999,
        ])->assertRedirect();

        $this->assertDatabaseHas('event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'attending',
        ]);

        $this->assertDatabaseMissing('event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $victim->id,
        ]);

        $this->actingAs($member)->post(route('events.rsvp', $event), [
            'status' => 'declined',
        ])->assertRedirect();

        $this->assertDatabaseCount('event_rsvps', 1);
        $this->assertDatabaseHas('event_rsvps', [
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => 'declined',
        ]);
        $this->assertDatabaseHas('activity_logs', ['activity' => 'event.rsvp.updated']);
    }

    public function test_finished_event_locks_rsvp_changes(): void
    {
        $member = $this->memberWithCompleteProfile();
        $event = $this->makeEvent($this->leaderWithCompleteProfile(), [
            'status' => Event::STATUS_FINISHED,
        ]);

        $this->actingAs($member)->post(route('events.rsvp', $event), [
            'status' => 'attending',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('event_rsvps', 0);
    }

    public function test_event_detail_displays_attendance_list(): void
    {
        $leader = $this->leaderWithCompleteProfile();
        $member = $this->memberWithCompleteProfile(['name' => 'Budi Rider']);
        $event = $this->makeEvent($leader);

        $this->actingAs($member)->post(route('events.rsvp', $event), [
            'status' => 'attending',
        ])->assertRedirect();

        $this->actingAs($leader)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee($member->member_number)
            ->assertSee('Budi Rider')
            ->assertSee('D 1234 ABC')
            ->assertSee('Akan Ikut');
    }

    public function test_attendance_export_is_a_safe_xlsx_file(): void
    {
        $leader = $this->leaderWithCompleteProfile();
        $member = $this->memberWithCompleteProfile([
            'name' => '=HYPERLINK("http://example.test")',
            'motorcycle_type' => '+CMD|calc',
        ]);
        $event = $this->makeEvent($leader, [
            'title' => 'Sunmori Lembang',
            'starts_at' => '2026-11-20 07:00:00',
        ]);

        $rsvp = new EventRsvp;
        $rsvp->forceFill([
            'event_id' => $event->id,
            'user_id' => $member->id,
            'status' => EventRsvp::STATUS_ATTENDING,
            'confirmed_at' => now(),
        ])->save();

        $response = $this->actingAs($leader)->get(route('events.export', $event));

        $response->assertOk();
        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $this->assertStringContainsString(
            'Kehadiran_Sunmori_Lembang_2026-11-20.xlsx',
            (string) $response->headers->get('content-disposition')
        );

        $content = $response->getContent();

        $this->assertIsString($content);
        $this->assertStringStartsWith("PK\x03\x04", $content);
        $this->assertStringContainsString('xl/worksheets/sheet1.xml', $content);
        $this->assertStringContainsString('&apos;=HYPERLINK', $content);
        $this->assertStringContainsString('&apos;+CMD|calc', $content);
        $this->assertDatabaseHas('activity_logs', ['activity' => 'event.attendance.exported']);
    }

    public function test_export_endpoint_is_rate_limited(): void
    {
        $leader = $this->leaderWithCompleteProfile();
        $event = $this->makeEvent($leader);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->actingAs($leader)
                ->get(route('events.export', $event))
                ->assertOk();
        }

        $this->actingAs($leader)
            ->get(route('events.export', $event))
            ->assertTooManyRequests();
    }

    public function test_only_admin_can_change_member_roles_and_cannot_change_own_role(): void
    {
        $admin = $this->userWithRole('admin');
        $leader = $this->leaderWithCompleteProfile();
        $member = $this->memberWithCompleteProfile();

        $this->actingAs($leader)->patch(route('admin.users.role.update', $member), [
            'role' => 'admin',
        ])->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.users.role.update', $member), [
            'role' => 'ketua',
        ])->assertRedirect();

        $this->assertSame('ketua', $member->refresh()->role);
        $this->assertDatabaseHas('activity_logs', ['activity' => 'member.role.changed']);

        $this->actingAs($admin)->patch(route('admin.users.role.update', $admin), [
            'role' => 'member',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame('admin', $admin->refresh()->role);
    }

    public function test_membership_approval_assigns_a_member_number_when_missing(): void
    {
        $leader = $this->leaderWithCompleteProfile();
        $community = Community::create([
            'name' => 'Komunitas Uji',
            'leader_id' => $leader->id,
        ]);

        $member = $this->memberWithCompleteProfile();
        $member->forceFill(['member_number' => null])->save();

        $membership = CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $member->id,
            'status' => 'pending',
        ]);

        $this->actingAs($leader)
            ->patch(route('ketua.communities.requests.approve', [$community, $membership]))
            ->assertRedirect();

        $member->refresh();

        $this->assertNotNull($member->member_number);
        $this->assertSame('active', $membership->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['activity' => 'community.membership.approved']);
    }

    public function test_event_profile_and_member_pages_render(): void
    {
        $admin = $this->userWithRole('admin');
        $event = $this->makeEvent($admin);

        $this->actingAs($admin)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee($event->title);

        $this->actingAs($admin)->get(route('events.create'))->assertOk();
        $this->actingAs($admin)->get(route('events.edit', $event))->assertOk();
        $this->actingAs($admin)->get(route('profile.edit'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_regular_member_cannot_access_member_management_page(): void
    {
        $member = $this->memberWithCompleteProfile();

        $this->actingAs($member)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    private function memberWithCompleteProfile(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'motorcycle_type' => 'Honda CB150R',
            'license_plate' => 'D 1234 ABC',
        ], $attributes));
    }

    private function leaderWithCompleteProfile(): User
    {
        return $this->userWithRole('ketua');
    }

    private function userWithRole(string $role): User
    {
        $user = $this->memberWithCompleteProfile();
        $user->forceFill(['role' => $role])->save();

        return $user->refresh();
    }

    private function makeEvent(User $creator, array $attributes = []): Event
    {
        $event = Event::create(array_merge([
            'title' => 'Acara Uji',
            'description' => 'Deskripsi acara uji.',
            'location' => 'Bandung',
            'starts_at' => '2026-11-20 07:00:00',
            'status' => Event::STATUS_UPCOMING,
        ], $attributes));

        $event->created_by = $creator->id;
        $event->updated_by = $creator->id;
        $event->save();

        return $event;
    }
}
