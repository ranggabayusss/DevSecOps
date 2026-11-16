<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;

class CommunityRequestController extends Controller
{
    public function index(Community $community)
    {
        abort_unless((int) $community->leader_id === (int) auth()->id(), 403);

        $requests = $community->members()
            ->with('user')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        return view('ketua.communities.requests.index', compact('community', 'requests'));
    }

    public function approve(Community $community, CommunityMember $membership)
    {
        $this->authorizeMembershipAction($community, $membership);

        DB::transaction(function () use ($community, $membership) {
            $lockedMembership = CommunityMember::whereKey($membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedMembership->status === 'pending', 404);

            $alreadyJoined = CommunityMember::where('user_id', $lockedMembership->user_id)
                ->where('status', 'active')
                ->where('id', '!=', $lockedMembership->id)
                ->exists();

            if ($alreadyJoined) {
                abort(409, 'Pengguna sudah menjadi anggota komunitas lain.');
            }

            $lockedMembership->update([
                'status' => 'active',
                'joined_at' => now(),
            ]);

            ActivityLogger::record(
                'community.membership.approved',
                'Permintaan anggota ID '.$lockedMembership->id.' pada komunitas ID '.$community->id.' disetujui.'
            );
        });

        return back()->with('success', 'Permintaan bergabung berhasil disetujui.');
    }

    public function reject(Community $community, CommunityMember $membership)
    {
        $this->authorizeMembershipAction($community, $membership);

        DB::transaction(function () use ($community, $membership) {
            $lockedMembership = CommunityMember::whereKey($membership->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedMembership->status === 'pending', 404);

            $lockedMembership->update(['status' => 'rejected']);

            ActivityLogger::record(
                'community.membership.rejected',
                'Permintaan anggota ID '.$lockedMembership->id.' pada komunitas ID '.$community->id.' ditolak.'
            );
        });

        return back()->with('success', 'Permintaan bergabung berhasil ditolak.');
    }

    private function authorizeMembershipAction(Community $community, CommunityMember $membership): void
    {
        abort_unless((int) $community->leader_id === (int) auth()->id(), 403);
        abort_unless((int) $membership->community_id === (int) $community->id, 404);
        abort_unless($membership->status === 'pending', 404);
    }
}
